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
            <li class="active"><a href="javascript:;">用户列表</a></li>
        </ul>
        
        <form class="well form-search" method="get" action="<?php echo U('member/index');?>" id="search">
            用户昵称： 
            <input type="text" name="nickname" style="width: 200px;" value="<?php echo ($_GET['nickname']); ?>" placeholder="请输入关键字...">
            <input type="submit" class="btn btn-primary" value="搜索" />
        </form>

        <table class="table table-hover table-bordered">
            <thead>
                <tr>
                    <th width="50">ID</th>
                    <th>用户昵称</th>
                    <th>咕鸽号</th>
                    <th>头像</th>
                    <th>性别</th>
                    <th>年龄</th>
                    <th>所在地区</th>
                    <th>职业</th>
                    <th>经验值</th>
                    <th>收益余额</th>
                    <th>充值余额</th>
                    <th>认证状态</th>
                    <th>是否允许直播</th>
                    <th width="120">操作</th>
                </tr>
            </thead>
            <tbody>
                <?php if(is_array($users)): foreach($users as $key=>$vo): ?><tr>
                    <td><?php echo ($vo["id"]); ?></td>
                    <td><?php echo ($vo["nickname"]); ?></td>
                    <td><?php echo ($vo["number"]); ?></td>
                    <td><img src="<?php echo ($vo["img"]); ?>" width="100" height="60"></td>
                    <td><?php echo ($vo['sex'] == 1 ? '男':''); echo ($vo['sex'] == 2 ? '女':''); echo ($vo['sex'] === 0 ? '未填写':''); ?></td>
                    <?php
 $today = date('Y',time()); $birthday = date('Y',$vo['birthday']); ?>
                    <td><?php echo ($birthday ? $today-$birthday:'未填写'); ?></td>
                    <td><?php echo ($province); echo ($city); ?></td>
                    <td><?php echo ($job); ?></td>
                    <td><?php echo ($experience); ?></td>
                    <td><?php echo ($earnings); ?></td>
                    <td><?php echo ($balance); ?></td>
                    <td>
                        <?php echo ($vo['verify'] == 0 ? '未认证':''); echo ($vo['verify'] == 1 ? '认证中':''); ?>
                        <?php echo ($vo['verify'] == 2 ? '通过认证':''); echo ($vo['verify'] == 3 ? '认证失败':''); ?>
                    </td>
                    <td><?php echo ($vo['status'] == 1 ? '允许直播':'禁止直播'); ?></td>
                    <td>
                        <a class="forbid" href='<?php echo U("member/status",array("id"=>$vo["id"],"status"=>3-$vo["status"]));?>'><?php echo ($vo['status'] == 1 ? '禁止直播':'允许直播'); ?></a>
                    </td>
                </tr><?php endforeach; endif; ?>
            </tbody>
        </table>
        <div class="pagination"><?php echo ($page); ?></div>
    </div>
    <script src="/public/js/common.js"></script>
</body>
</html>
<script type="text/javascript">
    $('.forbid').click(function(event) {
        var href = $(this).attr('href');

        $.get(href, function(data) {
            if (data.state === 'success') {
                //修改成功，刷新页面
                if (data.referer) {
                    location.href = data.referer;
                } else {
                    reloadPage(window);
                }

            } else {
                alert('删除失败');
            }


        },'json');

        return false;
    });

    // $('#search').submit(function(event) {
    //     alert(1);
    //     return false;
    // });


</script>