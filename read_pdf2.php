<?php
$file = 'd:/bps renata kerja/2024/project stat sektoral website/Taylor-Swift-Web-Project-main/datakitanew/26.04.23 SE2026-L.UB.pdf';
$data = file_get_contents($file);

// Find all FlateDecode streams and decompress them
preg_match_all('/<<[^>]*\/Filter\/FlateDecode[^>]*>>[\r\n]+stream[\r\n]+(.*?)[\r\n]+endstream/s', $data, $matches, PREG_OFFSET_CAPTURE);

$allText = '';
foreach ($matches[1] as $i => $m) {
    $compressed = $m[0];
    $decompressed = @gzuncompress($compressed);
    if ($decompressed === false) {
        $decompressed = @gzinflate($compressed);
    }
    if ($decompressed === false) {
        $decompressed = @gzdecode($compressed);
    }
    if ($decompressed) {
        // Extract text from PDF operators
        // Look for text between ( and ) before Tj, TJ
        preg_match_all('/\(([^)]*)\)\s*Tj/u', $decompressed, $tj);
        foreach ($tj[1] as $t) {
            $allText .= $t . ' ';
        }
        preg_match_all('/\[([^\]]*)\]\s*TJ/u', $decompressed, $tjArr);
        foreach ($tjArr[1] as $t) {
            preg_match_all('/\(([^)]*)\)/u', $t, $parts);
            foreach ($parts[1] as $p) {
                $allText .= $p;
            }
            $allText .= ' ';
        }
        // Also try BT...ET blocks
        preg_match_all('/BT(.*?)ET/s', $decompressed, $btBlocks);
        foreach ($btBlocks[1] as $block) {
            preg_match_all('/\(([^)]*)\)\s*Tj/u', $block, $tj2);
            foreach ($tj2[1] as $t) {
                $allText .= $t . ' ';
            }
        }
    }
}

echo $allText;
