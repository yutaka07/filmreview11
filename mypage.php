<?php
//共通変数・関数ファイルを読込み
require('function.php');

debug('「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「');
debug('「　マイページ　');
debug('「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「');
debugLogStart();

//================================
// 画面処理
//================================
//ログイン認証
require('auth.php');

// 画面表示用データ取得
//================================
$u_id = $_SESSION['user_id'];
// DBから商品データを取得
$productData = getMyProducts($u_id);
// DBからお気に入りデータを取得
$likeData = getMyLike($u_id);

// DBからきちんとデータがすべて取れているかのチェックは行わず、取れなければ何も表示しないこととする

debug('取得した商品データ：'.print_r($productData,true));
debug('取得したお気に入りデータ：'.print_r($likeData,true));

debug('画面表示処理終了 <<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<');
?>
<?php
$siteTitle = 'マイページ';
require('head.php'); 
?>

  <body class="page-mypage page-2colum page-logined">
    <style>
      #main{
        border: none !important;
      }
    </style>
    
    <!-- メニュー -->
    <?php
      require('header.php'); 
    ?>
    
    <p id="js-show-msg" style="display:none;" class="msg-slide">
      <?php echo getSessionFlash('msg_success'); ?>
    </p>

    <!-- メインコンテンツ -->
    <div id="contents" class="site-width">
      
      
      <!-- Main -->
      <section id="main" >
        <h1 class="page-title">MYPAGE</h1>
         <section class="list panel-list">
           <h2 class="title" style="margin-bottom:15px;">
            登録商品一覧
           </h2>
           <?php
             if(!empty($productData)):
              foreach($productData as $key => $val):
            ?>
                    <a href="registProduct.php<?php echo (!empty(appendGetParam())) ? appendGetParam().'?p_id='.$val['id'] : '?p_id='.$val['id'] ; ?>" class="panel">
                    <div class="panel-head">
                    <img src="<?php echo showImg(sanitize($val['pic'])); ?>" alt="<?php echo sanitize($val['name']); ?>">
                  </div>
                  <div class="panel-body">
                    <p class="panel-title"><?php echo sanitize($val['name']); ?></p>
                  </div>
                    </a>
            <?php
              endforeach;
             endif;
            ?>
         </section>
         
         <style>
           .list{
             margin-bottom: 30px;
           }
        </style>
        <section class="list panel-list">
          <h2 class="title" style="margin-bottom:15px;">
            お気に入り一覧
          </h2>
          <?php
             if(!empty($likeData)):
              foreach($likeData as $key => $val):
            ?>
              <a href="productDetail.php<?php echo (!empty(appendGetParam())) ? appendGetParam().'&p_id='.$val['id'] : '?p_id='.$val['id']; ?>" class="panel">
                <div class="panel-head">
                  <img src="<?php echo showImg(sanitize($val['pic'])); ?>" alt="<?php echo sanitize($val['name']); ?>">
                </div>
                <div class="panel-body">
                  <p class="panel-title"><?php echo sanitize($val['name']); ?></p>
                </div>
              </a>
          <?php
            endforeach;
           endif;
          ?>
        </section>
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