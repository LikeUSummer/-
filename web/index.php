<?php
session_start();
header("Content-type: text/html; charset=utf-8");
$error=0;
$salt=mt_rand();//用于校验身份的随机数

if(isset($_POST['name']))
{
	$name=$_POST['name'];
	$pwd=$_POST['password'];	
	$salt=$_POST['salt'];
	if(check($name,$pwd,$salt))
	{
		//setcookie('user_name', $data['user_name']);//设置cookies
        $_SESSION['user_name'] = $name;//设置session会话
        //$life_time = 3600;//有效期1小时
		//setcookie(session_name(), session_id(), time() + $life_time, "/");
		header('Location:friendsview.php');//登录成功，跳转到好友列表页面
	}
	else
		$error=1;
}

function check($name,$pwd,$salt)
{
	$server_addr = "127.0.0.1";
	$connect_opt = array(
	    "Database" => "WeChat",
	    "Uid" => "sa",
	    "PWD" => "Akakk41jun"
	);
	//连接数据库
	$connection = sqlsrv_connect($server_addr,$connect_opt);

	if ($connection==null)
		die(print_r(sqlsrv_errors(),true));

	$sql="SELECT Password FROM Login WHERE Name='" . $name . "';";
	$results = sqlsrv_query($connection,$sql);
	if($results)
	{
		$row = sqlsrv_fetch_array($results,SQLSRV_FETCH_ASSOC);
		$pwd_md5 = md5($row['Password']);	
		$final_md5 = md5($pwd_md5 . $salt);
		if($final_md5==$pwd)
		{
			return true;
		}
		
		sqlsrv_free_stmt($results);
	}
	return false;	
}

?>

<!DOCTYPE html>
<html>
	<head>
		<meta charset="utf-8">
		<meta http-equiv="X-UA-Compatible" content="IE=edge">
		<meta name="viewport" content="width=device-width, initial-scale=1.0">
		<title>登录</title>
		<link href="plugins/bootstrap/css/bootstrap.css" rel="stylesheet" type="text/css"/>
		<link href="plugins/font-awesome/css/font-awesome.css" rel="stylesheet" type="text/css"/>
		<link href="plugins/AdminLTE/css/AdminLTE.min.css" rel="stylesheet" type="text/css"/>
	</head>

	<body>
		<div class="login-box">
			<div class="login-logo">
	    		<b><i class='fa fa-wechat'></i>  微史记</b><h4>对话,见证我的历史</h4>
	  		</div>	
			<div class="login-box-body">
			<form id="login-form" action="index.php" method="post" onsubmit="login();">
				<div class="form-group has-feedback">
					<input id="name" name="name" class="form-control" placeholder="用户名">
					<span class="glyphicon glyphicon-user form-control-feedback"></span>
				</div>
				<div class="form-group has-feedback">
					<input type="password" name="password" id="password" class="form-control" placeholder="密码">
					<span class="glyphicon glyphicon-lock form-control-feedback"></span>
				</div>
				<input type="text" name="salt" id="salt" style="display: none;">
				<div class="row pull-right" id="info" style="color:#ffaa00;"></div>
				<div class="row">
					<button type="submit" class="btn btn-default btn-block btn-flat">登录</button>
				</div>
	    	</form>

	    	</div>
        </div>		
	</body>
	<script src="plugins/jquery/jquery.min.js"></script>
	<script src="plugins/bootstrap/js/bootstrap.js"></script>	
	<script src="plugins/AdminLTE/js/adminlte.min.js"></script>
	<script src="plugins/md5/md5.js"></script>
	<script type="text/javascript">
		<?php
			echo "var error=" . $error . ";";
		?>
		if(error)
			$('#info').text('用户名或密码错误');

		$('#name').on('input',function(){$('#info').text('');})
		$('#password').on('input',function(){$('#info').text('');})

		function login()
		{
			<?php
				echo "var salt=" . $salt . ";";
			?>
			var pwd_md5=hex_md5(hex_md5($('#password').val()));
			var final_md5=hex_md5(pwd_md5+salt);
			$('#password').val(final_md5);
			$('#salt').val(salt);
		}
	</script>
</html>

