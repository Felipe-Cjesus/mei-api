<?php

namespace App\Helpers;

use Illuminate\Http\JsonResponse;

class ApiResponse
{
    /**
     * Retorna resposta de sucesso padronizada.
     */
    public static function success(string $message, mixed $data = null, int $code = 200): JsonResponse
    {
        return response()->json([
            'message' => $message,
            'data' => $data,
        ], $code);
    }

    /**
     * Retorna resposta de erro padronizada.
     */
    public static function error(string $message, int $code = 400, mixed $data = null): JsonResponse
    {
        return response()->json([
            'message' => $message,
            'data' => $data,
        ], $code);
    }

    /**
     * Retorna resposta sem mensagem.
     */
    public static function sucessWithoutMessage(mixed $data = [], int $code = 200): JsonResponse
    {
        return response()->json([
            'data' => $data,
        ], $code);
    }
}
