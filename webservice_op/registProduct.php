<?php

//共通変数・関数ファイルを読込み
require('function.php');

debug('「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「');
debug('「　商品出品登録ページ　');
debug('「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「');
debugLogStart();

//ログイン認証
require('auth.php');

//================================
// 画面処理
//================================
//画面表示用データ取得
//======================
//getデータ格納
$p_id = (!empty($_GET['p_id'])) ?  $_GET['p_id'] : '';
//dbから商品データを取得
$dbFormData = (!empty($p_id)) ? getProduct($_SESSION['user_id'], $p_id) : '';
//新規登録画面か編集画面か判別フラグ
$edit_flg = (empty($dbFormData)) ? false : true;
//dbからカテゴリーデータを取得
$dbCategoryData = getCategory();
debug('商品id:' . $p_id);
debug('フォーム用dbデータ：' . print_r($dbFormData, true));
debug('カテゴリデータ：' . print_r($dbCategoryData, true));

//パラメータ改ざんチェック
//==========================
//GETパラメータはあるが、改ざん（urlをいじくった）されている場合、正しい商品データが取れないのでマイページへ遷移
if (!empty($p_id) && empty($dbFormData)) {
  debug('getパラメータの商品idが違います。マイページへ遷移します');
  header("Location:mypage.php");
}
//post送信時処理
if (!empty($_POST)) {
  debug('post送信されています');
  debug('POST情報：' . print_r($_POST, true));
  debug('FILES情報：' . print_r($_FILES, true));

  //変数にユーザー情報を代入
  $name = $_POST['name'];
  $category = $_POST['category_id'];
  $comment = $_POST['comment'];
  //画像をアップロードし、パスを格納
  $pic = (!empty($_FILES['pic']['name'])) ? uploadImg($_FILES['pic'], 'pic') : '';
  //画像をpostしていない（登録していない）がすでにdbに登録されている場合は、dbのパスを入れる（postには反映されないので）
  $pic = (empty($pic) && !empty($dbFormData['pic'])) ? $dbFormData['pic'] : $pic;
  //更新の場合はDBの情報と入力情報が異なる場合はバリデーションチェックを行う
  if (empty($dbFormData)) {
    //未入力チェック
    validRequired($name, 'name');
    //最大文字数チェック
    validMaxLen($name, 'name');
    //selectboxチェック
    validSelect($category, 'category_id');
    //最大文字数チェック
    validMaxLen($comment, 'comment', 500);
  } else {
    if ($dbFormData['name'] !== $name) {
      validRequired($name, 'name');
      validMaxLen($name, 'name');
    }
    if ($dbFormData['category_id'] !== $category) {
      validRequired($category, 'category_id');
    }
    if ($dbFormData['comment'] !== $comment) {
      validMaxLen($comment, 'comment');
    }
  }

  if (empty($err_msg)) {
    debug('バリデーションok');

    //例外処理
    try {
      //DB接続
      $dbh = dbConnect();
      //sql文作成
      if (!empty($dbFormData)) {
        debug('DB更新です');
        $sql = 'UPDATE product SET name = :name, category_id = :category, comment = :comment, pic = :pic WHERE user_id = :u_id AND id = :p_id';
        $data = array(':name' => $name, ':category' => $category,  ':comment' => $comment, ':pic' => $pic, ':u_id' => $_SESSION['user_id'], ':p_id' => $p_id);
      } else {
        debug('DB新規登録です');
        $sql = 'INSERT INTO product (name, category_id,  comment, pic, user_id, create_date) VALUES (:name, :category, :comment, :pic, :u_id, :date)';
        $data = array(':name' => $name, ':category' => $category,  ':comment' => $comment, ':pic' => $pic, ':u_id' => $_SESSION['user_id'], ':date' => date('Y-m-d H:i:s'));
      }
      debug('sql文：' . $sql);
      debug('流し込みデータ：' . print_r($data, true));

      //クエリ実行
      $stmt = queryPost($dbh, $sql, $data);

      if ($stmt) {
        $_SESSION['msg_success'] = SUC04;
        debug('マイページへ遷移します');
        header("Location:mypage.php"); //マイページへ
      }
    } catch (Exception $e) {
      error_log('エラー発生：' . $e->getMessage());
      $err_msg['common'] = MSG07;
    }
  }
}
debug('画面処理終了 <<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<');
?>
<?php
$siteTitle = (!$edit_flg) ? '映画の登録' : '映画の編集';
require('head.php');
?>

<body class="page-profedit page-2colum page-logind">

  <!-- メニュー -->
  <?php
  require('header.php');
  ?>
  <!-- メインコンテンツ -->
  <div id="contents" class="site-width">
    <!-- main -->
    <section id="main">
      <h1 class="page-title"><?php echo (!$edit_flg) ? '映画の登録' : '映画の編集'; ?></h1>
      <div class="form-container">
        <form action="" method="post" class="form" enctype="multipart/form-data" style="width:100%;box-sizing:border-box;">
          <div class="area-msg">
            <?php
            if (!empty($err_msg['common'])) echo $err_msg['common'];
            ?>
          </div>
          <label class="<?php if (!empty($err_msg['name'])) echo 'err'; ?>">
            映画名<span class="label-require">必須</span>
            <input type="text" name="name" value="<?php echo getFormData('name'); ?>">
          </label>
          <div class="area-msg">
            <?php
            if (!empty($err_msg['name'])) echo $err_msg['name'];
            ?>
          </div>
          <label class="<?php if (!empty($err_msg['category_id'])) echo 'err'; ?>">
            カテゴリ<span class="label-require">必須</span>
            <select name="category_id" id="">
              <option value="0" <?php if (getFormData('category_id') == 0) echo 'selected';  ?>>選択して下さい</option>
              <?php
              foreach ($dbCategoryData as $key => $val) {
              ?>
                <option value="<?php echo $val['id'] ?>" <?php if (getFormData('category_id') == $val['id']) echo 'selected'; ?>>
                  <?php echo $val['name']; ?>
                </option>
              <?php
              }
              ?>
            </select>
          </label>
          <div class="area-msg">
            <?php if (!empty($err_msg['category_id'])) echo $err_msg['category_id']; ?>
          </div>
          <label class="<?php if (!empty($err_msg['comment'])) echo 'err'; ?>">
            詳細
            <textarea name="comment" id="js-count" cols="30" rows="10" style="height: 150px;"><?php getFormData('comment'); ?></textarea>
          </label>
          <p class="counter-text"><span id="js-count-view">0</span>/500</p>
          <div class="area-msg">
            <?php if (!empty($err_msg['comment'])) echo $err_msg['comment']; ?>
          </div>
          <div style="overflow: hidden;">
            <div class="imgDrop-container">
              画像１
              <label class="area-drop <?php if (!empty($err_msg['pic'])) echo 'err'; ?>">
                <input type="hidden" name="MAX_FILE_SIZE" value="3145728">
                <input type="file" name="pic" class="input-file">
                <img src="<?php echo getFormData('pic'); ?>" alt="" class="prev-img" style="<?php if (empty(getFormData('pic'))) echo 'display:none;' ?>">
                ドラッグ&ドロップ
              </label>
              <div class="area-msg">
                <?php
                if (!empty($err_msg['pic'])) echo $err_msg['pic'];
                ?>
              </div>
            </div>
          </div>
          <div class="btn-container">
            <input type="submit" class="btn btn-mid" value="<?php echo (!$edit_flg) ? '登録する' : '更新する'; ?>">
          </div>
        </form>

      </div>
    </section>
    <!-- サイドバー -->
    <?php
    require('sidebar_mypage.php');
    ?>
  </div>

  <!-- footer -->
  <?php
  require('footer.php');
  ?>