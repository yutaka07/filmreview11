<?php

require('function.php');

debug('「「「「「「「「「「「「「「「「「「「「「「「「「「');
debug('パスワード再発行メール送信ページ');
debug('「「「「「「「「「「「「「「「「「「「「「「「「「「');
debugLogStart();

//ログイン認証はいらない

//===========================
//画面処理
//=========================

//post送信されていた場合
if (!empty($_POST)) {
  debug('post送信があります');
  debug('post情報:' . print_r($_POST, true));

  //変数にpost情報入力
  $email = $_POST['email'];

  //未入力チェック
  validRequired($email, 'email');

  if (empty($err_msg)) {
    debug('未入力チェックok');

    //email形式チェック
    validRequired($email, 'email');
    //email最大文字数チェック
    validMaxLen($email, 'email');

    if (empty($err_msg)) {
      debug('バリデーションチェックok');

      try {
        //dbに接続
        $dbh = dbConnect();
        //sql文作成
        $sql = 'SELECT COUNT(*) FROM users WHERE email = :email';
        $data = array(':email' => $email);
        //クエリ実行
        $stmt = queryPost($dbh, $sql, $data);
        //クエリ結果の値を取得
        $result = $stmt->fetch(PDO::FETCH_ASSOC);

        //emailがdbに登録されている場合
        if ($stmt && !empty(array_shift($result))) {
          debug('クエリ成功。dbに登録あり');

          $_SESSION['msg_success'] = SUC03;

          //認証キー生成
          $auth_key = makeRandKey();

          //メール送信
          $from = 'info@webukatu.com';
          $to = $email;
          $subject = '【パスワード再発行認証】｜WEBUKATUMARKET';
          //EOTはEndOfFileの略。ABCでもなんでもいい。先頭の<<<の後の文字列と合わせること。最後のEOTの前後に空白など何も入れてはいけない。
          //EOT内の半角空白も全てそのまま半角空白として扱われるのでインデントはしないこと
          $comment = <<<EOT
本メールアドレス宛にパスワード再発行のご依頼がありました。
下記のURLにて認証キーをご入力頂くとパスワードが再発行されます。

パスワード再発行認証キー入力ページ：http://localhost:8888/webservice_practice07/passRemindRecieve.php
認証キー：{$auth_key}
※認証キーの有効期限は30分となります

認証キーを再発行されたい場合は下記ページより再度再発行をお願い致します。
http://localhost:8888/webservice_practice07/passRemindSend.php

////////////////////////////////////////
ウェブカツマーケットカスタマーセンター
URL  http://webukatu.com/
E-mail info@webukatu.com
////////////////////////////////////////
EOT;
          sendMail($from, $to, $subject, $comment);

          //認証に必要な値をsessionに保存
          $_SESSION['auth_key'] = $auth_key;
          $_SESSION['auth_email'] = $email;
          $_SESSION['auth_key_limit'] = time() + (60 * 30);
          debug('セッション変数の中身' . print_r($_SESSION, true));

          header("Location:passRemindRecieve.php"); //認証キー入力ページへ
        } else {
          debug('クエリに失敗したかdbに登録のないemailが入力されました');
          $err_msg['common'] = MSG07;
        }
      } catch (Exception $e) {
        error_log('エラー発生：' . $e->getMessage());
        $err_msg['common'] = MSG07;
      }
    }
  }
}
?>
<?php
$siteTitle = 'パスワード再発行メール送信';
require('head.php');
?>
<body class="page-signup page-1colum">
  

<?php
require('header.php');
?>
<!-- メインコンテンツ -->
<div id="contents" class="site-width">

<!-- main -->
<section id="main">
  <div class="form-container">
    <form action="" class="form" method="post" >
      <p>ご指定のメールアドレス宛にパスワード再発行用のURLと認証キーをお送り致します。</p>
      <div class="area-msg">
        <?php echo getErrMsg('common'); ?>
      </div>
      <label class="<?php if(!empty($err_msg['email'])) echo $err_msg['email'] ; ?>">
        email
        <input type="text" name="email" value="<?php echo getFormData('email'); ?>">
      </label>
        <div class="area-msg">
          <?php echo getErrMsg('email'); ?>
        </div>
        <div class="btn-container">
          <input type="submit" class="btn btn-mid" value="送信する">
        </div>
    </form>
  </div>
  <a href="mypage.php">&lt;マイページへ戻る</a>

</section>

</div>

<!-- footer -->

<?php
require('footer.php');

