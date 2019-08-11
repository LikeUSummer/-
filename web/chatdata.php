<?php
session_start();
header("Content-type: text/html; charset=utf-8");
if (!isset($_SESSION['user_name']))
{
    header('Location:index.php');
    exit();
}

function fatal($msg)
{
    echo json_encode(array(
        "error" => $msg
    ));
    exit();
}

function gbk2utf8($string)
{
	return iconv("gbk","utf-8",$string);
}

function utf82gbk($string)
{
	return iconv("utf-8","gbk",$string);
}

$serverName = "127.0.0.1";
$connectionOption = array(
    "Database" => "WeChat",
    "Uid" => "sa",
    "PWD" => "Akakk41jun"
);
//连接数据库
$conn = sqlsrv_connect($serverName,$connectionOption);

if ($conn==null)
{
	fatal("数据库连接出错：" . sqlsrv_errors());
}

$id=$_GET['id'];
$draw = $_GET['draw'];//用于同步的序号，这个值会直接返回给前台

//排序
$order_column = $_GET['order']['0']['column'];//那一列排序，从0开始
$order_dir = $_GET['order']['0']['dir'];//ase desc 升序或者降序

//拼接排序sql
$order_sql = " ORDER BY Time ASC";
if(isset($order_column))
{
    //$i = intval($order_column);
    $order_sql = " ORDER BY Time ".$order_dir;//都按时间排序
}

//搜索
$search = utf82gbk($_GET['search']['value']);//获取前台传过来的过滤条件

//分页
$limit_flag = isset($_GET['start']) && $_GET['length'] != -1;
$start = 0;
if(isset($_GET['start']))
	$start = $_GET['start'];
$length = $_GET['length'];

//总记录数
$sum_sql = "SELECT COUNT(Sender) AS sum FROM F" . $id;
$records_total = 0;
$results = sqlsrv_query($conn,$sum_sql);
if($results)
{
	$row = sqlsrv_fetch_array($results,SQLSRV_FETCH_ASSOC);
	$records_total =  $row['sum'];
	sqlsrv_free_stmt($results);
}
else
{
	fatal("SQL查询失败：" . sqlsrv_errors());
}

//定义过滤条件查询过滤后的记录数sql
$records_filtered = 0;
$where_sql ="";
if(strlen($search)>0)
{
	$where_sql =" WHERE Sender+Content+CONVERT(varchar(32),Time,120) LIKE '%" .$search."%'";
    	$results = sqlsrv_query($conn,$sum_sql.$where_sql);
	if($results)
	{
		while ($row = sqlsrv_fetch_array($results,SQLSRV_FETCH_ASSOC))
		{
			$records_filtered = $row['sum'];
		}
		sqlsrv_free_stmt($results);
	}
}
else
{
    $records_filtered = $records_total;
}

$total_sql="SELECT * FROM F". $id . $where_sql . $order_sql;
$data=array();
$results = sqlsrv_query($conn,$total_sql);
if($results)
{
	$i=0;
	$end=$start+$length;
	while ($row = sqlsrv_fetch_array($results,SQLSRV_FETCH_ASSOC))
	{
		if(!$limit_flag || ($i>=$start && $i<$end))
		{
			array_push($data, array('sender'=>gbk2utf8($row['Sender']),'content'=>gbk2utf8($row['Content']),'type'=>$row['Type'],'time'=>$row['Time']->format('Y-m-d H:i:s')));
		}
		$i++;
	}
	sqlsrv_free_stmt($results);
}

echo json_encode(array(
    "draw" => intval($draw),
    "recordsTotal" => intval($records_total),
    "recordsFiltered" => intval($records_filtered),
    "data" => $data
),JSON_UNESCAPED_UNICODE);

//关闭连接
sqlsrv_close($conn);

/*
测试请求：
http://127.0.0.1/chatdata.php?draw=1&columns[0][data]=0&columns[0][name]=&columns[0][searchable]=true&columns[0][orderable]=true&columns[0][search][value]=&columns[0][search][regex]=false&order[0][column]=1&order[0][dir]=desc&start=0&length=10&search[value]=&search[regex]=false&_=1418644693360
*/
?>
