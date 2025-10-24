<?php
// Minimal single-page PDF (A4) without external libs.
function pdf_esc(string $s): string {
    $s = str_replace(["\\", "(", ")"], ["\\\\", "\\(", "\\)"], $s);
    // Replace non-ASCII with question marks to stay simple
    $s = preg_replace('/[^\x20-\x7E]/', '?', $s);
    return $s;
}

function simple_pdf(string $title, array $lines): string {
    $content = "BT /F1 14 Tf 50 800 Td (" . pdf_esc($title) . ") Tj T* ";
    $content .= "/F1 11 Tf ";
    foreach ($lines as $line) {
        $content .= "(" . pdf_esc($line) . ") Tj T* ";
    }
    $content .= "ET";
    $len = strlen($content);

    $pdf = "%PDF-1.4\n";
    $xref = [];
    $offset = strlen($pdf);

    $add = function(string $obj) use (&$pdf, &$xref, &$offset) {
        $xref[] = $offset;
        $pdf .= (count($xref)) . " 0 obj\n" . $obj . "\nendobj\n";
        $offset = strlen($pdf);
    };

    // 1. Catalog
    $add("<< /Type /Catalog /Pages 2 0 R >>");
    // 2. Pages
    $add("<< /Type /Pages /Kids [3 0 R] /Count 1 >>");
    // 3. Page
    $add("<< /Type /Page /Parent 2 0 R /MediaBox [0 0 595 842] /Resources << /Font << /F1 5 0 R >> >> /Contents 4 0 R >>");
    // 4. Content stream
    $add("<< /Length $len >>\nstream\n$content\nendstream");
    // 5. Font
    $add("<< /Type /Font /Subtype /Type1 /BaseFont /Helvetica >>");

    $startxref = strlen($pdf);
    $pdf .= "xref\n0 " . (count($xref)+1) . "\n";
    $pdf .= "0000000000 65535 f \n";
    foreach ($xref as $off) {
        $pdf .= sprintf("%010d 00000 n \n", $off);
    }
    $pdf .= "trailer << /Size " . (count($xref)+1) . " /Root 1 0 R >>\nstartxref\n$startxref\n%%EOF";
    return $pdf;
}
