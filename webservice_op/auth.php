<?php

//====================
//ログイン認証
//=====================
//ログインしている場合
if(!empty($_SESSION['login_date'])){
  //現在日時が最終ログイン＋有効期限を超えていた場合
  if(($_SESSION['login_date'] + $_SESSION['login_limit']) < time()){
    debug('ログイン有効期限が過ぎています');

    //セッションを削除する（ログアウト）
    session_destroy();
    //ログインページへ
    header("Location:login.php");
  }else{
    debug('ログイン有効期限内です');
    //最終ログイン日時を現在に更新
    $_SESSION['login_date'] = time();
  
    //現在実行中のスクリプトファイル名がlogin.phpの場合
    //$_SERVER['PHP_SELF']はドメインからのパスを返すので、今回だと「/webservice_op/login.php」が帰ってくるので
    //さらにbasename関数を使うことによりファイル名だけを取り出せる
    if(basename($_SERVER['PHP_SELF']) === 'login.php'){
     debug('マイページへ');
      header("Location:mypage.php"); //マイページへ

    }
  }
}else{
  debug('未ログインユーザーです');
  if(basename($_SERVER['PHP_SELF']) !== 'login.php'){
    header("Location:login.php");//ログインページへ
  }

}
