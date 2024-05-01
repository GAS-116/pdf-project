<?php

namespace Tests\Feature\PdfController;

use App\Http\Middleware\TokenMiddleware;
use App\Models\Pdf;
use App\Services\PdfService;
use App\Services\PdfTemplateService;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class UpdateSchemaTest extends TestCase
{
    public function testSendEmptyData()
    {
        $this->withoutMiddleware(TokenMiddleware::class);
        $response = $this->put('/api/admin/pdf', []);

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
        $response = $this->put('/api/admin/pdf', $invalidData);

        $response->assertStatus(422);
        $response->assertJson($expectedResponse);
    }

    public function testSuccessUpdatePdf()
    {
        Storage::fake('templates');
        $pdfService = $this->createMock(PdfService::class);
        $pdfService->expects($this->once())->method('update');
        $pdfService->expects($this->once())->method('getByCampaignUuid')->willReturn((new Pdf()));

        $this->app->instance(PdfService::class, $pdfService);

        $this->withoutMiddleware(TokenMiddleware::class);
        $response = $this->put('/api/admin/pdf', $this->getValidData());
        $response->assertStatus(204);
    }

    public function testFailUpdatePdf()
    {
        $pdfService = $this->createMock(PdfService::class);
        $pdfService->expects($this->once())->method('create')->willThrowException(new \Exception('DB Exception'));
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
