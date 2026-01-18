<?php
// list.php
require_once __DIR__ . '/funcs.php';
sschk();

$roles = role_styles();

// セッション内の適用状態（id => role_key）
if (!isset($_SESSION['role_map']) || !is_array($_SESSION['role_map'])) {
  $_SESSION['role_map'] = [];
}
$role_map = &$_SESSION['role_map'];

/**
 * POSTで役割語の適用状態を更新
 */
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $action = (string)($_POST['action'] ?? '');

  if ($action === 'apply_role') {
    $id = (int)($_POST['id'] ?? 0);
    $role_key = (string)($_POST['role_key'] ?? 'none');

    // role_key のホワイトリスト
    if ($id > 0 && array_key_exists($role_key, $roles)) {
      if ($role_key === 'none') {
        unset($role_map[$id]); // 未適用は状態を消す
      } else {
        $role_map[$id] = $role_key;
      }
    }
    redirect('list.php');
  }

  if ($action === 'reset_all') {
    $role_map = [];
    redirect('list.php');
  }
}

// DB取得
$pdo = db_conn();
$stmt = $pdo->query("SELECT * FROM bio_list ORDER BY id DESC");
$rows = $stmt->fetchAll();
$gender_options = gender_options();
?>
<!doctype html>
<html lang="ja">

<head>
  <meta charset="utf-8">
  <title>クレーム一覧</title>
  <link rel="stylesheet" href="css/style.css">

</head>

<body>
  <div class="wrap">
    <header>
      <div>
        <small><?php echo $_SESSION["name"]; ?>さんようこそ</small>
        <h1 style="margin:0;">クレーム一覧</h1>
        <small>役割語の適用はセッション中のみ維持され、DBの原文は変更しません。</small>
      </div>
      <div class="top-actions">
        <form method="post" style="margin:0;">
          <input type="hidden" name="action" value="reset_all">
          <button type="submit">すべて未適用に戻す</button>
        </form>
        <?php include("menu.php"); ?>
      </div>
    </header>

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

    <div class="list">
      <?php foreach ($rows as $r): ?>
        <?php
        $id = (int)$r['id'];
        $role_key = $role_map[$id] ?? 'none';

        $complaint_original = (string)($r['complaint_text'] ?? '');
        $complaint_show = convert_by_role($role_key, $complaint_original);

        $row_class = 'record';
        if ($role_key !== 'none') {
          $row_class .= ' role-' . $role_key; // 例: role-ojosama, role-baby...
        }

        // $row_class = ($role_key === 'ojosama') ? 'record ojosama' : 'record';
        ?>

        <div class="record-row">
          <!-- 左：カード本体 -->
          <div class="<?php echo h($row_class); ?>">
            <div class="content">
              <div class="meta">
                <span class="badge">ID: <?php echo h((string)$id); ?></span>
                <span class="badge">名前: <?php echo h((string)$r['name']); ?></span>
                <span class="badge">年齢: <?php echo h($r['age'] === null ? '' : (string)$r['age']); ?></span>
                <span class="badge">性別: <?php
                                        $g = (string)($r['gender'] ?? '');
                                        echo h($g === '' ? '' : ($gender_options[$g] ?? $g));
                                        ?></span>
                <span class="badge">email: <?php echo h((string)$r['email']); ?></span>
                <span class="badge">作成: <?php echo h((string)$r['created_at']); ?></span>

                <?php if ($role_key !== 'none'): ?>
                  <span class="badge role"><?php echo h($roles[$role_key]['label']); ?>モード</span>
                <?php endif; ?>
              </div>

              <div class="complaint">
                <?php echo nl2br(h($complaint_show)); ?>
              </div>

              <div class="ctrl">

                <form method="post" style="margin:0; display:flex; gap:10px; align-items:center;">
                  <input type="hidden" name="action" value="apply_role">
                  <input type="hidden" name="id" value="<?php echo h((string)$id); ?>">

                  <select name="role_key">
                    <?php foreach ($roles as $k => $info): ?>
                      <option value="<?php echo h($k); ?>" <?php echo ($role_key === $k ? 'selected' : ''); ?>>
                        <?php echo h($info['label']); ?>
                      </option>
                    <?php endforeach; ?>
                  </select>

                  <button type="submit">適用</button>
                </form>

              </div>
            </div>
          </div>

          <!-- 右：枠外の操作（編集・削除） -->
          <?php if ($_SESSION["kanri_flg"] == 1) { ?>
            <div class="record-side">
              <a class="side-btn" href="edit.php?id=<?php echo h((string)$id); ?>">編集</a>

              <form method="post" action="delete.php" style="margin:0;">
                <input type="hidden" name="id" value="<?php echo h((string)$id); ?>">
                <button class="side-btn side-btn--danger"
                  type="submit"
                  onclick="return confirm('本当に削除しますか？（ID: <?php echo h((string)$id); ?>）');">
                  削除
                </button>
              </form>
            </div>
          <?php } ?>


        </div>

      <?php endforeach; ?>
    </div>
  </div>
</body>

</html>