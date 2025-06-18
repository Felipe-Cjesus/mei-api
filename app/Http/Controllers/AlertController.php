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
    
        if (!$alerts) {
            return ApiResponse::error('Alert not found.', 404);
        }

        return ApiResponse::sucessWithoutMessage($alerts);
    }

    public function markAsRead($id)
    {
        $alert = Alert::where('id', $id)
            ->where('user_id', Auth::id())
            ->firstOrFail();

        if (!$alert) {
            return ApiResponse::error('Alert not found.', 404);
        }

        $alert->update(['read' => true, 'read_at' => now()]);

        return ApiResponse::success('Alert marked as read', $alert);
    }
}
