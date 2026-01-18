<?php
// index.php
require_once __DIR__ . '/funcs.php';

$error = get_flash('error', '');
$old = get_flash('old', []);
$gender_options = gender_options();

function oldv(array $old, string $key): string
{
  return isset($old[$key]) ? (string)$old[$key] : '';
}
?>

<!doctype html>
<html lang="ja">

<head>
  <meta charset="utf-8">
  <title>クレーム登録</title>
  <link rel="stylesheet" href="css/style.css">


</head>

<body>
  <div class="wrap">
    <header>
      <div>
        <small><?php echo $_SESSION["name"]; ?>さんようこそ</small>
        <h1>クレーム登録</h1>
      </div>
      <?php include("menu.php"); ?>
    </header>

    <?php if ($error): ?>
      <div class="error"><?php echo h($error); ?></div>
    <?php endif; ?>

    <form method="post" action="insert.php">
      <label>名前（必須 / 32文字以内）</label>
      <input type="text" name="name" value="<?php echo h(oldv($old, 'name')); ?>" maxlength="32" required>

      <div class="row">
        <div class="col">
          <label>年齢（任意 / 0-999）</label>
          <input type="number" name="age" value="<?php echo h(oldv($old, 'age')); ?>" min="0" max="999">
        </div>
        <div class="col">
          <label>性別（任意 / 3候補）</label>
          <select name="gender">
            <option value="">未選択</option>
            <?php foreach ($gender_options as $k => $label): ?>
              <option value="<?php echo h($k); ?>" <?php echo (oldv($old, 'gender') === $k ? 'selected' : ''); ?>>
                <?php echo h($label); ?>
              </option>
            <?php endforeach; ?>
          </select>
        </div>
      </div>

      <label>メール（必須 / 64文字以内・形式チェックなし）</label>
      <input type="text" name="email" value="<?php echo h(oldv($old, 'email')); ?>" maxlength="64" required>

      <label>クレーム内容（必須 / 2000文字以内・改行OK）</label>
      <textarea name="complaint_text" maxlength="2000" required><?php echo h(oldv($old, 'complaint_text')); ?></textarea>
      <small>保存されるのは「原文」です。役割語の変換は一覧表示の一時的な表示切り替えです。</small>

      <div class="actions">
        <button type="submit">登録</button>
        <a href="list.php">一覧へ</a>
      </div>
    </form>
  </div>
</body>

</html>