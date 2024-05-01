<?php

namespace App\Services;

use App\Repositories\BaseRepository as Repository;
use App\Repositories\PdfTemplateRepository;
use Auth;

class PdfTemplateService extends BaseService
{
    /**
     * @return Repository
     */
    public function getRepository(): Repository
    {
        return new PdfTemplateRepository();
    }

    /**
     * @param int $pdfId
     * @param string $name
     * @return \Illuminate\Database\Eloquent\Builder|\Illuminate\Database\Eloquent\Model|null|object
     */
    public function getByNameAndPdf($pdfId, $name)
    {
        return $this->repository->getQueryBuilder()->where(['pdf_id' => $pdfId, 'name' => $name])
            ->latest('created_at')->first();
    }

    /**
     * @return \Illuminate\Database\Eloquent\Builder[]|\Illuminate\Database\Eloquent\Collection
     */
    public function all()
    {
        return $this->repository->getQueryBuilder()->get();
    }
}
