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
<table class="table table-bordered table-hover">
    <thead>
    <tr>

        <td class="text-center">
            标题
        </td>
        <td class="text-center">
            描述
        </td>
        <td class="text-center">
            封面图片
        </td>
        <td class="text-center">操作</td>
    </tr>
    </thead>
    <tbody>

    <?php if(is_array($lists)): $i = 0; $__LIST__ = $lists;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$list): $mod = ($i % 2 );++$i;?><tr>
            <td class="text-center"><?php echo ($list["title"]); ?></td>
            <td class="text-center"><?php echo ($list["description"]); ?></td>
            <td class="text-center"><button class="btn btn-info btn-sm" type="button" onclick="preview('<?php echo ($list["picurl"]); ?>')">预览</button></td>

            <td class="text-center">
                <a onclick="parent.selected('<?php echo ($list["picurl"]); ?>','<?php echo ($list["title"]); ?>','<?php echo ($list["id"]); ?>')" data-toggle="tooltip" title="" class="btn btn-primary" data-original-title="选择">选择</a>
            </td>
        </tr><?php endforeach; endif; else: echo "" ;endif; ?>

    </tbody>
</table>
<script src="/public/js/layer/layer-min.js"></script>
<script>

    function preview(url){
        layer.open({
            type: 1,
            title: false,
            closeBtn: false,
            area: ['150px', '150px'],
            skin: 'layui-layer-nobg', //没有背景色
            shadeClose: true,
            content: "<img width='150px' height='150px' src='"+url+"'>"
        });
    }
</script>