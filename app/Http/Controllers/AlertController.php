<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Alert;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AlertController extends Controller
{
    public function index()
    {
        $alerts = Alert::where('user_id', Auth::id())
            ->orderByDesc('created_at')
            ->get();

        return response()->json($alerts);
    }

    public function markAsRead($id)
    {
        $alert = Alert::where('id', $id)
            ->where('user_id', Auth::id())
            ->firstOrFail();

        $alert->update(['read_at' => now()]);

        return response()->json(['message' => 'Alert marked as read']);
    }
}
