<?php

namespace App\Traits;

trait RespondsWithHttpStatus
{
    protected function success($message = '', $data = [], Int $status = 200)
    {
        return response([
            'success' => true,
            'message' => $message,
            'data' => $data
        ], $status);
    }

    protected function failure($error = 'Unprocessable Entity', $errors = [], Int $status = 422)
    {
        return response([
            'success' => false,
            'error' => $error,
            'errors' => $errors,
        ], $status);
    }

    protected function validateFailure($validator, Int $status = 400)
    {
        return response([
            'success' => false,
            'error' => 'Bad Request',
            'errors' => $validator->messages(),
        ], $status);
    }

    protected function exceptionFailure($error, Int $status = 422)
    {
        return response([
            'success' => false,
            'error' => 'Unprocessable Entity',
            'errors' => $error->getMessage(),
        ], $status);
    }
}