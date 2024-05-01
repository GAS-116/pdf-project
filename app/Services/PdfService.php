<?php

namespace App\Services;

use App\Models\Pdf;
use App\Models\PdfTemplate;
use App\Repositories\BaseRepository as Repository;
use App\Repositories\PdfRepository;
use Auth;
use Illuminate\Database\Eloquent\Model;
use Gas\Utils\Uuid;

class PdfService extends BaseService
{
    /**
     * @return Repository
     */
    public function getRepository(): Repository
    {
        return new PdfRepository();
    }

    /**
     * @param PdfTemplate $template
     * @param array $data
     * @param string $fonts
     * @param null $pdfName
     * @return string
     * @throws \Exception
     */
    public function generate(PdfTemplate $template, $data, $fonts, $pdfName = null)
    {
        return (new \App\Libs\Pdf(
            $template,
            $data,
            $fonts,
            $pdfName
        ))->generate();
    }

    public function getByCampaignUuid(Uuid $campaignUuid): ?Model
    {
        /* @phpstan-ignore-next-line */
        return $this->repository->getQueryBuilder()->where('campaign_uuid', $campaignUuid->asString())->first();
    }
}
