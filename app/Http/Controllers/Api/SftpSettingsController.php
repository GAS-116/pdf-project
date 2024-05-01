<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Requests\SftpSetting;
use App\Services\SftpSettingsService;
use App\Transformers\SftpSettingTransformer;
use Illuminate\Http\JsonResponse;
use League\Fractal\Resource\Item;
use Gas\Utils\Uuid;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\Translation\Exception\NotFoundResourceException;

class SftpSettingsController extends BaseController
{
    private SftpSettingsService $sftpSettingsService;

    public function __construct(SftpSettingsService $service)
    {
        $this->sftpSettingsService = $service;
        parent::__construct();
    }

    public function store(SftpSetting $request): array|JsonResponse
    {
        if ($this->sftpSettingsService->getSettingsByCampaignUuid(Uuid::fromString($request->input('campaign_uuid')))) {
            throw new BadRequestHttpException('Sftp settings already exists');
        }

        $data = [
            'host' => $request->get('host'),
            'port' => $request->get('port'),
            'username' => $request->get('username'),
            'private_key' => $request->get('private_key'),
            'passphrase' => $request->get('passphrase'),
            'root_path' => $request->get('root_path'),
            'campaign_uuid' => $request->get('campaign_uuid'),
        ];

        $result = $this->sftpSettingsService->create($data);

        return $this->successResponse(new Item($result, new SftpSettingTransformer()));
    }

    public function update(SftpSetting $request): JsonResponse
    {
        if (! $settings = $this->sftpSettingsService->getSettingsByCampaignUuid(Uuid::fromString($request->input('campaign_uuid')))) {
            throw new NotFoundResourceException('Sftp setting was not found', 404);
        }

        $data = [
            'host' => $request->get('host'),
            'port' => $request->get('port'),
            'username' => $request->get('username'),
            'private_key' => $request->get('private_key'),
            'passphrase' => $request->get('passphrase'),
            'root_path' => $request->get('root_path'),
        ];

        $this->sftpSettingsService->update($settings->getAttribute('id'), $data);

        return response()->json([], 204);
    }

    public function destroy(string $campaignUuid): JsonResponse
    {
        if (! $settings = $this->sftpSettingsService->getSettingsByCampaignUuid(Uuid::fromString($campaignUuid))) {
            throw new NotFoundResourceException('Sftp setting was not found', 404);
        }

        $this->sftpSettingsService->delete($settings->getAttribute('id'));

        return response()->json(['success' => true], 204);
    }

    public function show(string $campaignUuid): array|JsonResponse
    {
        if (! $settings = $this->sftpSettingsService->getSettingsByCampaignUuid(Uuid::fromString($campaignUuid))) {
            throw new NotFoundResourceException('Sftp setting was not found', 404);
        }

        return $this->successResponse(new Item($settings, new SftpSettingTransformer()));
    }
}
