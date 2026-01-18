<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="styles.css">
    <title>ログイン画面</title>
</head>
<body>

<header>
    <nav class="navbar navbar-default">ログイン画面</nav>
</header>

<form action="login_act.php" method="post">
    <div class="form-group">
        <label for="lid">ログインID</label>
        <!-- required属性で必須入力にする -->
        <input type="text" class="form-control" id="lid" name="lid" required>
    </div>
    <div class="form-group">
        <label for="lpw">パスワード</label>
        <!-- required属性で必須入力にする -->
        <input type="password" class="form-control" id="lpw" name="lpw" required>
    </div>
    <button type="submit" class="btn btn-primary">ログイン</button>
</form>

</body>
</html>