<?php

namespace App\Transformers;

use App\Models\Pdf;
use App\Models\PdfTemplate;
use Illuminate\Support\Facades\Storage;
use League\Fractal\Manager;
use League\Fractal\Resource\Collection;
use League\Fractal\Resource\Item;
use League\Fractal\Serializer\DataArraySerializer;
use League\Fractal\TransformerAbstract;

class PdfTemplateTransformer extends TransformerAbstract
{
    /**
     * A Fractal transformer.
     *
     * @param PdfTemplate $template
     * @return array
     */
    public function transform(PdfTemplate $template)
    {
        return [
            'name' => $template->name,
//            'template' => base64_encode(file_get_contents(Storage::disk('public')->get($pdf->template_file))),
        ];
    }
}
