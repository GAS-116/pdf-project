<?php

namespace App\Models;

use App\Traits\HasUuids;
use Illuminate\Database\Eloquent\Model;

class Icc extends Model
{
    use HasUuids;

    public $incrementing = false;

    public $table = 'icc';
    protected $fillable = ['name', 'filename'];
}
