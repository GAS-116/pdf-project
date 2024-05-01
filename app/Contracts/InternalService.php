<?php

namespace App\Contracts;

use Illuminate\Database\Eloquent\Model;

interface InternalService
{
    /**
     * @param mixed $data
     * @return Model
     */
    public function create($data);

    /**
     * @param $id
     * @return mixed
     */
    public function read(int $id);

    /**
     * @param $id
     * @param array $data
     * @return bool
     */
    public function update(int $id, array $data): bool;

    /**
     * @param $id
     * @return bool
     */
    public function delete(int $id): bool;
}
