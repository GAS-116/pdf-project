<?php

namespace App\Http\Requests\Rules;

class Pdf extends Base
{
    protected $schema = [

    ];

    public function fields()
    {
        return [
            'schema' => 'array',
            'schema.*.page' => 'required|int',
            'schema.*.name' => 'required|string',
            'schema.*.type' => 'required|in:image,text,concat,svg,eps,image_or_text,xmp_data',
            'schema.*.coordination' => 'required|array',
            'schema.*.icc' => 'string',
            'schema.*.size' => 'required|numeric|min:4',
            'schema.*.font' => 'required|string',
            'schema.*.width' => 'required|numeric|min:0',
            'schema.*.height' => 'required|numeric|min:0',
            'schema.*.custom_location' => 'required|boolean',
            'schema.*.xmp_data' => 'string',
            'schema.*.is_circle' => 'boolean',
            'schema.*.circle_radius' => 'numeric|min:0',
            'schema.*.circle_x' => 'numeric',
            'schema.*.circle_y' => 'numeric',
            'schema.*.line_height_pt' => 'numeric|min:0',
            'schema.*.letter_spacing' => 'numeric',
            'schema.*.is_multicell' => 'boolean',
            'schema.*.multicell_options' => 'array',
            'schema.*.xmp_data_value' => 'string',
        ];
    }
}
