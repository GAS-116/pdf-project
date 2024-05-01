<?php

namespace App\Repositories;

use App\Models\Pdf;

class PdfRepository extends BaseRepository
{
    public function __construct()
    {
        $this->model = Pdf::class;
    }
}
