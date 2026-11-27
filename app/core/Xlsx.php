<?php
/** Minimal .xlsx writer (needs PHP's ZipArchive, bundled with XAMPP). Falls back to an Excel-readable HTML table. */
final class Xlsx
{
    private static function col(int $i): string { $s = ''; for ($i++; $i > 0; $i = intdiv($i - 1, 26)) $s = chr(65 + ($i - 1) % 26) . $s; return $s; }

    public static function download(string $filename, string $title, array $cols, array $rows): void
    {
        while (ob_get_level()) ob_end_clean();
        if (!class_exists('ZipArchive')) {
            header('Content-Type: application/vnd.ms-excel');
            header('Content-Disposition: attachment; filename="' . preg_replace('/\.xlsx$/', '.xls', $filename) . '"');
            echo '<html><meta charset="utf-8"><body><table border="1"><tr>';
            foreach ($cols as $c) echo '<th>' . e($c) . '</th>';
            echo '</tr>';
            foreach ($rows as $r) { echo '<tr>'; foreach ($r as $v) echo '<td>' . e($v) . '</td>'; echo '</tr>'; }
            echo '</table></body></html>';
            exit;
        }
        $x = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?><worksheet xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main"><sheetData>';
        $all = array_merge([$cols], $rows);
        foreach ($all as $ri => $r) {
            $x .= '<row r="' . ($ri + 1) . '">';
            foreach (array_values($r) as $ci => $v) {
                $ref = self::col($ci) . ($ri + 1);
                $style = $ri === 0 ? ' s="1"' : '';
                if ($ri > 0 && (is_int($v) || is_float($v) || (is_string($v) && preg_match('/^-?[1-9]\d{0,8}(\.\d+)?$|^-?0(\.\d+)?$/', $v))))
                    $x .= '<c r="' . $ref . '"' . $style . '><v>' . $v . '</v></c>';
                else
                    $x .= '<c r="' . $ref . '" t="inlineStr"' . $style . '><is><t xml:space="preserve">' . htmlspecialchars((string) $v, ENT_XML1 | ENT_QUOTES, 'UTF-8') . '</t></is></c>';
            }
            $x .= '</row>';
        }
        $x .= '</sheetData></worksheet>';
        $tmp = tempnam(sys_get_temp_dir(), 'xl');
        $z = new ZipArchive();
        $z->open($tmp, ZipArchive::OVERWRITE);
        $z->addFromString('[Content_Types].xml', '<?xml version="1.0" encoding="UTF-8"?><Types xmlns="http://schemas.openxmlformats.org/package/2006/content-types"><Default Extension="rels" ContentType="application/vnd.openxmlformats-package.relationships+xml"/><Default Extension="xml" ContentType="application/xml"/><Override PartName="/xl/workbook.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.sheet.main+xml"/><Override PartName="/xl/worksheets/sheet1.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.worksheet+xml"/><Override PartName="/xl/styles.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.styles+xml"/></Types>');
        $z->addFromString('_rels/.rels', '<?xml version="1.0" encoding="UTF-8"?><Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships"><Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/officeDocument" Target="xl/workbook.xml"/></Relationships>');
        $sheet = htmlspecialchars(mb_substr(preg_replace('/[\\\\\/\?\*\[\]:]/', ' ', $title), 0, 30), ENT_XML1);
        $z->addFromString('xl/workbook.xml', '<?xml version="1.0" encoding="UTF-8"?><workbook xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main" xmlns:r="http://schemas.openxmlformats.org/officeDocument/2006/relationships"><sheets><sheet name="' . $sheet . '" sheetId="1" r:id="rId1"/></sheets></workbook>');
        $z->addFromString('xl/_rels/workbook.xml.rels', '<?xml version="1.0" encoding="UTF-8"?><Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships"><Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/worksheet" Target="worksheets/sheet1.xml"/><Relationship Id="rId2" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/styles" Target="styles.xml"/></Relationships>');
        $z->addFromString('xl/styles.xml', '<?xml version="1.0" encoding="UTF-8"?><styleSheet xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main"><fonts count="2"><font><sz val="11"/><name val="Calibri"/></font><font><b/><color rgb="FFFFFFFF"/><sz val="11"/><name val="Calibri"/></font></fonts><fills count="3"><fill><patternFill patternType="none"/></fill><fill><patternFill patternType="gray125"/></fill><fill><patternFill patternType="solid"><fgColor rgb="FF873D00"/></patternFill></fill></fills><borders count="1"><border/></borders><cellStyleXfs count="1"><xf/></cellStyleXfs><cellXfs count="2"><xf/><xf fontId="1" fillId="2" applyFont="1" applyFill="1"/></cellXfs></styleSheet>');
        $z->addFromString('xl/worksheets/sheet1.xml', $x);
        $z->close();
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header('Content-Length: ' . filesize($tmp));
        readfile($tmp);
        unlink($tmp);
        exit;
    }
}
