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

class UserSupportTicketManageController extends Controller
{
    public function __construct(protected SupportTicketSystemInterface $ticketRepo)
    {

    }

    public function listSupportTickets(Request $request)
    {
        $filters = $request->only([
            'department_id',
            'status',
            'priority',
            'per_page',
        ]);
        $tickets = $this->ticketRepo->getUserStoreTickets($filters);

        if ($tickets->count() > 0) {
            return response()->json([
                'data' => SupportTicketResource::collection($tickets),
                'meta' => new PaginationResource($tickets),
            ], 200);
        } else {
            return response()->json([
                'status' => 'success',
                'message' => 'Ticket Not Found',
                'data' => SupportTicketResource::collection($tickets),
            ], 404);
        }
    }

    public function getSupportTicketById(Request $request)
    {
        $ticketId = $request->id;
        $ticket = $this->ticketRepo->getTicketById($ticketId);
        if ($ticket) {
            return response()->json(new SupportTicketDetailsResource($ticket), 200);
        } else {
            return response()->json(['message' => __('messages.data_not_found')], 404);
        }
    }

    public function createSupportTicket(Request $request)
    {
        if (!auth('api')->check()) {
            unauthorized_response();
        }

        $validator = Validator::make($request->all(), [
            'department_id' => 'nullable|exists:departments,id',
            'title' => 'required|string|max:255',
            'subject' => 'required|string|max:255',
            'priority' => 'nullable|in:high,medium,low,urgent',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        $request['user_id'] = auth('api')->id();
        $this->ticketRepo->createTicket($request->all());

        return response()->json([
            'status' => true,
            'status_code' => 201,
            'message' => __('messages.save_success', ['name' => 'Support Ticket']),
        ], 201);
    }

    public function updateSupportTicket(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'id' => 'required|integer|exists:tickets,id',
            'department_id' => 'nullable|exists:departments,id',
            'title' => 'required|string|max:255',
            'subject' => 'required|string|max:255',
            'priority' => 'nullable|in:high,medium,low,urgent',
        ]);
        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }
        $ticket = Ticket::find($request->id);
        $isClosed = $ticket->status === 0;
        if ($isClosed) {
            return response()->json([
                'message' => __('messages.ticket.closed')
            ], 422);
        }
        $success = $this->ticketRepo->updateTicket($request->only([
            'id',
            'department_id',
            'title',
            'subject',
            'priority'
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
        $ticket = Ticket::where('id', $request->ticket_id)
            ->where('user_id', auth('api')->id())
            ->first();

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

    public function resolveSupportTicket(Request $request)
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

    public function addMessage(Request $request)
    {
        if (!auth('api')->check()) {
            return unauthorized_response();
        }

        $validator = Validator::make($request->all(), ([
            'ticket_id' => 'required|exists:tickets,id',
            'message' => 'nullable|string|max:1500',
            'file' => 'nullable|file|mimes:jpg,png,jpeg,webp,zip,pdf|max:2048',
        ]));

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }


        if (!$request->file('file') && (is_null($request->message) || trim($request->message) === '')) {
            return response()->json([
                'status' => false,
                'message' => 'Both file and message cannot be empty'
            ]);
        }

        $user = auth('api')->user();

        $ticket = Ticket::where('id',$request->ticket_id)
            ->where('user_id', $user->id)->first();

        if (!$ticket) {
            return response()->json([
                'status' => false,
                'message' => __('messages.data_not_found'),
            ], 404);
        }

        $filename = null;

        if ($request->hasFile('file')) {
            $file = $request->file('file');
            $filename = 'uploads/support-ticket/' . now()->timestamp . '_' . str_replace(['@', '.'], '_', $user->email) . '_' . $file->getClientOriginalName();
            Storage::disk('import')->put($filename, file_get_contents($file->getRealPath()));
        }

        $messageDetails = [
            'ticket_id' => $request->ticket_id,
            'sender_id' => $user->id,
            'sender_role' => 'user_level',
            'message' => $request->message,
            'file' => $filename,
        ];

        //support ticket add
        $this->ticketRepo->addMessage($messageDetails);
        // update ticket
        Ticket::where('id', (int)$request->ticket_id)->update(['updated_at' => now()]);

        return response()->json([
            'status' => true,
            'message' => __('messages.support_ticket.message.sent'),
        ], 201);
    }

    public function ticketMessages(Request $request, $ticket_id)
    {
        $request['ticket_id'] = $ticket_id;
        $validator = Validator::make($request->all(), [
            'ticket_id' => 'required|integer|exists:tickets,id',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        // check ticket id
        $ticket  = Ticket::where('id', $ticket_id)
            ->where('user_id', auth('api')->id())
            ->first();

        if (!$ticket){
            return response()->json([
                'status' => true,
                'messages' =>  'Data Not Found'
            ], 404);
        }

        $ticketMessages = $this->ticketRepo->getTicketMessages($ticket->id);

        return response()->json([
            'status' => true,
            'messages' =>  SupportTicketMessageResource::collection($ticketMessages),
            'ticket' => new SupportTicketDetailsResource($ticket),
        ], 200);

    }
}
