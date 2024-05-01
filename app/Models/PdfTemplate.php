<?php

namespace App\Models;

use App\Traits\HasUuids;
use Illuminate\Database\Eloquent\Model;

class PdfTemplate extends Model
{
    use HasUuids;

    public $incrementing = false;

    protected $fillable = ['file_name', 'pdf_id', 'name'];

    public function pdf()
    {
        return $this->hasOne(Pdf::class, 'id', 'pdf_id');
    }
}
