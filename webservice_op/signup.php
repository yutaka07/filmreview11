<?php
//共通関数・変数読み込み
require('function.php');

//post送信されていた場合
if (!empty($_POST)) {

  //変数にユーザー情報を入力
  $email = $_POST['email'];
  $pass = $_POST['pass'];
  $pass_re = $_POST['pass_re'];

  //未入力チェック
  validRequired($email, 'email');
  validRequired($pass, 'pass');
  validRequired($pass_re, 'pass_re');

  if (empty($err_msg)) {

    //email形式チェック
    validEmail($email, 'email');
    //email最大文字数チェック
    validMaxLen($email, 'email');
    //email重複チェック
    validEmailDup($email);

    //pass半角チェック
    validHalf($pass, 'pass');
    //pass最大文字数チェック
    validMaxLen($pass, 'pass');
    //pass最小文字数チェック
    validMinLen($pass, 'pass');

    debug('pass形式チェック：ok');
    if (empty($err_msg)) {

      //passとpass_reがあっているかどうかチェック
      validMatch($pass, $pass_re, 'pass_re');

      if (empty($err_msg)) {
        //例外処理
        try {
          //db接続
          $dbh = dbConnect();
          //sql文作成
          $sql = 'INSERT INTO users (email, password, create_date, login_time) VALUES (:email, :pass, :create_date, :login_time)';
          $data = array(':email' => $email, ':pass' => password_hash($pass, PASSWORD_DEFAULT), ':create_date' => date('Y-m-d H:i:s'), ':login_time' => date('Y-m-d H:i:s'));
          //クエリ実行
          $stmt = queryPost($dbh, $sql, $data);
          //クエリ成功の場合
          if ($stmt) {
            //ログイン有効期限(デフォルト１時間)
            $seelimit = 60 * 60;
            //最終ログイン日時を現在日時に
            $_SESSION['login_date'] = time();
            $_SESSION['login_limit'] = $seelimit;
            //ユーザーIDを格納
            $_SESSION['user_id'] = $dbh->lastInsertId();
            //マイページへ遷移させる
            header("Location:mypage.php");
          } else {
            debug('クエリに失敗しました');
            $err_msg['common'] = MSG07;
          }
        } catch (Exception $e) {
          error_log('エラー発生：' . $e->getMessage());
          $err_msg['common'] = MSG07;
        }
      }
    }
  }
}





?>
<?php
$siteTitle = 'ユーザー登録';
require('head.php');
?>

<body class="page-signup page-1colum">
  <!-- ヘッダー -->
  <?php
  require('header.php');
  ?>
  <!-- メインコンテンツ -->
  <div id="contents" class="site-width">
    <!-- メイン -->
    <section id="main">
      <div class="form-container">
        <form action="" method="post" class="form">
          <h1>ユーザー登録</h1>
          <div class="area-msg">
            <?php
            if (!empty($err_msg['common'])) echo $err_msg['common'];
            ?>
          </div>
          <label class="<?php if (!empty($err_msg['email'])) echo 'err'; ?>">
            メールアドレス
            <input type="text" name="email" value="<?php if (!empty($_POST['email'])) echo $_POST['email']; ?>">
          </label>
          <div class="area-msg">
            <?php
            if (!empty($err_msg['email'])) echo $err_msg['email']; ?>
          </div>
          <label class="<?php if (!empty($err_msg['pass'])) echo 'err'; ?>">
            パスワード<span style="font-size: 12px;">※半角英数字６文字以上</span>
            <input type="password" name="pass" value="<?php if (!empty($_POST['pass'])) echo $_POST['pass']; ?>">
          </label>
          <div class="area-msg">
            <?php
            if (!empty($err_msg['pass'])) echo $err_msg['pass']; ?>
          </div>
          <label class="<?php if (!empty($err_msg['pass_re'])) echo 'err'; ?>">
            パスワード（再入力）
            <input type="password" name="pass_re" value="<?php if (!empty($_POST['pass_re'])) echo $_POST['pass_re']; ?>">
          </label>
          <div class="area-msg">
            <?php
            if (!empty($err_msg['pass_re'])) echo $err_msg['pass_re']; ?>
          </div>
          <div class="btn-container">
            <input type="submit" value="登録する" class="btn btn-mid">
          </div>


        </form>

      </div>
    </section>
  </div>

  <?php
  require('footer.php');
  ?>