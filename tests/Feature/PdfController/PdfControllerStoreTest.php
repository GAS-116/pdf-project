<?php

namespace Tests\Feature\PdfController;

use App\Http\Middleware\TokenMiddleware;
use App\Models\Pdf;
use App\Services\PdfService;
use App\Services\PdfTemplateService;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class PdfControllerStoreTest extends TestCase
{
    public function testSendEmptyData()
    {
        $this->withoutMiddleware(TokenMiddleware::class);
        $response = $this->post('/api/admin/pdf', []);

        $response->assertStatus(422);
    }

    public function testSendInvalidCampaignUuidData()
    {
        $invalidData = [
            'campaign_uuid' => 'invalid_uuid',
            'schema' => [
                [
                    "name" => "email",
                    "type" => "text",
                    "coordination" => [
                        "x" => 10,
                        "y" => 20
                    ],
                    "size" => 10,
                    "font" => "Regular",
                    "width" => 30,
                    "height" => 50,
                    "custom_location" => false,
                    "is_multicell" => false
                ],
            ],
            'templates' => [
                ['name' => 'default', 'data' => 'some data'],
            ],
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
        $response = $this->post('/api/admin/pdf', $invalidData);

        $response->assertStatus(422);
        $response->assertJson($expectedResponse);
    }

    public function testSuccessCreatePdf()
    {
        Storage::fake('templates');
        $pdfService = $this->createMock(PdfService::class);
        $pdfService->expects($this->once())->method('create')
            ->willReturn(new Pdf());
        $pdfTemplateService = $this->createMock(PdfTemplateService::class);
        $pdfTemplateService->expects($this->once())->method('create');

        $this->app->instance(PdfService::class, $pdfService);
        $this->app->instance(PdfTemplateService::class, $pdfTemplateService);

        $this->withoutMiddleware(TokenMiddleware::class);
        $response = $this->post('/api/admin/pdf', $this->getValidData());
        $response->assertStatus(201);
    }

    public function testFailCreatePdf()
    {
        $pdfService = $this->createMock(PdfService::class);
        $pdfService->expects($this->once())->method('create');
        $this->app->instance(PdfService::class, $pdfService);

        $this->withoutMiddleware(TokenMiddleware::class);
        $response = $this->post('/api/admin/pdf', $this->getValidData());
        $response->assertStatus(500);
    }

    private function getValidData(): array
    {
        return [
            'campaign_uuid' => '5acc2049-6d46-f30b-4f2d-52f3218b98de',
            'schema' => [
                [
                    "name" => "email",
                    "type" => "text",
                    "coordination" => [
                        "x" => 10,
                        "y" => 20
                    ],
                    "size" => 10,
                    "font" => "Regular",
                    "width" => 30,
                    "height" => 50,
                    "custom_location" => false,
                    "is_multicell" => false
                ],
            ],
            'templates' => [
                ['name' => 'default', 'data' => 'dGVzdA=='],
            ],
        ];
    }
}
