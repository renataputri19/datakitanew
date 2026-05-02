<?php
$file = 'd:/bps renata kerja/2024/project stat sektoral website/Taylor-Swift-Web-Project-main/datakitanew/26.04.23 SE2026-L.UB.pdf';
$text = file_get_contents($file);
// Extract readable text from PDF BT/ET blocks
preg_match_all('/BT(.*?)ET/s', $text, $matches);
$out = '';
foreach ($matches[1] as $m) {
    preg_match_all('/\((.*?)\)\s*Tj/s', $m, $tj);
    foreach ($tj[1] as $t) {
        $out .= $t . ' ';
    }
    preg_match_all('/\[(.*?)\]\s*TJ/s', $m, $tjArr);
    foreach ($tjArr[1] as $t) {
        preg_match_all('/\((.*?)\)/s', $t, $parts);
        foreach ($parts[1] as $p) {
            $out .= $p;
        }
        $out .= ' ';
    }
}
// Clean up escaped characters
$out = str_replace(['\\(', '\\)', '\\\\'], ['(', ')', '\\'], $out);
echo $out;
