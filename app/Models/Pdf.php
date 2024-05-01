<?php

namespace App\Models;

use App\Traits\HasUuids;
use App\User;
use Illuminate\Database\Eloquent\Model;

class Pdf extends Model
{
    use HasUuids;

    public $incrementing = false;

    protected $fillable = ['schema', 'campaign_uuid', 'options'];

    protected $casts = ['schema' => 'array', 'options' => 'array'];

    public function templates()
    {
        return $this->hasMany(self::class, 'pdf_id', 'id');
    }

    public function user()
    {
        return $this->hasOne(User::class, 'id', 'user_id');
    }
}
