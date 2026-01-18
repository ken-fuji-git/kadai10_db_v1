<?php
// roles/pig.php
declare(strict_types=1);

function convert_to_pig(string $text): string
{
    $t = str_replace(["\r\n", "\r"], "\n", $text);
    $t = trim($t);
    if ($t === '') return $t;

    $t = preg_replace('/。/u', '。ブヒ。', $t);

    if (!preg_match('/^(ブヒ|ブーブー)/u', $t)) {
        $t = "ブーブー。{$t}";
    }

    $t .= '🐷';
    return $t;
}