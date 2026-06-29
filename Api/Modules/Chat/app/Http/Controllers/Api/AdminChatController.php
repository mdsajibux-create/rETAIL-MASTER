<?php

namespace Modules\Chat\app\Http\Controllers\Api;

use App\Http\Controllers\Api\V1\Controller;
use App\Http\Resources\Com\PaginationResource;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Modules\Chat\app\Models\Chat;
use Modules\Chat\app\Models\ChatMessage;
use Modules\Chat\app\Transformers\ChatListResource;
use Modules\Chat\app\Transformers\ChatMessageDetailsResource;

class AdminChatController extends Controller
{

    public function adminChatList(Request $request)
    {
        $auth_user = auth()->guard('api')->user();
        $auth_id = $auth_user->id;
        $auth_type = 'admin';

        // Make sure admin has an existing chat
        $chat = Chat::where('user_id', $auth_id)
            ->where('user_type', $auth_type)
            ->first();

        if (!$chat) {
            return response()->json([
                'success' => false,
                'message' => __('chat::messages.not_found', ['name' => 'Chat']),
            ]);
        }

        $name = $request->input('search');
        $type = $request->input('type');

        // Main query
        if ($type === 'branch') {
            $query = Chat::query()
                ->with(['user' => fn($q) => $q->where('activity_scope', 'branch_level')])
                ->whereNotIn('user_type', ['deliveryman', 'customer'])
                ->whereHasMorph(
                    'user',
                    [\App\Models\User::class],
                    fn($uq) => $uq->where('activity_scope', 'branch_level')
                );

        } elseif ($type === 'deliveryman') {
            $query = Chat::query()
                ->with('user')
                ->where('user_type', '!=', 'customer');
        } elseif ($type === 'customer') {
            $query = Chat::query()
                ->with('user')
                ->where('user_type', '!=', 'admin')
                ->where('user_type', '!=', 'deliveryman');
        } else {
            $scopes = ['branch_level', 'delivery_level', 'system_level'];

            $query = Chat::query()
                ->with(['user' => fn($q) => $q->whereIn('activity_scope', $scopes)])
                ->whereNotIn('user_type', ['customer'])
                ->where('user_id', '!=', $auth_id) //  unset auth user
                ->whereHasMorph(
                    'user',
                    [\App\Models\User::class],
                    fn($uq) => $uq->whereIn('activity_scope', $scopes)
                );
        }


        // Apply search filter
        if ($name) {
            // Get matching chat IDs separately to avoid complex polymorphic issues
            $matchingChatIds = collect();

         // Customer matches

            //  branch-level chats
            $all_branch_list_user_ids = Chat::query()
                ->where('chats.user_type', 'admin')
                ->where('chats.activity_scope', 'branch_level')
                ->join('users', 'chats.user_id', '=', 'users.id')
                ->where(function ($q) use ($name) {
                    $q->where('users.first_name', 'like', "%{$name}%")
                        ->orWhere('users.last_name', 'like', "%{$name}%")
                        ->orWhereRaw(
                            "LOWER(CONCAT(users.first_name, ' ', users.last_name)) LIKE ?",
                            ['%' . strtolower($name) . '%']
                        );
                })
                ->pluck('chats.id');

            $matchingChatIds = $matchingChatIds->merge($all_branch_list_user_ids);

            $deliverymanChats = Chat::where('user_type', 'deliveryman')
                ->join('users', 'chats.user_id', '=', 'users.id')
                ->where(function ($q) use ($name) {
                    $q->where('users.first_name', 'like', "%{$name}%")
                        ->orWhere('users.last_name', 'like', "%{$name}%")
                        ->orWhereRaw("LOWER(CONCAT(users.first_name, ' ', users.last_name)) LIKE ?", ["%" . strtolower($name) . "%"]);
                })
                ->pluck('chats.id');

            $matchingChatIds = $matchingChatIds->merge($deliverymanChats);

            // branch matches - direct database query
            $branchChats = Chat::where('user_type', 'branch_level')
                ->join('users', 'chats.user_id', '=', 'users.id')
                ->where(function ($q) use ($name) {
                    $q->where('users.first_name', 'like', "%{$name}%")
                        ->orWhere('users.last_name', 'like', "%{$name}%")
                        ->orWhereRaw("LOWER(CONCAT(users.first_name, ' ', users.last_name)) LIKE ?", ["%" . strtolower($name) . "%"]);
                })
                ->pluck('chats.id');

            $matchingChatIds = $matchingChatIds->merge($branchChats);

            // Filter main query by matching IDs
            if ($matchingChatIds->isNotEmpty()) {
                $query->whereIn('id', $matchingChatIds->toArray());
            } else {
                // No matches found
                $query->whereRaw('1 = 0');
            }
        }

        // Apply type filter
        if (!empty($type) && $type !== 'all' && $type !== 'branch') {
            $query->where('user_type', $type);
        }


        $chats = $query->paginate(20);

        return response()->json([
            'success' => true,
            'data' => ChatListResource::collection($chats),
            'meta' => new PaginationResource($chats),
        ]);
    }

    public function chatWiseFetchMessages(Request $request)
    {


        $validator = Validator::make($request->all(), [
            'receiver_id' => 'required|integer',
            'receiver_type' => 'required|string|in:customer,store,admin,deliveryman',
            'search' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors(),
            ], 422);
        }

        $auth_id = auth()->guard('api')->user()->id;
        $chat = Chat::where('user_id', $auth_id)->first();

        if (empty($chat)) {
            return response()->json([
                'success' => false,
                'message' => __('chat::messages.not_found', ['name' => 'Chat']),
            ]);
        }


        $auth_type = 'admin';

        $receiver_id = $request->receiver_id;
        $receiver_type = $request->receiver_type;


        // get message
        $message_query = ChatMessage::query()
            ->where(function ($query) use ($auth_id, $auth_type, $receiver_id, $receiver_type) {
                $query->where(function ($q) use ($auth_id, $auth_type, $receiver_id, $receiver_type) {
                    $q->where('sender_id', $auth_id)
                        ->where('sender_type', $auth_type)
                        ->where('receiver_id', $receiver_id)
                        ->where('receiver_type', $receiver_type);
                })->orWhere(function ($q) use ($auth_id, $auth_type, $receiver_id, $receiver_type) {
                    $q->where('sender_id', $receiver_id)
                        ->where('sender_type', $receiver_type)
                        ->where('receiver_id', $auth_id)
                        ->where('receiver_type', $auth_type);
                });
            });

        $unread_message = (clone $message_query)->where('is_seen', 0)->count();
        (clone $message_query)->where('is_seen', 1)->update(['is_seen' => 1]);

        $messages = $message_query
            ->latest()
            ->paginate(30);

        return response()->json([
            'success' => true,
            'unread_message' => $unread_message,
            'data' => ChatMessageDetailsResource::collection($messages),
            'meta' => new PaginationResource($messages)
        ]);
    }

    public function markAsSeen(Request $request)
    {
        ChatMessage::where('chat_id', $request->chat_id)
            ->where('receiver_id', auth()->id())
            ->where('is_seen', 0)
            ->update(['is_seen' => 1]);

        return response()->json(['success' => true]);
    }
}
