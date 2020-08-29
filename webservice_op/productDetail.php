<?php
//共通関数・変数読み込み
require('function.php');

debug('「「「「「「「「「「「「「「「「「「「「「「「「');
debug('商品詳細ページ');
debug('「「「「「「「「「「「「「「「「「「「「「「「「');
debugLogStart();

//========================
//画面処理
//=======================

//画面表示用データ取得
//========================
//GETデータを格納
$p_id = (!empty($_GET['p_id'])) ? $_GET['p_id'] : '';
//DBから商品データを取得
$viewData = getProductOne($p_id);
//dbからメッセージデータ取得
$msgData = getMessageData($p_id);

$dbGoodNum = count(getGood($p_id));
debug('取得したメッセージデータ：' . print_r($msgData, true));
//パラメータに不正な値が入っているかチェック
if (empty($viewData)) {
  error_log('エラー発生：指定したページに不正な値が入りました');
  header("Location:index.php"); //トップページへ
}
debug('取得したDBデータ：' . print_r($viewData, true));


//post送信されていた場合
if (!empty($_POST)) {

  //ログイン認証
  require('auth.php');

  //バリデーションチェック
  $msg = (isset($_POST['msg'])) ? $_POST['msg'] : '';

  //最大文字数チェック
  validMaxLen($msg, 'msg');
  //未入力チェック
  validRequired($msg, 'msg');
  if (empty($err_msg)) {
    debug('バリデーションOKです。');

    //例外処理
    try {
      // DBへ接続
      $dbh = dbConnect();
      // SQL文作成
      $sql = 'INSERT INTO message (product_id, send_date, from_user, msg, create_date) VALUES (:p_id, :send_date, :from_user, :msg, :date)';
      $data = array(':p_id' => $p_id, ':send_date' => date('Y-m-d H:i:s'), ':from_user' => $_SESSION['user_id'], ':msg' => $msg, ':date' => date('Y-m-d H:i:s'));
      // クエリ実行
      $stmt = queryPost($dbh, $sql, $data);

      // クエリ成功の場合
      if ($stmt) {
        $_POST = array(); //postをクリア
        header("Location: " . $_SERVER['PHP_SELF'] . '?p_id=' . $p_id); //自分自身に遷移する
      }
    } catch (Exception $e) {
      error_log('エラー発生:' . $e->getMessage());
      $err_msg['common'] = MSG07;
    }
  }
}
debug('画面表示処理終了 <<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<');
?>
<?php
$siteTitle = '商品詳細';
require('head.php');
?>

<body class="page-productDtail page-1colum">

  <?php
  require('header.php');
  ?>

  <!-- メインコンテンツ -->
  <div id="contents" class="site-width">

    <!-- main -->
    <section id="main">
      <div class="panel">
        <div class="panel-head">
          <img src="<?php echo $viewData['pic']; ?>" alt="">
        </div>
        <div class="panel-body">
          <div class="js-click-like" aria-hidden="true" data-productid="<?php echo sanitize($viewData['id']); ?>">
            <h2  class="panel-title"><?php echo $viewData['name']; ?></h2>
            <i class="fa fa-heart icn-like  <?php if (isLike($_SESSION['user_id'], $viewData['id'])) { echo 'active';} ?>"></i>
            <span style="font-size: 20px; margin-left: 10px"><?php echo $dbGoodNum; ?></span>
          </div>
       
          <p class="panel-comment"><span>あらすじ</br></span><?php echo $viewData['comment']; ?></p>
        </div>
      </div>
      <div class="area-bord">
        <?php
        if (!empty($viewData)) {
          foreach ($msgData as $key => $val) {
            if ($val['from_user'] !== $viewData['user_id']) {
        ?>
              <div class="msg-cnt msg-left" >
                <div class="avatar">
                  <img src="<?php echo $val['pic']; ?>" alt="" class="avater">
                </div>
                <p  class="msg-inrTxt"><?php echo sanitize($val['msg']); ?></p>
              </div>
            <?php
            } else {
            ?>
              <div class="msg-cnt msg-right" >
                <div class="avatar">
                  <img src="<?php echo $val['pic']; ?>" alt="" class="avater">
                </div>
                <p  class="msg-inrTxt"><?php echo sanitize($val['msg']); ?></p>
              </div>
        <?php
            }
          }
        }
        ?>
      </div>
      <div class="area-send-msg">
        <form action="" method="post">
          <textarea name="msg" cols="30" rows="3"></textarea>
          <div class="btn-right"><input type="submit" value="送信" class="btn btn-send"></div>
        </form>
      </div>


    </section>

  </div>

  <?php
  require('footer.php');
  ?>