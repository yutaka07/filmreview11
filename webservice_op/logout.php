<?php
//共通関数・変数読み込み
require('function.php');

debug('「「「「「「「「「「「「「「「「「「「「');
debug('ログインページ');
debug('「「「「「「「「「「「「「「「「「「「「「「');
debugLogStart();

//セッション削除
session_destroy();
debug('ログインぺージへ遷移します');
//ログインページへ
header("Location:login.php");
?>