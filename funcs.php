<?php
// funcs.php

//エラー表示
declare(strict_types=1);

// セッション（役割語の適用状態を保持）
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

//SQLエラー
function sql_error($stmt)
{
    //execute（SQL実行時にエラーがある場合）
    $error = $stmt->errorInfo();
    exit("SQLError:" . $error[2]);
}

/**
 * リダイレクト
 */
function redirect(string $path): void
{
    header("Location: {$path}");
    exit;
}

/**
 * フラッシュ（1回だけ表示するメッセージ）
 */
function set_flash(string $key, $value): void
{
    $_SESSION['flash'][$key] = $value;
}

function get_flash(string $key, $default = null)
{
    if (!isset($_SESSION['flash'][$key])) return $default;
    $v = $_SESSION['flash'][$key];
    unset($_SESSION['flash'][$key]);
    return $v;
}

// config.php にDB接続情報を置きます。
// GitHub公開を想定し、config.php はコミットしない運用！
$configPath = __DIR__ . '/config.php';
if (!file_exists($configPath)) {
    http_response_code(500);
    echo "config.php が見つかりません。config.php を作成してください。";
    exit;
}
require_once $configPath;

//役割語はフォルダ内へ。
require_once __DIR__ . '/roles/ojosama.php';
require_once __DIR__ . '/roles/baby.php';
require_once __DIR__ . '/roles/chimp.php';
require_once __DIR__ . '/roles/pig.php';

/**
 * DB接続（PDO）を返す
 * config.php に以下の定数を定義：
 *   DB_HOST, DB_NAME, DB_USER, DB_PASS
 */
function db_conn(): PDO
{
    $dsn = 'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=utf8mb4';

    try {
        $pdo = new PDO($dsn, DB_USER, DB_PASS, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
        ]);
        return $pdo;
    } catch (PDOException $e) {
        http_response_code(500);
        echo 'DB Connection Error: ' . htmlspecialchars($e->getMessage(), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
        exit;
    }
}

/**
 * XSS対策
 */
function h(string $s): string
{
    return htmlspecialchars($s, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

// function h(?string $s): string {
//     return htmlspecialchars($s ?? '', ENT_QUOTES, 'UTF-8');
// }



/**
 * 入力バリデーション（要件に合わせた最小）
 * - 仕様：name必須32 / age任意0-999 / gender任意3候補 / email必須64（形式チェックなし）
 * - complaint_text は「クレーム内容」。今回は必須にしています（不要なら必須チェックを外してください）
 */
function validate_complaint_input(array $post): array
{
    $errors = [];

    $name = trim((string)($post['name'] ?? ''));
    $age  = trim((string)($post['age'] ?? ''));
    $gender = trim((string)($post['gender'] ?? ''));
    $email  = trim((string)($post['email'] ?? ''));
    $complaint_text = (string)($post['complaint_text'] ?? ''); // 改行を残すためtrimしない（必要なら前後だけtrim）
    $complaint_text_trimmed = trim($complaint_text);

    // name
    if ($name === '') $errors[] = '名前（name）は必須です。';
    if (mb_strlen($name) > 32) $errors[] = '名前（name）は32文字以内にしてください。';

    // age（任意）
    if ($age !== '') {
        if (!preg_match('/^\d+$/', $age)) {
            $errors[] = '年齢（age）は数値のみです（空欄OK）。';
        } else {
            $age_i = (int)$age;
            if ($age_i < 0 || $age_i > 999) $errors[] = '年齢（age）は0〜999です。';
        }
    }

    // gender（任意・3候補固定）
    $gender_options = gender_options();
    if ($gender !== '' && !array_key_exists($gender, $gender_options)) {
        $errors[] = '性別（gender）の値が不正です。';
    }

    // email（必須・形式チェックなし）
    if ($email === '') $errors[] = 'メール（email）は必須です。';
    if (mb_strlen($email) > 64) $errors[] = 'メール（email）は64文字以内にしてください。';

    // complaint_text（クレーム内容）
    if ($complaint_text_trimmed === '') $errors[] = 'クレーム内容は必須です。';
    if (mb_strlen($complaint_text) > 2000) $errors[] = 'クレーム内容は2000文字以内にしてください。';

    return $errors;
}

/**
 * gender候補（3つ固定）
 */
function gender_options(): array
{
    return [
        'male' => '男性',
        'female' => '女性',
        'other' => 'その他',
    ];
}

/**
 * 役割語マスタ（v0）
 * 将来増やすときはここに追加 → list.php のドロップダウンも自動で増やせる。
 */
function role_styles(): array
{
    return [
        'none' => ['label' => '未適用'],
        'ojosama' => ['label' => 'お嬢様'],
        'baby' => ['label' => '赤ちゃん'],
        'chimp' => ['label' => 'チンパンジー'],
        'pig' => ['label' => 'ブタ'],
    ];
}

/**
 * 役割語変換の入口
 * - 今回は「クレーム内容だけ」変換する
 */
function convert_by_role(string $role_key, string $text): string
{
    if ($role_key === 'none') return $text;

    $map = [
        'ojosama' => 'convert_to_ojosama',
        'baby'    => 'convert_to_baby',
        'chimp'   => 'convert_to_chimp',
        'pig'     => 'convert_to_pig',
    ];

    if (!isset($map[$role_key])) return $text;

    $fn = $map[$role_key];
    if (!function_exists($fn)) return $text;

    return $fn($text);
}

//SessionCheck
function sschk()
{
    //isset()でchk_ssidがあるか？を確認
    //chk_ssidはログイン成功した時のsession_idが入っている
    if (!isset($_SESSION["chk_ssid"]) || $_SESSION["chk_ssid"] != session_id()) {
        exit("LOGIN ERROR");
    } else {
        session_regenerate_id(true); //新しいセッションIDを発行する
        $_SESSION["chk_ssid"] = session_id(); //新しいセッションIDを保存する
    }
}
