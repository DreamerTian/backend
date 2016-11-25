<?php if (!defined('THINK_PATH')) exit();?><!doctype html>
<html>
<head>
	<meta charset="utf-8">
	<!-- Set render engine for 360 browser -->
	<meta name="renderer" content="webkit">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <!-- HTML5 shim for IE8 support of HTML5 elements -->
    <!--[if lt IE 9]>
      <script src="https://oss.maxcdn.com/libs/html5shiv/3.7.0/html5shiv.js"></script>
    <![endif]-->

	<link href="/public/simpleboot/themes/<?php echo C('SP_ADMIN_STYLE');?>/theme.min.css" rel="stylesheet">
    <link href="/public/simpleboot/css/simplebootadmin.css" rel="stylesheet">
    <link href="/public/js/artDialog/skins/default.css" rel="stylesheet" />
    <link href="/public/simpleboot/font-awesome/4.4.0/css/font-awesome.min.css"  rel="stylesheet" type="text/css">
    <style>
		.length_3{width: 180px;}
		form .input-order{margin-bottom: 0px;padding:3px;width:40px;}
		.table-actions{margin-top: 5px; margin-bottom: 5px;padding:0px;}
		.table-list{margin-bottom: 0px;}
	</style>
	<!--[if IE 7]>
	<link rel="stylesheet" href="/public/simpleboot/font-awesome/4.4.0/css/font-awesome-ie7.min.css">
	<![endif]-->
<script type="text/javascript">
//全局变量
var GV = {
    DIMAUB: "/",
    JS_ROOT: "public/js/",
    TOKEN: ""
};
</script>
<!-- Le javascript
    ================================================== -->
    <!-- Placed at the end of the document so the pages load faster -->
    <script src="/public/js/jquery.js"></script>
    <script src="/public/js/wind.js"></script>
    <script src="/public/simpleboot/bootstrap/js/bootstrap.min.js"></script>
<?php if(APP_DEBUG): ?><style>
		#think_page_trace_open{
			z-index:9999;
		}
	</style><?php endif; ?>
</head>
<body>
	<div class="wrap js-check-wrap">
		<ul class="nav nav-tabs">
			<li class="active"><a href="javascript:;">多图文推送列表</a></li>
			<li><a href="<?php echo U('Indexadmin/add_news');?>" target="_self">添加多图文推送</a></li>
		</ul>
		<form class="well form-search" method="post" action="<?php echo U('Indexadmin/news');?>">
			关键字：
			<select class="select_2" name="type">
				<option value="">全部</option>
				<?php echo ($option); ?>
			</select>
			<input type="submit" class="btn btn-primary" value="搜索" />
		</form>
		<form class="js-ajax-form" action="" method="post">
			<div class="table-actions">
				<button class="btn btn-primary btn-small js-ajax-submit" type="submit" data-action="<?php echo U('Indexadmin/del_news');?>" data-subcheck="true" data-msg="你确定删除吗？">删除</button>
			</div>
			<table class="table table-hover table-bordered table-list">
				<thead>
					<tr>
						<th width="15"><label><input type="checkbox" class="js-check-all" data-direction="x" data-checklist="js-check-x"></label></th>
						<th>ID</th>
						<th>关键字</th>
						<th>预览</th>
						<th>操作</th>
					</tr>
				</thead>
				<?php if(is_array($lists)): foreach($lists as $key=>$vo): ?><tr>
					<td><input type="checkbox" class="js-check" data-yid="js-check-y" data-xid="js-check-x" name="ids[]" value="<?php echo ($vo["id"]); ?>" title="ID:<?php echo ($vo["id"]); ?>"></td>
					<td align="center"><?php echo ($vo["id"]); ?></td>
					<td><?php echo ($vo['keyword']); ?></td>
					<td><button class="btn btn-info btn-sm" type="button" onclick="preview('<?php echo ($vo["id"]); ?>')">预览</button></td>
					<td>
						<a href="<?php echo U('Indexadmin/del_news',array('id'=>$vo['id']));?>" class="btn btn-danger" data-msg="<?php echo L('DELETE_USER_CONFIRM_MESSAGE');?>"><i class="fa fa-trash-o"></i></a>
					</td>
				</tr><?php endforeach; endif; ?>
			</table>

			<div class="pagination"><?php echo ($Page); ?></div>
		</form>
	</div>
	<script src="/public/js/common.js"></script>
	<script src="/public/js/layer/layer-min.js"></script>
	<script>
		function preview(id){
			layer.open({
				type: 2,
				title: false,
				closeBtn: false,
				area: ['320px','500px'],
				skin: 'layui-layer-nobg', //没有背景色
				shadeClose: true,
				content: "<?php echo U('Indexadmin/preview');?>?id="+id,
			});
		}
		function refersh_window() {
			var refersh_time = getCookie('refersh_time');
			if (refersh_time == 1) {
				window.location = "<?php echo U('Indexadmin/news',$formget);?>";
			}
		}
		setInterval(function() {
			refersh_window();
		}, 2000);

		$(function() {
			setCookie("refersh_time", 0);
			Wind.use('ajaxForm', 'artDialog', 'iframeTools', function() {
				//批量移动
				$('.js-articles-move').click(function(e) {
					var str = 0;
					var id = tag = '';
					$("input[name='ids[]']").each(function() {
						if ($(this).attr('checked')) {
							str = 1;
							id += tag + $(this).val();
							tag = ',';
						}
					});
					if (str == 0) {
						art.dialog.through({
							id : 'error',
							icon : 'error',
							content : '您没有勾选信息，无法进行操作！',
							cancelVal : '关闭',
							cancel : true
						});
						return false;
					}
					var $this = $(this);

					var httpurl = "<?php echo U('article/move');?>";

					art.dialog.open(httpurl, {
						title : "批量移动",
						width : "80%"
					});
				});
			});
		});
	</script>
</body>
</html>