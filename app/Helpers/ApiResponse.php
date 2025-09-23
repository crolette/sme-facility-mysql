<?php

namespace App\Helpers;

class ApiResponse
{
    public static function success($data = null, $message = 'Success.', $code = 200)
    {
        session()->flash('message', $message);
        session()->flash('type', 'success');
        
        return response()->json([
            'status' => 'success',
            'message' => $message,
            'type' => 'success',
            'data' => $data,
        ], $code);
    }

    public static function error($message = 'Error.', $errors = [], $code = 400)
    {
        session()->flash('message', $message);
        session()->flash('type', 'error');

        return response()->json([
            'status' => 'error',
            'message' => $message,
            'type' => 'error',
            'errors' => $errors,
        ], $code);
    }

    public static function notAuthorized($message = 'Not authorized', $code = 403)
    {
        session()->flash('message', $message);
        session()->flash('type', 'error');

        return response()->json([
            'status' => 'error',
            'message' => $message,
            'type' => 'error',
        ], $code);
    }
}
