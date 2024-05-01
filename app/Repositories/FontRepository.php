<?php

namespace App\Repositories;

use App\Models\Font;

class FontRepository extends BaseRepository
{
    public function __construct()
    {
        $this->model = Font::class;
    }
}
