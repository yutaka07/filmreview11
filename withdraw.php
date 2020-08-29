<?php
//共通変数・関数読み込み
require('function.php');

debug('「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「');
debug('退会ページ');
debug('「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「');
debugLogStart();

//ログイン認証
require('auth.php');

//post送信があった場合

if (!empty($_POST)) {
  debug('post送信があります');
  //例外処理
  try {
    //DB接続
    $dbh = dbConnect();
    //sql文作成
    $sql1 = 'UPDATE users SET delete_flg = 1 WHERE id = :u_id';
    $sql2 = 'UPDATE product SET delete_flg = 1 WHERE user_id = :u_id';
    $sql3 = 'UPDATE `like` SET delete_flg = 1 WHERE user_id = :u_id';
    $data = array(':u_id' => $_SESSION['user_id']);

    //クエリ実行
    $stmt1 = queryPost($dbh, $sql1, $data);
    $stmt2 = queryPost($dbh, $sql2, $data);
    $stmt3 = queryPost($dbh, $sql3, $data);

    //クエリ実行成功の場合（最悪usersテーブルのみ削除されていればいい）
    if ($stmt1) {
      // セッション削除
      session_destroy();

      debug('セッションの中身：' . print_r($_SESSION, true));
      debug('トップへ遷移します');
      header("Location:index.php");
    } else {
      debug('クエリ失敗しました');
      $err_msg['common'] = MSG07;
    }
  } catch (Exception $e) {
    error_log('エラー発生：' . $e->getMessage());
    $err_msg['common'] = MSG07;
  }
}

debug('画面処理終了
<<<<<<<<<<<<<<<<<<<<<<<<');

?>

<?php
$siteTitle = '退会';
require('head.php');
?>

<body class="page-withdraw">

  <?php
  require('header.php');
  ?>

  <div id="contents" class="site-width">
    <section id="main">
      <div class="form-container">
        <form action="" method="post" class="form">
          <h1 class="title">退会</h1>
          <div class="area-msg">
            <?php if (!empty($err_msg['common'])) echo $err_msg['common']; ?>
          </div>
          <div class="btn-container" style="text-align:center;">
            <input type="submit" class="btn btn-mid" name="submit" value="退会" >
          </div>
        </form>
      </div>
      <a href="mypage.php">&lt; マイページに戻る</a>
    </section>
  </div>

<?php
require('footer.php');
?>