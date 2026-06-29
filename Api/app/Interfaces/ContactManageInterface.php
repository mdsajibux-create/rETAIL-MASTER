<?php

namespace App\Interfaces;
interface ContactManageInterface
{
    public function sendContactMessage(array $data);

    public function getContactMessages(array $filters);

    public function replyMessage(array $data);

    public function changeStatus(array $data);

    public function delete(array $ids);
}
