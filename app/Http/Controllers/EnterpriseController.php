<?php

namespace App\Http\Controllers;

use App\Models\Enterprise;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use App\Helpers\ApiResponse;

class EnterpriseController extends Controller
{
    use AuthorizesRequests;

    public function index()
    {
        $enterprise = Enterprise::where('user_id', Auth::id())->first();

        if (!$enterprise) {
            return ApiResponse::error('Enterprise not found', 404);
        }

        return ApiResponse::success($enterprise);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'company_name'  => 'required|string|max:255',
            'company_id'    => 'required|string|min:14|max:14',
            'state'         => 'nullable|string|max:50',
            'city'          => 'nullable|string|max:100',
            'address'       => 'nullable|string|max:255',
            'number'        => 'nullable|string|max:10',
            'contact'       => 'nullable|string|max:100',
            'social_media'  => 'nullable|string|max:100',
        ]);

        $enterprise = Enterprise::create([
            'user_id' => Auth::id(),
            ...$validated
        ]);

        return ApiResponse::success($enterprise, 201, 'Enterprise created successfully');
    }

    public function update(Request $request, $id)
    {
        $enterprise = Enterprise::findOrFail($id);
        $this->authorize('update', $enterprise);

        $validated = $request->validate([
            'company_name'  => 'sometimes|string|max:255',
            'company_id'    => 'sometimes|string|min:14|max:14',
            'state'         => 'sometimes|string|max:50',
            'city'          => 'sometimes|string|max:100',
            'address'       => 'sometimes|string|max:255',
            'number'        => 'sometimes|string|max:10',
            'contact'       => 'sometimes|string|max:100',
            'social_media'  => 'sometimes|string|max:100',
        ]);

        $enterprise->update($validated);

        return ApiResponse::success($enterprise);
    }

    public function show($id)
    {
        $enterprise = Enterprise::findOrFail($id);
        $this->authorize('view', $enterprise);

        return ApiResponse::success($enterprise);
    }

    public function destroy($id)
    {
        $enterprise = Enterprise::findOrFail($id);

        if (!$enterprise) {
            return ApiResponse::error('Enterprise not found', 404);
        }

        $this->authorize('delete', $enterprise);

        $enterprise->delete();

        return ApiResponse::success([],204,'Enterprise deleted');
    }
}