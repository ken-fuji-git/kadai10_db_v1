<?php
// delete.php

//エラーを厳密に扱う
declare(strict_types=1);

//includeではなくrequire_once（動かないと困る！）
//include("funcs.php");
require_once __DIR__ . '/funcs.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {//POST以外は拒否
  http_response_code(405);//405 Method Not Allowed
  exit('Method Not Allowed');//終了
}

//必ず整数にして、0以下なら弾く
$id = (int)($_POST['id'] ?? 0);
if ($id <= 0) {
  set_flash('error', 'IDが不正です。');//フラッシュメッセージをセット⭐️
  redirect('list.php');
}

$pdo = db_conn();
$sql = 'DELETE FROM bio_list WHERE id = :id';
$stmt = $pdo->prepare($sql);
$stmt->bindValue(':id', $id, PDO::PARAM_INT);
$stmt->execute();

// 役割語のセッションが残っていれば削除する
if (isset($_SESSION['role_map'][$id])) {//役割語が設定されているなら
  unset($_SESSION['role_map'][$id]);
}

set_flash('success', '削除しました。');//せっかくなのでフラッシュを使おう⭐️
redirect('list.php');