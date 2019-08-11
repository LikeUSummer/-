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
		<title>好友列表</title>
		<link href="plugins/bootstrap/css/bootstrap.css" rel="stylesheet" type="text/css"/>
		<link href="plugins/font-awesome/css/font-awesome.css" rel="stylesheet" type="text/css"/>
		<link href="plugins/datatables.net-bs/css/dataTables.bootstrap.min.css" rel="stylesheet" type="text/css"/>
		<link href="plugins/AdminLTE/css/AdminLTE.min.css" rel="stylesheet" type="text/css"/>

	</head>

	<body>
	<div class="col-md-6 col-sm-12 col-lg-5">
	<div class="box box-success">
        <div class="box-header with-border">
			<i class='fa fa-wechat'></i><h1 class='box-title'>微史记</h1>
			<button onclick="window.location.href='logout.php'" class="btn btn-default pull-right"><i class="glyphicon glyphicon-off"></i></button>
        </div>
        <div class="box-body">
         	<div class="input-group input-group-lg">
                <input id="search-input" type="text" class="form-control">
                <span class="input-group-btn">
                    <button onclick="search();" style="margin-top:0px;" class="btn btn-success btn-flat"><i class='fa fa-search'></i></button>
                </span>
            </div>
			<table id="friends_table" class="table table-bordered table-hover" style="width: 100%;">
			<thead style="display: none;">
				<tr>
				  <th><h4 class='box-title'><b>朋友们</b></h4></th>
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
		var options={
			"processing": true,
	        "serverSide": true,
	        "ajax": "friendsdata.php",	
	        "columns" : [{"data" : 0}],
	        //"aaSorting": [0,'asc'],
            //"fnDrawCallback": function (oSettings) {alert(oSettings.jqXHR.responseText);},
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
					"next":       "下一页",
					"previous":   "上一页"
				}
			},
			"ordering": true, 
			"searching": false,
			"lengthChange": false,//每页项目数的设置框
			"lengthMenu": [10,20,50,100],//控制分页选项框内是否有下拉菜单，如果没有就只能手动输入
			"paging":false,//是否分页
			"info":true,
			"autoWidth": true
		}

	    var tab=$('#friends_table').DataTable(options);

	    $('#friends_table tbody').on('click','tr',function (){
	        var data = tab.row(this).data();
	        window.location.href='chatview.php?name='+data[0]+'&id='+data[1];
    	});
	    //重绘，给好友添加头像
		tab.on( 'draw', function (){
    		var info = tab.page.info();
    		var data = tab.data();
    		var n=info.end-info.start;//当前页实际项数
    		for(var i=0;i<n;i++)
    		{
    			var head_img="WeChat/"+data[i][1]+"/"+data[i][1]+".jpg ";
    			$("td").eq(i).html("<img class='direct-chat-img' src='"+head_img+"'><h4 class='box-title'>&nbsp;&nbsp;"+data[i][0]+"</h4>");
    		}
		});

		function search()
		{
			var key=$('#search-input').val();
			tab.search(key).draw();
		}
	</script>
</html>

