<?php

namespace App\Http\Controllers;

use App\Helpers\ApiResponse;
use App\Models\DasPayment;
use App\Models\Expense;
use App\Models\Income;
use App\Models\Invoice;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ReportController extends Controller
{
    public function monthly(Request $request)
    {
        $userId = Auth::id();
        $year = $request->input('year', now()->year);
        $monthFilter = $request->input('month');
        $enterpriseId = $request->input('enterprise_id');

        if (isset($year) && ($year == 0 || $year == null || $year == '')) {
            return ApiResponse::error('Invalid year filter.', 400);
        }

        if (isset($monthFilter) && ($monthFilter > 12 || $monthFilter <= 0)) {
            return ApiResponse::error('Invalid month filter. Must be between 1 and 12', 400);
        }

        // 1. Invoices no ano/mês filtrado
        $invoiceQuery = Invoice::where('user_id', $userId)->whereYear('issue_date', $year);
        if ($enterpriseId) {
            $invoiceQuery->where('enterprise_id', $enterpriseId);
        }
        if ($monthFilter) {
            $invoiceQuery->whereMonth('issue_date', $monthFilter);
        }
        $invoices = $invoiceQuery->get(['issue_date', 'value']);

        // 2. Incomes no ano/mês filtrado
        $incomeQuery = Income::where('user_id', $userId)->whereYear('date', $year);
        if ($enterpriseId) {
            $incomeQuery->where('enterprise_id', $enterpriseId);
        }
        if ($monthFilter) {
            $incomeQuery->whereMonth('date', $monthFilter);
        }
        $incomes = $incomeQuery->get(['date', 'amount', 'received']);

        // 3. Expenses no ano/mês filtrado
        $expenseQuery = Expense::where('user_id', $userId)->whereYear('date', $year);
        if ($enterpriseId) {
            $expenseQuery->where('enterprise_id', $enterpriseId);
        }
        if ($monthFilter) {
            $expenseQuery->whereMonth('date', $monthFilter);
        }
        $expenses = $expenseQuery->get(['date', 'amount']);

        // 4. DasPayments no ano/mês filtrado
        $dasPaymentQuery = DasPayment::where('user_id', $userId)->whereYear('due_date', $year);
        if ($enterpriseId) {
            $dasPaymentQuery->where('enterprise_id', $enterpriseId);
        }
        if ($monthFilter) {
            $dasPaymentQuery->whereMonth('due_date', $monthFilter);
        }
        $dasPayments = $dasPaymentQuery->get(['due_date', 'amount']);

        // Agrupamento em memória por mês ('01' a '12') - 100% agnóstico de banco de dados
        $invoicesByMonth    = $invoices->groupBy(fn ($item) => Carbon::parse($item->issue_date)->format('m'));
        $incomesByMonth     = $incomes->groupBy(fn ($item) => Carbon::parse($item->date)->format('m'));
        $expensesByMonth    = $expenses->groupBy(fn ($item) => Carbon::parse($item->date)->format('m'));
        $dasPaymentsByMonth = $dasPayments->groupBy(fn ($item) => Carbon::parse($item->due_date)->format('m'));

        $months = collect(range(1, 12))->mapWithKeys(function ($m) use (
            $invoicesByMonth,
            $incomesByMonth,
            $expensesByMonth,
            $dasPaymentsByMonth
        ) {
            $monthKey = str_pad($m, 2, '0', STR_PAD_LEFT);

            $monthInvoices = $invoicesByMonth->get($monthKey, collect());
            $monthIncomes  = $incomesByMonth->get($monthKey, collect());
            $monthExpenses = $expensesByMonth->get($monthKey, collect());
            $monthDas      = $dasPaymentsByMonth->get($monthKey, collect());

            $receivedIncomes = $monthIncomes->where('received', true);
            $pendingIncomes  = $monthIncomes->where('received', false);

            $incomeMonthlyTotal  = (float) $receivedIncomes->sum('amount');
            $expenseMonthlyTotal = (float) $monthExpenses->sum('amount');
            $dasMonthlyTotal     = (float) $monthDas->sum('amount');

            return [
                $monthKey => [
                    'month'                           => $monthKey,
                    'invoice_monthly_total'           => (float) $monthInvoices->sum('value'),
                    'invoice_monthly_quantity'        => $monthInvoices->count(),
                    'income_monthly_total'            => $incomeMonthlyTotal,
                    'income_monthly_quantity'         => $receivedIncomes->count(),
                    'income_monthly_total_pending'    => (float) $pendingIncomes->sum('amount'),
                    'income_monthly_quantity_pending' => $pendingIncomes->count(),
                    'expense_monthly_total'           => $expenseMonthlyTotal,
                    'expense_monthly_quantity'        => $monthExpenses->count(),
                    'daspayment_monthly_total'        => $dasMonthlyTotal,
                    'das_monthly_quantity'            => $monthDas->count(),
                    'balance'                         => round($incomeMonthlyTotal - $expenseMonthlyTotal - $dasMonthlyTotal, 2),
                ]
            ];
        });

        if ($monthFilter) {
            $key = str_pad($monthFilter, 2, '0', STR_PAD_LEFT);
            $months = collect([$months->get($key)]);
        } else {
            $months = $months->values();
        }

        return ApiResponse::success([
            'year'   => (int) $year,
            'months' => $months,
            'total'  => [
                'invoice_total'           => $months->sum('invoice_monthly_total'),
                'invoice_quantity'        => $months->sum('invoice_monthly_quantity'),
                'income_total'            => $months->sum('income_monthly_total'),
                'income_quantity'         => $months->sum('income_monthly_quantity'),
                'income_total_pending'    => $months->sum('income_monthly_total_pending'),
                'income_quantity_pending' => $months->sum('income_monthly_quantity_pending'),
                'expense_total'           => $months->sum('expense_monthly_total'),
                'expense_quantity'        => $months->sum('expense_monthly_quantity'),
                'daspayment_total'        => $months->sum('daspayment_monthly_total'),
                'das_quantity'            => $months->sum('das_monthly_quantity'),
                'balance'                 => $months->sum('balance'),
            ],
        ], 200, 'Monthly report successfully generated.');
    }
}
