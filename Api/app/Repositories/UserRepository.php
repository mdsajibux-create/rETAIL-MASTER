<?php

namespace App\Repositories;

use App\Mail\ForgetPassword;
use App\Models\User;
use Illuminate\Support\Facades\Mail;
use Prettus\Repository\Criteria\RequestCriteria;
use Prettus\Repository\Eloquent\BaseRepository;
use Prettus\Repository\Exceptions\RepositoryException;

class UserRepository extends BaseRepository
{

    /**
     * @var array
     */
    protected $fieldSearchable = [
        'first_name',
        'last_name',
        'phone',
        'email'
    ];
    /**
     * Specify Model class name
     *
     * @return string
     */
    function model()
    {
        return User::class;
    }

    public function boot()
    {
        try {
            $this->pushCriteria(app(RequestCriteria::class));
        } catch (RepositoryException $e) {
        }
    }

    public function sendResetEmail($email, $token)
    {
        try {
            Mail::to($email)->send(new ForgetPassword($token));
            return true;
        } catch (\Exception $e) {
            return false;
        }
    }
}
