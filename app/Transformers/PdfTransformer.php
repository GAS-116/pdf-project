<?php

namespace App\Transformers;

use App\Models\Pdf;
use Illuminate\Support\Facades\Storage;
use League\Fractal\Manager;
use League\Fractal\Resource\Collection;
use League\Fractal\Resource\Item;
use League\Fractal\Serializer\DataArraySerializer;
use League\Fractal\TransformerAbstract;

class PdfTransformer extends TransformerAbstract
{
    /**
     * A Fractal transformer.
     *
     * @param Pdf $pdf
     * @return array
     * @throws \Illuminate\Contracts\Filesystem\FileNotFoundException
     */
    public function transform(Pdf $pdf)
    {
        return [
            'campaign_uuid' => $pdf->campaign_uuid,
            'schema' => $pdf->schema,
//            'template' => base64_encode(file_get_contents(Storage::disk('public')->get($pdf->template_file))),
        ];
    }
}
