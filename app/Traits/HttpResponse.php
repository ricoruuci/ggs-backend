<?php

namespace App\Traits;

trait HttpResponse
{
    protected function responsePagination($data, $httpCode = 200)
    {
        return response()->json($data, $httpCode);
    }

    protected function responseData($data, $httpCode = 200)
    {
        if ($data)
        {
            return response()->json([
                'data' => $data
            ], $httpCode);
        }
        else
        {
            return response()->json([
                'data' => ''
            ]);
        }
    }

    protected function responseError($message, $httpCode)
    {
        return response()->json([
            'success' => false,
            'message' => $message
        ], $httpCode);
    }

    protected function responseSuccess($message, $httpCode, $data = null)
    {
        if($data)
        {
            return response()->json([
                'success' => true,
                'message' => $message,
                'data' => $data
            ], $httpCode);
        }
        else
        {
            return response()->json([
                'success' => true,
                'message' => $message
            ], $httpCode);
        }
    }
}