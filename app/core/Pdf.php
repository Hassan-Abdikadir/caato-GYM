<?php
/** Minimal dependency-free PDF writer (Helvetica, rectangles, one JPEG logo, tables). */
final class Pdf
{
    public float $w, $h;
    private array $pages = [];
    private string $cur = '';
    public float $y = 0;
    private ?array $img = null;

    public function __construct(bool $landscape = false)
    {
        $this->w = $landscape ? 841.89 : 595.28;
        $this->h = $landscape ? 595.28 : 841.89;
        $this->addPage();
    }

    public function addPage(): void
    {
        if ($this->cur !== '' || $this->pages) $this->pages[] = $this->cur;
        $this->cur = '';
        $this->y = $this->h - 40;
    }

    private static function rgb(string $hex): string
    {
        $hex = ltrim($hex, '#');
        return sprintf('%.3F %.3F %.3F', hexdec(substr($hex, 0, 2)) / 255, hexdec(substr($hex, 2, 2)) / 255, hexdec(substr($hex, 4, 2)) / 255);
    }

    private static function esc(string $s): string
    {
        $c = @iconv('UTF-8', 'windows-1252//TRANSLIT//IGNORE', $s);
        $s = $c === false ? preg_replace('/[^\x20-\x7E]/', '?', $s) : $c;
        return str_replace(['\\', '(', ')', "\r", "\n"], ['\\\\', '\\(', '\\)', '', ' '], $s);
    }

    public function text(float $x, float $y, string $s, float $size = 10, bool $bold = false, string $color = '#252525'): void
    {
        $this->cur .= sprintf("BT /%s %.1F Tf %s rg %.2F %.2F Td (%s) Tj ET\n", $bold ? 'F2' : 'F1', $size, self::rgb($color), $x, $y, self::esc($s));
    }

    public function rect(float $x, float $y, float $w, float $h, string $fill): void
    {
        $this->cur .= sprintf("%s rg %.2F %.2F %.2F %.2F re f\n", self::rgb($fill), $x, $y, $w, $h);
    }

    public function hline(float $x1, float $x2, float $y, string $color = '#DDDDDD'): void
    {
        $this->cur .= sprintf("%s RG 0.5 w %.2F %.2F m %.2F %.2F l S\n", self::rgb($color), $x1, $y, $x2, $y);
    }

    public function logo(string $jpgPath, float $x, float $y, float $w): void
    {
        $info = @getimagesize($jpgPath);
        if (!$info || $info[2] !== IMAGETYPE_JPEG) return;
        $this->img = ['data' => file_get_contents($jpgPath), 'w' => $info[0], 'h' => $info[1]];
        $h = $w * $info[1] / $info[0];
        $this->cur .= sprintf("q %.2F 0 0 %.2F %.2F %.2F cm /Im1 Do Q\n", $w, $h, $x, $y);
    }

    /** Branded page header used by every document. */
    public function header(string $title, string $subtitle = ''): void
    {
        $this->rect(0, $this->h - 70, $this->w, 70, '#873D00');
        $this->rect(0, $this->h - 74, $this->w, 4, '#C52B43');
        $this->rect(24, $this->h - 64, 52, 56, '#FFFFFF');
        $this->logo(ROOT . '/assets/img/logo.jpg', 26, $this->h - 62, 48);
        $this->text(90, $this->h - 34, 'CAATO FITNESS GYM', 17, true, '#FFFFFF');
        $this->text(90, $this->h - 52, $title . ($subtitle ? '  |  ' . $subtitle : ''), 10, false, '#FFFFFF');
        $this->y = $this->h - 98;
    }

    public function table(array $cols, array $rows, float $size = 8): void
    {
        $x0 = 28; $tw = $this->w - 56; $n = max(1, count($cols));
        $cw = $tw / $n; $maxc = max(4, (int) floor($cw / ($size * 0.52)));
        $head = function () use ($cols, $x0, $cw, $size) {
            $this->rect($x0 - 4, $this->y - 4, $this->w - 48, 16, '#873D00');
            foreach ($cols as $i => $c) $this->text($x0 + $i * $cw, $this->y, mb_strimwidth((string) $c, 0, 40, '.'), $size, true, '#FFFFFF');
            $this->y -= 18;
        };
        $head();
        $alt = false;
        foreach ($rows as $r) {
            if ($this->y < 50) { $this->footer(); $this->addPage(); $this->header('Report (continued)'); $head(); }
            if ($alt) $this->rect($x0 - 4, $this->y - 4, $this->w - 48, 14, '#F5F5F5');
            $alt = !$alt;
            foreach (array_values($r) as $i => $v) $this->text($x0 + $i * $cw, $this->y, mb_strimwidth((string) $v, 0, $maxc, '.'), $size);
            $this->y -= 14;
        }
        if (!$rows) $this->text($x0, $this->y, 'No records found for the selected filters.', 9);
    }

    public function footer(): void
    {
        $this->hline(28, $this->w - 28, 34);
        $this->text(28, 22, 'Generated ' . date('d M Y H:i') . ' - CAATO FITNESS GYM Management System', 7, false, '#777777');
    }

    public function output(string $filename, bool $download = true): void
    {
        $this->footer();
        $pages = array_merge($this->pages, [$this->cur]);
        $objs = [];
        $objs[1] = '<< /Type /Catalog /Pages 2 0 R >>';
        $kids = [];
        $objs[3] = '<< /Type /Font /Subtype /Type1 /BaseFont /Helvetica /Encoding /WinAnsiEncoding >>';
        $objs[4] = '<< /Type /Font /Subtype /Type1 /BaseFont /Helvetica-Bold /Encoding /WinAnsiEncoding >>';
        $next = 5; $imgRef = '';
        if ($this->img) {
            $objs[$next] = ['<< /Type /XObject /Subtype /Image /Width ' . $this->img['w'] . ' /Height ' . $this->img['h'] .
                ' /ColorSpace /DeviceRGB /BitsPerComponent 8 /Filter /DCTDecode /Length ' . strlen($this->img['data']) . ' >>', $this->img['data']];
            $imgRef = ' /XObject << /Im1 ' . $next . ' 0 R >>';
            $next++;
        }
        foreach ($pages as $content) {
            $cid = $next++; $pid = $next++;
            $objs[$cid] = ['<< /Length ' . strlen($content) . ' >>', $content];
            $objs[$pid] = '<< /Type /Page /Parent 2 0 R /MediaBox [0 0 ' . round($this->w, 2) . ' ' . round($this->h, 2) .
                '] /Resources << /Font << /F1 3 0 R /F2 4 0 R >>' . $imgRef . ' >> /Contents ' . $cid . ' 0 R >>';
            $kids[] = $pid . ' 0 R';
        }
        $objs[2] = '<< /Type /Pages /Kids [' . implode(' ', $kids) . '] /Count ' . count($kids) . ' >>';
        ksort($objs);
        $out = "%PDF-1.4\n"; $off = [];
        foreach ($objs as $id => $o) {
            $off[$id] = strlen($out);
            if (is_array($o)) $out .= "$id 0 obj\n{$o[0]}\nstream\n{$o[1]}\nendstream\nendobj\n";
            else $out .= "$id 0 obj\n$o\nendobj\n";
        }
        $xr = strlen($out); $cnt = count($objs) + 1;
        $out .= "xref\n0 $cnt\n0000000000 65535 f \n";
        foreach ($objs as $id => $_) $out .= sprintf("%010d 00000 n \n", $off[$id]);
        $out .= "trailer\n<< /Size $cnt /Root 1 0 R >>\nstartxref\n$xr\n%%EOF";
        while (ob_get_level()) ob_end_clean();
        header('Content-Type: application/pdf');
        header('Content-Disposition: ' . ($download ? 'attachment' : 'inline') . '; filename="' . $filename . '"');
        header('Content-Length: ' . strlen($out));
        echo $out;
        exit;
    }
}
