<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Api\V1\Controller;
use App\Http\Resources\Admin\AdminContactMessageListResource;
use App\Http\Resources\Com\PaginationResource;
use App\Interfaces\ContactManageInterface;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class AdminContactManageController extends Controller
{
    public function __construct(protected ContactManageInterface $contactRepo)
    {

    }

    public function listContacts(Request $request)
    {
        $filters = [
            'search' => $request->input('search'),
            'status' => $request->input('status'),
            'per_page' => $request->input('per_page'),
        ];
        $messages = $this->contactRepo->getContactMessages($filters);
        if (!empty($messages)) {
            return response()->json([
                'data' => AdminContactMessageListResource::collection($messages),
                'meta' => new PaginationResource($messages),
            ]);
        } else {
            return response()->json([
                'status' => false,
                'status_code' => 404,
                'message' => __('messages.data_not_found'),
            ]);
        }
    }

    public function replyContacts(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'id' => 'required|exists:contact_us_messages,id',
            'reply' => 'required|string|max:2000',
        ]);
        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }
        if (!auth('api')->check() && auth('api')->user()->activity_scope !== 'store_level') {
            unauthorized_response();
        }
        $success = $this->contactRepo->replyMessage($request->all());
        if ($success) {
            return $this->success(__('messages.save_success', ['name' => 'Reply']));
        } else {
            return $this->failed(__('messages.save_failed', ['name' => 'Reply']));
        }
    }

    public function changeContactStatus(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'ids*' => 'required|exists:contact_us_messages,id',
            'status' => 'required|in:0,1,2',
        ]);
        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }
        $success = $this->contactRepo->changeStatus($request->all());
        if ($success) {
            return $this->success(__('messages.update_success', ['name' => 'Status']));
        } else {
            return $this->failed(__('messages.update_failed', ['name' => 'Status']));
        }
    }

    public function deleteContacts(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'ids*' => 'required|exists:contact_us_messages,id',
        ]);
        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }
        $success = $this->contactRepo->delete($request->ids);
        if ($success) {
            return $this->success(__('messages.delete_success', ['name' => 'Contact']));
        } else {
            return $this->failed(__('messages.delete_failed', ['name' => 'Contact']));
        }
    }
}
