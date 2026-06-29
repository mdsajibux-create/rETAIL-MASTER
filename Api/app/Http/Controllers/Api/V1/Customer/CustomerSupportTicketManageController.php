<?php

namespace App\Http\Controllers\Api\V1\Customer;

use App\Http\Controllers\Api\V1\Controller;
use App\Http\Requests\SupportTicketRequest;
use App\Http\Resources\Com\PaginationResource;
use App\Http\Resources\Com\SupportTicket\SupportTicketDetailsResource;
use App\Http\Resources\Com\SupportTicket\SupportTicketMessageResource;
use App\Http\Resources\Com\SupportTicket\SupportTicketResource;
use App\Interfaces\SupportTicketSystemInterface;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Modules\SupportTicket\app\Models\Ticket;

class CustomerSupportTicketManageController extends Controller
{
    public function __construct(protected SupportTicketSystemInterface $ticketRepo)
    {

    }

    public function listSupportTickets(Request $request)
    {
        $filters = $request->only([
            'search',
            'priority',
            'department_id',
            'status',
            'per_page',
        ]);

        $tickets = $this->ticketRepo->getCustomerTickets($filters);

        return response()->json([
            'data' => SupportTicketResource::collection($tickets),
            'meta' => new PaginationResource($tickets)
        ], 200);
    }

    public function getSupportTicketById(Request $request)
    {

        if (!auth('api_customer')->check()) {
            unauthorized_response();
        }
        $customer = auth('api_customer')->user();

        $validator = Validator::make(['ticket_id' => $request->ticket_id], [
            'ticket_id' => 'required|exists:tickets,id',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        $ticket = Ticket::find($request->ticket_id);

        if ($ticket->user_id == null || $ticket->user_id != $customer->id) {
            return response()->json([
                'message' => __('messages.ticket_does_not_belongs_to_this_customer')
            ], 422);
        }

        $ticket = $this->ticketRepo->getTicketById($request->ticket_id);

        if ($ticket) {
            return response()->json([
                'data' => new SupportTicketDetailsResource($ticket)
            ], 200);
        } else {
            return response()->json([
                'message' => __('messages.data_not_found')
            ], 404);
        }
    }

    public function createSupportTicket(SupportTicketRequest $request)
    {
        if (!auth('api_customer')->check()) {
            unauthorized_response();
        }

        $request['user_id'] = auth('api_customer')->user()->id;

        $success = $this->ticketRepo->createTicket($request->all());

        if ($success) {
            return response()->json([
                'message' => __('messages.save_success', ['name' => 'Support Ticket']),
            ], 200);
        } else {
            return response()->json([
                'message' => __('messages.save_failed', ['name' => 'Support Ticket'])
            ], 500);
        }
    }

    public function updateSupportTicket(Request $request)
    {
        if (!auth('api_customer')->check()) {
            unauthorized_response();
        }
        $customer = auth('api_customer')->user();

        $validator = Validator::make($request->all(), [
            'id' => 'required|exists:tickets,id',
            'department_id' => 'nullable|exists:departments,id',
            'title' => 'nullable|string|max:255',
            'priority' => 'nullable|in:low,high,medium,urgent',
            'subject' => 'nullable|string|max:255',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => $validator->errors()
            ], 422);
        }

        $ticket = Ticket::find($request->id);
        $isClosed = $ticket->status == 0;

        if ($ticket->user_id == null || $ticket->user_id != $customer->id) {
            return response()->json([
                'message' => __('messages.ticket_does_not_belongs_to_this_customer')
            ], 422);
        }
        if ($isClosed) {
            return response()->json([
                'message' => __('messages.ticket.closed')
            ], 422);
        }
        try {

            $this->ticketRepo->updateTicket($request->only([
                'id',
                'department_id',
                'title',
                'priority',
                'subject'
            ]));

            return response()->json([
                'message' => __('messages.update_success', ['name' => 'Support Ticket']),
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function resolveSupportTicket(Request $request)
    {
        $ticketId = $request->input('ticket_id');
        try {
            $this->ticketRepo->resolveTicket($ticketId);
            return response()->json([
                'message' => __('messages.ticket.resolved'),
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function addTicketMessage(Request $request)
    {
        if (auth('api_customer')->check()) {
            unauthorized_response();
        }
        $validator = Validator::make($request->all(), [
            'ticket_id' => 'required|exists:tickets,id',
            'message' => 'nullable|string',
            'file' => 'nullable|file|mimes:jpg,png,jpeg,webp,zip,pdf|max:2048'
        ]);
        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }
        if (!$request->file('file') && (is_null($request->message) || trim($request->message) === '')) {
            return response()->json([
                'status' => false,
                'message' => 'Both file and message cannot be empty'
            ]);
        }
        if ($request->hasFile('file')) {
            // Retrieve the uploaded file
            $file = $request->file('file');

            // Generate a filename with a timestamp
            $timestamp = now()->timestamp;
            $email = str_replace(['@', '.'], '_', auth('api_customer')->user()->email);
            $originalName = $file->getClientOriginalName(); // Get the original file name
            $filename = 'uploads/support-ticket/' . $timestamp . '_' . $email . '_' . $originalName;

            // Save the uploaded file to private storage
            Storage::disk('import')->put($filename, file_get_contents($file->getRealPath()));
        }

        $messageDetails = [
            'ticket_id' => $request->ticket_id,
            'sender_id' => auth('api_customer')->user()->id,
            'sender_role' => 'customer_level',
            'message' => $request->message,
            'file' => $filename ?? null,
        ];
        $message = $this->ticketRepo->addMessage($messageDetails);
        $ticket = Ticket::findorfail($request->ticket_id);
        $ticket->touch();

        return response()->json([
            'status' => 'success',
            'message' => __('messages.support_ticket.message.sent'),
            'data' => $message
        ], 201);
    }

    public function ticketMessages(Request $request, $ticket_id)
    {
        $request['ticket_id'] = $ticket_id;
        $validator = Validator::make($request->all(), [
            'ticket_id' => 'required|exists:tickets,id',
        ]);
        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }
        $ticketMessages = $this->ticketRepo->getTicketMessages($request->all());
        return response()->json([
            'status' => true,
            'status_code' => 200,
            'data' => SupportTicketMessageResource::collection($ticketMessages)
        ]);
    }
}
