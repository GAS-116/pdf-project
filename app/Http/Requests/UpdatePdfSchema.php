<?php

namespace App\Http\Requests;

use App\Http\Requests\Rules\Pdf;
use Illuminate\Foundation\Http\FormRequest;
use RecursiveArrayIterator;
use RecursiveIteratorIterator;

class UpdatePdfSchema extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return array_merge(['campaign_uuid' => 'required|uuid'], (new Pdf())->getRules());
    }
}
