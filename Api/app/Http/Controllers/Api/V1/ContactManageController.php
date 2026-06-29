<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Requests\ContactRequest;
use App\Interfaces\ContactManageInterface;

class ContactManageController extends Controller
{
    public function __construct(protected ContactManageInterface $contactRepo)
    {

    }
    public function store(ContactRequest $request)
    {
        $success = $this->contactRepo->sendContactMessage($request->all());
        if ($success) {
            return $this->success(__('messages.save_success', ['name' => 'Your message']),200);
        } else {
            return $this->failed(__('messages.currently_not_available'),500);
        }
    }
}
