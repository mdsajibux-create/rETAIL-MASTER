<?php

namespace App\Repositories;

use App\Interfaces\ContactManageInterface;
use App\Mail\ContactUserMail;
use App\Models\ContactUsMessage;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;

class ContactManageRepository implements ContactManageInterface
{
    public function sendContactMessage(array $data)
    {
        $success = ContactUsMessage::create($data);
        if ($success) {
            try {
                Mail::to($data['email'])->send(new ContactUserMail($success));
                return true;
            } catch (\Exception $exception) {}
        } else {
            return false;
        }
    }

    public function getContactMessages(array $filters)
    {
        try {
            $query = ContactUsMessage::query();

            if (isset($filters['search']) && $filters['search']) {
                $query->where(function ($query) use ($filters) {
                    $query->where('name', 'like', '%' . $filters['search'] . '%')
                        ->orWhere('email', 'like', '%' . $filters['search'] . '%')
                        ->orWhere('phone', 'like', '%' . $filters['search'] . '%')
                        ->orWhere('message', 'like', '%' . $filters['search'] . '%');
                });
            }
            if (isset($filters['status']) && $filters['status']) {
                $query->where('status', $filters['status']);
            }
            $per_page = isset($filters['per_page']) ? $filters['per_page'] : 10;
            return $query->paginate($per_page);
        } catch (\Exception $exception) {
            return false;
        }

    }

    public function replyMessage(array $data)
    {
        $message = ContactUsMessage::findorfail($data['id']);
        $success = $message->update([
            'reply' => $data['reply'],
            'replied_by' => auth('api')->id(),
            'replied_at' => now(),
            'status' => 1
        ]);
        if ($success) {
            Mail::to($message->email)->send(new ContactUserMail($message));
            return true;
        } else {
            return false;
        }
    }

    public function changeStatus(array $data)
    {
        $messages = ContactUsMessage::whereIn('id', $data['ids'])
            ->update(['status' => $data['status']]);
        if ($messages) {
            return true;
        } else {
            return false;
        }
    }

    public function delete(array $ids)
    {
        $messages = ContactUsMessage::whereIn('id', $ids);
        if ($messages) {
            $messages->delete();
            return true;
        } else {
            return false;
        }
    }
}