<?php if (!defined('THINK_PATH')) exit();?><!DOCTYPE html>
<html>
<head>
    <title><?php echo ($post_title); ?> <?php echo ($site_name); ?> </title>
    <meta name="keywords" content="<?php echo ($post_keywords); ?>" />
    <meta name="description" content="<?php echo ($post_excerpt); ?>">
    <link href="/public/simpleboot/bootstrap/css/bootstrap-responsive.min.css" rel="stylesheet">
    <style>
        #article_content img{height:auto !important}
        #article_content {word-wrap: break-word;}
        .btn {margin-top: 33px;}

    </style>
</head>
<body class="" bgcolor="#faebd7">

<div class="container tc-main">
    <div class="row">
        <div class="span3"></div>
        <div class="span9">

            <div class="tc-box first-box article-box">
                <h2><?php echo ($post_title); ?></h2>
                <div class="article-infobox">
                    <span><?php echo ($post_date); ?></span>
                </div>
                <hr>
                <div id="article_content">
                    <?php echo ($post_content); ?>
                </div>
            </div>

            <?php $ad=sp_getad("portal_article_bottom"); ?>
            <?php if(!empty($ad)): ?><div class="tc-box">
                    <div class="headtitle">
                        <h2>赞助商</h2>
                    </div>
                    <div>
                        <?php echo ($ad); ?>
                    </div>
                </div><?php endif; ?>

        </div>
    </div>

</div>
</body>
</html>