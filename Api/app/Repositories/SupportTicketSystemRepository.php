<?php

namespace App\Repositories;

use App\Interfaces\SupportTicketSystemInterface;
use Modules\SupportTicket\app\Models\Ticket;
use Modules\SupportTicket\app\Models\TicketMessage;
use Illuminate\Support\Carbon;

class SupportTicketSystemRepository implements SupportTicketSystemInterface
{
    public function __construct(protected Ticket $ticket, protected TicketMessage $ticketMessage)
    {

    }

    public function getTickets(array $filters)
    {
        $query = $this->ticket->with(['department', 'user','messages.sender', 'messages.receiver']);

        if (!empty($filters['search'])) {
            $searchTerm = '%' . $filters['search'] . '%';

            $query->where(function ($query) use ($searchTerm) {
                $query->where('title', 'like', $searchTerm)
                    ->orWhere('subject', 'like', $searchTerm)
                    ->orWhereHas('customer', function ($query) use ($searchTerm) {
                        $query->where('first_name', 'like', $searchTerm)
                            ->orWhere('last_name', 'like', $searchTerm);
                    });
            });
        }

        if (isset($filters['department_id'])) {
            $query->where('department_id', $filters['department_id']);
        }
        if (isset($filters['priority'])) {
            $query->where('priority', $filters['priority']);
        }
        if (isset($filters['status'])) {
            $query->where('status', $filters['status']);
        }
        $tickets = $query->latest()
            ->paginate($filters['per_page'] ?? 10);
        // Sort and fetch results
        return $tickets;
    }

    public function getSellerStoreTickets(array $filters)
    {
        $query = $this->ticket->with(['department']);

        if (isset($filters['department_id'])) {
            $query->where('department_id', $filters['department_id']);
        }
        if (isset($filters['status'])) {
            $query->where('status', $filters['status']);
        }
        if (isset($filters['priority'])) {
            $query->where('priority', $filters['priority']);
        }
        $tickets = $query->latest()
            ->paginate($filters['per_page'] ?? 10);
        return $tickets;
    }

    public function getCustomerTickets(array $filters)
    {
        $query = $this->ticket->with([
            'department',
            'messages.sender',
            'messages.receiver'
        ]);

        if (isset($filters['search']) && !empty($filters['search'])) {
            $searchTerm = '%' . $filters['search'] . '%';
            $query->where(function ($query) use ($searchTerm) {
                $query->where('title', 'like', $searchTerm)
                    ->orWhere('subject', 'like', $searchTerm);
            });
        }

        if (isset($filters['department_id'])) {
            $query->where('department_id', $filters['department_id']);
        }

        if (isset($filters['priority'])) {
            $query->where('priority', $filters['priority']);
        }

        if (isset($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        $tickets = $query->where('user_id', auth('api_customer')->user()->id)
            ->latest()
            ->paginate($filters['per_page'] ?? 10);

        // Sort and fetch results
        return $tickets;
    }

    public function getTicketById($ticketId)
    {
        return $this->ticket->with(['department', 'user', 'messages.sender', 'messages.receiver'])->find($ticketId);
    }

    public function createTicket(array $data)
    {
        try {
            $this->ticket->create($data);
            return true;
        } catch (\Exception $e) {
            return false;
        }
    }

    public function addMessage(array $messageDetails)
    {
        return $this->ticketMessage->create($messageDetails);
    }

    public function replyMessage(array $messageDetails)
    {
        return $this->ticketMessage->create($messageDetails);
    }

    public function updateTicket(array $data)
    {
        $ticket = $this->ticket->find($data['id']);
        if ($ticket->count() > 0) {
            $ticket->update($data);
            return $ticket;
        } else {
            return false;
        }
    }

    public function resolveTicket($ticketId)
    {
        $ticket = $this->ticket->find($ticketId);
        if ($ticket->count() > 0) {
            $ticket->update([
                'status' => 0,
                'resolved_at' => Carbon::now()
            ]);
            return $ticket;
        } else {
            return false;
        }
    }

    public function getTicketMessages(array $data)
    {

        $query = $this->ticketMessage
            ->where('ticket_id', $data['ticket_id'])
            ->with(['sender', 'receiver'])
            ->orderBy('created_at', 'asc'); // Change to `desc` if you want latest messages first

        return $query->get();
    }

    public function getAdminTicketMessages(int $ticket_id)
    {
        $query = $this->ticketMessage
            ->where('ticket_id', $ticket_id)
            ->with(['sender', 'receiver'])
            ->orderBy('created_at', 'asc'); // Change to `desc` if you want latest messages first
        return $query->get();
    }

    public function markMessageAsRead($messageId)
    {
        $message = $this->ticketMessage->findOrFail($messageId);
        $message->update([
            'is_read' => true,
            'read_at' => Carbon::now()
        ]);
        return $message;
    }

}
