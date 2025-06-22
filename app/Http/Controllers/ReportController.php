<?php

namespace App\Http\Controllers;

use App\Helpers\ApiResponse;
use App\Models\Expense;
use App\Models\Income;
use App\Models\Invoice;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ReportController extends Controller
{
    public function monthly(Request $request)
    {
        $userId = Auth::id();
        $year = $request->input('year', now()->year);
        $monthFilter = $request->input('month');

        if(isset($year) && ($year == 0 || $year == null || $year == '')) {
            return ApiResponse::error('Invalid year filter.', 400);
        }

        if(isset($monthFilter) && ($monthFilter > 12 || $monthFilter <= 0)) {
            return ApiResponse::error('Invalid month filter. Must be between 1 and 12', 400);
        }

        // Inicializa todos os 12 meses com valores zerados
        $months = collect(range(1, 12))->map(function ($month) {
            return [
                'month' => str_pad($month, 2, '0', STR_PAD_LEFT),
                'invoice_total' => 0.00,
                'income_total' => 0.00,
                'expense_total' => 0.00,
            ];
        })->keyBy('month');

        // Total de invoices por mês
        $invoiceQuery = Invoice::where('user_id', $userId)->whereYear('issue_date', $year);
        if ($monthFilter) {
            $invoiceQuery->whereMonth('issue_date', $monthFilter);
        }
        $invoiceQuery->selectRaw('MONTH(issue_date) as month, SUM(value) as total')
            ->groupByRaw('MONTH(issue_date)')
            ->get()
            ->each(function ($row) use (&$months) {
                $monthKey = str_pad($row->month, 2, '0', STR_PAD_LEFT);
                $months->put($monthKey, array_merge($months[$monthKey], [
                    'invoice_total' => (float) $row->total,
                ]));
            });

        // Total de incomes por mês
        $incomeQuery = Income::where('user_id', $userId)->whereYear('date', $year);
        if ($monthFilter) {
            $incomeQuery->whereMonth('date', $monthFilter);
        }
        $incomeQuery->selectRaw('MONTH(date) as month, SUM(amount) as total')
            ->groupByRaw('MONTH(date)')
            ->get()
            ->each(function ($row) use (&$months) {
                $monthKey = str_pad($row->month, 2, '0', STR_PAD_LEFT);
                $months->put($monthKey, array_merge($months[$monthKey], [
                    'income_total' => (float) $row->total,
                ]));
            });

        // Total de expenses por mês
        $expenseQuery = Expense::where('user_id', $userId)->whereYear('date', $year);
        if ($monthFilter) {
            $expenseQuery->whereMonth('date', $monthFilter);
        }
        $expenseQuery->selectRaw('MONTH(date) as month, SUM(amount) as total')
            ->groupByRaw('MONTH(date)')
            ->get()
            ->each(function ($row) use (&$months) {
                $monthKey = str_pad($row->month, 2, '0', STR_PAD_LEFT);
                $months->put($monthKey, array_merge($months[$monthKey], [
                    'expense_total' => (float) $row->total,
                ]));
            });

        if ($monthFilter) {
            $key = str_pad($monthFilter, 2, '0', STR_PAD_LEFT);
            $months = collect([$months->get($key)]);
        } else {
            $months = $months->values();
        }

        // Calcula o balanço para cada mês (receita - despesa)
        $months = $months->map(function ($item) {
            $item['balance'] = round($item['income_total'] - $item['expense_total'], 2);
            return $item;
        });

        return ApiResponse::success([
            'year' => (int) $year,
            'months' => $months,
        ],200,'Monthly report successfully generated.');
    }
}
