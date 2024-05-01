<?php

namespace Tests\Feature\PdfController;

use App\Http\Middleware\TokenMiddleware;
use App\Models\Pdf;
use App\Services\PdfService;
use App\Services\PdfTemplateService;
use Illuminate\Support\Facades\Storage;
use gas\Utils\Uuid;
use Tests\TestCase;

class DestroyTest extends TestCase
{
    public function testSuccess()
    {
        $pdf = (new Pdf());
        $pdfService = $this->createMock(PdfService::class);
        $pdfService->expects($this->once())->method('getByCampaignUuid')
            ->withConsecutive([Uuid::fromString($this->getCampaignUuid())])
            ->willReturn($pdf);
        $this->app->instance(PdfService::class, $pdfService);

        $this->withoutMiddleware(TokenMiddleware::class);
        $response = $this->delete('/api/admin/pdf/'.$this->getCampaignUuid());

        $response->assertStatus(204);
    }

    public function testNotFound()
    {
        $pdfService = $this->createMock(PdfService::class);
        $pdfService->expects($this->once())->method('getByCampaignUuid')
            ->withConsecutive([Uuid::fromString($this->getCampaignUuid())]);
        $this->app->instance(PdfService::class, $pdfService);

        $this->withoutMiddleware(TokenMiddleware::class);
        $response = $this->delete('/api/admin/pdf/'.$this->getCampaignUuid());

        $response->assertStatus(404);
    }

    public function testFailInvalidCampaignUuid()
    {
        $this->withoutMiddleware(TokenMiddleware::class);
        $response = $this->delete('/api/admin/pdf/invalid');

        $response->assertStatus(500);
    }

    private function getCampaignUuid(): string
    {
        return '5acc2049-6d46-f30b-4f2d-52f3218b98de';
    }
}
