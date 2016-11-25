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
			<li class="active"><a href="javascript:;">关键字管理</a></li>
			<li><a href="<?php echo U('Indexadmin/add_keywords');?>" target="_self">添加关键字</a></li>
		</ul>
		<form class="well form-search" method="post" action="<?php echo U('Indexadmin/keywords');?>">
			分类： 
			<select class="select_2" name="type">
				<option value='0'>全部</option>
				<option value='1'>文本</option>
				<option value='2'>图片</option>
				<option value='3'>语音</option>
				<option value='4'>视频</option>
				<option value='5'>音乐</option>
				<option value='6'>图文</option>
				<option value='7'>多图文</option>
			</select>
			关键字： 
			<input type="text" name="keyword" style="width: 200px;" value="<?php echo ($formget["keyword"]); ?>" placeholder="请输入关键字...">
			<input type="submit" class="btn btn-primary" value="搜索" />
		</form>
		<form class="js-ajax-form" action="" method="post">
			<div class="table-actions">
				<button class="btn btn-primary btn-small js-ajax-submit" type="submit" data-action="<?php echo U('Indexadmin/del_keywords');?>" data-subcheck="true" data-msg="你确定删除吗？">删除</button>
			</div>
			<table class="table table-hover table-bordered table-list">
				<thead>
					<tr>
						<th width="15"><label><input type="checkbox" class="js-check-all" data-direction="x" data-checklist="js-check-x"></label></th>
						<th>ID</th>
						<th>关键字</th>
						<th>点击量</th>
						<th>回复类型</th>
						<th>操作</th>
					</tr>
				</thead>
				<?php if(is_array($lists)): foreach($lists as $key=>$vo): ?><tr>
					<td><input type="checkbox" class="js-check" data-yid="js-check-y" data-xid="js-check-x" name="ids[]" value="<?php echo ($vo["id"]); ?>" title="ID:<?php echo ($vo["id"]); ?>"></td>
					<td align="center"><?php echo ($vo["id"]); ?></td>
					<td><?php echo ($vo['keyword']); ?></td>
					<td><?php echo ($vo['count']); ?></td>
					<td>
						<?php echo ($vo['type'] == 1 ?'文本':''); ?>
						<?php echo ($vo['type'] == 2 ?'图片':''); ?>
						<?php echo ($vo['type'] == 3 ?'语音':''); ?>
						<?php echo ($vo['type'] == 4 ?'视频':''); ?>
						<?php echo ($vo['type'] == 5 ?'音乐':''); ?>
						<?php echo ($vo['type'] == 6 ?'图文':''); ?>
						<?php echo ($vo['type'] == 7 ?'多图文':''); ?>
					</td>
					<td>
						<a href="<?php echo U('Indexadmin/edit_keywords',array('id'=>$vo['id']));?>" class="btn btn-primary" ><i class="fa fa-pencil"></i></a>
						<a href="<?php echo U('Indexadmin/del_keywords',array('id'=>$vo['id']));?>" class="btn btn-danger" data-msg="<?php echo L('DELETE_USER_CONFIRM_MESSAGE');?>"><i class="fa fa-trash-o"></i></a>
					</td>
				</tr><?php endforeach; endif; ?>
			</table>

			<div class="pagination"><?php echo ($Page); ?></div>
		</form>
	</div>
	<script src="/public/js/common.js"></script>
	<script>
		function refersh_window() {
			var refersh_time = getCookie('refersh_time');
			if (refersh_time == 1) {
				window.location = "<?php echo U('Indexadmin/keywords',$formget);?>";
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