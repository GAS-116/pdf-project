<?php

namespace App\Transformers;

use App\Models\Icc;
use League\Fractal\TransformerAbstract;

class IccTransformer extends TransformerAbstract
{
    /**
     * A Fractal transformer.
     *
     * @param Icc $icc
     * @return array
     */
    public function transform(Icc $icc)
    {
        return [
            'name' => $icc->name,
//            'template' => base64_encode(file_get_contents(Storage::disk('public')->get($pdf->template_file))),
        ];
    }
}
