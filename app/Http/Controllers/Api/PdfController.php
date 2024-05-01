<?php

namespace App\Http\Controllers\Api;

use App\Http\Requests\CreatePdf;
use App\Http\Requests\GeneratePdf;
use App\Http\Requests\UpdatePdfSchema;
use App\Models\PdfTemplate;
use App\Services\FontService;
use App\Services\PdfService;
use App\Services\PdfTemplateService;
use App\Transformers\PdfTransformer;
use App\Utils\PdfDataFormatter;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use League\Fractal\Resource\Item;
use Gas\Utils\Uuid;
use Symfony\Component\Translation\Exception\NotFoundResourceException;

class PdfController extends BaseController
{
    protected PdfService $pdfService;

    protected PdfTemplateService $pdfTemplateService;

    protected FontService $fontService;

    public function __construct(
        PdfService $pdfService,
        PdfTemplateService $pdfTemplateService,
        FontService $fontService
    ) {
        parent::__construct();
        $this->pdfService = $pdfService;
        $this->pdfTemplateService = $pdfTemplateService;
        $this->fontService = $fontService;
    }

    public function show(string $campaignUuid): array|JsonResponse
    {
        if (! $pdf = $this->pdfService->getByCampaignUuid(Uuid::fromString($campaignUuid))) {
            return response()->json([], 404);
        }

        return $this->successResponse(new Item($pdf, new PdfTransformer()));
    }

    public function store(CreatePdf $request): JsonResponse
    {
        DB::beginTransaction();
        try {
            if ($pdf = $this->pdfService->getByCampaignUuid(Uuid::fromString($request->input('campaign_uuid')))) {
                $this->pdfService->update($pdf->getAttribute('id'), ['schema' => $request->input('schema'), 'options' => $request->input('options')]);
            } else {
                $pdf = $this->pdfService->create([
                    'campaign_uuid' => $request->input('campaign_uuid'),
                    'schema' => $request->input('schema'),
                    'options' => $request->input('options'),
                ]);
            }
            foreach ($request->input('templates') as $template) {
                $filename = md5(Str::random().uniqid()).'.pdf';
                Storage::disk('templates')->put($filename, base64_decode($template['data']));
                $this->pdfTemplateService->create(['pdf_id' => $pdf->getAttribute('id'), 'name' => $template['name'], 'file_name' => $filename]);
            }
            DB::commit();
        } catch (Exception $exception) {
            DB::rollBack();
            Log::error($exception->getMessage());
            throw new Exception($exception->getMessage(), 500);
        }

        return response()->json([], 201);
    }

    public function updateSchema(UpdatePdfSchema $request): array|JsonResponse
    {
        if (! $pdf = $this->pdfService->getByCampaignUuid(Uuid::fromString($request->input('campaign_uuid')))) {
            throw new NotFoundResourceException('Pdf is not found', 404);
        }

        DB::beginTransaction();
        try {
            $this->pdfService->update($pdf->getAttribute('id'), ['schema' => $request->input('schema'), 'options' => $request->input('options')]);
            DB::commit();
        } catch (Exception $exception) {
            DB::rollBack();
            Log::error($exception->getMessage());
            throw new Exception($exception->getMessage(), 500);
        }

        return response()->json([], 204);
    }

    public function generate(GeneratePdf $request): JsonResponse
    {
        if (! $pdf = $this->pdfService->getByCampaignUuid(Uuid::fromString($request->input('campaign_uuid')))) {
            throw new NotFoundResourceException('Pdf schema is not found', 404);
        }

        if (! $pdfTemplate = $this->pdfTemplateService->getByNameAndPdf($pdf->getAttribute('id'), $request->input('template_name'))) {
            throw new NotFoundResourceException('Pdf template is not found');
        }
        /** @var PdfTemplate $pdfTemplate */
        $data = PdfDataFormatter::separateDataToPages($pdfTemplate, $request->input('data'));
        try {
            $fonts = $this->fontService->getFontsBySchema($pdfTemplate->pdf->schema);
            $pdfPath = $this->pdfService->generate($pdfTemplate, $data, $fonts, basename($request->input('file_name')));

            if (! $pdfFile = file_get_contents($pdfPath)) {
                throw new Exception('File doesn\'t exist'.$pdfPath);
            }

            $base64 = base64_encode($pdfFile);
            unlink($pdfPath);
            Log::info('Pdf successfully generated');
        } catch (Exception $exception) {
            Log::info('Pdf were not generated');
            Log::error($exception->getMessage());
            Log::error($exception->getTraceAsString());
            throw new Exception($exception->getMessage(), 500);
        }

        return response()->json([
            'data' => $base64,
            'file_name' => $request->file_name ?? '',
        ]);
    }

    public function destroy(string $campaignUuid): JsonResponse
    {
        if (! $pdf = $this->pdfService->getByCampaignUuid(Uuid::fromString($campaignUuid))) {
            return response()->json([
                'success' => false,
                'message' => 'pdf is not found',
            ], 404);
        }

        $this->pdfService->delete($pdf->getAttribute('id'));

        return response()->json([
            'success' => true,
        ], 204);
    }
}
