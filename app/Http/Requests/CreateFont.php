<?php

namespace App\Http\Requests;

use App\Models\Enums\FontTypesEnum;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CreateFont extends FormRequest
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
        return [
            'name' => 'required|unique:fonts',
            'php_file' => 'required_if:font_type,=,'.FontTypesEnum::PHP.',required_without:font_type',
            'z_file' => 'required_if:font_type,=,'.FontTypesEnum::PHP.',required_without:font_type',
            'z_file_name' => 'required_if:font_type,=,'.FontTypesEnum::PHP.',required_without:font_type',
            'ttf_file' => 'required_if:font_type,=,'.FontTypesEnum::TTF,
            'font_type' => ['nullable', Rule::in(FontTypesEnum::list())],
        ];
    }
}
