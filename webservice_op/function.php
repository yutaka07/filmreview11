<?php


//============================
//ログ
//============================
//ログをとるか
ini_set('log_errors', 'on');
//ログの出力ファイルを指定
ini_set('error_log', 'php.log');

//==============================
//デバッグ
//===========================
//デバッグフラグ
$debug_flg = 'false';
//デバッグログ関数
function debug($str)
{
  global $debug_flg;
  if (!empty($debug_flg)) {
    error_log('デバッグ：' . $str);
  }
}

//================================
//セッション準備・セッション有効期限を伸ばす
//================================
//セッションの置き場所を変更する（/var/tmp/に置くと３０日は間削除されない)
session_save_path('/var/tmp/');
//ガーベージコレクションが削除するセッションの有効期限を設定（３０日以上立っているものに対して１００分の１の確率で削除）
ini_set('session.gc_maxlifetime', 60 * 60 * 24 * 30);
//ブラウザを閉じても削除されないようにクッキーの有効期限を伸ばす
ini_set('session.cookie_lifetime', 60 * 60 * 24 * 30);
//セッションを使う
session_start();
//現在のせっしょんIDを新しく生成したものと置き換える（なりすましのセキュリティー対策）
session_regenerate_id();

//=======================
//画面表示処理開始ログ吐き出し関数
//========================
function debugLogStart()
{
  debug('<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<画面処理開始');
  debug('セッションID:' . session_id());
  debug('セッションの変数中身：' . print_r($_SESSION, true));
  debug('現在日時タイムスタンプ：' . time());
  if (!empty($_SESSION['login_date']) && !empty($_SESSION['login_limit'])) {
    debug('ログイン期限日時タイムスタンプ:' . ($_SESSION['login_date'] + $_SESSION['login_limit']));
  }
}

//=====================
//定数
//=====================
//エラーメッセージ定数に指定
define('MSG01', '入力必須です');
define('MSG02', 'emailの形式で入力して下さい');
define('MSG03', 'パスワード（再入力）があっていません');
define('MSG04', '半角英数字のみご利用いただけます');
define('MSG05', '６文字以上で入力して下さい');
define('MSG06', '２５５文字以内で入力して下さい');
define('MSG07', 'エラーが発生しました。しばらくたってからやり直して下さい');
define('MSG08', 'そのemailはすでに登録されています');
define('MSG09', 'emailまたはpasswordが違います');
define('MSG10', '電話番号の形式が違います');
define('MSG11', '郵便番号の形式が違います');
define('MSG12', '古いパスワードが違います');
define('MSG13', '古いパスワードと同じです');
define('MSG14', '文字で入力してください');
define('MSG15', '正しくありません');
define('MSG16', '有効期限が切れています');
define('MSG17', '半角数字のみご利用いただけます');
define('SUC01', 'パスワードを変更しました');
define('SUC02', 'プロフィールを変更しました');
define('SUC03', 'メールを送信しました');
define('SUC04', '登録しました');
define('SUC05', '購入しました！相手と連絡を取りましょう！');




//=====================
//バリデーション関数
//======================
//エラーメッセージ格納用の配列
$err_msg = array();

//バリデーション関数（未入力チェック）
function validRequired($str, $key)
{
  if ($str === '') {
    global $err_msg;
    $err_msg[$key] = MSG01;
  }
}
//バリデーション関数（email形式チェック）
function validEmail($str, $key)
{
  if (!preg_match("/^([a-zA-Z0-9])+([a-zA-Z0-9\._-])*@([a-zA-Z0-9_-])+([a-zA-Z0-9\._-]+)+$/", $str)) {
    global $err_msg;
    $err_msg[$key] = MSG02;
  }
}
//バリデーション関数（重複チェック）
function validEmailDup($email)
{
  global $err_msg;
  //例外処理
  try {
    //DB接続
    $dbh = dbConnect();
    //sql作成
    $sql = 'SELECT count(*) FROM users WHERE email = :email AND delete_flg = 0';
    $data = array(':email' => $email);
    //クエリ実行
    $stmt = queryPost($dbh, $sql, $data);
    //クエリ結果の値を取得
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    //array_shift関数は配列の先頭を取得する値です。クエリ結果は配列形式で入っているので、array_shiftで１つ目だけを取り出して判定をする
    if (!empty(array_shift($result))) {
      $err_msg['email'] = MSG08;
    }
  } catch (Exception $e) {

    error_log('エラーが発生：' . $e->getMessage());
    $err_msg['common'] = MSG07;
  }
}

//バリデーション関数（最小文字数チェック）
function validMinLen($str, $key, $min = 6)
{
  if (mb_strlen($str) < $min) {
    global $err_msg;
    $err_msg[$key] = MSG05;
  }
}
//バリデーション関数（最大文字数チェック）
function validMaxLen($str, $key, $max = 255)
{
  if (mb_strlen($str) > $max) {
    global $err_msg;
    $err_msg[$key] = MSG06;
  }
}
//バリデーション関数（半角チェック）
function validHalf($str, $key)
{
  if (!preg_match("/^[a-zA-Z0-9]+$/", $str)) {
    global $err_msg;
    $err_msg[$key] = MSG04;
  }
}
//バリデーション関数（同値チェック）
function validMatch($str1, $str2, $key)
{
  if ($str1 !== $str2) {
    global $err_msg;
    $err_msg[$key] = MSG03;
  }
}
//電話番号形式チェック
function validTel($str, $key)
{
  if (!preg_match("/0\d{1,4}\d{1,4}\d{4}/", $str)) {
    global $err_msg;
    $err_msg[$key] = MSG10;
  }
}
//郵便番号チェック
function validZip($str, $key)
{
  if (!preg_match("/^\d{7}$/", $str)) {
    global $err_msg;
    $err_msg[$key] = MSG11;
  }
}
//半角数字チェック
function validNumber($str, $key)
{
  if (!preg_match("/^[0-9]+$/", $str)) {
    global $err_msg;
    $err_msg[$key] = MSG17;
  }
}
//パスワードチェック
function validPass($str, $key)
{
  //半角英数字チェック
  validHalf($str, $key);
  //最大文字数チェック
  validMaxLen($str, $key);
  //最小文字数チェック
  validMinLen($str, $key);
}

//ユーザー情報取得
function getUser($u_id)
{
  debug('ユーザー情報取得');
  //例外処理
  try {
    //db接続
    $dbh = dbConnect();
    //sql文作成
    $sql = 'SELECT * FROM users WHERE id = :u_id AND delete_flg = 0';
    $data = array(':u_id' => $u_id);
    //クエリ実行
    $stmt = queryPost($dbh, $sql, $data);
    //クエリ結果のデータを１レコード返却
    if ($stmt) {
      return $stmt->fetch(PDO::FETCH_ASSOC);
    } else {
      return false;
    }
  } catch (Exception $e) {
    error_log('エラー発生：' . $e->getMessage());
  }
}
function getUserPic($u_id)
{
  debug('ユーザー情報取得');
  //例外処理
  try {
    //db接続
    $dbh = dbConnect();
    //sql文作成
    $sql = 'SELECT pic FROM users WHERE id = :u_id AND delete_flg = 0';
    $data = array(':u_id' => $u_id);
    //クエリ実行
    $stmt = queryPost($dbh, $sql, $data);
    //クエリ結果のデータを１レコード返却
    if ($stmt) {
      $result = $stmt->fetch(PDO::FETCH_ASSOC);

      return sanitize(showImg($result['pic']));
    } else {
      return false;
    }
  } catch (Exception $e) {
    error_log('エラー発生：' . $e->getMessage());
  }
}
//エラーメッセージ表示
function getErrMsg($key){
  global $err_msg;
  if(!empty($err_msg[$key])){
    return $err_msg[$key];
  }
}
function makeRandKey($length = 8){
  static $chars = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJLKMNOPQRSTUVWXYZ0123456789';
  $str = '';
  for($i = 0 ; $i < $length ; ++$i){
    $str .= $chars[mt_rand(0,61)];
  }
  return $str;
}
function getSessionFlash($key){
  if(!empty($_SESSION[$key])){
    $data = $_SESSION[$key];
    $_SESSION[$key] = '';
    return $data;
  }
}
function getProduct($u_id, $p_id){
  try{
    $dbh = dbConnect();
    $sql = 'SELECT * FROM product WHERE user_id = :u_id AND id = :p_id
    AND delete_flg = 0';
    $data = array(':u_id' => $u_id, ':p_id' => $p_id);

    $stmt = queryPost($dbh, $sql, $data);

    if($stmt){
      return $stmt->fetch(PDO::FETCH_ASSOC);
    }else{
      return false;
    }
  }catch(Exception $e){
    error_log('エラー発生：' .$e->getMessage());
  }
}

function getcategory(){
  try{
    $dbh = dbConnect();
    $sql = 'SELECT * FROM category ';
    $data = array();
    //クエリ実行
    $stmt = queryPost($dbh, $sql, $data);

    if($stmt){
      //クエリ結果の全データを返却
      return $stmt->fetchAll();
    }else{
      return false;
    }

  }catch(Exception $e){
    error_log('エラー発生：' . $e->getMessage());
  }
}
//selectboxチェック
function validSelect($str, $key){
  if(!preg_match("/^[0-9]+$/", $str)){
    global $err_msg;
    $err_msg[$key] = MSG15;
  }
}
function getProductOne($p_id){
  debug('商品データを取得します');
  debug('商品id:' .$p_id);
  //例外処理
  try{
    //db接続
    $dbh = dbConnect();
    //sql文作成
    $sql = 'SELECT p.id , p.name , p.comment , p.pic , p.user_id , p.create_date , p.update_date , c.name AS category FROM product AS p LEFT JOIN category AS c ON p.category_id = c.id WHERE p.id = :p_id AND p.delete_flg = 0 AND c.delete_flg = 0';
    $data = array(':p_id' => $p_id);
    //クエリ実行
    $stmt = queryPost($dbh, $sql, $data);

    if($stmt){
      //クエリ結果のデータを１レコード返却
      return $stmt->fetch(PDO::FETCH_ASSOC);
    }else{
      return false;
    }
  }catch(Exception $e){
    error_log('エラー発生：'.$e->getMessage());

  }
}
function getMessageData($p_id){
  debug('メッセージデータを取得します');
  debug('商品id:' .$p_id);
  //例外処理
  try{
    //db接続
    $dbh = dbConnect();
    //sql文作成
    $sql = 'SELECT m.id, m.send_date, m.from_user, m.msg, m.create_date, m.update_date, u.pic FROM message AS m LEFT JOIN users AS u ON m.from_user = u.id WHERE m.product_id = :p_id AND m.delete_flg = 0 ';
    $data = array(':p_id' => $p_id);
    //クエリ実行
    $stmt = queryPost($dbh, $sql, $data);

    if($stmt){
      
      //クエリ結果のデータを１レコード返却
      return $stmt->fetchAll();
    }else{
      return false;
    }
  }catch(Exception $e){
    error_log('エラー発生：'.$e->getMessage());

  }
}
function showImg($path){
  if(empty($path)){
    return 'img/sample-img.png';
  }else{
    return $path;
  }
}

function getProductList($currentMinNum =1, $category, $sort, $span = 20){
  debug('商品情報取得します');
  //例外処理
  try{
    //db接続
    $dbh = dbConnect();
    //件数用のsql文作成
    $sql = 'SELECT id FROM product ';
    if(!empty($category)){
       $sql .= 'WHERE category_id = '.$category;
    }
      if(!empty($sort)){
        switch($sort){
          case 1: 
            $sql .= ' ORDER BY create_date DESC';
          break;
          case 2: 
            $sql .= ' ORDER BY create_date ASC';
        }
      }
    $data = array();
    //クエリ実行
    $stmt = queryPost($dbh, $sql, $data);
    $rst['total'] = $stmt->rowCount();//総レコード数
    $rst['total_page'] = ceil($rst['total']/$span);//総ページ数
    if(!$stmt){
      return false;
    }
    //ページング用のsql文作成
    $sql = 'SELECT * FROM product';
    if(!empty($category)) $sql .= ' WHERE category_id = '.$category;
    if(!empty($sort)){
      switch($sort){
        case 1:
          $sql .= ' ORDER BY create_date DESC';
          break;
        case 2:
          $sql .= ' ORDER BY create_date ASC';
          break;
      }
    } 
    $sql .= ' LIMIT '.$span.' OFFSET '.$currentMinNum;
    $data = array();
    debug('SQL：'.$sql);
    // クエリ実行
    $stmt = queryPost($dbh, $sql, $data);

    if($stmt){
      //クエリ結果のデータを全レコードを格納
      $rst['data'] = $stmt->fetchAll();
      return $rst;
    }else{
      return false;
    }
  }catch(Exception $e){
    error_log('エラー発生：' .$e->getMessage());
  }
}
 
//ページング
// $currentPageNum : 現在のページ数
// $totalPageNum : 総ページ数
// $link : 検索用GETパラメータリンク
// $pageColNum : ページネーション表示数
function pagination($currentPageNum, $totalPageNum, $link = '', $pageColNum = 5){
  //現在のページが、総ページ数と同じかつそうページ数が表示項目以上なら、左リンクに４個出す
  if($currentPageNum == $totalPageNum && $totalPageNum >= $pageColNum){
    $minPageNum = $currentPageNum-4;
    $maxPageNum = $currentPageNum;
    //現在のページ数が、そうページ数の１ページ前なら、左にリンク３個、みぎに１個出す
  }elseif($currentPageNum == ($totalPageNum-1) && $totalPageNum >= $pageColNum){
    $minPageNum = $currentPageNum-3;
    $maxPageNum = $currentPageNum+1;
    // 現ページが2の場合は左にリンク１個、右にリンク３個だす。
  }elseif( $currentPageNum == 2 && $totalPageNum >= $pageColNum){
    $minPageNum = $currentPageNum-1;
    $maxPageNum = $currentPageNum+3;
  // 現ページが1の場合は左に何も出さない。右に５個出す。
  }elseif( $currentPageNum == 1 && $totalPageNum >= $pageColNum){
    $minPageNum = $currentPageNum;
    $maxPageNum = 5;
  // 総ページ数が表示項目数より少ない場合は、総ページ数をループのMax、ループのMinを１に設定
  }elseif($totalPageNum <= $pageColNum){
    $minPageNum = 1;
    $maxPageNum = $totalPageNum;
  // それ以外は左に２個出す。
  }else{
    $minPageNum = $currentPageNum-2;
    $maxPageNum = $currentPageNum+2;
  }
  echo '<div class="pagination">';
    echo '<ul class="pagination-list">';
      if($currentPageNum != 1){
        echo '<li class="list-item"><a href="?p=1'.$link.'">&lt;</a></li>';
      }
      for($i = $minPageNum; $i <= $maxPageNum; $i++){
        echo '<li class="list-item ';
        if($currentPageNum == $i ){ echo 'active'; }
        echo '"><a href="?p='.$i.$link.'">'.$i.'</a></li>';
      }
      if($currentPageNum != $maxPageNum && $maxPageNum > 1){
        echo '<li class="list-item"><a href="?p='.$maxPageNum.$link.'">&gt;</a></li>';
      }
    echo '</ul>';
  echo '</div>';
}
function appendGetParam($arr_del_key = array()){
  if(!empty($_GET)){
    $str = '?';
    foreach($_GET as $key => $val){
      if(in_array($key, $arr_del_key, true)){
        $str .= $key.'='.$val.'&';
      }
    }
    $str = mb_substr($str, 0, -1, "UTF-8");
    return $str;
  }
}

//画像処理
function uploadImg($file, $key)
{
  debug('画像アップロード処理開始');
  debug('file情報' . print_r($_FILES, true));

  if (isset($file['error']) && is_int($file['error'])) {
    try {
      //バリデーション
      //$file['error']の値を確認。配列ないには「UPLOAD_ERR_OK」などの定数が入っている
      //「UPLOAD_ERR_OK」などの定数はphpでファイルアップロード時に自動的に定義される。定数には値として０や１などの数値が入っている
      switch ($file['error']) {
        case UPLOAD_ERR_OK: //ok
          break;
        case UPLOAD_ERR_NO_FILE:
          throw new RuntimeException('ファイルが選択されていません');
        case UPLOAD_ERR_INI_SIZE:
        case UPLOAD_ERR_FORM_SIZE:
          throw new RuntimeException('ファイルサイズが大き過ぎます');
        default:
          throw new RuntimeException('その他のエラーが発生しました');
      }
      //$file['mime']の値はブラウザ側で偽装可能なのでMIMEタイプを自前でチェックする
      //exif_imagetype関数は「IMAGETYPE_GIF」「IMAGETYPE_JPEG」などの定数を返す
      $type = @exif_imagetype($file['tmp_name']);
      if (!in_array($type, [IMAGETYPE_GIF, IMAGETYPE_JPEG, IMAGETYPE_PNG], true)) {
        throw new Exception('画像形式が未対応です');
      }
      $path = 'upload/' . sha1_file($file['tmp_name']) . image_type_to_extension($type);
      if (!move_uploaded_file($file['tmp_name'], $path)) {
        throw new Exception('ファイル保存時にエラーが発生しました');
      }
      //保存したパーミッションを（権限）を変更する
      chmod($path, 0664);
      debug('ファイルは正常にアップロードされました');
      debug('ファイルパス:' . $path);
      return $path;
    } catch (RuntimeException $e) {
      debug($e->getMessage());
      global $err_msg;
      $err_msg['$key'] = $e->getMessage();
    }
  }
}
//サニタイズ
function sanitize($str)
{
  return htmlspecialchars($str, ENT_QUOTES);
}

//フォーム入力保持
function getFormData($str, $flg = false)
{
  if ($flg) {
    $method = $_GET;
  } else {
    $method = $_POST;
  }
  global $dbFormData;
  global $err_msg;
  //ユーザーデータがある場合
  if (!empty($dbFormData[$str])) {
    //フォームにエラーがある場合
    if (!empty($err_msg[$str])) {
      //postにデータがある場合
      if (isset($method[$str])) {
        return sanitize($method[$str]);
      } else {
        //ない場合（基本ありえない）
        return sanitize($dbFormData[$str]);
      }
    } else {
      //postにデータがありdbと情報が違う場合
      if (isset($method[$str]) && $method[$str] !== $dbFormData[$str]) {
        return sanitize($method[$str]);
      } else {
        return sanitize($dbFormData[$str]);
      }
    }
  } else {
    if (isset($method[$str])) {
      return sanitize($method[$str]);
    }
  }
}

function sendMail($from, $to, $subject, $comment){
  if(!empty($to) && !empty($subject) && !empty($comment)){
      //文字化けしないように設定（お決まりパターン）
      mb_language("Japanese"); //現在使っている言語を設定する
      mb_internal_encoding("UTF-8"); //内部の日本語をどうエンコーディング（機械が分かる言葉へ変換）するかを設定
      
      //メールを送信（送信結果はtrueかfalseで返ってくる）
      $result = mb_send_mail($to, $subject, $comment, "From: ".$from);
      //送信結果を判定
      if ($result) {
        debug('メールを送信しました。');
      } else {
        debug('【エラー発生】メールの送信に失敗しました。');
      }
  }
}





//======================
//データベース
//=====================
function dbConnect()
{

  $dsn = 'mysql:dbname=heroku_9ed8a0aff968656;host=us-cdbr-east-02.cleardb.com;charset=utf8';
  $user = 'b7418c95d0de12';
  $password = 'b41d2be8';
  $options = array(
    //sql実行失敗時にはエラーコードのみ設定
    PDO::ATTR_ERRMODE => PDO::ERRMODE_SILENT,
    //デフォルトフェッチモードを連想配列に
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    //バッファードクエリを使う（１度に結果セットを取得し、サーバー負荷を軽減）
    //selectで得た結果に対してもrowcountメソッドを使えるようにする
    PDO::MYSQL_ATTR_USE_BUFFERED_QUERY => TRUE,
  );
  //PDOオブジェクト生成（DBへ接続）
  $dbh = new PDO($dsn, $user, $password, $options);
  return $dbh;
}

function queryPost($dbh, $sql, $data)
{
  //クエリ作成
  $stmt = $dbh->prepare($sql);
  //プレースホルダーに値をセットしsql文を実行
  if (!$stmt->execute($data)) {
    debug('クエリ失敗しました');
    debug('失敗したsql:' . print_r($stmt, true));
    global $err_msg;
    $err_msg['common'] = MSG07;
    return 0;
  } else {
    return $stmt;
  }
}
function isLike($u_id, $p_id){
  debug('お気に入り情報があるか確認します。');
  debug('ユーザーID：'.$u_id);
  debug('商品ID：'.$p_id);
  //例外処理
  try {
    // DBへ接続
    $dbh = dbConnect();
    // SQL文作成
    $sql = 'SELECT * FROM favorite WHERE product_id = :p_id AND user_id = :u_id';
    $data = array(':u_id' => $u_id, ':p_id' => $p_id);
    // クエリ実行
    $stmt = queryPost($dbh, $sql, $data);

    if($stmt->rowCount()){
      debug('お気に入りです');
      return true;
    }else{
      debug('特に気に入ってません');
      return false;
    }

  } catch (Exception $e) {
    error_log('エラー発生:' . $e->getMessage());
  }
}
//================================
// ログイン認証
//================================
function isLogin(){
  // ログインしている場合
  if( !empty($_SESSION['login_date']) ){
    debug('ログイン済みユーザーです。');

    // 現在日時が最終ログイン日時＋有効期限を超えていた場合
    if( ($_SESSION['login_date'] + $_SESSION['login_limit']) < time()){
      debug('ログイン有効期限オーバーです。');

      // セッションを削除（ログアウトする）
      session_destroy();
      return false;
    }else{
      debug('ログイン有効期限以内です。');
      return true;
    }

  }else{
    debug('未ログインユーザーです。');
    return false;
  }
}

function getGood($p_id){
  try{
    $dbh = dbConnect();
    $sql = 'SELECT * FROM favorite WHERE product_id = :p_id AND delete_flg = 0';
    $data = array(':p_id' => $p_id);
    $stmt = queryPost($dbh, $sql, $data);
    if($stmt){
      return $stmt->fetchAll();
    }else{
      return false;
    }
  }catch(Exception $e){
    error_log('エラー発生'.$e->getMessage());
  }
}
function getMyProducts($u_id){
  try{
    $dbh = dbConnect();
    $sql = 'SELECT * FROM product WHERE user_id = :u_id AND delete_flg = 0';
    $data = array(':u_id' => $u_id);
    $stmt = queryPost($dbh, $sql, $data);
    if($stmt){
      return $stmt->fetchAll();
    }else{
      return false;
    }
  }catch(Exception $e){
    error_log('エラー発生：' .$e->getMessage());
  }
}
function getMyLike($u_id){
  try{
    $dbh = dbConnect();
    $sql = 'SELECT * FROM favorite AS f LEFT JOIN product AS p ON f.product_id = p.id WHERE f.user_id = :u_id AND f.delete_flg = 0';
    $data = array(':u_id' => $u_id);
    $stmt = queryPost($dbh, $sql, $data);
    if($stmt){
      return $stmt->fetchAll();
    }else{
      return false;
    }
  }catch(Exception $e){
    error_log('エラー発生：' .$e->getMessage());
  }
}