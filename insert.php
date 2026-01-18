<?php
// insert.php
require_once __DIR__ . '/funcs.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect('index.php');
}

$errors = validate_complaint_input($_POST);
if ($errors) {
    set_flash('error', implode("\n", $errors));
    // 入力保持（学習向け）
    set_flash('old', [
        'name' => (string)($_POST['name'] ?? ''),
        'age' => (string)($_POST['age'] ?? ''),
        'gender' => (string)($_POST['gender'] ?? ''),
        'email' => (string)($_POST['email'] ?? ''),
        'complaint_text' => (string)($_POST['complaint_text'] ?? ''),
    ]);
    redirect('index.php');
}


$age_raw = trim((string)($_POST['age'] ?? ''));
$age = ($age_raw === '') ? null : (int)$age_raw;
$gender = trim((string)($_POST['gender'] ?? ''));
$gender = ($gender === '') ? null : $gender;
$email = trim((string)$_POST['email']);
$complaint_text = (string)$_POST['complaint_text']; // 改行保持

$pdo = db_conn();

$sql = "INSERT INTO bio_list (name, age, gender, email, complaint_text, created_at)
        VALUES (:name, :age, :gender, :email, :complaint_text, NOW())";
$stmt = $pdo->prepare($sql);
$stmt->execute([
    ':name' => $name,
    ':age' => $age,
    ':gender' => $gender,
    ':email' => $email,
    ':complaint_text' => $complaint_text,
]);

redirect('list.php');