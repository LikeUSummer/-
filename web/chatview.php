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
				<button onclick="window.location.href='friendsview.php'" class="btn btn-default pull-right"><i class="glyphicon glyphicon-log-out"></i></button>
            </div>
            <div class="box-body">
              	<div class="direct-chat-messages" id="chat-box" style="height:100%;">

              	</div>
	         	<div class="input-group input-group-lg">
	                <input id="search-input" type="text" class="form-control">
	                <span class="input-group-btn">
	                    <button onclick="search();" style="margin-top:0px;" class="btn btn-success btn-flat"><i class='fa fa-search'></i></button>
	                </span>
	            </div>
				<table id="chat_table" class="table table-bordered table-hover" style="width: 100%;display:none;">
	 			<thead>
					<tr>
					  <th>发送者</th>
					  <th>内容</th>
					  <th>类型</th>
					  <th>时间</th>
					</tr>
				</thead>
				</table>
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
		var options={
			"processing": true,
	        "serverSide": true,
	        "ajax": {
	        	"url":"chatdata.php",
	        	"type":"GET",
	        	"data":function(data){data['id']=id;}//附加请求参数
	        },	
	        "columns" : [{"visible": false,"data":null,"defaultContent":""},
	        			{"visible": false,"data":null,"defaultContent":""},
	        			{"visible": false,"data":null,"defaultContent":""},
	        			{"visible": false,"data":null,"defaultContent":""}],
	        "aaSorting": [3,'asc'],
            "fnDrawCallback": get_data,
			"language": {
				"decimal":        "",
				"emptyTable":     "无数据",
				"info":           "",//"第 _START_ 到 _END_ 项（共 _TOTAL_ 项）",
				"infoEmpty":      "",
				"infoFiltered":   "",//"(从 _MAX_ 项中检索)",
				"infoPostFix":    "",
				"thousands":      ",",
				"lengthMenu":     "每页显示 _MENU_ 项",
				"loadingRecords": "正在加载...",
				"processing":     "",
				"search":         "<i class='fa fa-search'></i>",
				"zeroRecords":    "",//"未找到",
				"paginate": {
					"first":      "首页",
					"last":       "尾页",
					"next":       ">",
					"previous":   "<"
				}
			},
			"ordering": true, 
			"searching": false,
			"lengthChange": false,//每页项目数的设置框
			"lengthMenu": [10,20,50,100],//控制分页选项框内是否有下拉菜单，如果没有就只能手动输入
			"paging":true,//是否分页
			"pageLength":20,//初始分页尺寸
			"info":true,
			"autoWidth": true
		}
		//$.fn.dataTable.ext.errMode = 'none';//设置datatable不报错
	    var tab=$('#chat_table').DataTable(options);

	    function search()
		{
			var key=$('#search-input').val();
			tab.search(key).draw();
		}

		function get_data(oSettings)//获取到一页数据后的回调
		{
			var data=JSON.parse(oSettings.jqXHR.responseText);
			process(data['data']);
		}

		function process(data)
		{
			var dir="WeChat/"+id+"/";
			var TEXT=1,SHARING=2,PICTURE=3,RECORDING=4,ATTACHMENT=5,VIDEO=6,VOICE=7;
			var n=data.length;
			$('#chat-box').html("");
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
	</script>
</html>

