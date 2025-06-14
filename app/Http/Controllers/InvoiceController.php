<?php

namespace App\Http\Controllers;

use App\Models\Invoice;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class InvoiceController extends Controller
{
    use AuthorizesRequests;
    
    public function index()
    {
        return Invoice::where('user_id', Auth::id())->get();
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'number'      => 'required|string|max:255',
            'issue_date'  => 'required|date',
            'value'       => 'required|numeric',
            'description' => 'required|string',
            'nf_url'      => 'nullable|string',
        ]);

        $nota = Invoice::create([
            'user_id' => Auth::id(),
            ...$validated
        ]);

        return response()->json($nota, 201);
    }

    public function show($id)
    {
        // $nota = Invoice::where('user_id', Auth::id())->findOrFail($id);
        $nota = Invoice::findOrFail($id);
        $this->authorize('view', $nota);

        return response()->json($nota);
    }

    public function update(Request $request, $id)
    {
        // $nota = Invoice::where('user_id', Auth::id())->findOrFail($id);
        $nota = Invoice::findOrFail($id);
        $this->authorize('update', $nota);

        $validated = $request->validate([
            'number'      => 'sometimes|string|max:255',
            'issue_date'  => 'sometimes|date',
            'value'       => 'sometimes|numeric',
            'description' => 'sometimes|string',
            'nf_url'      => 'nullable|string',
        ]);

        $nota->update($validated);

        return response()->json($nota);
    }

    public function destroy($id)
    {
        // $nota = Invoice::where('user_id', Auth::id())->findOrFail($id);
        $nota = Invoice::findOrFail($id);
        $this->authorize('delete', $nota);

        $nota->delete();

        return response()->json(['message' => 'Nota fiscal excluída com sucesso']);
    }
}
