<?php

namespace App\Http\Controllers;

use App\Helpers\ApiResponse;
use App\Models\DasPayment;
use App\Models\Expense;
use App\Models\Income;
use App\Models\Invoice;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class ReportController extends Controller
{
    public function monthly(Request $request)
    {
        $userId = Auth::id();
        $year = $request->input('year', now()->year);
        $monthFilter = $request->input('month');
        $enterpriseId = $request->input('enterprise_id');

        if(isset($year) && ($year == 0 || $year == null || $year == '')) {
            return ApiResponse::error('Invalid year filter.', 400);
        }

        if(isset($monthFilter) && ($monthFilter > 12 || $monthFilter <= 0)) {
            return ApiResponse::error('Invalid month filter. Must be between 1 and 12', 400);
        }

        $monthExpr = function (string $column): string {
            return DB::connection()->getDriverName() === 'sqlite'
                ? "CAST(strftime('%m', {$column}) AS INTEGER)"
                : "MONTH({$column})";
        };

        // Inicializa todos os 12 meses com valores zerados
        $months = collect(range(1, 12))->map(function ($month) {
            return [
                'month'                             => str_pad($month, 2, '0', STR_PAD_LEFT),
                'invoice_monthly_total'             => 0.00,
                'invoice_monthly_quantity'          => 0,
                'income_monthly_total'              => 0.00,
                'income_monthly_quantity'           => 0,
                'income_monthly_total_pending'      => 0.00,
                'income_monthly_quantity_pending'   => 0,
                'expense_monthly_total'             => 0.00,
                'expense_monthly_quantity'          => 0,
                'daspayment_monthly_total'          => 0.00,
                'das_monthly_quantity'              => 0
            ];
        })->keyBy('month');

        // Total de invoices por mês
        $invoiceQuery = Invoice::where('user_id', $userId)->whereYear('issue_date', $year);
        if ($enterpriseId) {
            $invoiceQuery->where('enterprise_id', $enterpriseId);
        }
        if ($monthFilter) {
            $invoiceQuery->whereMonth('issue_date', $monthFilter);
        }
        $invoiceExpr = $monthExpr('issue_date');
        $invoiceQuery->selectRaw("{$invoiceExpr} as month, SUM(value) as total")
            ->groupByRaw($invoiceExpr)
            ->get()
            ->each(function ($row) use (&$months) {
                $monthKey = str_pad($row->month, 2, '0', STR_PAD_LEFT);
                $months->put($monthKey, array_merge($months[$monthKey], [
                    'invoice_monthly_total' => (float) $row->total,
                ]));
            });

        $invoiceQuantity = Invoice::where('user_id', $userId)->whereYear('issue_date', $year);
        if ($enterpriseId) {
            $invoiceQuantity->where('enterprise_id', $enterpriseId);
        }
        if ($monthFilter) {
            $invoiceQuantity->whereMonth('issue_date', $monthFilter);
        }
        $invoiceQuantity->selectRaw("{$invoiceExpr} as month, COUNT(*) as total")
            ->groupByRaw($invoiceExpr)
            ->get()
            ->each(function ($row) use (&$months) {
                $monthKey = str_pad($row->month, 2, '0', STR_PAD_LEFT);
                $months->put($monthKey, array_merge($months[$monthKey], [
                    'invoice_monthly_quantity' => (int) $row->total,
                ]));
            });

        // Total de incomes recebidos por mês
        $incomeDateExpr = $monthExpr('date');
        $incomeQuery = Income::where('user_id', $userId)
                             ->whereYear('date', $year)
                             ->where('received', true);
        if ($enterpriseId) {
            $incomeQuery->where('enterprise_id', $enterpriseId);
        }
        if ($monthFilter) {
            $incomeQuery->whereMonth('date', $monthFilter);
        }
        $incomeQuery->selectRaw("{$incomeDateExpr} as month, SUM(amount) as total")
            ->groupByRaw($incomeDateExpr)
            ->get()
            ->each(function ($row) use (&$months) {
                $monthKey = str_pad($row->month, 2, '0', STR_PAD_LEFT);
                $months->put($monthKey, array_merge($months[$monthKey], [
                    'income_monthly_total' => (float) $row->total,
                ]));
            });

        $incomeQuantity = Income::where('user_id', $userId)
                                ->whereYear('date', $year)
                                ->where('received', true);
        if ($enterpriseId) {
            $incomeQuantity->where('enterprise_id', $enterpriseId);
        }
        if ($monthFilter) {
            $incomeQuantity->whereMonth('date', $monthFilter);
        }
        $incomeQuantity->selectRaw("{$incomeDateExpr} as month, COUNT(*) as total")
            ->groupByRaw($incomeDateExpr)
            ->get()
            ->each(function ($row) use (&$months) {
                $monthKey = str_pad($row->month, 2, '0', STR_PAD_LEFT);
                $months->put($monthKey, array_merge($months[$monthKey], [
                    'income_monthly_quantity' => (int) $row->total,
                ]));
            });

        // Total de incomes pendentes por mês
        $incomeQuery = Income::where('user_id', $userId)
                             ->whereYear('date', $year)
                             ->where('received', false);
        if ($enterpriseId) {
            $incomeQuery->where('enterprise_id', $enterpriseId);
        }
        if ($monthFilter) {
            $incomeQuery->whereMonth('date', $monthFilter);
        }
        $incomeQuery->selectRaw("{$incomeDateExpr} as month, SUM(amount) as total")
            ->groupByRaw($incomeDateExpr)
            ->get()
            ->each(function ($row) use (&$months) {
                $monthKey = str_pad($row->month, 2, '0', STR_PAD_LEFT);
                $months->put($monthKey, array_merge($months[$monthKey], [
                    'income_monthly_total_pending' => (float) $row->total,
                ]));
            });

        $incomeQuantity = Income::where('user_id', $userId)
                                ->whereYear('date', $year)
                                ->where('received', false);
        if ($enterpriseId) {
            $incomeQuantity->where('enterprise_id', $enterpriseId);
        }
        if ($monthFilter) {
            $incomeQuantity->whereMonth('date', $monthFilter);
        }
        $incomeQuantity->selectRaw("{$incomeDateExpr} as month, COUNT(*) as total")
            ->groupByRaw($incomeDateExpr)
            ->get()
            ->each(function ($row) use (&$months) {
                $monthKey = str_pad($row->month, 2, '0', STR_PAD_LEFT);
                $months->put($monthKey, array_merge($months[$monthKey], [
                    'income_monthly_quantity_pending' => (int) $row->total,
                ]));
            });

        // Total de expenses por mês
        $expenseDateExpr = $monthExpr('date');
        $expenseQuery = Expense::where('user_id', $userId)->whereYear('date', $year);
        if ($enterpriseId) {
            $expenseQuery->where('enterprise_id', $enterpriseId);
        }
        if ($monthFilter) {
            $expenseQuery->whereMonth('date', $monthFilter);
        }
        $expenseQuery->selectRaw("{$expenseDateExpr} as month, SUM(amount) as total")
            ->groupByRaw($expenseDateExpr)
            ->get()
            ->each(function ($row) use (&$months) {
                $monthKey = str_pad($row->month, 2, '0', STR_PAD_LEFT);
                $months->put($monthKey, array_merge($months[$monthKey], [
                    'expense_monthly_total' => (float) $row->total,
                ]));
            });

        $expenseQuantity = Expense::where('user_id', $userId)->whereYear('date', $year);
        if ($enterpriseId) {
            $expenseQuantity->where('enterprise_id', $enterpriseId);
        }
        if ($monthFilter) {
            $expenseQuantity->whereMonth('date', $monthFilter);
        }
        $expenseQuantity->selectRaw("{$expenseDateExpr} as month, COUNT(*) as total")
            ->groupByRaw($expenseDateExpr)
            ->get()
            ->each(function ($row) use (&$months) {
                $monthKey = str_pad($row->month, 2, '0', STR_PAD_LEFT);
                $months->put($monthKey, array_merge($months[$monthKey], [
                    'expense_monthly_quantity' => (int) $row->total,
                ]));
            });

        // Total de DAS por mês
        $dasDateExpr = $monthExpr('due_date');
        $dasPaymentQuery = DasPayment::where('user_id', $userId)->whereYear('due_date', $year);
        if ($enterpriseId) {
            $dasPaymentQuery->where('enterprise_id', $enterpriseId);
        }
        if ($monthFilter) {
            $dasPaymentQuery->whereMonth('due_date', $monthFilter);
        }
        $dasPaymentQuery->selectRaw("{$dasDateExpr} as month, SUM(amount) as total")
            ->groupByRaw($dasDateExpr)
            ->get()
            ->each(function ($row) use (&$months) {
                $monthKey = str_pad($row->month, 2, '0', STR_PAD_LEFT);
                $months->put($monthKey, array_merge($months[$monthKey], [
                    'daspayment_monthly_total' => (float) $row->total,
                ]));
            });
            
        $dasQuantityQuery = DasPayment::where('user_id', $userId)->whereYear('due_date', $year);
        if ($enterpriseId) {
            $dasQuantityQuery->where('enterprise_id', $enterpriseId);
        }
        if ($monthFilter) {
            $dasQuantityQuery->whereMonth('due_date', $monthFilter);
        }
        $dasQuantityQuery->selectRaw("{$dasDateExpr} as month, COUNT(id) as total")
            ->groupByRaw($dasDateExpr)
            ->get()
            ->each(function ($row) use (&$months) {
                $monthKey = str_pad($row->month, 2, '0', STR_PAD_LEFT);
                $months->put($monthKey, array_merge($months[$monthKey], [
                    'das_monthly_quantity' => (int) $row->total,
                ]));
            });

        if ($monthFilter) {
            $key = str_pad($monthFilter, 2, '0', STR_PAD_LEFT);
            $months = collect([$months->get($key)]);
        } else {
            $months = $months->values();
        }

        // Calcula o balanço para cada mês (receita - despesa - DAS)
        $months = $months->map(function ($item) {
            $item['balance'] = round($item['income_monthly_total'] - $item['expense_monthly_total'] - $item['daspayment_monthly_total'], 2);
            return $item;
        });

        return ApiResponse::success([
            'year'   => (int) $year,
            'months' => $months,
            'total'  => [
                'invoice_total'             => $months->sum('invoice_monthly_total'),
                'invoice_quantity'          => $months->sum('invoice_monthly_quantity'),
                'income_total'              => $months->sum('income_monthly_total'),
                'income_quantity'           => $months->sum('income_monthly_quantity'),
                'income_total_pending'      => $months->sum('income_monthly_total_pending'),
                'income_quantity_pending'   => $months->sum('income_monthly_quantity_pending'),
                'expense_total'             => $months->sum('expense_monthly_total'),
                'expense_quantity'          => $months->sum('expense_monthly_quantity'),
                'daspayment_total'          => $months->sum('daspayment_monthly_total'),
                'das_quantity'              => $months->sum('das_monthly_quantity'),
                'balance'                   => $months->sum('balance')
            ],
        ], 200, 'Monthly report successfully generated.');
    }
}
