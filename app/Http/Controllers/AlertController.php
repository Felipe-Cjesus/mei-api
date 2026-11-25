<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Alert;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Helpers\ApiResponse;

class AlertController extends Controller
{
    public function index(Request $request)
    {
        $query = Alert::where('user_id', Auth::id());

        // Filtro por enterprise_id
        if ($request->filled('enterprise_id')) {
            $query->where('enterprise_id', $request->input('enterprise_id'));
        }

        // Filtro por leitura
        if ($request->has('read')) {
            $read = $request->boolean('read');
            $query->where('read', $read);
        }
    
        // Filtro por tipo
        if ($request->filled('type')) {
            $query->where('type', $request->input('type'));
        }
    
        $alerts = $query->orderBy('created_at', 'desc')->get();

        return ApiResponse::success($alerts);
    }

    public function markAsRead($id)
    {
        $alert = Alert::where('id', $id)
            ->where('user_id', Auth::id())
            ->firstOrFail();

        $alert->update([
            'read'    => true,
            'read_at' => now()
        ]);

        return ApiResponse::success($alert, 200, 'Alert marked as read.');
    }
}
