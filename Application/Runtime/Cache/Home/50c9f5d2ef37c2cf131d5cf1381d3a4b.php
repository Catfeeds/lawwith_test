<?php if (!defined('THINK_PATH')) exit();?><!DOCTYPE html>
<html lang="en" class="no-js">
    <head>
        <meta charset="utf-8">
        <title>律携-<?php echo ($meta_title); ?></title>
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <meta name="description" content="">
        <meta name="author" content="">
        <!-- CSS -->
        <link rel='stylesheet' href='/Public/Home/css/app.css'>
        <link rel="stylesheet" href="/Public/Home/css/reset.css">
        <link rel="stylesheet" href="/Public/Home/css/supersized.css">
        <link rel="stylesheet" href="/Public/Home/css/style.css">
        <link rel="icon" href="http://lx2016.oss-cn-beijing.aliyuncs.com/Icon/2016-06-15/lx_logo80.png" type="image/x-icon"/>
        <!--[if lt IE 9]>
            <script src="http://html5shim.googlecode.com/svn/trunk/html5.js"></script>
        <![endif]-->
        
        <!-- Javascript -->
        <script src="/Public/Home/js/jquery-1.8.2.min.js"></script>
        <script src="/Public/Home/js/supersized.3.2.7.min.js"></script>
        <script src="/Public/Home/js/scripts.js"></script>
    </head>

    <body>
        <div class="page-container">
            <h1>账户登录</h1>
            <form action="<?php echo U('Login/login');?>" method="post">
                <input type="text" name="mobile" class="mobile" placeholder="请输入账号" value="<?php echo (cookie('mobile')); ?>">
                <input type="password" name="passwd" class="password" placeholder="请输入密码">
                <button type="submit">登陆</button>
                <div class="error-tips"></div>
            </form>
        </div>
    </body>
<script type="text/javascript">
    jQuery(function($){
        $.supersized({
            // Functionality
            slide_interval     : 4000,    // Length between transitions
            transition         : 1,    // 0-None, 1-Fade, 2-Slide Top, 3-Slide Right, 4-Slide Bottom, 5-Slide Left, 6-Carousel Right, 7-Carousel Left
            transition_speed   : 1000,    // Speed of transition
            performance        : 1,    // 0-Normal, 1-Hybrid speed/quality, 2-Optimizes image quality, 3-Optimizes transition speed // (Only works for Firefox/IE, not Webkit)

            // Size & Position
            min_width          : 0,    // Min width allowed (in pixels)
            min_height         : 0,    // Min height allowed (in pixels)
            vertical_center    : 1,    // Vertically center background
            horizontal_center  : 1,    // Horizontally center background
            fit_always         : 0,    // Image will never exceed browser width or height (Ignores min. dimensions)
            fit_portrait       : 1,    // Portrait images will not exceed browser height
            fit_landscape      : 0,    // Landscape images will not exceed browser width

            // Components
            slide_links        : 'blank',    // Individual links for each slide (Options: false, 'num', 'name', 'blank')
            slides             : [    // Slideshow Images
                //{image : '/Public/Home/img/backgrounds/1.jpg'},
                {image : '/Public/Home/img/backgrounds/2.jpg'},
                //{image : '/Public/Home/img/backgrounds/3.jpg'}
            ]
        });
    });
</script>
</html>