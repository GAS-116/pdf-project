<?php

namespace App\Http\Controllers\Api;

use App\Http\Requests\CreateTemplatePdf;
use App\Http\Requests\UpdatePdfTemplateRequest;
use App\Services\PdfService;
use App\Services\PdfTemplateService;
use App\Transformers\PdfTemplateTransformer;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use League\Fractal\Resource\Collection;
use Gas\Utils\Uuid;
use Symfony\Component\Translation\Exception\NotFoundResourceException;

class PdfTemplateController extends BaseController
{
    protected PdfService $pdfService;

    protected PdfTemplateService $pdfTemplateService;

    public function __construct(PdfService $pdfService, PdfTemplateService $pdfTemplateService)
    {
        parent::__construct();
        $this->pdfService = $pdfService;
        $this->pdfTemplateService = $pdfTemplateService;
    }

    public function store(CreateTemplatePdf $request): JsonResponse
    {
        if (! $pdf = $this->pdfService->getByCampaignUuid(Uuid::fromString($request->input('campaign_uuid')))) {
            throw new NotFoundResourceException('Pdf schema is not found', 404);
        }

        foreach ($request->input('templates') as $data) {
            $filename = md5(Str::random().uniqid()).'.pdf';
            Storage::disk('templates')->put($filename, base64_decode($data['data']));
            $this->pdfTemplateService->create(['pdf_id' => $pdf->getAttribute('id'), 'name' => $data['name'], 'file_name' => $filename]);
        }

        return response()->json([], 201);
    }

    public function update(UpdatePdfTemplateRequest $request): JsonResponse
    {
        if (! $pdf = $this->pdfService->getByCampaignUuid(Uuid::fromString($request->input('campaign_uuid')))) {
            throw new NotFoundResourceException('Pdf template is not found', 404);
        }

        if (! $template = $this->pdfTemplateService->getByNameAndPdf($pdf->getAttribute('id'), $request->input('name'))) {
            throw new NotFoundResourceException('Pdf template is not found', 404);
        }

        $filename = $template->file_name;
        Storage::disk('templates')->put($filename, base64_decode($request->input('data')));

        return response()->json([], 204);
    }
}
