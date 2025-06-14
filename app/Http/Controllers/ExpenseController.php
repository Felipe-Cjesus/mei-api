<?php

namespace App\Http\Controllers;

use App\Models\Expense;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class ExpenseController extends Controller
{
    use AuthorizesRequests;
    
    // Listar todas as despesas do usuário autenticado
    public function index()
    {
        $expenses = Expense::where('user_id', Auth::id())->orderByDesc('date')->get();
        return response()->json($expenses);
    }

    // Criar nova despesa
    public function store(Request $request)
    {
        $validated = $request->validate([
            'description' => 'required|string|max:255',
            'amount' => 'required|numeric',
            'date' => 'required|date',
            'type' => 'required|in:manual,nota_fiscal',
            'document_path' => 'nullable|string',
        ]);

        $validated['user_id'] = Auth::id();

        $expense = Expense::create($validated);

        return response()->json($expense, 201);
    }

    // Mostrar uma despesa específica (do usuário)
    public function show($id)
    {
        $expense = Expense::findOrFail($id);
        $this->authorize('view', $expense);
    
        return response()->json($expense);
    }

    // Atualizar uma despesa
    public function update(Request $request, $id)
    {
        $expense = Expense::findOrFail($id);
        $this->authorize('update', $expense);

        $validated = $request->validate([
            'description' => 'sometimes|string|max:255',
            'amount' => 'sometimes|numeric',
            'date' => 'sometimes|date',
            'type' => 'sometimes|in:manual,nota_fiscal',
            'document_path' => 'nullable|string',
        ]);

        $expense->update($validated);

        return response()->json($expense);
    }

    // Excluir uma despesa
    public function destroy($id)
    {
        $expense = Expense::findOrFail($id);
        $this->authorize('delete', $expense);

        $expense->delete();

        return response()->json(['message' => 'Expense deleted successfully']);
    }
}