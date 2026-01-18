<?php
// edit.php
declare(strict_types=1);

require_once __DIR__ . '/funcs.php';
sschk();

$id = (int)($_GET['id'] ?? 0);
if ($id <= 0) {
    set_flash('error', 'IDが不正です。');
    redirect('list.php');
}

$pdo = db_conn();
$stmt = $pdo->prepare('SELECT * FROM bio_list WHERE id = :id');
$stmt->bindValue(':id', $id, PDO::PARAM_INT);
$stmt->execute();
$row = $stmt->fetch();

if (!$row) {
    set_flash('error', '対象データが見つかりません。');
    redirect('list.php');
}

$gender_options = gender_options();

// 直前のバリデーションエラーで戻ってきた場合、入力を優先して表示
$old = get_flash('old', []);
if (is_array($old) && !empty($old)) {
    $row = array_merge($row, $old);
}
$error = get_flash('error', '');
?>

<!doctype html>
<html lang="ja">

<head>
    <meta charset="utf-8">
    <title>クレーム更新</title>
    <link rel="stylesheet" href="css/style.css">
</head>

<body>
    <div class="wrap wrap--narrow">
        <header style="margin-bottom:12px;">
            <div>
                <h1 style="margin:0;">クレーム更新</h1>
                <small>ID: <?php echo h((string)$id); ?></small>
            </div>
            <div class="top-actions">
                <a href="list.php">一覧へ戻る</a>
            </div>
        </header>

        <?php if ($error !== ''): ?>
            <div class="error"><?php echo h($error); ?></div>
        <?php endif; ?>

        <form method="post" action="update.php">
            <input type="hidden" name="id" value="<?php echo h((string)$id); ?>">

            <label>名前（必須）</label>
            <input type="text" name="name" maxlength="32" value="<?php echo h((string)($row['name'] ?? '')); ?>">

            <div class="row">
                <div class="col">
                    <label>年齢（任意）</label>
                    <input type="number" name="age" min="0" max="999" value="<?php echo h((string)($row['age'] ?? '')); ?>">
                </div>
                <div class="col">
                    <label>性別（任意）</label>
                    <?php $g = (string)($row['gender'] ?? ''); ?>
                    <select name="gender">
                        <option value="">未選択</option>
                        <?php foreach ($gender_options as $k => $label): ?>
                            <option value="<?php echo h($k); ?>" <?php echo ($g === $k ? 'selected' : ''); ?>>
                                <?php echo h($label); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>

            <label>メール（必須）</label>
            <input type="text" name="email" maxlength="64" value="<?php echo h((string)($row['email'] ?? '')); ?>">

            <label>クレーム内容（必須）</label>
            <textarea name="complaint_text" maxlength="2000"><?php echo h((string)($row['complaint_text'] ?? '')); ?></textarea>

            <div class="actions">
                <button type="submit">更新する</button>
                <a href="list.php">キャンセル</a>
            </div>
        </form>
    </div>
</body>

</html>