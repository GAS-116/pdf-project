<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Models\SftpSetting;

class SftpSettingsRepository extends BaseRepository
{
    public function __construct()
    {
        $this->model = SftpSetting::class;
    }
}
