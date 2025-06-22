<?php

namespace App\Helpers;

use Illuminate\Http\JsonResponse;

class ApiResponse
{
    /**
     * Retorna resposta de sucesso padronizada.
     */
    public static function success(mixed $data = null, int $code = 200, string $message = ''): JsonResponse
    {
        $response = [];

        if (isset($message) && $message != '') {
            $response['message'] = $message;
        }

        $response['data'] = $data;

        return response()->json($response, $code);
    }

    /**
     * Retorna resposta de erro padronizada.
     */
    public static function error(string $message, int $code = 400, mixed $data = null): JsonResponse
    {
        return response()->json([
            'message'   => $message,
            'data'      => $data,
        ], $code);
    }
}
