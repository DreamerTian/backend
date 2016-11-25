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
	<div class="wrap">
		<ul class="nav nav-tabs">
			<li><a href="<?php echo U('Indexadmin/imgLists');?>">图文回复列表</a></li>
			<li><a href="<?php echo U('Indexadmin/add_imgs');?>" target="_self">添加图文回复</a></li>
			<li class="active"><a href="<?php echo U('Indexadmin/edit_imgs');?>" target="_self">编辑图文回复</a></li>
		</ul>
		<form method="post" class="form-horizontal js-ajax-form" action="<?php echo U('Weixin/Indexadmin/edit_imgs_post');?>">
			<fieldset>
				<div class="control-group">
					<label class="control-label">关键字：</label>
					<div class="controls">
						<select name="keyword">
							<?php echo ($option); ?>
						</select>
						<span class="form-required">请选择关键字</span>
					</div>
				</div>
				<div class="control-group">
					<label class="control-label">标题：</label>
					<div class="controls">
						<input type="text" name="title" required placeholder="请输入标题" value="<?php echo ($info["title"]); ?>">
						<span class="form-required">*</span>
					</div>
				</div>
				<div class="control-group">
					<label class="control-label">链接地址：</label>
					<div class="controls">
						<input type="text" name="url" required placeholder="请输入链接地址" value="<?php echo ($info["url"]); ?>">
						<span class="form-required">*</span>
					</div>
				</div>
				<div class="control-group">
					<div style="float: left">
						<label class="control-label" style="margin-top: 50px">头像地址： </label>
						<div class="controls">
							<div>
								<input type="hidden" name="picurl" id="thumb" value="">
								<a href="javascript:void(0);" onclick="flashupload('thumb_images', '附件上传','thumb',thumb_images,'1,jpg|jpeg|gif|png|bmp,1,,,1','','','');return false;">
									<?php if(empty($info['picurl'])): ?><img src="/admin/themes/simplebootx/Public/assets/images/default-thumbnail.png" id="thumb_preview" width="135" style="cursor: hand" />
										<?php else: ?>
										<img src="<?php echo sp_get_asset_upload_path($info['picurl']);?>" id="thumb_preview" width="135" style="cursor: hand"/><?php endif; ?>
								</a>
								<input type="button" class="btn btn-small" onclick="$('#thumb_preview').attr('src','/admin/themes/simplebootx/Public/assets/images/default-thumbnail.png');$('#thumb').val('');return false;" value="取消图片">
							</div>
						</div>
					</div>
				</div>
				<div class="control-group">
					<label class="control-label">简介：</label>
					<div class="controls">
						<textarea class="form-control" rows="5" name="description" required style="width: 420px"><?php echo ($info["description"]); ?></textarea>
						<span class="form-required">*</span>
					</div>
				</div>
			</fieldset>
			<div class="form-actions">
				<input type="hidden" name="id" value="<?php echo ($info["id"]); ?>">
				<button type="submit" class="btn btn-primary js-ajax-submit"><?php echo L('ADD');?></button>
				<a class="btn" href="<?php echo U('Weixin/Indexadmin/imgLists');?>"><?php echo L('BACK');?></a>
			</div>
		</form>
	</div>
	<script type="text/javascript" src="/public/js/common.js"></script>
	<script type="text/javascript" src="/public/js/content_addtop.js"></script>
	<script type="text/javascript">
		//编辑器路径定义
		var editorURL = GV.DIMAUB;
	</script>
	<script type="text/javascript" src="/public/js/ueditor/ueditor.config.js"></script>
	<script type="text/javascript" src="/public/js/ueditor/ueditor.all.min.js"></script>

	<script type="text/javascript">
		//iframe层 上传图片
		$('.uploadImg').click(function(event) {
			layer.open({
				type: 2,
				title: '图片上传',
				area: ['480px', '600px'],
				fix: false, //不固定
				maxmin: false,
				scrollbar: false,
				content: '<?php echo U("Weixin/Indexadmin/add");?>'
			});
		});
	</script>
</body>
</html>