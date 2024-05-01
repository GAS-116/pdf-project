<?php

namespace Tests\Feature\PdfTemplateController;

use App\Http\Middleware\TokenMiddleware;
use App\Models\Pdf;
use App\Models\PdfTemplate;
use App\Services\PdfService;
use App\Services\PdfTemplateService;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class UpdateTest extends TestCase
{
    public function testSendEmptyData()
    {
        $this->withoutMiddleware(TokenMiddleware::class);
        $response = $this->put('/api/admin/pdf/template', []);

        $response->assertStatus(422);
    }

    public function testSendInvalidCampaignUuidData()
    {
        $invalidData = [
            'campaign_uuid' => 'invalid_uuid',
            'name' => 'default',
            'data' => 'some data',
        ];

        $expectedResponse = [
            "message" => 'The given data was invalid.',
            'errors' => [
                'campaign_uuid' => [
                    'The campaign uuid must be a valid UUID.'
                ],
            ],
            'status' => 422,
            'description' => '',
        ];

        $this->withoutMiddleware(TokenMiddleware::class);
        $response = $this->put('/api/admin/pdf/template', $invalidData);

        $response->assertStatus(422);
        $response->assertJson($expectedResponse);
    }

    public function testSuccessUpdatePdfTemplate()
    {
        Storage::fake('templates');
        $pdfService = $this->createMock(PdfService::class);
        $pdfService->expects($this->once())->method('getByCampaignUuid')
            ->willReturn((new Pdf())->fill(['id' => 1]));
        $pdfTemplateService = $this->createMock(PdfTemplateService::class);
        $pdfTemplateService->expects($this->once())->method('getByNameAndPdf')
            ->willReturn((new PdfTemplate())->fill(['file_name' => 'test.txt']));

        $this->app->instance(PdfService::class, $pdfService);
        $this->app->instance(PdfTemplateService::class, $pdfTemplateService);

        $this->withoutMiddleware(TokenMiddleware::class);
        $response = $this->put('/api/admin/pdf/template', $this->getValidData());
        $response->assertStatus(204);
    }

    public function testNotFoundPdf()
    {
        $pdfService = $this->createMock(PdfService::class);
        $pdfService->expects($this->once())->method('getByCampaignUuid')->willReturn(null);
        $this->app->instance(PdfService::class, $pdfService);

        $this->withoutMiddleware(TokenMiddleware::class);
        $response = $this->put('/api/admin/pdf/template', $this->getValidData());
        $response->assertStatus(404);
    }

    private function getValidData(): array
    {
        return [
            'campaign_uuid' => '5acc2049-6d46-f30b-4f2d-52f3218b98de',
            'name' => 'default',
            'data' => 'dGVzdA==',
        ];
    }
}
