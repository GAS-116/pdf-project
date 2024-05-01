<?php

namespace App\Libs;

use App\Models\Enums\FontTypesEnum;
use App\Models\Icc;
use App\Models\PdfTemplate;
use App\TCPDF\TcpdfFpdi;
use Exception;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Imagick;
use ImagickException;
use TCPDF_COLORS;
use TCPDF_FONTS;

class Pdf
{
    /**
     * @var PdfTemplate
     */
    protected $template;

    /**
     * @var array
     */
    protected $data;

    /**
     * @var TcpdfFpdi
     */
    protected $fpdi;

    protected $fonts;

    protected $pdfOptions;

    protected $pdfName;

    public function __construct(PdfTemplate $template, $data, $fonts, $pdfName = null)
    {
        $this->template = $template;
        $this->data = $data;
        $this->fonts = $fonts;
        $this->pdfOptions = $template->pdf->options;
        $this->pdfName = $pdfName;

        $this->fpdi = new TcpdfFpdi(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);

        $this->setMargin();
        $this->addFonts();
    }

    public function generate()
    {
        $this->fpdi->SetPrintHeader(false);
        $this->fpdi->SetPrintFooter(false);
        // add a page
        $this->fpdi->AddPage('', $this->getPageSize());

        $templatePath = storage_path(env('PDF_TEMPLATE_PATH').'/'.$this->template->file_name);

        // set the source file
        $this->fpdi->setSourceFile($templatePath);

        $tempImg = [];
        $pdfPageCount = count($this->data);
        $isSkip = false;

        for ($i = 1; $i <= $pdfPageCount; $i++) {

            // import page 1
            $tplIdx = $this->fpdi->importPage($i);

            // use the imported page and place it at point 10,10 with a width of 100 mm
            $this->fpdi->useImportedPage($tplIdx, 0, 0, $this->getPageSize()[0], $this->getPageSize()[1]);

            foreach ($this->template->pdf->schema as $item) {
                if (! isset($this->data['page_'.$i][$item['name']])) {
                    continue;
                }

                if (isset($item['xmp_data'])) {
                    $this->fpdi->setExtraXMP($item['xmp_data']);
                }

                $value = $this->getValue($item['name'], 'page_'.$i);

                ///////////////////////////////////////////////////////////////////////////////////////////////////////////
                //////////////SOLUTION ONLY FOR RIESTER PROJECT (remove after using)///////////////////////////////////////
                //////////////////////////////////////////////////////////////////////////////////////////////////////////
                if (strtolower($item['name']) === 't3_dvag_riester') {
                    if (empty($value)) {
                        $value = $this->getValue('t4_dvag_riester', 'page_'.$i);
                        $isSkip = true;
                    }
                }

                if ($isSkip && strtolower($item['name']) === 't4_dvag_riester') {
                    continue;
                }

                if ($item['type'] == 'image_or_text') {
                    if (@imagecreatefromstring(base64_decode($value))) {
                        $item['type'] = 'image';
                    } else {
                        $item['type'] = 'text';
                    }
                }
                ////////////////////////////////////////////////////////////////////////////////////////////////////////////

                $coordinates = $item['custom_location'] ?
                    [
                        'x' => $this->data['page_'.$i][$item['name']]['coordination']['x'],
                        'y' => $this->data['page_'.$i][$item['name']]['coordination']['y'],
                    ] :
                    [
                        'x' => $item['coordination']['x'],
                        'y' => $item['coordination']['y'],
                    ];

                if (($item['type'] == 'text') || ($item['type'] == 'concat')) {
                    if (isset($item['color'])) {
                        $this->setColor($item, 'text');
                    } else {
                        $this->fpdi->setTextColor(0, 0, 0, 100);
                    }

                    if (isset($item['font_type']) && $item['font_type'] == FontTypesEnum::TTF) {
                        $storagePrefixPath = Storage::disk('fonts')->getDriver()->getAdapter()->getPathPrefix();
                        $fontName = TCPDF_FONTS::addTTFfont(
//                            public_path("../vendor/tecnickcom/tcpdf/fonts") . '/' . $this->fonts[$item['font']],
                            $storagePrefixPath.$this->fonts[$item['font']]->filename,
                            'TrueTypeUnicode',
                            '',
                            96
                        );
                        $this->fpdi->SetFont($fontName, '', (int) $item['size'], '', false);
                    } else {
                        if (isset($this->fonts[$item['font']])) {
                            $this->fpdi->SetFont(
                                $item['font'],
                                '',
                                (int) $item['size'],
                                public_path(
                                    '../vendor/tecnickcom/tcpdf/fonts'
                                ).'/'.$this->fonts[$item['font']]->filename
                            );
                        } else {
                            Log::warning('Font not found: '.$item['font']);
                        }
                    }

                    $text = '';

                    // if item is of type 'concat' go ahead and search for text items in the value field to concatenate
                    if ($item['type'] == 'concat') {
                        $textItemNames = explode('|', $value);
                        foreach ($textItemNames as $textItemName) {
                            if (in_array($textItemName, ['$s', '$space', '_s', '_space', '\\s', '\\space'], true)) {
                                $_text = ' ';
                            } elseif (in_array($textItemName, ['$nl', 'newline', '_nl', '_newline', '\\nl', '\\newline'], true)) {
                                $_text = "\r\n";
                            } elseif (in_array($textItemName, ['$p', 'paragraph', '_p', '_paragraph', '\\p', '\\paragraph'], true)) {
                                $_text = "\r\n\r\n";
                            } elseif (preg_match('/^[{](.+)[}]$/', $textItemName, $matches)) {
                                $searchItemValue = $this->getValue($matches[1], 'page_'.$i);
                                $_text = iconv('UTF-8', 'windows-1252//IGNORE', $searchItemValue);
                            } else {
                                $_text = iconv('UTF-8', 'windows-1252//IGNORE', $textItemName);
                            }
                            $text .= utf8_encode($_text);
                        }
                    } else {
                        if (isset($item['font_type']) && $item['font_type'] == FontTypesEnum::TTF) {
                            $text = $value;
                        } else {
                            $text = iconv('UTF-8', 'windows-1252//IGNORE', $value);
                            $text = utf8_encode($text);
                        }
                    }

                    $this->fpdi->SetXY($coordinates['x'], $coordinates['y']);

                    if (isset($item['line_height_pt'])) {
                        $this->fpdi->setCellHeightRatio(round($item['line_height_pt'] / $item['size'], 2));
                    }

                    if (isset($item['letter_spacing'])) {
                        $this->fpdi->setFontSpacing($item['letter_spacing']);
                    }

                    $align = 'L';
                    $border = 0;
                    if (isset($item['multicell_options'])) {
                        $align = $item['multicell_options']['allign'] ?? $item['multicell_options']['align'] ?? 'L';
                        $border = $item['multicell_options']['border'] ?? 0;
                    }

                    if ($item['is_multicell']) {
                        $this->fpdi->MultiCell($item['width'], $item['height'], $text, $border, $align);
                    } else {
                        $this->fpdi->Write(0, $text, '', false, $align);
                    }
                } elseif ($item['type'] == 'svg') {
                    if (isset($item['color'])) {
                        $this->setColor($item, 'draw');
                    }

                    $svgName = md5(Str::random().uniqid()).'.svg';
                    Storage::disk('public')->put($svgName, base64_decode($value));

                    $tempImg[] = $svgName;

                    $this->fpdi->setRasterizeVectorImages(true);
                    $this->fpdi->ImageSVG(
                        '@'.file_get_contents(storage_path('app/public/').$svgName),
                        $coordinates['x'],
                        $coordinates['y'],
                        $item['width'],
                        $item['height']
                    );
                } elseif ($item['type'] == 'eps') {
                    if (isset($item['color'])) {
                        $this->setColor($item, 'draw');
                    }
                    $epsName = md5(Str::random().uniqid()).'.eps';

                    Storage::disk('public')->put($epsName, base64_decode($value));
                    $this->fpdi->setRasterizeVectorImages(false);

                    $tempImg[] = $epsName;
                    if (isset($item['is_circle']) && $item['is_circle']) {
                        $this->cropImage(
                            $epsName,
                            'eps',
                            $coordinates['x'],
                            $coordinates['y'],
                            $item['width'],
                            $item['height'],
                            $item['circle_radius'] ?? false
                        );
                    } else {
                        try {
                            $this->fpdi->ImageEps(
                                storage_path('app/public/').$epsName,
                                $coordinates['x'],
                                $coordinates['y'],
                                $item['width'],
                                $item['height'],
                                '',
                                true,
                                '',
                                '',
                                0,
                                false
                            );
                        } catch (Exception $e) {
                            Log::error('Error with EPS: '.$e->getMessage());
                            throw new Exception($e->getMessage(), 400);
                        }
                    }
                } elseif ($item['type'] == 'xmp_data') {
                    if (isset($item['xmp_data_value'])) {
                        $xmpData = '<xmp:'.$item['xmp_data_value'].'>'.$value.'</xmp:'.$item['xmp_data_value'].'>';
                        $this->fpdi->setExtraXMP($xmpData);
                    }
                } else {
                    if (isset($item['color'])) {
                        $this->setColor($item, 'draw');
                        if (isset($item['color']['spot_color'])) {
                            //Color for circle line in this order
                            $style['color'][0] = $item['color']['spot_color']['c'] ?? -1;
                            $style['color'][1] = $item['color']['spot_color']['m'] ?? -1;
                            $style['color'][2] = $item['color']['spot_color']['y'] ?? -1;
                            $style['color'][3] = $item['color']['spot_color']['k'] ?? -1;
                            $style['color'][4] = $item['color']['spot_color']['name'] ?? '';
                        } else {
                            $style = array_values($item['color']);
                        }
                    }
                    $imgName = md5(Str::random().uniqid()).'.png';
                    Storage::disk('public')->put($imgName, base64_decode($value));
                    $info = getimagesize(storage_path('app/public/').$imgName);
                    if ($info['mime'] == 'image/png') {
                        $extention = 'png';
                    } elseif ($info['mime'] == 'image/jpg' || $info['mime'] == 'image/jpeg') {
                        $extention = 'jpeg';
                    } else {
                        throw new Exception('Image extension is not supported', 403);
                    }

                    $old = $newImgName = $imgName;
                    $imgName = $imgName.'.'.$extention;
                    $newImgName = $newImgName.'_new.'.$extention;
                    Storage::disk('public')->move($old, $imgName);
                    $tempImg[] = $imgName;
                    $tempImg[] = $newImgName;

                    if (isset($item['icc'])) {
                        $imgName = $this->convertRgbToCmyk(
                            $item['icc'],
                            $imgName,
                            $newImgName
                        );
                    }

                    if (isset($item['is_circle']) && $item['is_circle']) {
                        $this->cropImage(
                            $imgName,
                            'image',
                            $coordinates['x'],
                            $coordinates['y'],
                            $item['width'],
                            $item['height'],
                            $item['circle_radius'] ?? false,
                            $item['circle_x'] ?? '',
                            $item['circle_y'] ?? '',
                            $style ?? []
                        );
                    } else {
                        $this->fpdi->Image(
                            storage_path('app/public/').$imgName,
                            $coordinates['x'],
                            $coordinates['y'],
                            $item['width'],
                            $item['height']
                        );
                    }
                    $style = [];
                }
            }

            if ($i != $pdfPageCount) {
                $this->fpdi->AddPage('', $this->getPageSize());
            }
        }

        if (isset($this->pdfName) && $this->pdfName) {
            $pdfPath = storage_path('app/public/pdf/'.$this->pdfName);
        } else {
            $pdfPath = storage_path('app/public/pdf/'.Str::random().'.pdf');
        }

        $this->fpdi->Output($pdfPath, 'F');

        foreach ($tempImg as $img) {
            if (Storage::disk('public')->exists($img)) {
                Storage::disk('public')->delete($img);
            }
        }

        return $pdfPath;
    }

    protected function customCopy($src, $dst)
    {

        // open the source directory
        $dir = opendir($src);

        // Make the destination directory if not exist
        @mkdir($dst);

        // Loop through the files in source directory
        while ($file = readdir($dir)) {
            if (($file != '.') && ($file != '..')) {
                if (is_dir($src.'/'.$file)) {

                    // Recursively calling custom copy function
                    // for sub directory
                    $this->customCopy($src.'/'.$file, $dst.'/'.$file);
                } else {
                    copy($src.'/'.$file, $dst.'/'.$file);
                }
            }
        }

        closedir($dir);
    }

    public function addFonts()
    {
//        $src = public_path("../resources/fonts");
        $src = storage_path(env('PDF_FONT_PATH'));
        $dst = public_path('../vendor/tecnickcom/tcpdf/fonts');
        $this->customCopy($src, $dst);

        foreach ($this->fonts as $font) {
            if ($font->font_type != FontTypesEnum::TTF) {
                $this->fpdi->AddFont($font->name, '', public_path('../vendor/tecnickcom/tcpdf/fonts').'/'.$font->filename);
            }
        }
    }

    protected function getValue($key, $page)
    {
        $value = is_array($this->data[$page][$key]) ? $this->data[$page][$key]['value'] : $this->data[$page][$key];
        if (is_array($value)) {
            return implode("\n", $value);
        }

        return $value;
    }

    private function getPageSize()
    {
        if (! isset($this->pdfOptions['page_size'])) {
            return [0 => 210, 1 => 297];
        }

        return $this->pdfOptions['page_size'];
    }

    private function setMargin()
    {
        if (isset($this->pdfOptions['margin_bottom'])) {
            $this->fpdi->SetAutoPageBreak(true, $this->pdfOptions['margin_bottom']);
        } else {
            $this->fpdi->SetAutoPageBreak(true, 0);
        }

        if (isset($this->pdfOptions['margin_top'])) {
            $this->fpdi->SetTopMargin($this->pdfOptions['margin_top']);
        }

        if (isset($this->pdfOptions['margin_right'])) {
            $this->fpdi->SetRightMargin($this->pdfOptions['margin_right']);
        }

        if (isset($this->pdfOptions['margin_left'])) {
            $this->fpdi->SetLeftMargin($this->pdfOptions['margin_left']);
        }
    }

    private function addNewSpotColor($item)
    {
        $spotColors = $this->fpdi->getAllSpotColors();
        if (! TCPDF_COLORS::getSpotColor(
            $item['color']['spot_color']['name'],
            $spotColors
        )) {
            $this->fpdi->AddSpotColor(
                $item['color']['spot_color']['name'],
                $item['color']['spot_color']['c'],
                $item['color']['spot_color']['m'],
                $item['color']['spot_color']['y'],
                $item['color']['spot_color']['k']
            );
        }

        return $item['color']['spot_color']['name'];
    }

    private function setColor($item, $type)
    {
        if (isset($item['color']['spot_color'])) {
            $name = $this->addNewSpotColor($item);
            $this->fpdi->setSpotColor(
                $type,
                $name,
                $item['color']['spot_color']['k'] ?? 100
            );
        } elseif (isset($item['color']['r'])) {
            //RGB colors
            $this->fpdi->setColor($type, $item['color']['r'], $item['color']['g'], $item['color']['b']);
        } elseif (isset($item['color']['c'])) {
            //CMYK colors
            $this->fpdi->setColor($type, $item['color']['c'], $item['color']['m'], $item['color']['y'], $item['color']['k']);
        }
    }

    /**
     * @param $icc
     * @param $imagePath
     * @param string $newImgPath
     * @return void|string
     * @throws ImagickException
     */
    private function convertRgbToCmyk($icc, $imagePath, $newImgPath = '')
    {
        if ($iccProfile = Icc::where('name', '=', $icc)->first()) {
            $iccProfile = storage_path(env('PDF_ICC_PATH')).'/'.$iccProfile->filename;
            $image = new Imagick();
            $image->clear();
            $image->readImage(storage_path('app/public/').$imagePath);
            if ($image->getImageColorspace() == Imagick::COLORSPACE_CMYK) {
                return $imagePath;
            }
            $iccCmyk = file_get_contents($iccProfile);
            $image->profileImage('cmyk', $iccCmyk);
            unset($iccCmyk);
            $image->transformImageColorspace(Imagick::COLORSPACE_CMYK);
            $image->writeImage(storage_path('app/public/').$newImgPath);

            return $newImgPath;
        }
    }

    public function cropImage($filename, $type, $imageX, $imageY, $width, $height, $radius = false, $circleX = '', $circleY = '', $style = [])
    {
        $middleHeight = round($height / 2);
        $middleWidth = round($width / 2);
        if (! $radius) {
            $radius = sqrt(pow($width, 2) + pow($height, 2)) / 2;
        }
        $circleX = $circleX ?: $imageX + $middleWidth;
        $circleY = $circleY ?: $imageY + $middleHeight;

        $this->fpdi->StartTransform();
        $this->fpdi->StarPolygon($circleX, $circleY, $radius, 90, 3, 0, 1, 'CNZ', $style, '', '', $style, '');
        if ($type == 'eps') {
            $this->fpdi->ImageEps(storage_path('app/public/').$filename, $imageX, $imageY, $width, $height);
        } else {
            $this->fpdi->Image(storage_path('app/public/').$filename, $imageX, $imageY, $width, $height, '', 'URL', '', true, 300);
        }
        $this->fpdi->StopTransform();
    }
}
