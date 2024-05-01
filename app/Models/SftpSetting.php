<?php

declare(strict_types=1);

namespace App\Models;

use App\Traits\HasUuids;
use App\User;
use Illuminate\Database\Eloquent\Model;

/**
 * App\Models\SftpSetting.
 *
 * @property string $id
 * @property string $campaign_uuid
 * @property string $host
 * @property int $port
 * @property string $username
 * @property string $private_key
 * @property string|null $passphrase
 * @property string $root_path
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 * @property-read User $user
 */
class SftpSetting extends Model
{
    use HasUuids;

    public $incrementing = false;

    protected $fillable = [
        'campaign_uuid',
        'host',
        'port',
        'username',
        'private_key',
        'passphrase',
        'root_path',
    ];

    protected $hidden = ['private_key', 'passphrase'];
}
