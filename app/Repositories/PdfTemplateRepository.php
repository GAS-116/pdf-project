<?php

namespace App\Repositories;

use App\Models\PdfTemplate;

class PdfTemplateRepository extends BaseRepository
{
    public function __construct()
    {
        $this->model = PdfTemplate::class;
    }
}
