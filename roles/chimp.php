<?php
// roles/chimp.php
declare(strict_types=1);

function convert_to_chimp(string $text): string
{
    $t = str_replace(["\r\n", "\r"], "\n", $text);
    $t = trim($t);
    if ($t === '') return $t;

    // “ネタ”語彙（やり過ぎない）
    $t = preg_replace('/返金/u', '返金（🍌バナナ）', $t);

    // 句点のたびに合いの手
    $t = preg_replace('/。/u', '。ウッキッキー。', $t);

    // 先頭
    if (!preg_match('/^(ウッキ|キー)/u', $t)) {
        $t = "ウッキッキー！ {$t}";
    }


    $t .= '🙉';
    return $t;
}