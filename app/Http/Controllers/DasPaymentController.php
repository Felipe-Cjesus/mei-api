<?php

namespace App\Http\Controllers;

use App\Models\DasPayment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class DasPaymentController extends Controller
{
    use AuthorizesRequests;

    public function index(Request $request)
    {
        $payments = DasPayment::where('user_id', Auth::id())->orderByDesc('due_date')->get();
        if($payments)
        {
            $payments = DasPayment::paginate(
                page: $request->get('page', 1),
                perPage: $request->get('per_page', 50)
            );
        }

        if (!$payments) {
            return response()->json([
                'message' => 'Nothing found.',
                'data'    => null
            ], 404);
        }

        return response()->json([
            'data'    => $payments
        ]);
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

        if (!$payment) {
            return response()->json([
                'message' => 'error.',
                'data'    => null
            ], 400);
        }

        return response()->json([
            'data'    => $payment
        ], 201);
    }

    public function show($id)
    {
        $payment = DasPayment::findOrFail($id);
        
        if (!$payment) {
            return response()->json([
                'message' => 'Document not found.',
                'data'    => null
            ], 404);
        }

        $this->authorize('view', $payment);

        return response()->json([
            'data'    => $payment
        ]);
    }

    public function update(Request $request, $id)
    {
        $payment = DasPayment::findOrFail($id);
        
        if (!$payment) {
            return response()->json([
                'message' => 'Not found.',
                'data'    => null
            ], 404);
        }

        $this->authorize('update', $payment);

        $validated = $request->validate([
            'reference' => 'sometimes|string|max:20',
            'due_date' => 'sometimes|date',
            'payment_date' => 'nullable|date',
            'amount' => 'sometimes|numeric',
            'status' => 'sometimes|in:paid,pending,overdue,exempt',
        ]);

        $payment->update($validated);

        return response()->json([
            'data'    => $payment
        ]);
    }

    public function destroy($id)
    {
        $payment = DasPayment::findOrFail($id);
        
        if (!$payment) {
            return response()->json([
                'message' => 'Document not found.',
                'data'    => null
            ], 404);
        }

        $this->authorize('delete', $payment);

        $payment->delete();

        return response()->json([], 204);
    }
}
