<?php

namespace App\Http\Controllers;

use App\Models\Invoice;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use App\Helpers\ApiResponse;

class InvoiceController extends Controller
{
    use AuthorizesRequests;
    
    public function index(Request $request)
    {
        $invoices = Invoice::where('user_id', Auth::id())->orderByDesc('issue_date')->get();
        
        if($invoices)
        {
            $invoices = Invoice::paginate(
                page: $request->get('page', 1),
                perPage: $request->get('per_page', 50)
            );
        }

        return ApiResponse::success($invoices);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'number'      => 'required|string|max:255',
            'issue_date'  => 'required|date',
            'value'       => 'required|numeric',
            'description' => 'required|string',
            'nf_url'      => 'nullable|string',
            'file'        => 'nullable|file|mimes:pdf,xml|max:2048',
        ]);

        if ($request->hasFile('file')) {
            $path = $request->file('file')->store('invoices', 'public');
            $validated['nf_url'] = $path;
        }

        $invoice = Invoice::create([
            'user_id' => Auth::id(),
            ...$validated
        ]);

        return ApiResponse::success($invoice, 201, 'Invoice created successfully');
    }

    public function show($id)
    {
        // $invoice = Invoice::where('user_id', Auth::id())->findOrFail($id);
        $invoice = Invoice::findOrFail($id);
        $this->authorize('view', $invoice);

        return ApiResponse::success($invoice);
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

        return ApiResponse::success($invoice);
    }

    public function destroy($id)
    {
        // $invoice = Invoice::where('user_id', Auth::id())->findOrFail($id);
        $invoice = Invoice::findOrFail($id);
        $this->authorize('delete', $invoice);

        $invoice->delete();

        return ApiResponse::success([],204,'Invoice deleted successfully');
    }
}
