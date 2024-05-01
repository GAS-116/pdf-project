<?php

namespace App\Traits;

use Ramsey\Uuid\Uuid;

trait HasUuids
{
    /**
     * Setup model event hooks: when creating an item we must set an uuid automatically.
     * @return void
     */
    public static function boot()
    {
        parent::boot();
        self::creating(function ($model) {
            if (empty($model->id)) {
                $model->id = str_replace('-', '', Uuid::uuid4()->toString());
            }
        });
    }
}
