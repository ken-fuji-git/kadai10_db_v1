<?php
// update.php
declare(strict_types=1);

require_once __DIR__ . '/funcs.php';
sschk();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {//POST以外は拒否
  http_response_code(405);
  exit('Method Not Allowed');
}

$id = (int)($_POST['id'] ?? 0);//必ず整数にして、0以下なら弾く
if ($id <= 0) {
  set_flash('error', 'IDが不正です。');
  redirect('list.php');
}

// 入力チェック（既存のバリデーションを流用）
$errors = validate_complaint_input($_POST);
if (!empty($errors)) {// エラーがあれば
  set_flash('error', implode("\n", $errors));
  // 入力値を戻す（idは除外してもよいが、ここでは残してOK）
  set_flash('old', [
    'name' => (string)($_POST['name'] ?? ''),
    'age' => (string)($_POST['age'] ?? ''),
    'gender' => (string)($_POST['gender'] ?? ''),
    'email' => (string)($_POST['email'] ?? ''),
    'complaint_text' => (string)($_POST['complaint_text'] ?? ''),
  ]);
  redirect('edit.php?id=' . $id);
}

// 正規化
$name = trim((string)$_POST['name']);
$age_raw = trim((string)($_POST['age'] ?? ''));
$age = ($age_raw === '') ? null : (int)$age_raw; // NULLを許可
$gender = trim((string)($_POST['gender'] ?? ''));
$email = trim((string)$_POST['email']);
$complaint_text = (string)($_POST['complaint_text'] ?? '');

$pdo = db_conn();
$sql = 'UPDATE bio_list
        SET name = :name,
            age = :age,
            gender = :gender,
            email = :email,
            complaint_text = :complaint_text
        WHERE id = :id';
$stmt = $pdo->prepare($sql);
$stmt->bindValue(':name', $name, PDO::PARAM_STR);

// age: null許可
if ($age === null) {
  $stmt->bindValue(':age', null, PDO::PARAM_NULL);
} else {
  $stmt->bindValue(':age', $age, PDO::PARAM_INT);
}

// gender: 空ならNULL運用にする（テーブル定義がNULL許可なら推奨）
if ($gender === '') {
  $stmt->bindValue(':gender', null, PDO::PARAM_NULL);
} else {
  $stmt->bindValue(':gender', $gender, PDO::PARAM_STR);
}

$stmt->bindValue(':email', $email, PDO::PARAM_STR);
$stmt->bindValue(':complaint_text', $complaint_text, PDO::PARAM_STR);
$stmt->bindValue(':id', $id, PDO::PARAM_INT);

$stmt->execute();

set_flash('success', '更新しました。');
redirect('list.php');