<?php
header("Content-type: text/html; charset=utf-8");
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
	</head>

	<body>
		<div class="col-md-6 col-sm-12 col-lg-5">
		<div class="box box-success">
            <div class="box-header ui-sortable-handle" style="cursor: move;">
              <i class="fa fa-comments-o"></i>
              <?php
              	echo "<h3 class='box-title'>". $_GET['name'] ."</h3>"
              ?>
              <button onclick="window.location.href='friendsview.php'" class="btn btn-normal pull-right"><i class="fa fa-list-ul"></i> 返回好友列表</button>
            </div>
            <div class="box-body chat" id="chat-box">

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
		var dir="WeChat/"+id+"/";
		var TEXT=1,SHARING=2,PICTURE=3,RECORDING=4,ATTACHMENT=5,VIDEO=6,VOICE=7;
		
		function process(data)
		{
			var n=data.length;
			for(var i=0;i<n;i++)
			{
				var head_img;
				if(data[i]['sender']=='我')
				{
					head_img="WeChat/me.jpg";
				}
				else
				{
					head_img=dir+id+".jpg ";
					data[i]['sender']=name;
				}
				//处理图像和文件内容
				var type=data[i]['type'];
				if(type==PICTURE)
					data[i]['content']="<img alt='[图像丢失]' src='"+dir+data[i]['content']+"' width=80%/>";
				else if(type!=TEXT)
				{
					if(type==SHARING)
						data[i]['content']="<a href='"+data[i]['content']+"'>"+data[i]['content']+"</a>";
					else
						data[i]['content']="<a href='"+dir+data[i]['content']+"'>"+data[i]['content']+"</a>";
				}
				//生成view
				$('#chat-box').append(
					"<div class='item'>\
					<img src="+head_img+" alt='头像'>\
					<p class='message'>\
					<a href='#' class='name'>\
					<small class='text-muted pull-right'>\
					<i class='fa fa-clock-o'></i>"+data[i]['time']+
					"</small>"+data[i]['sender']+
					"</a>"+data[i]['content']+
					"</p></div>"
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

