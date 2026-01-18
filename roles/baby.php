<?php
// roles/baby.php
declare(strict_types=1);

function convert_to_baby(string $text): string
{
    $t = str_replace(["\r\n", "\r"], "\n", $text);
    $t = trim($t);
    if ($t === '') return $t;

    // 強め表現を少しだけ丸める（最低限）
    $t = preg_replace('/(最悪|ありえない)/u', 'やだやだでちゅ', $t);
    $t = preg_replace('/(返金|交換)をお願いできますでしょうか/u', '$1してほしいでちゅ', $t);

    // 語尾（入れすぎると読めなくなるので控えめ）
    $t = strtr($t, [
        'です。' => 'でちゅ。',
        'ます。' => 'まちゅ。',
        'でした。' => 'だったでちゅ。',
        'ました。' => 'したでちゅ。',
    ]);

    // 先頭に軽く（毎文入れない）
    if (!preg_match('/^(ばぶー|えーん|うぇーん)/u', $t)) {
        $t = "ばぶー… {$t}";
    }

    $t .= '🍼';
    return $t;
}