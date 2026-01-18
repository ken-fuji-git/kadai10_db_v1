# ①課題名
クレームお嬢様（ログイン、ユーザー管理対応）

## ②課題内容（どんな作品か）
- クレームを登録、一覧管理するアプリです。
- クレーム内容のニュアンスをお嬢様口調に変更できるようにしました。
- お嬢様以外にもあかちゃん、チンパンジー、ブタに変更できます。

## ③アプリのデプロイURL
- ログイン画面
https://fujiwarakenta.sakura.ne.jp/kadai10_db_v1/login.php

~~~
【ログイン情報】

▼管理者（更新削除権限あり）
ログインID：test1
パスワード：test1

▼一般（更新削除権限なし）
ログインID：test2
パスワード：test2
~~~

## 更新箇所
- ログイン画面、ユーザー管理機能を追加しました。
- flashの機能を使って、以下のシーンで補助の一文を表示させるようにしました。
~~~
   ログイン直後：成功した旨のメッセージと管理者権限があるかどうかの表示
   ユーザー登録後：成功した旨のメッセージ
~~~

## 工夫した点・こだわった点
- クレームをお嬢様言葉にして運用者の心の負担を軽減する目的で作りました。
- 登録データ自体には触らず、一時的にセッションで変換テキストを保持します。
- フラッシュの機能を使って、登録や入力エラー時にテキストで表示できるようになったので親切なUIになっていると思います。

## flashについて
- flash：次の1回だけ表示するための一時メッセージ/一時データ
- redirect すると、POSTの結果（エラー・成功・入力値）はそのままでは次ページに渡らない！
　そこで flash を使って
	•	成功メッセージ（「更新しました」等）
	•	エラーメッセージ（「必須です」等）
	•	入力復元データ（old）
　を redirectをまたいで1回だけ渡すのが目的！！
- **セッション（$_SESSION）**に入れておき、次のページ表示（GET）で取り出したら 即削除！
~~~
function set_flash($key, $value){
    $_SESSION['flash'][$key] = $value;
}

function get_flash($key, $default = null){
    if (!isset($_SESSION['flash'][$key])) return $default;
    $v = $_SESSION['flash'][$key];  //取り出してから
    unset($_SESSION['flash'][$key]);//削除
    return $v;
}

//update.php の例（バリデーションNGの時）
set_flash('old', [
   'name' => ($_POST['name'] ?? ''),
   'age' => ($_POST['age'] ?? ''),
   'gender' => ($_POST['gender'] ?? ''),
   'email' => ($_POST['email'] ?? ''),
   'complaint_text' => ($_POST['complaint_text'] ?? ''),
]);

//成功時
set_flash('success', '更新しました。');
redirect('list.php');

//ID不正
//$_POST['id'] が送られていればそれを使う送られていなければ 0 を使う（＝不正扱いにできる）
$id = ($_POST['id'] ?? 0);
if ($id <= 0) {
  set_flash('error', 'IDが不正です。');
  redirect('list.php');
}
~~~

## 1)登録フロー（index → insert → DB → list）
~~~
[ユーザー] 
   │ ① フォーム入力（名前/年齢/性別/Email/クレーム内容）
   ▼
[index.php]
   │ ② POST で送信
   ▼
[insert.php]
   │ ③ validate_complaint_input($_POST)
   │    ├─ NG → flash(error + old) に保存
   │    │        redirect(index.php)
   │    └─ OK → DBへINSERT（原文を complaint_text に保存）
   ▼
[MySQL bio_list]
   │ ④ INSERT完了
   ▼
redirect(list.php)
   ▼
[list.php] ⑤ 一覧表示（原文はそのまま表示）
~~~
 - DBに保存されるのは原文（complaint_text）だけ

## 2)一覧表示フロー（list.php GET：表示だけ）
~~~
[ブラウザ GET list.php]
   ▼
[list.php]
   │ ① funcs.php を読み込む（session_start / DB接続 / 変換関数など）
   │
   │ ② role_map（セッション）を用意
   │    └─ $_SESSION['role_map'] がなければ [] に初期化
   │
   │ ③ DBから一覧取得
   │    └─ SELECT * FROM bio_list ORDER BY id DESC
   │
   │ ④ 行ごとに処理
   │    ├─ id を取得
   │    ├─ role_key を決定（role_map[id] があればそれ、なければ none）
   │    ├─ complaint_original = complaint_text（原文）
   │    ├─ complaint_show = convert_by_role(role_key, complaint_original)
   │    └─ row_class を決定
   │         ├─ none → "record"
   │         └─ ojosama → "record role-ojosama"（など）
   │
   └ ⑤ HTML出力
        ├─ complaint_show を表示（nl2br + h）
        ├─ ドロップダウン + 適用ボタン（POST用）
        └─ class に応じて CSS が背景（透かし）を描画
~~~
- role_map はセッションで！

## 3) 役割語「適用」フロー（list.php POST：セッションを書き換える）
~~~
[ユーザー] ① 行のドロップダウンで役割語を選択
   │
   │ ② 「適用」ボタン押下（POST）
   ▼
[list.php]（POSTで受ける）
   │ ③ action を確認
   │    └─ action === "apply_role"
   │
   │ ④ id と role_key を取得
   │
   │ ⑤ ホワイトリスト確認（role_styles() に存在するか）
   │
   │ ⑥ セッション更新（DBは触らない）
   │    ├─ role_key === "none" → unset(role_map[id])
   │    └─ それ以外           → role_map[id] = role_key
   │
   └ ⑦ redirect(list.php)   ← PRG（Post/Redirect/Get）
        ▼
       [list.php GET]
        └ ⑧ 2) の一覧表示フローで反映される
~~~
- 「適用」→ セッションの role_map を更新

## 4) 「すべて未適用に戻す」フロー（list.php POST）
~~~
[ユーザー] ① 「すべて未適用に戻す」押下（POST）
   ▼
[list.php]
   │ ② action === "reset_all"
   │ ③ role_map = []（セッション状態を全解除）
   └ ④ redirect(list.php)
        ▼
       [list.php GET] → 全行が未適用表示に戻る
~~~

## 5) お嬢様のバラ透かし背景が出る流れ（CSS）
~~~
[list.php で row_class に role-ojosama が付く]
   ▼
<div class="record role-ojosama"> ... </div>
   ▼
[CSS]
.record.role-ojosama::before が発火
   ├─ 疑似要素 ::before を全面に生成（inset:0）
   ├─ 背景画像 rose.svg を repeat
   └─ opacity で薄く透かす（クリックは邪魔しない）
   ▼
「お嬢様モードの行」だけ背景が変わる
~~~

## 全体まとめ（1枚で俯瞰）
~~~
DB（原文）: bio_list.complaint_text  ← 変わらない（唯一の正）
     ▲
     │ SELECT（list表示のたびに読む）
     │
list.php（GET）: 役割語の状態を session(role_map) から読み、
                  convert_by_role() で表示だけ変える
     ▲
     │ POST（適用/解除）で role_map を更新（DB更新なし）
     │
ユーザー操作: ドロップダウン選択 → 適用
~~~

## 難しかった点・次回トライしたいこと（又は機能）
- 置換だけだと限界があるので、役割語のAI対応版を作ってみたいです。

## フリー項目（感想、シェアしたいこと等なんでも）
引き続きがんばります！