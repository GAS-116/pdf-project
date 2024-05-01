<?php

declare(strict_types=1);

namespace App\Services;

use App\Repositories\BaseRepository as Repository;
use App\Repositories\SftpSettingsRepository;
use Illuminate\Database\Eloquent\Model;
use Gas\Utils\Uuid;

class SftpSettingsService extends BaseService
{
    /**
     * @return Repository
     */
    public function getRepository(): Repository
    {
        return new SftpSettingsRepository();
    }

    public function getSettingsByCampaignUuid(Uuid $campaignUuid): ?Model
    {
        /* @phpstan-ignore-next-line */
        return $this->getRepository()->getQueryBuilder()
            ->where('campaign_uuid', $campaignUuid->asString())
            ->first();
    }
}
