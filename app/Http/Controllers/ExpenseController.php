<?php

namespace App\Http\Controllers;

use App\Models\Expense;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use App\Helpers\ApiResponse;

class ExpenseController extends Controller
{
    use AuthorizesRequests;
    
    // Listar todas as despesas do usuário autenticado
    public function index(Request $request)
    {
        $expenses = Expense::where('user_id', Auth::id())->orderByDesc('date')->get();

        if($expenses)
        {
            $expenses = Expense::paginate(
                page: $request->get('page', 1),
                perPage: $request->get('per_page', 50)
            );
        }
        
        return ApiResponse::success($expenses);
    }

    // Criar nova despesa
    public function store(Request $request)
    {
        $validated = $request->validate([
            'description'   => 'required|string|max:255',
            'amount'        => 'required|numeric',
            'date'          => 'required|date',
            'type'          => 'required|in:manual,nota_fiscal',
            'document_path' => 'nullable|string',
            'file'          => 'nullable|file|mimes:pdf,xml,jpg,jpeg,png|max:2048',
        ]);

        if ($request->hasFile('file')) {
            $path = $request->file('file')->store('expenses', 'public');
            $validated['document_path'] = $path;
        }

        $expense = Expense::create([
            'user_id' => Auth::id(),
            ...$validated
        ]);

        return ApiResponse::success($expense, 201, 'Expense created successfully');
    }

    // Mostrar uma despesa específica (do usuário)
    public function show($id)
    {
        $expense = Expense::findOrFail($id);
        $this->authorize('view', $expense);
    
        return ApiResponse::success($expense);
    }

    // Atualizar uma despesa
    public function update(Request $request, $id)
    {
        $expense = Expense::findOrFail($id);
        $this->authorize('update', $expense);

        $validated = $request->validate([
            'description'   => 'sometimes|string|max:255',
            'amount'        => 'sometimes|numeric',
            'date'          => 'sometimes|date',
            'type'          => 'sometimes|in:manual,nota_fiscal',
            'document_path' => 'nullable|string',
        ]);

        $expense->update($validated);

        return ApiResponse::success($expense);
    }

    // Excluir uma despesa
    public function destroy($id)
    {
        $expense = Expense::findOrFail($id);
        $this->authorize('delete', $expense);

        $expense->delete();

        return ApiResponse::success([],204,'Expense deleted successfully');
    }
}