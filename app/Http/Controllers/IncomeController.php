<?php

namespace App\Http\Controllers;

use App\Models\Income;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use App\Helpers\ApiResponse;
use Illuminate\Validation\Rule;

class IncomeController extends Controller
{
    use AuthorizesRequests;

    public function index(Request $request)
    {
        $perPage = $request->input('perPage', 50);
        $page = $request->input('page', 1);
        $year = (int) $request->input('year', now()->year);
        $date = $request->input('date');
        $description = $request->input('description');
        $enterpriseId = $request->input('enterprise_id');

        if(isset($year) && ($year == 0 || $year == null || $year == '')) {
            return ApiResponse::error('Invalid year filter.', 400);
        }

        if(isset($date) && ($date == 0 || $date == null || $date == '')) {
            return ApiResponse::error('Invalid date filter.', 400);
        }

        $incomes = Income::where('user_id', Auth::id());

        if ($enterpriseId) {
            $incomes->where('enterprise_id', $enterpriseId);
        }

        if ($request->has('year')) {
            $incomes->whereYear('date', $year);
        }

        if ($request->has('date')) {
            $incomes->whereDate('date', $date);
        }

        if ($request->has('description')) {
            $incomes->where('description', 'like', '%' . $description . '%');
        }

        $incomes->orderByDesc('date');

        $incomes = $incomes->paginate(
            perPage: $perPage,
            page: $request->input('page', 1)
        );
        
        return ApiResponse::success($incomes);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'enterprise_id' => [
                'required',
                Rule::exists('enterprises', 'id')->where(fn ($query) => $query->where('user_id', Auth::id())),
            ],
            'description'   => 'required|string|max:255',
            'amount'        => 'required|numeric',
            'date'          => 'required|date',
            'received'      => 'required|boolean',
        ]);

        $income = Income::create([
            'user_id' => Auth::id(),
            ...$validated
        ]);

        return ApiResponse::success($income, 201, 'Income created successfully');
    }

    public function show($id)
    {
        $income = Income::findOrFail($id);
        $this->authorize('view', $income);

        return ApiResponse::success($income);
    }

    // Atualizar
    public function update(Request $request, $id)
    {
        $income = Income::findOrFail($id);
        $this->authorize('update', $income);

        $validated = $request->validate([
            'enterprise_id' => [
                'sometimes',
                'required',
                Rule::exists('enterprises', 'id')->where(fn ($query) => $query->where('user_id', Auth::id())),
            ],
            'description'   => 'sometimes|string|max:255',
            'amount'        => 'sometimes|numeric',
            'date'          => 'sometimes|date',
            'received'      => 'sometimes|boolean',
        ]);

        $income->update($validated);

        return ApiResponse::success($income);
    }

    // Excluir
    public function destroy($id)
    {
        $income = Income::findOrFail($id);
        $this->authorize('delete', $income);

        $income->delete();

        return ApiResponse::success([], 204, 'Income deleted successfully');
    }
}
