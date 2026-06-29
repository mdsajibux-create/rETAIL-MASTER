<?php

namespace Modules\Chat\app\Http\Controllers\Api;

use App\Http\Controllers\Api\V1\Controller;

class ChatManageController extends Controller
{
    public function getChatCredentials(){
        if (!auth()->guard('api')->check()){
            return response()->json(['error' => 'Unauthorized'], 401);
        }
        //pusher Update settings
        $app_id = !empty(env('PUSHER_APP_ID')) ? env('PUSHER_APP_ID') : '';
        $app_key = !empty(env('PUSHER_APP_KEY')) ? env('PUSHER_APP_KEY') : '';
        $app_secret = !empty(env('PUSHER_APP_SECRET')) ? env('PUSHER_APP_SECRET') : '';
        $app_cluster = !empty(env('PUSHER_APP_CLUSTER')) ? env('PUSHER_APP_CLUSTER') : '';

        return response()->json([
            'app_id' => $app_id,
            'app_key' => $app_key,
            'app_secret' => $app_secret,
            'app_cluster' => $app_cluster
        ]);
    }
}
