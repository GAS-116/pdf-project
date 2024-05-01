<?php

declare(strict_types=1);

namespace App\Transformers;

use App\Models\SftpSetting;
use League\Fractal\TransformerAbstract;

class SftpSettingTransformer extends TransformerAbstract
{
    public function transform(SftpSetting $sftpSetting)
    {
        return [
            'host' => $sftpSetting->host,
            'port' => $sftpSetting->port,
            'username' => $sftpSetting->username,
            'root_path' => $sftpSetting->root_path,
        ];
    }
}
