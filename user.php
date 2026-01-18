<?php
session_start();
//※htdocsと同じ階層に「includes」を作成してfuncs.phpを入れましょう！
//include "../../includes/funcs.php";
include "funcs.php";
sschk();
?>

<!DOCTYPE html>
<html lang="ja">

<head>
  <meta charset="UTF-8">
  <title>USERデータ登録</title>
  <link href="css/bootstrap.min.css" rel="stylesheet">
  <style>
    div {
      padding: 10px;
      font-size: 16px;
    }
  </style>
</head>

<body>

  <!-- Head[Start] -->
  <header>
    <?php echo $_SESSION["name"]; ?>さん　
    <?php include("menu.php"); ?>
  </header>
  <!-- Head[End] -->

  <?php
  $success = get_flash('success', '');
  $error   = get_flash('error', '');
  ?>

  <?php if ($success !== ''): ?>
    <div class="notice"><?php echo h($success); ?></div>
  <?php endif; ?>
  <?php if ($error !== ''): ?>
    <div class="error"><?php echo h($error); ?></div>
  <?php endif; ?>

  <!-- Main[Start] -->
  <form method="post" action="user_insert.php">
    <div class="jumbotron">
      <fieldset>
        <legend>ユーザー登録</legend>
        <label>名前：<input type="text" name="name"></label><br>
        <label>Login ID：<input type="text" name="lid"></label><br>
        <label>Login PW<input type="text" name="lpw"></label><br>
        <label>管理FLG：
          一般<input type="radio" name="kanri_flg" value="0">　
          管理者<input type="radio" name="kanri_flg" value="1">　
          部外者<input type="radio" name="kanri_flg" value="2">　
        </label>
        <br>
        <!-- <label>退会FLG：<input type="text" name="life_flg"></label><br> -->
        <input type="submit" value="送信">
      </fieldset>
    </div>
  </form>
  <!-- Main[End] -->


</body>

</html>