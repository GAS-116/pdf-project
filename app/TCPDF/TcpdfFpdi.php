<?php

namespace App\TCPDF;

use Exception;
use setasign\Fpdi\Tcpdf\Fpdi;
use TCPDF_FONTS;
use TCPDF_STATIC;

class TcpdfFpdi extends Fpdi
{
    /**
     * Embed vector-based Adobe Illustrator (AI) or AI-compatible EPS files.
     * NOTE: EPS is not yet fully implemented, use the setRasterizeVectorImages() method to enable/disable rasterization of vector images using ImageMagick library.
     * Only vector drawing is supported, not text or bitmap.
     * Although the script was successfully tested with various AI format versions, best results are probably achieved with files that were exported in the AI3 format (tested with Illustrator CS2, Freehand MX and Photoshop CS2).
     * @param $file (string) Name of the file containing the image or a '@' character followed by the EPS/AI data string.
     * @param string $x (float) Abscissa of the upper-left corner.
     * @param string $y (float) Ordinate of the upper-left corner.
     * @param int $w (float) Width of the image in the page. If not specified or equal to zero, it is automatically calculated.
     * @param int $h (float) Height of the image in the page. If not specified or equal to zero, it is automatically calculated.
     * @param string $link (mixed) URL or identifier returned by AddLink().
     * @param bool $useBoundingBox (boolean) specifies whether to position the bounding box (true) or the complete canvas (false) at location (x,y). Default value is true.
     * @param string $align (string) Indicates the alignment of the pointer next to image insertion relative to image height. The value can be:<ul><li>T: top-right for LTR or top-left for RTL</li><li>M: middle-right for LTR or middle-left for RTL</li><li>B: bottom-right for LTR or bottom-left for RTL</li><li>N: next line</li></ul>
     * @param string $palign (string) Allows to center or align the image on the current line. Possible values are:<ul><li>L : left align</li><li>C : center</li><li>R : right align</li><li>'' : empty string : left for LTR or right for RTL</li></ul>
     * @param int $border (mixed) Indicates if borders must be drawn around the cell. The value can be a number:<ul><li>0: no border (default)</li><li>1: frame</li></ul> or a string containing some or all of the following characters (in any order):<ul><li>L: left</li><li>T: top</li><li>R: right</li><li>B: bottom</li></ul> or an array of line styles for each border group - for example: array('LTRB' => array('width' => 2, 'cap' => 'butt', 'join' => 'miter', 'dash' => 0, 'color' => array(0, 0, 0)))
     * @param bool $fitonpage (boolean) if true the image is resized to not exceed page dimensions.
     * @param bool $fixoutvals (boolean) if true remove values outside the bounding box.
     * @return \image|void
     * @throws \Exception
     * @author Valentin Schmidt, Nicola Asuni
     * @since 3.1.000 (2008-06-09)
     * @public
     */
    public function ImageEps($file, $x = '', $y = '', $w = 0, $h = 0, $link = '', $useBoundingBox = true, $align = '', $palign = '', $border = 0, $fitonpage = false, $fixoutvals = false)
    {
        if ($this->state != 2) {
            return;
        }
        if ($this->rasterize_vector_images and ($w > 0) and ($h > 0)) {
            // convert EPS to raster image using GD or ImageMagick libraries
            return $this->Image($file, $x, $y, $w, $h, 'EPS', $link, $align, true, 300, $palign, false, false, $border, false, false, $fitonpage);
        }
        if ($x === '') {
            $x = $this->x;
        }
        if ($y === '') {
            $y = $this->y;
        }
        // check page for no-write regions and adapt page margins if necessary
        list($x, $y) = $this->checkPageRegions($h, $x, $y);
        $k = $this->k;
        if ($file[0] === '@') { // image from string
            $data = substr($file, 1);
        } else { // EPS/AI file
            $data = TCPDF_STATIC::fileGetContents($file);
        }
        if ($data === false) {
            $this->Error('EPS file not found: '.$file);
        }
        $regs = [];
        // EPS/AI compatibility check (only checks files created by Adobe Illustrator!)
        preg_match("/%%Creator:([^\r\n]+)/", $data, $regs); // find Creator
//        if (count($regs) > 1) {
//            $version_str = trim($regs[1]); # e.g. "Adobe Illustrator(R) 8.0"
//            if (strpos($version_str, 'Adobe Illustrator') !== false) {
//                $versexp = explode(' ', $version_str);
//                $version = (float)array_pop($versexp);
//                if ($version >= 9) {
//                    $this->Error('This version of Adobe Illustrator file is not supported: '.$file);
//                }
//            }
//        }
        // strip binary bytes in front of PS-header
        $start = strpos($data, '%!PS-Adobe');
        if ($start > 0) {
            $data = substr($data, $start);
        }
        // find BoundingBox params
        preg_match("/%%BoundingBox:([^\r\n]+)/", $data, $regs);
        if (count($regs) > 1) {
            list($x1, $y1, $x2, $y2) = explode(' ', trim($regs[1]));
        } else {
            $this->Error('No BoundingBox found in EPS/AI file: '.$file);
        }
        $start = strpos($data, '%%EndSetup');
        if ($start === false) {
            $start = strpos($data, '%%EndProlog');
        }
        if ($start === false) {
            $start = strpos($data, '%%BoundingBox');
        }
        $data = substr($data, $start);
        $end = strpos($data, '%%PageTrailer');
        if ($end === false) {
            $end = strpos($data, 'showpage');
        }
        if ($end) {
            $data = substr($data, 0, $end);
        }
        // calculate image width and height on document
        if (($w <= 0) and ($h <= 0)) {
            $w = ($x2 - $x1) / $k;
            $h = ($y2 - $y1) / $k;
        } elseif ($w <= 0) {
            $w = ($x2 - $x1) / $k * ($h / (($y2 - $y1) / $k));
        } elseif ($h <= 0) {
            $h = ($y2 - $y1) / $k * ($w / (($x2 - $x1) / $k));
        }
        // fit the image on available space
        list($w, $h, $x, $y) = $this->fitBlock($w, $h, $x, $y, $fitonpage);
        if ($this->rasterize_vector_images) {
            // convert EPS to raster image using GD or ImageMagick libraries
            return $this->Image($file, $x, $y, $w, $h, 'EPS', $link, $align, true, 300, $palign, false, false, $border, false, false, $fitonpage);
        }
        // set scaling factors
        $scale_x = $w / (($x2 - $x1) / $k);
        $scale_y = $h / (($y2 - $y1) / $k);
        // set alignment
        $this->img_rb_y = $y + $h;
        // set alignment
        if ($this->rtl) {
            if ($palign == 'L') {
                $ximg = $this->lMargin;
            } elseif ($palign == 'C') {
                $ximg = ($this->w + $this->lMargin - $this->rMargin - $w) / 2;
            } elseif ($palign == 'R') {
                $ximg = $this->w - $this->rMargin - $w;
            } else {
                $ximg = $x - $w;
            }
            $this->img_rb_x = $ximg;
        } else {
            if ($palign == 'L') {
                $ximg = $this->lMargin;
            } elseif ($palign == 'C') {
                $ximg = ($this->w + $this->lMargin - $this->rMargin - $w) / 2;
            } elseif ($palign == 'R') {
                $ximg = $this->w - $this->rMargin - $w;
            } else {
                $ximg = $x;
            }
            $this->img_rb_x = $ximg + $w;
        }
        if ($useBoundingBox) {
            $dx = $ximg * $k - $x1;
            $dy = $y * $k - $y1;
        } else {
            $dx = $ximg * $k;
            $dy = $y * $k;
        }
        // save the current graphic state
        $this->_out('q'.$this->epsmarker);
        // translate
        $this->_out(sprintf('%F %F %F %F %F %F cm', 1, 0, 0, 1, $dx, $dy + ($this->hPt - (2 * $y * $k) - ($y2 - $y1))));
        // scale
        if (isset($scale_x)) {
            $this->_out(sprintf('%F %F %F %F %F %F cm', $scale_x, 0, 0, $scale_y, $x1 * (1 - $scale_x), $y2 * (1 - $scale_y)));
        }
        // handle pc/unix/mac line endings
        $lines = preg_split('/[\r\n]+/si', $data, -1, PREG_SPLIT_NO_EMPTY);
        $u = 0;
        $cnt = count($lines);
        for ($i = 0; $i < $cnt; $i++) {
            $line = $lines[$i];
            if (($line == '') or ($line[0] == '%')) {
                continue;
            }
            $len = strlen($line);
            // check for spot color names
            $color_name = '';
            if (strcasecmp('x', substr(trim($line), -1)) == 0) {
                if (preg_match('/\([^\)]*\)/', $line, $matches) > 0) {
                    // extract spot color name
                    $color_name = $matches[0];
                    // remove color name from string
                    $line = str_replace(' '.$color_name, '', $line);
                    // remove pharentesis from color name
                    $color_name = substr($color_name, 1, -1);
                }
            }
            $chunks = explode(' ', $line);
            $cmd = trim(array_pop($chunks));
            // RGB
            if (($cmd == 'Xa') or ($cmd == 'XA')) {
                $b = array_pop($chunks);
                $g = array_pop($chunks);
                $r = array_pop($chunks);
                $this->_out(''.$r.' '.$g.' '.$b.' '.($cmd == 'Xa' ? 'rg' : 'RG')); //substr($line, 0, -2).'rg' -> in EPS (AI8): c m y k r g b rg!
                continue;
            }
            $skip = false;
            if ($fixoutvals) {
                // check for values outside the bounding box
                switch ($cmd) {
                    case 'm':
                    case 'l':
                    case 'L':
                    {
                        // skip values outside bounding box
                        foreach ($chunks as $key => $val) {
                            if ((($key % 2) == 0) and (($val < $x1) or ($val > $x2))) {
                                $skip = true;
                            } elseif ((($key % 2) != 0) and (($val < $y1) or ($val > $y2))) {
                                $skip = true;
                            }
                        }
                    }
                }
            }
            switch ($cmd) {
                case 'm':
                case 'l':
                case 'v':
                case 'y':
                case 'c':
                case 'k':
                case 'K':
                case 'g':
                case 'G':
                case 's':
                case 'S':
                case 'J':
                case 'j':
                case 'w':
                case 'M':
                case 'd':
                case 'n':
                {
                    if ($skip) {
                        break;
                    }
                    $this->_out($line);
                    break;
                }
                case 'x':
                {// custom fill color
                    if (empty($color_name)) {
                        // CMYK color
                        list($col_c, $col_m, $col_y, $col_k) = $chunks;
                        $this->_out(''.$col_c.' '.$col_m.' '.$col_y.' '.$col_k.' k');
                    } else {
                        // Spot Color (CMYK + tint)
                        list($col_c, $col_m, $col_y, $col_k, $col_t) = $chunks;
                        $this->AddSpotColor($color_name, ($col_c * 100), ($col_m * 100), ($col_y * 100), ($col_k * 100));
                        $color_cmd = sprintf('/CS%d cs %F scn', $this->spot_colors[$color_name]['i'], (1 - $col_t));
                        $this->_out($color_cmd);
                    }
                    break;
                }
                case 'X':
                { // custom stroke color
                    if (empty($color_name)) {
                        // CMYK color
                        list($col_c, $col_m, $col_y, $col_k) = $chunks;
                        $this->_out(''.$col_c.' '.$col_m.' '.$col_y.' '.$col_k.' K');
                    } else {
                        // Spot Color (CMYK + tint)
                        list($col_c, $col_m, $col_y, $col_k, $col_t) = $chunks;
                        $this->AddSpotColor($color_name, ($col_c * 100), ($col_m * 100), ($col_y * 100), ($col_k * 100));
                        $color_cmd = sprintf('/CS%d CS %F SCN', $this->spot_colors[$color_name]['i'], (1 - $col_t));
                        $this->_out($color_cmd);
                    }
                    break;
                }
                case 'Y':
                case 'N':
                case 'V':
                case 'L':
                case 'C':
                {
                    if ($skip) {
                        break;
                    }
                    $line[($len - 1)] = strtolower($cmd);
                    $this->_out($line);
                    break;
                }
                case 'b':
                case 'B':
                {
                    $this->_out($cmd.'*');
                    break;
                }
                case 'f':
                case 'F':
                {
                    if ($u > 0) {
                        $isU = false;
                        $max = min(($i + 5), $cnt);
                        for ($j = ($i + 1); $j < $max; $j++) {
                            $isU = ($isU or (($lines[$j] == 'U') or ($lines[$j] == '*U')));
                        }
                        if ($isU) {
                            $this->_out('f*');
                        }
                    } else {
                        $this->_out('f*');
                    }
                    break;
                }
                case '*u':
                {
                    $u++;
                    break;
                }
                case '*U':
                {
                    $u--;
                    break;
                }
            }
        }
        // restore previous graphic state
        $this->_out($this->epsmarker.'Q');
        if (! empty($border)) {
            $bx = $this->x;
            $by = $this->y;
            $this->x = $ximg;
            if ($this->rtl) {
                $this->x += $w;
            }
            $this->y = $y;
            $this->Cell($w, $h, '', $border, 0, '', 0, '', 0, true);
            $this->x = $bx;
            $this->y = $by;
        }
        if ($link) {
            $this->Link($ximg, $y, $w, $h, $link, 0);
        }
        // set pointer to align the next text/objects
        switch ($align) {
            case 'T':
            {
                $this->y = $y;
                $this->x = $this->img_rb_x;
                break;
            }
            case 'M':
            {
                $this->y = $y + round($h / 2);
                $this->x = $this->img_rb_x;
                break;
            }
            case 'B':
            {
                $this->y = $this->img_rb_y;
                $this->x = $this->img_rb_x;
                break;
            }
            case 'N':
            {
                $this->SetY($this->img_rb_y);
                break;
            }
            default:
            {
                break;
            }
        }
        $this->endlinex = $this->img_rb_x;
    }

    /**
     * Put XMP data object and return ID.
     * @return int (int) The object ID.
     * @since 5.9.121 (2011-09-28)
     * @protected
     */
    protected function _putXMP()
    {
        $oid = $this->_newobj();
        // store current isunicode value
        $prev_isunicode = $this->isunicode;
        $this->isunicode = true;
        $prev_encrypted = $this->encrypted;
        $this->encrypted = false;
        // set XMP data
        $xmp = '<?xpacket begin="'.(string) TCPDF_FONTS::unichr(0xfeff, $this->isunicode).'" id="W5M0MpCehiHzreSzNTczkc9d"?>'."\n";
        $xmp .= '<x:xmpmeta xmlns:x="adobe:ns:meta/" x:xmptk="Adobe XMP Core 4.2.1-c043 52.372728, 2009/01/18-15:08:04">'."\n";
        $xmp .= "\t".'<rdf:RDF xmlns:rdf="http://www.w3.org/1999/02/22-rdf-syntax-ns#">'."\n";
        $xmp .= "\t\t".'<rdf:Description rdf:about="" xmlns:dc="http://purl.org/dc/elements/1.1/">'."\n";
        $xmp .= "\t\t\t".'<dc:format>application/pdf</dc:format>'."\n";
        $xmp .= "\t\t\t".'<dc:title>'."\n";
        $xmp .= "\t\t\t\t".'<rdf:Alt>'."\n";
        $xmp .= "\t\t\t\t\t".'<rdf:li xml:lang="x-default">'.(string) TCPDF_STATIC::_escapeXML($this->title).'</rdf:li>'."\n";
        $xmp .= "\t\t\t\t".'</rdf:Alt>'."\n";
        $xmp .= "\t\t\t".'</dc:title>'."\n";
        $xmp .= "\t\t\t".'<dc:creator>'."\n";
        $xmp .= "\t\t\t\t".'<rdf:Seq>'."\n";
        $xmp .= "\t\t\t\t\t".'<rdf:li>'.(string) TCPDF_STATIC::_escapeXML($this->author).'</rdf:li>'."\n";
        $xmp .= "\t\t\t\t".'</rdf:Seq>'."\n";
        $xmp .= "\t\t\t".'</dc:creator>'."\n";
        $xmp .= "\t\t\t".'<dc:description>'."\n";
        $xmp .= "\t\t\t\t".'<rdf:Alt>'."\n";
        $xmp .= "\t\t\t\t\t".'<rdf:li xml:lang="x-default">'.(string) TCPDF_STATIC::_escapeXML($this->subject).'</rdf:li>'."\n";
        $xmp .= "\t\t\t\t".'</rdf:Alt>'."\n";
        $xmp .= "\t\t\t".'</dc:description>'."\n";
        $xmp .= "\t\t\t".'<dc:subject>'."\n";
        $xmp .= "\t\t\t\t".'<rdf:Bag>'."\n";
        $xmp .= "\t\t\t\t\t".'<rdf:li>'.(string) TCPDF_STATIC::_escapeXML($this->keywords).'</rdf:li>'."\n";
        $xmp .= "\t\t\t\t".'</rdf:Bag>'."\n";
        $xmp .= "\t\t\t".'</dc:subject>'."\n";
        $xmp .= "\t\t".'</rdf:Description>'."\n";
        // convert doc creation date format
        $dcdate = TCPDF_STATIC::getFormattedDate($this->doc_creation_timestamp);
        $doccreationdate = substr($dcdate, 0, 4).'-'.substr($dcdate, 4, 2).'-'.substr($dcdate, 6, 2);
        $doccreationdate .= 'T'.substr($dcdate, 8, 2).':'.substr($dcdate, 10, 2).':'.substr($dcdate, 12, 2);
        $doccreationdate .= substr($dcdate, 14, 3).':'.substr($dcdate, 18, 2);
        $doccreationdate = TCPDF_STATIC::_escapeXML($doccreationdate);
        // convert doc modification date format
        $dmdate = TCPDF_STATIC::getFormattedDate($this->doc_modification_timestamp);
        $docmoddate = substr($dmdate, 0, 4).'-'.substr($dmdate, 4, 2).'-'.substr($dmdate, 6, 2);
        $docmoddate .= 'T'.substr($dmdate, 8, 2).':'.substr($dmdate, 10, 2).':'.substr($dmdate, 12, 2);
        $docmoddate .= substr($dmdate, 14, 3).':'.substr($dmdate, 18, 2);
        $docmoddate = TCPDF_STATIC::_escapeXML($docmoddate);
        $xmp .= "\t\t".'<rdf:Description rdf:about="" xmlns:xmp="http://ns.adobe.com/xap/1.0/">'."\n";
        $xmp .= "\t\t\t".'<xmp:CreateDate>'.(string) $doccreationdate.'</xmp:CreateDate>'."\n";
        $xmp .= "\t\t\t".'<xmp:CreatorTool>'.$this->creator.'</xmp:CreatorTool>'."\n";
        $xmp .= "\t\t\t".'<xmp:ModifyDate>'.(string) $docmoddate.'</xmp:ModifyDate>'."\n";
        $xmp .= "\t\t\t".'<xmp:MetadataDate>'.(string) $doccreationdate.'</xmp:MetadataDate>'."\n";
        $xmp .= $this->custom_xmp;
        $xmp .= "\t\t".'</rdf:Description>'."\n";
        $xmp .= "\t\t".'<rdf:Description rdf:about="" xmlns:pdf="http://ns.adobe.com/pdf/1.3/">'."\n";
        $xmp .= "\t\t\t".'<pdf:Keywords>'.(string) TCPDF_STATIC::_escapeXML($this->keywords).'</pdf:Keywords>'."\n";
        $xmp .= "\t\t\t".'<pdf:Producer>'.(string) TCPDF_STATIC::_escapeXML(TCPDF_STATIC::getTCPDFProducer()).'</pdf:Producer>'."\n";
        $xmp .= "\t\t".'</rdf:Description>'."\n";
        $xmp .= "\t\t".'<rdf:Description rdf:about="" xmlns:xmpMM="http://ns.adobe.com/xap/1.0/mm/">'."\n";
        $uuid = 'uuid:'.substr($this->file_id, 0, 8).'-'.substr($this->file_id, 8, 4).'-'.substr($this->file_id, 12, 4).'-'.substr($this->file_id, 16, 4).'-'.substr($this->file_id, 20, 12);
        $xmp .= "\t\t\t".'<xmpMM:DocumentID>'.$uuid.'</xmpMM:DocumentID>'."\n";
        $xmp .= "\t\t\t".'<xmpMM:InstanceID>'.$uuid.'</xmpMM:InstanceID>'."\n";
        $xmp .= "\t\t".'</rdf:Description>'."\n";
        if ($this->pdfa_mode) {
            $xmp .= "\t\t".'<rdf:Description rdf:about="" xmlns:pdfaid="http://www.aiim.org/pdfa/ns/id/">'."\n";
            $xmp .= "\t\t\t".'<pdfaid:part>'.$this->pdfa_version.'</pdfaid:part>'."\n";
            $xmp .= "\t\t\t".'<pdfaid:conformance>B</pdfaid:conformance>'."\n";
            $xmp .= "\t\t".'</rdf:Description>'."\n";
        }
        // XMP extension schemas
        $xmp .= "\t\t".'<rdf:Description rdf:about="" xmlns:pdfaExtension="http://www.aiim.org/pdfa/ns/extension/" xmlns:pdfaSchema="http://www.aiim.org/pdfa/ns/schema#" xmlns:pdfaProperty="http://www.aiim.org/pdfa/ns/property#">'."\n";
        $xmp .= "\t\t\t".'<pdfaExtension:schemas>'."\n";
        $xmp .= "\t\t\t\t".'<rdf:Bag>'."\n";
        $xmp .= "\t\t\t\t\t".'<rdf:li rdf:parseType="Resource">'."\n";
        $xmp .= "\t\t\t\t\t\t".'<pdfaSchema:namespaceURI>http://ns.adobe.com/pdf/1.3/</pdfaSchema:namespaceURI>'."\n";
        $xmp .= "\t\t\t\t\t\t".'<pdfaSchema:prefix>pdf</pdfaSchema:prefix>'."\n";
        $xmp .= "\t\t\t\t\t\t".'<pdfaSchema:schema>Adobe PDF Schema</pdfaSchema:schema>'."\n";
        $xmp .= "\t\t\t\t\t\t".'<pdfaSchema:property>'."\n";
        $xmp .= "\t\t\t\t\t\t\t".'<rdf:Seq>'."\n";
        $xmp .= "\t\t\t\t\t\t\t\t".'<rdf:li rdf:parseType="Resource">'."\n";
        $xmp .= "\t\t\t\t\t\t\t\t\t".'<pdfaProperty:category>internal</pdfaProperty:category>'."\n";
        $xmp .= "\t\t\t\t\t\t\t\t\t".'<pdfaProperty:description>Adobe PDF Schema</pdfaProperty:description>'."\n";
        $xmp .= "\t\t\t\t\t\t\t\t\t".'<pdfaProperty:name>InstanceID</pdfaProperty:name>'."\n";
        $xmp .= "\t\t\t\t\t\t\t\t\t".'<pdfaProperty:valueType>URI</pdfaProperty:valueType>'."\n";
        $xmp .= "\t\t\t\t\t\t\t\t".'</rdf:li>'."\n";
        $xmp .= "\t\t\t\t\t\t\t".'</rdf:Seq>'."\n";
        $xmp .= "\t\t\t\t\t\t".'</pdfaSchema:property>'."\n";
        $xmp .= "\t\t\t\t\t".'</rdf:li>'."\n";
        $xmp .= "\t\t\t\t\t".'<rdf:li rdf:parseType="Resource">'."\n";
        $xmp .= "\t\t\t\t\t\t".'<pdfaSchema:namespaceURI>http://ns.adobe.com/xap/1.0/mm/</pdfaSchema:namespaceURI>'."\n";
        $xmp .= "\t\t\t\t\t\t".'<pdfaSchema:prefix>xmpMM</pdfaSchema:prefix>'."\n";
        $xmp .= "\t\t\t\t\t\t".'<pdfaSchema:schema>XMP Media Management Schema</pdfaSchema:schema>'."\n";
        $xmp .= "\t\t\t\t\t\t".'<pdfaSchema:property>'."\n";
        $xmp .= "\t\t\t\t\t\t\t".'<rdf:Seq>'."\n";
        $xmp .= "\t\t\t\t\t\t\t\t".'<rdf:li rdf:parseType="Resource">'."\n";
        $xmp .= "\t\t\t\t\t\t\t\t\t".'<pdfaProperty:category>internal</pdfaProperty:category>'."\n";
        $xmp .= "\t\t\t\t\t\t\t\t\t".'<pdfaProperty:description>UUID based identifier for specific incarnation of a document</pdfaProperty:description>'."\n";
        $xmp .= "\t\t\t\t\t\t\t\t\t".'<pdfaProperty:name>InstanceID</pdfaProperty:name>'."\n";
        $xmp .= "\t\t\t\t\t\t\t\t\t".'<pdfaProperty:valueType>URI</pdfaProperty:valueType>'."\n";
        $xmp .= "\t\t\t\t\t\t\t\t".'</rdf:li>'."\n";
        $xmp .= "\t\t\t\t\t\t\t".'</rdf:Seq>'."\n";
        $xmp .= "\t\t\t\t\t\t".'</pdfaSchema:property>'."\n";
        $xmp .= "\t\t\t\t\t".'</rdf:li>'."\n";
        $xmp .= "\t\t\t\t\t".'<rdf:li rdf:parseType="Resource">'."\n";
        $xmp .= "\t\t\t\t\t\t".'<pdfaSchema:namespaceURI>http://www.aiim.org/pdfa/ns/id/</pdfaSchema:namespaceURI>'."\n";
        $xmp .= "\t\t\t\t\t\t".'<pdfaSchema:prefix>pdfaid</pdfaSchema:prefix>'."\n";
        $xmp .= "\t\t\t\t\t\t".'<pdfaSchema:schema>PDF/A ID Schema</pdfaSchema:schema>'."\n";
        $xmp .= "\t\t\t\t\t\t".'<pdfaSchema:property>'."\n";
        $xmp .= "\t\t\t\t\t\t\t".'<rdf:Seq>'."\n";
        $xmp .= "\t\t\t\t\t\t\t\t".'<rdf:li rdf:parseType="Resource">'."\n";
        $xmp .= "\t\t\t\t\t\t\t\t\t".'<pdfaProperty:category>internal</pdfaProperty:category>'."\n";
        $xmp .= "\t\t\t\t\custom_xmpt\t\t\t\t".'<pdfaProperty:description>Part of PDF/A standard</pdfaProperty:description>'."\n";
        $xmp .= "\t\t\t\t\t\t\t\t\t".'<pdfaProperty:name>part</pdfaProperty:name>'."\n";
        $xmp .= "\t\t\t\t\t\t\t\t\t".'<pdfaProperty:valueType>Integer</pdfaProperty:valueType>'."\n";
        $xmp .= "\t\t\t\t\t\t\t\t".'</rdf:li>'."\n";
        $xmp .= "\t\t\t\t\t\t\t\t".'<rdf:li rdf:parseType="Resource">'."\n";
        $xmp .= "\t\t\t\t\t\t\t\t\t".'<pdfaProperty:category>internal</pdfaProperty:category>'."\n";
        $xmp .= "\t\t\t\t\t\t\t\t\t".'<pdfaProperty:description>Amendment of PDF/A standard</pdfaProperty:description>'."\n";
        $xmp .= "\t\t\t\t\t\t\t\t\t".'<pdfaProperty:name>amd</pdfaProperty:name>'."\n";
        $xmp .= "\t\t\t\t\t\t\t\t\t".'<pdfaProperty:valueType>Text</pdfaProperty:valueType>'."\n";
        $xmp .= "\t\t\t\t\t\t\t\t".'</rdf:li>'."\n";
        $xmp .= "\t\t\t\t\t\t\t\t".'<rdf:li rdf:parseType="Resource">'."\n";
        $xmp .= "\t\t\t\t\t\t\t\t\t".'<pdfaProperty:category>internal</pdfaProperty:category>'."\n";
        $xmp .= "\t\t\t\t\t\t\t\t\t".'<pdfaProperty:description>Conformance level of PDF/A standard</pdfaProperty:description>'."\n";
        $xmp .= "\t\t\t\t\t\t\t\t\t".'<pdfaProperty:name>conformance</pdfaProperty:name>'."\n";
        $xmp .= "\t\t\t\t\t\t\t\t\t".'<pdfaProperty:valueType>Text</pdfaProperty:valueType>'."\n";
        $xmp .= "\t\t\t\t\t\t\t\t".'</rdf:li>'."\n";
        $xmp .= "\t\t\t\t\t\t\t".'</rdf:Seq>'."\n";
        $xmp .= "\t\t\t\t\t\t".'</pdfaSchema:property>'."\n";
        $xmp .= "\t\t\t\t\t".'</rdf:li>'."\n";
        $xmp .= "\t\t\t\t".'</rdf:Bag>'."\n";
        $xmp .= "\t\t\t".'</pdfaExtension:schemas>'."\n";
        $xmp .= "\t\t".'</rdf:Description>'."\n";
        $xmp .= $this->custom_xmp_rdf;
        $xmp .= "\t".'</rdf:RDF>'."\n";
        $xmp .= '</x:xmpmeta>'."\n";
        $xmp .= '<?xpacket end="w"?>';
        $out = '<< /Type /Metadata /Subtype /XML /Length '.strlen($xmp).' >> stream'."\n".$xmp."\n".'endstream'."\n".'endobj';
        // restore previous isunicode value
        $this->isunicode = $prev_isunicode;
        $this->encrypted = $prev_encrypted;
        $this->_out($out);

        return $oid;
    }

    /**
     * Throw an exception or print an error message and die if the K_TCPDF_PARSER_THROW_EXCEPTION_ERROR constant is set to true.
     * @param $msg (string) The error message
     * @throws Exception
     * @public
     * @since 1.0
     */
    public function Error($msg)
    {
        // unset all class variables
        $this->_destroy(true);
        throw new Exception('TCPDF ERROR: '.$msg);
    }
}
