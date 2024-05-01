<?php

namespace App\Transformers;

use App\Models\Font;
use App\Models\Pdf;
use Illuminate\Support\Facades\Storage;
use League\Fractal\TransformerAbstract;

class FontTransformer extends TransformerAbstract
{
    /**
     * A Fractal transformer.
     *
     * @param Font $font
     * @return array
     */
    public function transform(Font $font)
    {
        return [
            'name' => $font->name,
//            'template' => base64_encode(file_get_contents(Storage::disk('public')->get($pdf->template_file))),
        ];
    }
}
