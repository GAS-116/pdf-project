<?php

namespace App\Services;

use App\Repositories\BaseRepository as Repository;
use App\Repositories\FontRepository;

class FontService extends BaseService
{
    /**
     * @return Repository
     */
    public function getRepository(): Repository
    {
        return new FontRepository();
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
     * @return \Illuminate\Database\Eloquent\Builder[]|\Illuminate\Support\Collection
     */
    public function getFontsBySchema(array $schema)
    {
        $fontNames = [];
        foreach ($schema as $item) {
            if (! isset($item['font'])) {
                continue;
            }

            $fontNames[] = $item['font'];
        }

        return $this->repository->getQueryBuilder()->whereIn('name', $fontNames)->get()->keyBy('name');
    }
}
