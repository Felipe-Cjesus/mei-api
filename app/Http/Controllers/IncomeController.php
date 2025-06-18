<?php

namespace App\Http\Controllers;

use App\Models\Income;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class IncomeController extends Controller
{
    use AuthorizesRequests;

    public function index(Request $request)
    {
        $incomes = Income::where('user_id', Auth::id())->orderByDesc('date')->get();
        
        if($incomes) {
            $incomes = Income::paginate(
                page: $request->get('page', 1),
                perPage: $request->get('per_page', 50)
            );
        }
        
        return response()->json($incomes);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'description' => 'required|string|max:255',
            'amount'      => 'required|numeric',
            'date'        => 'required|date',
            'received'    => 'required|boolean',
        ]);

        $income = Income::create([
            'user_id' => Auth::id(),
            ...$validated
        ]);

        return response()->json($income, 201);
    }

    public function show($id)
    {
        $income = Income::findOrFail($id);
        $this->authorize('view', $income);

        return response()->json($income);
    }

    // Atualizar
    public function update(Request $request, $id)
    {
        $income = Income::findOrFail($id);
        $this->authorize('update', $income);

        $validated = $request->validate([
            'description' => 'sometimes|string|max:255',
            'amount' => 'sometimes|numeric',
            'date' => 'sometimes|date',
            'type' => 'sometimes|in:manual,nota_fiscal',
            'document_path' => 'nullable|string',
        ]);

        $income->update($validated);

        return response()->json($income);
    }

    // Excluir
    public function destroy($id)
    {
        $income = Income::findOrFail($id);
        $this->authorize('delete', $income);

        $income->delete();

        return response()->json(['message' => 'Income deleted successfully']);
    }
}
