<?php
require('function.php');


debug('「「「「「「「「「「「「「「「「「「「「「「');
debug('トップページ');
debug('「「「「「「「「「「「「「「「「「「「「「「「');
debugLogStart();

//===========================
//画面処理
//==========================

//画面表示用データ取得
//========================-
//GETパラメータを取得
//------------------------
//カレントページ
$currentPageNum = (!empty($_GET['p'])) ? $_GET['p'] : 1; //デフォルトは１ページ目
//カテゴリー
$category = (!empty($_GET['c_id'])) ? $_GET['c_id'] : '';
//ソート順
$sort = (!empty($_GET['sort'])) ? $_GET['sort'] : '';
//パラメータに不正な値が入っているかチェック
if (!is_int((int) $currentPageNum)) {
  error_log('エラー発生：指定したページに不正な値が入りました');
  header("Location:index.php"); //トップページへ
}

//表示件数
$listSpan = 20;
//現在の表示レコードの先頭を算出
$currentMinNum = (($currentPageNum - 1) * $listSpan); //1ページ目なら(1-1)*20=0,2ページ目なら(2-1)*20=20
//DBから商品データを取得
$dbProductData = getProductList($currentMinNum, $category, $sort);
//DBからカテゴリーデータを取得
$dbCategoryData = getCategory();
// debug('フォーム用dbデータ：'.print_r($dbFormData, true));
debug('カテゴリーデータ：' .print_r($dbCategoryData, true));

debug('画面表示処理終了');
?>





<?php
$siteTitle = 'HOME';
require('head.php');
?>

<body class="page-home page-2colum">

  <?php
  require('header.php');
  ?>
  <!-- メインコンテンツ -->
  <div id="contents" class="site-width">
    <!-- トップバー -->
    <section id="topbar">
      <form method="get" name="">
      <h1 class="title">カテゴリー</h1>
      <div class="selectbox">
        <span class="icn_select"></span>
        <select name="c_id" id="">
          <option value="0" <?php if (getFormData('c_id', true) == 0) echo 'selected'; ?>>選択してください</option>
          <?php
          foreach ($dbCategoryData as $key => $val) {
          ?>
            <option value="<?php echo $val['id'] ?>" <?php if (getFormData('c_id', true) == $val['id']) echo 'selected';  ?>>
              <?php echo $val['name']; ?>
            </option>
          <?php
          }
          ?>
          </select>
          </div>
          <h1 class="title">表示順</h1>
        <div class="selectbox">
          <span class="icn_select"></span>
          <select name="sort">
            <option value="0" <?php if (getFormData('sort', true) == 0) {
                                echo 'selected';
                              } ?>>選択してください</option>
            <option value="1" <?php if (getFormData('sort', true) == 1) {
                                echo 'selected';
                              } ?>>最新順</option>
            <option value="2" <?php if (getFormData('sort', true) == 2) {
                                echo 'selected';
                              } ?>>昔順</option>
          </select>
        </div>
        <input type="submit" value="検索" class="btn" style="width:50px; height: 25px; margin-left: 15px; padding: 0px;"/>
      </form>
    </section>

    <!-- main -->
    <section id="main">
    <div class="search-title">
        <div class="search-left">
          <span class="total-num"><?php echo sanitize($dbProductData['total']); ?></span>件の商品が見つかりました
        </div>
      </div>
      <h1><?php echo (!empty($category)) ? $dbCategoryData[($category-1)]['name'] : 'おすすめ'; 
      ?></h1>
      <div class="panel-list">
        <?php foreach ($dbProductData['data'] as $key => $val) : ?>
          <div class="panel">
            <div class="panel-head">
              <a href="productDetail.php<?php echo (!empty(appendGetParam())) ? appendGetParam() . '&p_id=' . $val['id'] : '?p_id=' . $val['id']; ?>"><img src="<?php echo sanitize($val['pic']); ?>" alt="<?php echo sanitize($val['name']); ?>"></a>
            </div>
            <div class="panel-body">
              <h2 class="panel-title"><a href="productDetail.php<?php echo (!empty(appendGetParam())) ? appendGetParam() . '&p_id=' . $val['id'] : '?p_id=' . $val['id']; ?>"><?php echo sanitize($val['name']); ?></a></h2>
              <p class="panel-comment"><span>あらすじ</br></span><?php echo sanitize($val['comment']); ?></p>
            </div>
          </div>
        <?php
        endforeach;
        ?>
      </div>
      <?php pagination($currentPageNum, $dbProductData['total_page']);
      ?>
    </section>
  </div>
  <?php
  require('footer.php');
  ?>