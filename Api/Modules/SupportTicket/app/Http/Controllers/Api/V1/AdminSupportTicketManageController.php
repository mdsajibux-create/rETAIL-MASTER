<?php

namespace Modules\SupportTicket\app\Http\Controllers\Api\V1;

use App\Http\Controllers\Api\V1\Controller;
use App\Http\Resources\Com\PaginationResource;
use App\Http\Resources\Com\SupportTicket\SupportTicketDetailsResource;
use App\Http\Resources\Com\SupportTicket\SupportTicketMessageResource;
use App\Http\Resources\Com\SupportTicket\SupportTicketResource;
use App\Interfaces\SupportTicketSystemInterface;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Modules\SupportTicket\app\Models\Ticket;

class AdminSupportTicketManageController extends Controller
{
    public function __construct(protected SupportTicketSystemInterface $ticketRepo)
    {

    }

    public function index(Request $request)
    {
        $filters = $request->only([
            'search',
            'brand_id',
            'department_id',
            'status',
            'priority',
            'per_page',
        ]);

        $tickets = $this->ticketRepo->getTickets($filters);

        return response()->json([
            'data' => SupportTicketResource::collection($tickets),
            'meta' => new PaginationResource($tickets),
        ], 200);
    }

    public function show(Request $request)
    {
        $ticketId = $request->id;
        $ticket = $this->ticketRepo->getTicketById($ticketId);

        if ($ticket) {
            return response()->json(
                new SupportTicketDetailsResource($ticket)
                , 200);
        }

        return response()->json([
            'message' => __('messages.data_not_found'),
        ], 404);
    }

    public function update(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'id' => 'required',
            'department_id' => 'nullable|exists:departments,id',
            'title' => 'nullable|string|max:255',
            'subject' => 'nullable|string|max:255',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 200);
        }

        $isClosed = Ticket::find($request->input('id'))->pluck('status')->contains(0);

        if ($isClosed) {
            return response()->json([
                'message' => __('messages.ticket.closed')
            ], 422);
        }

        $success = $this->ticketRepo->updateTicket($request->only([
            'id',
            'department_id',
            'title',
            'subject'
        ]));

        if ($success) {
            return response()->json([
                'message' => __('messages.update_success', ['name' => 'Support Ticket']),
            ], 200);
        } else {
            return response()->json([
                'message' => __('messages.update_failed', ['name' => 'Support Ticket']),
            ], 500);
        }
    }

    public function changePriorityStatus(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'ticket_id' => 'required|integer|exists:tickets,id',
            'priority' => 'required|in:high,medium,low,urgent',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        $ticket = Ticket::find($request->ticket_id);

        $isClosed = $ticket->status === 0;

        if ($isClosed) {
            return response()->json([
                'message' => __('messages.ticket.closed')
            ], 422);
        }

        $success = $ticket->update([
            'priority' => $request->priority
        ]);

        if ($success) {
            return response()->json([
                'message' => __('messages.update_success', ['name' => 'Support Ticket priority']),
            ], 200);
        } else {
            return response()->json([
                'message' => __('messages.update_failed', ['name' => 'Support Ticket priority']),
            ], 500);
        }
    }

    public function resolve(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'ticket_id' => 'required|integer|exists:tickets,id',
        ]);
        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }
        $ticketId = $request->ticket_id;
        $success = $this->ticketRepo->resolveTicket($ticketId);
        if ($success) {
            return response()->json([
                'message' => __('messages.ticket.resolved'),
            ], 200);
        } else {
            return response()->json([
                'message' => __('messages.update_failed', ['name' => 'Support Ticket status']),
            ], 200);
        }
    }


    public function replyMessage(Request $request)
    {
        if (!auth('api')->check()) {
            return unauthorized_response();
        }

        $user = auth('api')->user();

        if ($user->activity_scope !== 'system_level') {
            return response()->json([
                'messages' => __('messages.authorization_invalid')
            ], 403);
        }

        // Validation
        $validator = Validator::make($request->all(), [
            'ticket_id' => 'required|exists:tickets,id',
            'message' => 'nullable|string',
            'file' => 'nullable|file|mimes:jpg,png,jpeg,webp,zip,pdf|max:2048'
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        if (!$request->hasFile('file') && blank($request->message)) {
            return response()->json([
                'status' => false,
                'message' => 'Both file and message cannot be empty'
            ]);
        }

        // Handle file upload (if any)
        $filename = null;
        if ($request->hasFile('file')) {
            $file = $request->file('file');
            $timestamp = now()->timestamp;
            $email = str_replace(['@', '.'], '_', $user->email);
            $originalName = $file->getClientOriginalName();

            $filename = "uploads/support-ticket/{$timestamp}_{$email}_{$originalName}";
            Storage::disk('import')->put($filename, file_get_contents($file->getRealPath()));
        }

        // Create message
        $messageDetails = [
            'ticket_id'   => $request->ticket_id,
            'receiver_id' => $user->id,
            'sender_role' => $user->activity_scope,
            'message'     => $request->message,
            'file'        => $filename,
        ];

        $message = $this->ticketRepo->addMessage($messageDetails);

        // Update ticket timestamp
        Ticket::findOrFail($request->ticket_id)->touch();

        // Success response
        return response()->json([
            'status'  => 'success',
            'message' => __('messages.support_ticket.message.sent'),
            'data'    => $message,
        ], 201);
    }

    public function getTicketMessages(Request $request, $ticket_id)
    {
        if (!auth('api')->check()) {
            unauthorized_response();
        }
        $user = auth('api')->user();
        if ($user->activity_scope === 'system_level') {
            $validator = Validator::make(
                ['ticket_id' => $ticket_id],
                ['ticket_id' => 'required|integer|exists:tickets,id']
            );
            if ($validator->fails()) {
                return response()->json($validator->errors(), 422);
            }
            $ticketMessages = $this->ticketRepo->getAdminTicketMessages($ticket_id);
            return response()->json(SupportTicketMessageResource::collection($ticketMessages));
        } else {
            return response()->json([
                'messages' => __('messages.authorization_invalid')
            ], 403);
        }
    }
    public function destroy(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'ticket_ids' => 'required|array|min:1',
            'ticket_ids.*' => 'nullable|exists:tickets,id',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors(),
            ], 422);
        }

        $deleted = 0;
        $failed = 0;

        $tickets = Ticket::whereIn('id', $request->ticket_ids)->get();

        foreach ($tickets as $ticket) {
            try {
                $ticket->update(['status' => 0]);
                $ticket->messages()->delete();
                $ticket->delete();
                $deleted++;
            } catch (\Throwable $e) {
                $failed++;
            }
        }

        return response()->json([
            'success' => true,
            'message' => __('messages.delete_success', ['name' => 'Support Tickets']),
            'deleted_tickets' => $deleted,
            'failed_tickets' => $failed,
        ]);
    }

}
