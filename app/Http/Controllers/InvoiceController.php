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
        $invoices = Invoice::where('user_id', Auth::id())->orderByDesc('date')->get();
        return response()->json($invoices);
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

        $invoice = Invoice::create([
            'user_id' => Auth::id(),
            ...$validated
        ]);

        return response()->json($invoice, 201);
    }

    public function show($id)
    {
        // $invoice = Invoice::where('user_id', Auth::id())->findOrFail($id);
        $invoice = Invoice::findOrFail($id);
        $this->authorize('view', $invoice);

        return response()->json($invoice);
    }

    public function update(Request $request, $id)
    {
        // $invoice = Invoice::where('user_id', Auth::id())->findOrFail($id);
        $invoice = Invoice::findOrFail($id);
        $this->authorize('update', $invoice);

        $validated = $request->validate([
            'number'      => 'sometimes|string|max:255',
            'issue_date'  => 'sometimes|date',
            'value'       => 'sometimes|numeric',
            'description' => 'sometimes|string',
            'nf_url'      => 'nullable|string',
        ]);

        $invoice->update($validated);

        return response()->json($invoice);
    }

    public function destroy($id)
    {
        // $invoice = Invoice::where('user_id', Auth::id())->findOrFail($id);
        $invoice = Invoice::findOrFail($id);
        $this->authorize('delete', $invoice);

        $invoice->delete();

        return response()->json(['message' => 'Invoice deleted successfully']);
    }
}
