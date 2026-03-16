<?php

namespace App\Exceptions;

use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Validation\ValidationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Database\QueryException;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\MethodNotAllowedHttpException;
use Throwable;

class Handler extends ExceptionHandler
{
    public function render($request, Throwable $e)
    {          
        // Валидация (422)
        if ($e instanceof ValidationException) {
            return response()->json([
                'success' => false,
                'error' => [
                    'code' => 'VALIDATION_ERROR',
                    'message' => 'Ошибка валидации',
                    'details' => $e->errors()
                ]
            ], 422);
        }

        // Модель не найдена (404)
        if ($e instanceof ModelNotFoundException) {
            return response()->json([
                'success' => false,
                'error' => [
                    'code' => 'NOT_FOUND',
                    'message' => 'Ресурс не найден'
                ]
            ], 404);
        }

        // Метод не разрешён (405)
        if ($e instanceof MethodNotAllowedHttpException) {
            return response()->json([
                'success' => false,
                'error' => [
                    'code' => 'METHOD_NOT_ALLOWED',
                    'message' => 'Метод не поддерживается'
                ]
            ], 405);
        }

        // Ошибка базы данных (500)
        if ($e instanceof QueryException) {
            return response()->json([
                'success' => false,
                'error' => [
                    'code' => 'DATABASE_ERROR',
                    'message' => 'Ошибка базы данных'
                ]
            ], 500);
        }

        // Любые другие исключения
        $statusCode = method_exists($e, 'getStatusCode') ? $e->getStatusCode() : 500;
        $message = $e->getMessage() ?: 'Ошибка';

        return response()->json([
            'success' => false,
            'error' => [
                'code' => 'ERROR',
                'message' => $message
            ]
        ], $statusCode);
    }
}