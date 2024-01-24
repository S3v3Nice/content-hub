<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Support\MessageBag;

trait ApiJsonResponse
{
    private function successJsonResponse(string $message = ''): JsonResponse
    {
        $response = [
            'success' => true,
        ];

        if (mb_strlen($message) !== 0) {
            $response['message'] = $message;
        }

        return response()->json($response);
    }

    private function errorJsonResponse(string $message = '', array|MessageBag $errors = []): JsonResponse
    {
        $response = [
            'success' => false,
        ];

        if (mb_strlen($message) !== 0) {
            $response['message'] = $message;
        }
        if (count($errors) !== 0) {
            $response['errors'] = $errors;
        }

        return response()->json($response);
    }
}