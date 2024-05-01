<?php

namespace App\Services;

use App\Repositories\BaseRepository as Repository;
use App\Repositories\UserRepository;

class UserService extends BaseService
{
    /**
     * @return Repository
     */
    public function getRepository(): Repository
    {
        return new UserRepository();
    }

    /**
     * @return \Illuminate\Database\Eloquent\Builder[]|\Illuminate\Database\Eloquent\Collection
     */
    public function all()
    {
        return $this->repository->getQueryBuilder()->get();
    }

    /**
     * @param string $token
     * @return \Illuminate\Database\Eloquent\Builder|\Illuminate\Database\Eloquent\Model|null|object
     */
    public function getUserByToken($token)
    {
        return $this->repository->getQueryBuilder()->where('remember_token', $token)->first();
    }

    /**
     * @param string $email
     * @return \Illuminate\Database\Eloquent\Builder|\Illuminate\Database\Eloquent\Model|null|object
     */
    public function getUsersByEmail($email)
    {
        return $this->repository->getQueryBuilder()->where('email', $email)->first();
    }
}
