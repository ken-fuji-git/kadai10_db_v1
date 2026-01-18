<?php

session_start();

require_once("funcs.php");
$lid = $_POST['lid'];;
$lpw = $_POST['lpw'];
$pdo = db_conn();

$stmt = $pdo->prepare("SELECT * FROM user_table WHERE lid=:lid AND life_flg=0");
$stmt->bindValue(':lid', $lid, PDO::PARAM_STR);
$status = $stmt->execute();

if ($status == false) {
    sql_error($stmt);
}

$val = $stmt->fetch();
//$count = $stmt->fetchColumn(); //SELECT COUNT(*)で取得したカラム数を取得することもできる！

$pw = password_verify($lpw, $val["lpw"]);

if ($pw) {
    $_SESSION["chk_ssid"] = session_id();
    $_SESSION["kanri_flg"] = $val["kanri_flg"];
    $_SESSION["name"] = $val["name"];
    if($val["kanri_flg"]==1){
        $kanri = "あなたは管理ユーザーです。";
    }
    set_flash('success', 'ログインしました。'.$kanri);
    redirect("list.php");
} else {
    redirect('login.php');
}

exit();
