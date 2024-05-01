<?php

namespace App\Repositories;

use http\Client\Request;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
use Symfony\Component\Routing\Exception\ResourceNotFoundException;

abstract class BaseRepository
{
    /** @var string */
    protected $model;

    /** @var Validator */
    protected $validator;

    /**
     * @return Builder
     */
    public function getQueryBuilder(): Builder
    {
        return $this->model::query();
    }

    /**
     * @param mixed $data
     * @return Model
     */
    public function create($data): Model
    {
        return $this->model::create($data);
    }

    /**
     * @param $id
     * @return Model|Collection
     */
    public function findOne($id)
    {
        $result = $this->model::find($id);
        if (! $result) {
            throw new ResourceNotFoundException("Cannot find record with ID '{$id}'");
        }

        return $result;
    }

    /**
     * @param $id
     * @param array $data
     * @return bool
     */
    public function update($id, array $data): bool
    {
        /** @var Model $record */
        $record = $this->findOne($id);
        if (! $record) {
            throw new ResourceNotFoundException("Cannot find resource with ID '{$id}'!");
        }

        $data = collect($data);

        return $record->update($data->toArray());
    }

    /**
     * @param $id
     * @return bool
     * @throws \Exception
     */
    public function delete($id): bool
    {
        $record = $this->findOne($id);
        if ($record instanceof Model) {
            return $record->delete();
        }

        throw new ResourceNotFoundException("Cannot find record with UUID '{$id}'");
    }
}
