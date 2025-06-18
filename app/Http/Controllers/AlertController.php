<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Alert;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

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
            return response()->json([
                'message' => 'Não foi possível encontrar o alerta.',
                'data'    => null
            ], 404);
        }

        return response()->json([
            'data' => $alerts
        ]);
    }

    public function markAsRead($id)
    {
        $alert = Alert::where('id', $id)
            ->where('user_id', Auth::id())
            ->firstOrFail();

        if (!$alert) {
            return response()->json([
                'message' => 'Não foi possível encontrar o alerta.',
                'data'    => null
            ], 404);
        }

        $alert->update(['read' => true, 'read_at' => now()]);

        // return response()->json(['message' => 'Alert marked as read']);
        return response()->json([
            'message' => 'Alerta marcado como lido',
            'data'    => $alert
        ]);
    }
}
