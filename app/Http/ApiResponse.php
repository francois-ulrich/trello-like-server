<?php

namespace App\Http;

use Illuminate\Http\JsonResponse;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\App;
use Throwable;

// https://stackoverflow.com/questions/12806386/is-there-any-standard-for-json-api-response-format

class ApiResponse
{
    public static function success( mixed $data = null, ?string $message = null, int $status = 200 ): JsonResponse {
        $response = [
            'data' => $data,
        ];

        if($message != null)
            $response["message"] = $message;

        return response()->json($response, $status);
    }

    public static function error(
        string $message,
        int $code = 400,
        ?Throwable $e = null
    ): JsonResponse {
        $response = [
            'error' => array_merge(
                ['code' => $code, 'message' => $message],
                $details ?? []
            ),
        ];

        if (!App::isProduction() && config('app.debug') && $e!=null) {
            $response['error']['debug'] = [
                'message' => $e->getMessage() ?: \Symfony\Component\HttpFoundation\Response::$statusTexts[$e->getStatusCode()],
                'exception' => get_class($e),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => collect($e->getTrace())->take(10),
            ];
        }

        return response()->json($response, $code);
    }

    public static function created(mixed $data, ?string $message = null): JsonResponse
    {
        return self::success($data, $message ?? 'Resource created.', 201);
    }

    public static function updated(mixed $data, ?string $message = null): JsonResponse
    {
        return self::created($data, $message ?? 'Resource updated.', 200);
    }

    public static function deleted(mixed $data, ?string $message = null): JsonResponse
    {
        return self::success($data, $message ?? 'Resource deleted.', 200);
    }

    // /**
    //  * Validation error (422)
    //  */
    // public static function validationError(array $errors, ?string $message = null): JsonResponse
    // {
    //     return self::error(
    //         $message ?? 'Validation failed',
    //         422,
    //         ['fields' => $errors]
    //     );
    // }
}
