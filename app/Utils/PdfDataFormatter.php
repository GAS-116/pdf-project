<?php

namespace App\Utils;

use App\Models\PdfTemplate;

class PdfDataFormatter
{
    public static function separateDataToPages(PdfTemplate $pdfTemplate, array $data): array
    {
        $result = [];

        if (str_contains((string) (array_key_first($data)), 'page_')) {
            return $data;
        }

        foreach ($pdfTemplate->pdf->schema as $item) {
            if (! isset($data[$item['name']])) {
                continue;
            }

            $page = $item['page'] ?? 1;
            $result['page_'.$page][$item['name']] = $data[$item['name']];
        }

        return $result;
    }
}
