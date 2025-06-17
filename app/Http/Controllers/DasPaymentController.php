<?php

namespace App\Http\Controllers;

use App\Models\DasPayment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class DasPaymentController extends Controller
{
    use AuthorizesRequests;

    public function index()
    {
        $payments = DasPayment::where('user_id', Auth::id())->orderByDesc('due_date')->get();
        return response()->json($payments);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'reference' => 'required|string|max:20', // Ex: 05/2025
            'due_date' => 'required|date',
            'payment_date' => 'nullable|date',
            'amount' => 'required|numeric',
            'status' => 'required|in:paid,pending,overdue,exempt',
        ]);

        $validated['user_id'] = Auth::id();

        $payment = DasPayment::create($validated);

        return response()->json($payment, 201);
    }

    public function show($id)
    {
        $payment = DasPayment::findOrFail($id);
        $this->authorize('view', $payment);

        return response()->json($payment);
    }

    public function update(Request $request, $id)
    {
        $payment = DasPayment::findOrFail($id);
        $this->authorize('update', $payment);

        $validated = $request->validate([
            'reference' => 'sometimes|string|max:20',
            'due_date' => 'sometimes|date',
            'payment_date' => 'nullable|date',
            'amount' => 'sometimes|numeric',
            'status' => 'sometimes|in:paid,pending,overdue,exempt',
        ]);

        $payment->update($validated);

        return response()->json($payment);
    }

    public function destroy($id)
    {
        $payment = DasPayment::findOrFail($id);
        $this->authorize('delete', $payment);

        $payment->delete();

        return response()->json(['message' => 'DAS payment deleted successfully']);
    }
}
