<?php
//共通関数・変数読み込み
require('function.php');

debug('「「「「「「「「「「「「「「「「「「「「');
debug('ログインページ');
debug('「「「「「「「「「「「「「「「「「「「「「「');
debugLogStart();

//ログイン認証
require('auth.php');

//post送信されていた場合
if (!empty($_POST)) {
  $email = $_POST['email'];
  $pass = $_POST['pass'];
  $pass_save = (!empty($_POST['pass_save'])) ? true : false;

  //バリデーションチェック
  //未入力チェック
  validRequired($email, 'email');
  validRequired($pass, 'pass');

  //email形式チェック
  validEmail($email, 'email');
  //email最大文字数チェック
  validMaxLen($email, 'email');

  //pass半角チェック
  validHalf($pass, 'pass');
  //pass最大文字数チェック
  validMaxLen($pass, 'pass');
  //pass最小文字数チェック
  validMinLen($pass, 'pass');

  if (empty($err_msg)) {
    debug('バリデーションチェックok');

    //例外処理
    try {
      //db接続
      $dbh = dbConnect();
      //sql文作成
      $sql = 'SELECT password, id FROM users WHERE email = :email AND delete_flg = 0';
      $data = array(':email' => $email);
      //クエリ実行
      $stmt = queryPost($dbh, $sql, $data);
      //クエリ結果の値を取得
      $result = $stmt->fetch(PDO::FETCH_ASSOC);

      debug('クエリの中身：' . print_r($result, true));

      //パスワードの照合
      if (!empty($result) && password_verify($pass, array_shift($result))) {
        debug('パスワードがマッチしました');

        //ログイン有効期限を１時間にする
        $seeLimit = 60 * 60;

        //最終ログイン日時を現在に
        $_SESSION['login_date'] = time();

        //ログインほじにチェックがある場合
        if ($pass_save) {
          debug('ログインほじにチェックがあります');
          //ログイン有効期限を３０日にする
          $_SESSION['login_limit'] = $seeLimit * 24 * 30;
        } else {
          debug('ログインほじにチェックがありません');
          //次回からログイン保持しないので、ログイン有効期限を１時間にする
          $_SESSION['login_limit'] = $seeLimit;
        }
        //ユーザーID格納
        $_SESSION['user_id'] = $result['id'];
        debug('セッションの中身：' . print_r($_SESSION, true));
        debug('マイページへ遷移します');

        header("Location:mypage.php");
      } else {
        debug('パスワードがアンマッチです');

        $err_msg['common'] = MSG09;
      }
    } catch (Exception $e) {
      error_log('エラー発生：' . $e->getMessage());
      $err_msg['common'] = MSG07;
    }
  }
}
debug('画面処理表示終了
<<<<<<<<<<<<<<<<<<<<<<<<<<<<');
?>

<?php
$siteTitle = 'ログイン';
require('head.php');
?>
<body class="page-login">

<?php
require('header.php');
?>

<!-- メインメニュー -->
<div id="contents" class="site-width">
  <!-- main -->
  <section id="main">
    <div class="form-container">
      <form action="" method="post" class="form">
        <h1 class="title">ログイン</h1>
        <div class="area-msg">
          <?php if(!empty($err_msg['common'])) echo $err_msg['common']; ?>
        </div>
        <label class="<?php if(!empty($err_msg['email'])) echo 'err'; ?>">
        メールアドレス
        <input type="text" name="email" value="<?php if(!empty($_POST['email'])) echo $_POST['email'] ; ?>">
      </label>
      <div class="area-msg">
        <?php if(!empty($err_msg['email'])) echo $err_msg['email']; ?>
      </div>
        <label class="<?php if(!empty($err_msg['pass'])) echo 'err'; ?>">
        パスワード
        <input type="password" name="pass" value="<?php if(!empty($_POST['pass'])) echo $_POST['pass'] ; ?>">
      </label>
      <div class="area-msg">
        <?php if(!empty($err_msg['pass'])) echo $err_msg['pass']; ?>
      </div>
      <div class="pass-save">
        <input type="checkbox" name="pass_save" >
        <span>次回ログインを省略する</span>
      </div>
      <div class="btn-container">
        <input type="submit" value="ログイン" class="btn btn-mid">
      </div>
      パスワードを忘れた方は<a href="passRemindSend.php">こちら</a>
      </form>

    </div>

  </section>
</div>  
