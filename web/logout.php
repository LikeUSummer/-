<?php
session_start();

if (isset($_SESSION['user_name']))
{
    //删除cookies
	//setcookie('user_name','', time()-3600);
    //setcookie('PHPSESSID', '', time()-3600);
    //删除会话
    $_SESSION = [];
    session_destroy();
}

header('Location:index.php');
?>
