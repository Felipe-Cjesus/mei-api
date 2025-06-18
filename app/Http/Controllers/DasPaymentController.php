<?php

namespace App\Http\Controllers;

use App\Models\DasPayment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use App\Helpers\ApiResponse;

use Illuminate\Database\Eloquent\ModelNotFoundException;

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

            $payments = $payments->toArray();

            if(isset($payments['links'])) {
                unset($payments['links']);
            }
        }

        if (!$payments) {
            return ApiResponse::error('Nothing found.', 404);
        }

        return ApiResponse::sucessWithoutMessage($payments);
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
            return ApiResponse::error('error.', 400);
        }

        return ApiResponse::sucessWithoutMessage($payment,201);
    }

    public function show($id)
    {
        try 
        {
            // $payment = DasPayment::findOrFail($id);
            $payment = DasPayment::find($id);

            if (!$payment) {
                return ApiResponse::error('Id not found.', 404,);
            }

            $this->authorize('view', $payment);

            return ApiResponse::sucessWithoutMessage($payment);
        }
        catch (ModelNotFoundException $e) {
            return ApiResponse::error($e->getMessage(), 404);
        }
    }

    public function update(Request $request, $id)
    {
        $payment = DasPayment::findOrFail($id);
        
        if (!$payment) {
            return ApiResponse::error('Id not found.', 404);
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

        return ApiResponse::sucessWithoutMessage($payment);
    }

    public function destroy($id)
    {
        $payment = DasPayment::findOrFail($id);
        
        if (!$payment) {
            return ApiResponse::error('Id not found.', 404);
        }

        $this->authorize('delete', $payment);

        $payment->delete();

        return ApiResponse::sucessWithoutMessage([],204);
    }
}
