<?php

namespace Modules\Chat\app\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class AdminChatManageController extends Controller
{
    public function chatPusherSettings(Request $request)
    {

        if ($request->isMethod('POST')) {
            // get data
             $com_pusher_app_id = $request->com_pusher_app_id;
             $com_pusher_app_key = $request->com_pusher_app_key;
             $com_pusher_app_secret = $request->com_pusher_app_secret;
             $com_pusher_app_cluster = $request->com_pusher_app_cluster;

             // set info
             $pusher = [
                 'PUSHER_APP_ID' => $com_pusher_app_id,
                 'PUSHER_APP_KEY' => $com_pusher_app_key,
                 'PUSHER_APP_SECRET' => $com_pusher_app_secret,
                 'PUSHER_APP_CLUSTER' => $com_pusher_app_cluster
             ];

             // update env file pusher info
            updateEnvValues($pusher);
            return response()->json([
                'message' => __('messages.update_success', ['name' => 'Chat Settings']),
            ]);

        } else {
            //pusher Update settings
            $app_id = !empty(env('PUSHER_APP_ID')) ? env('PUSHER_APP_ID') : '';
            $app_key = !empty(env('PUSHER_APP_KEY')) ? env('PUSHER_APP_KEY') : '';
            $app_secret = !empty(env('PUSHER_APP_SECRET')) ? env('PUSHER_APP_SECRET') : '';
            $app_cluster = !empty(env('PUSHER_APP_CLUSTER')) ? env('PUSHER_APP_CLUSTER') : '';

            return response()->json([
                'com_pusher_app_id' => $app_id,
                'com_pusher_app_key' => $app_key,
                'com_pusher_app_secret' => $app_secret,
                'com_pusher_app_cluster' => $app_cluster
            ]);
        }

    }
}
