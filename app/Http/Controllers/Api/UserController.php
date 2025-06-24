<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class UserController extends Controller
{
    /**
     * Listagem dos usuários
     * @return \Illuminate\Http\JsonResponse
     * @author  Felipe Costa de Jesus
     * @copyright (c) 13/06/2025
     */
    public function index() : JsonResponse
    {
        $users = User::orderBy('id', 'DESC')->paginate(15);

        return response()->json([
            'status' => true,
            'users'  => $users,
        ], 200);

        // return response()->json([
        //     'status' => "OK",
        // ], 200);
    }
}
