<?php
//$_SESSION使うよ！
session_start();

//※htdocsと同じ階層に「includes」を作成してfuncs.phpを入れましょう！
//include "../../includes/funcs.php";
require_once __DIR__ . '/funcs.php';
sschk();

//1. POSTデータ取得


$name = trim((string)$_POST['name']);
$lid = trim((string)$_POST['lid']);
$lpw = trim((string)$_POST['lpw']);
$kanri_flg = (int)$_POST['kanri_flg'];
$kanri_flg = ($kanri_flg === '') ? 0 : (int)$_POST['kanri_flg'];
$lpw = password_hash($lpw, PASSWORD_DEFAULT);   //パスワードハッシュ化

// $name      = filter_input( INPUT_POST, "name" );
// $lid       = filter_input( INPUT_POST, "lid" );
// $lpw       = filter_input( INPUT_POST, "lpw" );
// $kanri_flg = filter_input( INPUT_POST, "kanri_flg" );
// $lpw       = password_hash($lpw, PASSWORD_DEFAULT);   //パスワードハッシュ化

//2. DB接続します
$pdo = db_conn();

//３．データ登録SQL作成
$sql = "INSERT INTO user_table(name,lid,lpw,kanri_flg,life_flg)VALUES(:name,:lid,:lpw,:kanri_flg,0)";
$stmt = $pdo->prepare($sql);
$stmt->bindValue(':name', $name, PDO::PARAM_STR); //Integer（数値の場合 PDO::PARAM_INT)
$stmt->bindValue(':lid', $lid, PDO::PARAM_STR); //Integer（数値の場合 PDO::PARAM_INT)
$stmt->bindValue(':lpw', $lpw, PDO::PARAM_STR); //Integer（数値の場合 PDO::PARAM_INT)::PARAM_STR); //Integer（数値の場合 PDO::PARAM_INT)
$stmt->bindValue(':kanri_flg', $kanri_flg, PDO::PARAM_INT); //Integer（数値の場合 PDO::PARAM_INT)
$status = $stmt->execute();

//４．データ登録処理後
if ($status == false) {
    sql_error($stmt);
} else {
    set_flash('success', 'ユーザー登録に成功しました。');
    redirect("user.php");
}
