<?php

namespace App\Repositories;

use App\Models\Icc;

class IccRepository extends BaseRepository
{
    public function __construct()
    {
        $this->model = Icc::class;
    }
}
