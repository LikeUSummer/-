<?php
session_start();
header("Content-type: text/html; charset=utf-8");
if (!isset($_SESSION['user_name']))
{
    header('Location:index.php');
    exit();
}
?>
<!DOCTYPE html>
<html>
	<head>
		<meta charset="utf-8">
		<meta http-equiv="X-UA-Compatible" content="IE=edge">
		<meta name="viewport" content="width=device-width, initial-scale=1.0">
		<title>聊天记录</title>
		<link href="plugins/bootstrap/css/bootstrap.css" rel="stylesheet" type="text/css"/>
		<link href="plugins/font-awesome/css/font-awesome.css" rel="stylesheet" type="text/css"/>
		<link href="plugins/datatables.net-bs/css/dataTables.bootstrap.min.css" rel="stylesheet" type="text/css"/>
		<link href="plugins/AdminLTE/css/AdminLTE.min.css" rel="stylesheet" type="text/css"/>
		<style type="text/css">
			a{text-decoration:underline;color:#ffffff;display:block;overflow:hidden;}
		</style>
	</head>

	<body>
		<div class="col-md-6 col-sm-12 col-lg-5">
		<div class="box box-success direct-chat direct-chat-success">
            <div class="box-header with-border">
				<i class="fa fa-comments-o"></i>
				<?php
					echo "<h3 class='box-title'>". $_GET['name'] ."</h3>"
				?>
				<button onclick="window.location.href='friendsview.php'" class="btn btn-normal pull-right"><i class="glyphicon glyphicon-log-out"></i></button>
            </div>
            <div class="box-body">
              <div class="direct-chat-messages" id="chat-box" style="height:100%;">

              </div>
            </div>
        </div>
    	</div>
	</body>
	<script src="plugins/jquery/jquery.min.js"></script>
	<script src="plugins/bootstrap/js/bootstrap.js"></script>	
	<script src="plugins/datatables.net/js/jquery.dataTables.min.js"></script>
	<script src="plugins/datatables.net-bs/js/dataTables.bootstrap.min.js"></script>
	<script src="plugins/AdminLTE/js/adminlte.min.js"></script>
	<script type="text/javascript">
		<?php
			echo "var id=" . $_GET["id"] . ";";
			echo "var name='" . $_GET["name"] . "';";
		?>
		
		function process(data)
		{
			var dir="WeChat/"+id+"/";
			var TEXT=1,SHARING=2,PICTURE=3,RECORDING=4,ATTACHMENT=5,VIDEO=6,VOICE=7;
			var n=data.length;
			for(var i=0;i<n;i++)
			{
				var me,head_img;
				if(data[i]['sender']=='我')
				{
					me=1;
					head_img="WeChat/me.jpg";
					data[i]['sender']="";
				}
				else
				{
					me=0;
					head_img=dir+id+".jpg ";
					if(data[i]['sender']=='你')
						data[i]['sender']=name;
				}
				//处理图像和文件内容
				var type=data[i]['type'];
				if(type==PICTURE)
					data[i]['content']="<img alt='[图像丢失]' src='"+dir+data[i]['content']+"' width=100%/>";
				else if(type!=TEXT)
				{
					if(type==SHARING)
						data[i]['content']="<a href='"+data[i]['content']+"'>"+data[i]['content']+"</a>";
					else
						data[i]['content']="<a href='"+dir+data[i]['content']+"'>"+data[i]['content']+"</a>";
				}
				//生成view
				if(me)
					$('#chat-box').append(
					"<div class='direct-chat-msg right'>\
	                <div class='direct-chat-info clearfix'>\
	                <span class='direct-chat-name pull-right'>"+data[i]['sender']+"</span>\
	                <span class='direct-chat-timestamp pull-left'>"+data[i]['time']+"</span>\
	                </div>\
	                <img class='direct-chat-img' src='"+head_img+"'>\
	                <div class='direct-chat-text'>"+data[i]['content']+"</div>\
	                </div>"
					);
				else
					$('#chat-box').append(
					"<div class='direct-chat-msg'>\
                  	<div class='direct-chat-info clearfix'>\
                    <span class='direct-chat-name pull-left'>"+data[i]['sender']+"</span>\
                    <span class='direct-chat-timestamp pull-right'>"+data[i]['time']+"</span>\
                  	</div>\
                  	<img class='direct-chat-img' src='"+head_img+"'>\
                  	<div class='direct-chat-text'>"+data[i]['content']+"</div>\
                	</div>"
					);	
			}
		}

		$.ajax({
			type: 'post',
			url: 'chat_data.php?id='+id,
			async: false,
			dataType: 'json',
			success: process,
			error: function () {
			   alert('获取信息失败');
			}
		});	
	</script>
</html>

