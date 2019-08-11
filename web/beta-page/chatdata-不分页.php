<?php
session_start();
header("Content-type: text/html; charset=utf-8");
if (!isset($_SESSION['user_name']))
{
    header('Location:index.php');
    exit();
}

function gbk2utf8($string){
	return iconv("gbk","utf-8",$string);
}

function utf82gbk($string){
	return iconv("utf-8","gbk",$string);
}

$id=$_GET['id'];

$server_addr = "127.0.0.1";
$connect_opt = array(
    "Database" => "WeChat",
    "Uid" => "sa",
    "PWD" => "Akakk41jun"
);
//连接数据库
$connection = sqlsrv_connect($server_addr,$connect_opt);

if ($connection==null){
	die(print_r(sqlsrv_errors(),true));
}

//查询聊天记录
$data=array();
$sql = "SELECT * FROM F" . $id . " ORDER BY Time";
$results = sqlsrv_query($connection,$sql);
if($results){
	while ($row = sqlsrv_fetch_array($results,SQLSRV_FETCH_ASSOC)){
		array_push($data, array('sender'=>gbk2utf8($row['Sender']),'content'=>gbk2utf8($row['Content']),'type'=>$row['Type'],'time'=>$row['Time']->format('Y-m-d H:i:s')));
	}
	sqlsrv_free_stmt($results);
}
else
	die(print_r(sqlsrv_errors(), true));

//关闭连接
sqlsrv_close($connection);

echo json_encode($data);

?>
