<?php

//共通関数・変数を読み込む
require('function.php');

debug('「「「「「「「「「「「「「「「「「「「「「「「「「「「「');
debug('パスワード再発行認証ページ');
debug('「「「「「「「「「「「「「「「「「「「「「「「「「「「「');
debugLogStart();

//ログイン認証はなし（ログインできない人が使う画面なので）

//sessionに認証キーがあるか確認、なければレダイレクト
if(empty($_SESSION['auth_key'])){
  header("Location:passRemindSend.php");//認証キー発行ページへ
}

//=======================
//画面処理
//=======================-
//post送信されていた場合
if(!empty($_POST)){

  //変数に認証キーを代入
  $auth_key = $_POST['token'];

  //未入力チェック
  validRequired($auth_key, 'token');

  if(empty($err_msg)){
    debug('未入力チェックok');

    //半角英数チェック
    validHalf($auth_key, 'token');

    if(empty($err_msg)){
      debug('バリデーションチェックok');

      if($auth_key !== $_SESSION['auth_key']){
        $err_msg['common'] = MSG15;
      }
      if(time() > $_SESSION['auth_key_limit']){
        $err_msg['common'] = MSG16;
      }

      if(empty($err_msg)){
        debug('認証ok');

        $pass = makeRandKey();//パスワード生成

        debug('passの中身：' .print_r($pass, true));
        //例外処理
        try{
          //db接続
          $dbh = dbConnect();
          //sql文作成
          $sql = 'UPDATE users SET password = :pass WHERE email = :email AND delete_flg = 0';
          $data = array(':email' => $_SESSION['auth_email'], ':pass' => password_hash($pass, PASSWORD_DEFAULT));
          //クエリ実行
          $stmt = queryPost($dbh, $sql, $data);

          //クエリ成功の場合
          if($stmt){
            debug('クエリ成功');

            //メール送信
            $from = 'info@webukatu.com';
            $to = $_SESSION['auth_email'];
            $subject = '【パスワード再発行完了】| WEBUKATUMARKET';
            $comment = <<<EOT
            本メールアドレス宛にパスワードの再発行を致しました。
下記のURLにて再発行パスワードをご入力頂き、ログインください。

ログインページ：http://localhost:8888/webservice_practice07/login.php
再発行パスワード：{$pass}
※ログイン後、パスワードのご変更をお願い致します

////////////////////////////////////////
ウェブカツマーケットカスタマーセンター
URL  http://webukatu.com/
E-mail info@webukatu.com
////////////////////////////////////////
EOT;

            sendMail($from, $to, $subject, $comment);

            //セッション削除
            session_unset();
            $_SESSION['meg_success'] = SUC03;

            debug('セッションの中身：' . print_r($_SESSION, true));
            header("Location:login.php");//ログインページへ遷移
          }else{
            debug('クエリに失敗しました');
            $err_msg['common'] = MSG07;
          }
        }catch(Exception $e){
          error_log('エラー発生：' .$e->getMessage());
          $err_msg['common'] = MSG07;
        }
      }
    }
  }
}
?>
<?php
$siteTitle = 'パスワード再発行認証';
require('head.php');
?>
<body class="page-signup page-1colum">
  
<?php
require('header.php');
?>
<p id="js-show-msg" style="display: none;" class="msg-slide">
    <?php echo getSessionFlash('msg_success'); ?>
</p>

<div id="contents" class="site-width">
  <section id="main">
    <div form-container>
      <form action="" method="post" class="form">
        <div class="area-msg">
          <?php echo getErrMsg('common'); ?>
        </div>
        <label class="<?php if(!empty($err_msg['token'])) echo 'err' ; ?>">
        認証キー
        <input type="text" name="token" value="<?php echo getFormData('token'); ?>">
        </label>
        <div class="area-msg">
          <?php echo getErrMsg('token'); ?>
        </div>
        <div class="btn-container">
          <input type="submit" class="btn btn-mid" value="再発行する">
        </div>
      </form>
    </div>
    <a href="passRemindSend.php">&lt;パスワード再発行メールを再度送信する</a>
  </section>
</div>

<!-- footer -->
<?php
require('footer.php');
