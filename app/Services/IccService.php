<?php

namespace App\Services;

use App\Repositories\BaseRepository as Repository;
use App\Repositories\IccRepository;

class IccService extends BaseService
{
    /**
     * @return Repository
     */
    public function getRepository(): Repository
    {
        return new IccRepository();
    }

    /**
     * @param string $name
     * @return \Illuminate\Database\Eloquent\Builder|\Illuminate\Database\Eloquent\Model|null|object
     */
    public function getByName($name)
    {
        return $this->repository->getQueryBuilder()->where('name', $name)->first();
    }

    /**
     * @return \Illuminate\Database\Eloquent\Builder[]|\Illuminate\Database\Eloquent\Collection
     */
    public function all()
    {
        return $this->repository->getQueryBuilder()->get();
    }

    /**
     * @param array $schema
     * @return \Illuminate\Support\Collection
     */
    public function getIccBySchema(array $schema)
    {
        $fontNames = [];
        foreach ($schema as $item) {
            if (! isset($item['icc'])) {
                continue;
            }

            $fontNames[] = $item['icc'];
        }

        return $this->repository->getQueryBuilder()->whereIn('name', $fontNames)->get()->pluck('filename', 'name');
    }
}
