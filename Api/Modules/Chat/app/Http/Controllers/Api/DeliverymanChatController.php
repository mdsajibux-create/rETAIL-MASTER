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
use Modules\Order\app\Models\Order;

class DeliverymanChatController extends Controller
{
    public function deliverymanChatList(Request $request)
    {

        $auth_user = auth()->guard('api')->user();
        $auth_id = $auth_user->id;
        $auth_type = 'deliveryman';

        $chat = Chat::where('user_id', $auth_id)
            ->where('user_type', $auth_type)
            ->first();

        if (!$chat) {
            return response()->json([
                'success' => false,
                'message' => 'Chats not found',
            ]);
        }


        $sender_chat_ids = ChatMessage::where('sender_id', $auth_id)
            ->where('sender_type', 'deliveryman')
            ->pluck('receiver_chat_id');

        $receiver_chat_ids = ChatMessage::where('receiver_id', $auth_id)
            ->where('receiver_type', 'deliveryman')
            ->pluck('chat_id');

        // Merge and get chat IDs
        $all_chat_ids = $sender_chat_ids->merge($receiver_chat_ids)->unique();

        // Remove current chat ID if necessary
        $currentChat = $chat;

        if ($currentChat) {
            $all_chat_ids = $all_chat_ids->filter(fn ($id) => $id != $currentChat->id)->values();
        }

        // Always add web branch user chat
        $web_branch_user_id = webBranchUserId();
        if ($web_branch_user_id) {
            $web_branch_chat_id = Chat::where('user_id', $web_branch_user_id)
                ->value('id');

            if ($web_branch_chat_id) {
                $all_chat_ids = collect($all_chat_ids)
                    ->merge([$web_branch_chat_id])
                    ->unique()
                    ->values();
            }
        }


        // Get all order customer list
        $customer_orders = Order::with('customer.chats')
            ->where('confirmed_by', $auth_user->id)
            ->get();

        // get all customer if created order
        if ($customer_orders->isNotEmpty()) {
            $customer_ids = $customer_orders->map(function ($order) {
                return $order->customer?->id;
            })->filter()->unique()->values();

            $customer_chat_ids = Chat::whereIn('user_id', $customer_ids)
                ->where('user_type', 'customer')
                ->pluck('id');

            // marge in array
            $all_ids = collect($all_chat_ids)->merge($customer_chat_ids)->unique()->values();
            $all_chat_ids = $all_ids;
        }


        $query = Chat::with('user')
            ->whereIn('id', $all_chat_ids)
            ->where('user_type', '!=', 'deliveryman');

        $name = $request->input('search');
        if (!empty($name)) {
            $query->where(function ($q) use ($name) {
                // For user_type = customer (Customer model)
                $q->orWhere(function ($q2) use ($name) {
                    $q2->where('user_type', 'customer')
                        ->whereHasMorph('user', ['customer'], function ($q3) use ($name) {
                            $q3->where('first_name', 'like', "%{$name}%")
                                ->orWhere('last_name', 'like', "%{$name}%");
                        });
                });

                // admin (User model with first_name / last_name)
                $q->orWhere(function ($q2) use ($name) {
                    $q2->where('user_type', 'admin')
                        ->whereHasMorph('user', ['admin'], function ($q3) use ($name) {
                            $q3->where('first_name', 'like', "%{$name}%")
                                ->orWhere('last_name', 'like', "%{$name}%");
                        });
                });
            });

        }

        $type = $request->input('type');

        if (!empty($type) && $type !== 'all') {
            $query->where('user_type', $type);
        }

        // Paginate
        $chats = $query->paginate(20);


        return response()->json([
            'success'  => true,
            'data' => ChatListResource::collection($chats),
            'meta' => new PaginationResource($chats)
        ]);
    }

    public function deliverymanChatWiseFetchMessages(Request $request) {
        $validator = Validator::make($request->all(), [
            'receiver_id'   => 'required|integer',
            'receiver_type' => 'required|string|in:customer,store,admin,deliveryman',
            'search'        => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors'  => $validator->errors(),
            ], 422);
        }

        $auth_id = auth()->guard('api')->user()->id;
        $chat = Chat::where('user_id',$auth_id)->first();

        if (empty($chat)) {
            return response()->json([
                'success' => false,
                'message'  => 'Chats not found',
            ]);
        }


        $auth_type = 'deliveryman';

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
            ->orderBy('created_at', 'asc')
            ->paginate(30);

        return response()->json([
            'success'  => true,
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
