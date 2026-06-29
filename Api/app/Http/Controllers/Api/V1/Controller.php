<?php

namespace App\Http\Controllers\Api\V1;


use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller as BaseController;


class Controller extends BaseController
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;

    public function success($message,$status_code=200, $data = [])
    {
        return response()->json([
            'success' => true,
            'message' => $message,
            'data' => $data,
        ],$status_code);
    }

    public function failed($message,$status_code=200)
    {
        return response()->json([
            'success' => false,
            'message' => $message 
        ],$status_code);
    }
}
