<?php

namespace App\Interfaces;
interface ProductQueryManageInterface
{
    public function askQuestion(array $data);

    public function searchQuestion(array $data);

    public function getSellerQuestions(array $data);

    public function replyQuestion(array $data);

    public function getAllQuestionsAndReplies(array $data);

    public function bulkDelete(array $ids);

    public function changeStatus(int $id);
}
