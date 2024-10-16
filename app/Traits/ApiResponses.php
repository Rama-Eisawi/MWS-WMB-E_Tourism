<?php

namespace App\Traits;

/**
 * Trait ApiResponses
 * This trait provides utility methods for standardizing JSON API responses.
 * It defines two main methods: success and failed responses.
 */
trait ApiResponses
{
    /**
     * Generate a success response with data and message.
     *
     * @param mixed  $data         The data to return in the response body.
     * @param string $message      The success message to return.
     * @param int    $status_code  The HTTP status code (e.g., 200).
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function success($data, $message, $status_code)
    {
        // Return a JSON response with success status, message, and data
        return response()->json([
            'status' => 'success',
            'message' => $message,
            'data' => $data
        ], $status_code);
    }

    /**
     * Generate a failed response with a message and status code.
     *
     * @param string $message      The error or failure message to return.
     * @param int    $status_code  The HTTP status code (e.g., 400, 500).
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function failed($message, $status_code)
    {
        // Return a JSON response with failed status and message
        return response()->json([
            'status' => 'failed',
            'message' => $message,
        ], $status_code);
    }
}
