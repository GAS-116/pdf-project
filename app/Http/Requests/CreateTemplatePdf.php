<?php

namespace App\Http\Requests;

use App\Http\Requests\Rules\Pdf;
use Illuminate\Foundation\Http\FormRequest;
use RecursiveArrayIterator;
use RecursiveIteratorIterator;

class CreateTemplatePdf extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'campaign_uuid' => 'required|uuid',
            'templates.*.name' => 'required',
            'templates.*.data' => 'required',
        ];
    }
}
