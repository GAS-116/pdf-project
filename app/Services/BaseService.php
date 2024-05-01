<?php

namespace App\Services;

use App\Contracts\InternalService;
use App\Repositories\BaseRepository as Repository;
use Illuminate\Database\Eloquent\Model;

abstract class BaseService implements InternalService
{
    /**
     * @var Repository
     */
    protected $repository;

    /**
     * Service constructor.
     */
    public function __construct()
    {
        $this->repository = $this->getRepository();
    }

    /**
     * @param mixed $data
     * @return Model
     */
    public function create($data)
    {
        return $this->repository->create($data);
    }

    /**
     * @param int $id
     * @return mixed
     */
    public function read($id)
    {
        return $this->repository->findOne($id);
    }

    /**
     * @param int $id
     * @param array $data
     * @return bool
     */
    public function update($id, array $data): bool
    {
        return $this->repository->update($id, $data);
    }

    /**
     * @param int $id
     * @return bool
     * @throws \Exception
     */
    public function delete($id): bool
    {
        return $this->repository->delete($id);
    }

    /**
     * @return Repository
     */
    abstract public function getRepository(): Repository;
}
