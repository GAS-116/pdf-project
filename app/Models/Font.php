<?php

namespace App\Models;

use App\Traits\HasUuids;
use Illuminate\Database\Eloquent\Model;

class Font extends Model
{
    use HasUuids;

    public $incrementing = false;

    protected $fillable = ['name', 'filename', 'font_type'];
}
