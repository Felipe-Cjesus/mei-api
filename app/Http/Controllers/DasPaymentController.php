<?php

namespace App\Http\Controllers;

use App\Models\DasPayment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use App\Helpers\ApiResponse;
use Illuminate\Validation\Rule;

class DasPaymentController extends Controller
{
    use AuthorizesRequests;

    public function index(Request $request)
    {
        $perPage = $request->input('perPage', 50);
        $page = $request->input('page', 1);
        $year = $request->input('year', now()->year);
        $status = $request->input('status');
        $reference = $request->input('reference');
        $enterpriseId = $request->input('enterprise_id');

        if(isset($year) && ($year == 0 || $year == null || $year == '')) {
            return ApiResponse::error('Invalid year filter.', 400);
        }

        $payments = DasPayment::where('user_id', Auth::id());

        if ($enterpriseId) {
            $payments->where('enterprise_id', $enterpriseId);
        }

        if ($request->has('year')) {
            $payments->whereYear('due_date', $year);
        }

        if ($request->has('status')) {
            $payments->where('status', $status);
        }

        if ($request->has('reference')) {
            $payments->where('reference', 'like', '%' . $reference . '%');
        }

        $payments->orderByDesc('due_date');

        $payments = $payments->paginate(
            perPage: $perPage,
            page: $request->input('page', 1)
        );

        $payments = $payments->toArray();

        if(isset($payments['links'])) {
            unset($payments['links']);
        }

        return ApiResponse::success($payments);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'enterprise_id' => [
                'nullable',
                Rule::exists('enterprises', 'id')->where(fn ($query) => $query->where('user_id', Auth::id())),
            ],
            'reference'     => 'required|string|max:20', // Ex: 05/2025
            'due_date'      => 'required|date',
            'payment_date'  => 'nullable|date',
            'amount'        => 'required|numeric',
            'status'        => 'required|in:paid,pending,overdue,exempt',
        ]);

        $payment = DasPayment::create([
            'user_id' => Auth::id(),
            ...$validated
        ]);

        if (!$payment) {
            return ApiResponse::error('error.', 400);
        }

        return ApiResponse::success($payment, 201, "DasPayment created successfully.");
    }

    public function show($id)
    {
        $payment = DasPayment::findOrFail($id);
        $this->authorize('view', $payment);

        return ApiResponse::success($payment);
    }

    public function update(Request $request, $id)
    {
        $payment = DasPayment::findOrFail($id);
        $this->authorize('update', $payment);

        $validated = $request->validate([
            'enterprise_id' => [
                'sometimes',
                'nullable',
                Rule::exists('enterprises', 'id')->where(fn ($query) => $query->where('user_id', Auth::id())),
            ],
            'reference'     => 'sometimes|string|max:20',
            'due_date'      => 'sometimes|date',
            'payment_date'  => 'nullable|date',
            'amount'        => 'sometimes|numeric',
            'status'        => 'sometimes|in:paid,pending,overdue,exempt',
        ]);

        $payment->update($validated);

        return ApiResponse::success($payment);
    }

    public function destroy($id)
    {
        $payment = DasPayment::findOrFail($id);
        $this->authorize('delete', $payment);

        $payment->delete();

        return ApiResponse::success([], 204, 'DasPayment deleted successfully.');
    }
}
