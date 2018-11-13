<?php if (!defined('THINK_PATH')) exit();?><!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <meta name="description" content="律携后台管理" />
    <meta name="author" content="" />
    <link rel="icon" href="http://lx2016.oss-cn-beijing.aliyuncs.com/Icon/2016-06-15/lx_logo80.png" type="image/x-icon"/>
    <title>律携 - <?php echo ($meta_title); ?></title>

    <!--<link rel="stylesheet" href="http://fonts.useso.com/css?family=Arimo:400,700,400italic">-->
    <link rel="stylesheet" href="/Public/Admin/css/fonts/linecons/css/linecons.css">
    <link rel="stylesheet" href="/Public/Admin/css/fonts/fontawesome/css/font-awesome.min.css">
    <link rel="stylesheet" href="/Public/Admin/css/bootstrap.css">
    <link rel="stylesheet" href="/Public/Admin/css/xenon-core.css">
    <link rel="stylesheet" href="/Public/Admin/css/xenon-forms.css">
    <link rel="stylesheet" href="/Public/Admin/css/xenon-components.css">
    <link rel="stylesheet" href="/Public/Admin/css/xenon-skins.css">
    <link rel="stylesheet" href="/Public/Admin/css/custom.css">
    <link href="/Public/Admin/css/bootstrap.min.css" rel="stylesheet">
    <script src="/Public/Admin/js/jquery-1.11.1.min.js"></script>

    <!-- HTML5 shim and Respond.js IE8 support of HTML5 elements and media queries -->
    <!--[if lt IE 9]>
    <script src="https://oss.maxcdn.com/html5shiv/3.7.2/html5shiv.min.js"></script>
    <script src="https://oss.maxcdn.com/respond/1.4.2/respond.min.js"></script>
    <![endif]-->
    <style>.main-menu li a{font-size: 14px}</style>
</head>
<body class="page-body">

<div class="settings-pane">

    <a href="#" data-toggle="settings-pane" data-animate="true">
        &times;
    </a>

    <div class="settings-pane-inner">

        <div class="row">

            <div class="col-md-4">

                <div class="user-info">

                    <div class="user-image">
                        <a href="<?php echo U('Index/profile');?>">
                            <img src="/Public/Admin/images/user-4.png" class="img-responsive img-circle" />
                        </a>
                    </div>

                    <div class="user-details">

                        <h3>
                            <a href="<?php echo U('Index/profile');?>"><?php echo (session('username')); ?></a>

                            <!-- Available statuses: is-online, is-idle, is-busy and is-offline -->
                            <span class="user-status is-online"></span>
                        </h3>

                        <p class="user-title">
                            <?php if(is_array($myrole)): foreach($myrole as $key=>$rol): echo ($rol['remark']); ?>&nbsp;<?php endforeach; endif; ?>
                        </p>

                        <div class="user-links">
                            <a href="<?php echo U('Index/profile');?>" class="btn btn-primary">编辑资料</a>
                            <!--<a href="extra-profile.html" class="btn btn-success">升级</a>-->
                        </div>

                    </div>

                </div>

            </div>

            <div class="col-md-8 link-blocks-env">
            </div>

        </div>

    </div>

</div>

<div class="page-container">
    <div class="sidebar-menu toggle-others fixed">
        <div class="sidebar-menu-inner">
            <header class="logo-env">
                <!-- logo -->
                <div class="logo">
                    <a href="<?php echo U('Index/index');?>" class="logo-expanded">
                        <img src="/Public/Admin/images/logo@2x.png" width="80" alt="" />
                    </a>

                    <a href="<?php echo U('Index/index');?>" class="logo-collapsed">
                        &#45;&#45;<img src="/Public/Admin/images/logo-collapsed@2x.png" width="40" alt="" />&#45;&#45;
                    </a>
                </div>
                <!-- This will toggle the mobile menu and will be visible only on mobile devices -->
                <div class="mobile-menu-toggle visible-xs">
                    <a href="#" data-toggle="user-info-menu">
                        <i class="fa-bell-o"></i>
                        <span class="badge badge-success">7</span>
                    </a>

                    <a href="#" data-toggle="mobile-menu">
                        <i class="fa-bars"></i>
                    </a>
                </div>

                <!-- This will open the popup with user profile settings, you can use for any purpose, just be creative -->
                <div class="settings-icon">
                    <a href="#" data-toggle="settings-pane" data-animate="true">
                        <i class="linecons-cog"></i>
                    </a>
                </div>
            </header>

            <ul id="main-menu" class="main-menu">
                <li>
                    <a href="#">
                        <i class="fa-users"></i>
                        <span class="title">用户管理</span>
                    </a>
                    <ul>
                        <li class="active">
                            <a href="<?php echo U('User/index',array('all'=>lx_encrypt(1)));?>">
                                <i class="fa-user-md"></i>
                                <span class="title">认证管理</span>
                            </a>
                        </li>
                        <li class="active">
                            <a href="<?php echo U('User/index',array('t'=>lx_encrypt(1)));?>">
                                <i class="fa-user-md"></i>
                                <span class="title">律师管理</span>
                            </a>
                        </li>
                        <li>
                            <a href="<?php echo U('User/index',array('t'=>lx_encrypt(2)));?>">
                                <i class="fa-gavel"></i>
                                <span class="title">法务管理</span>
                            </a>
                        </li>
                        <li>
                            <a href="<?php echo U('User/index',array('t'=>lx_encrypt(3)));?>">
                                <i class="fa-graduation-cap"></i>
                                <span class="title">学者管理</span>
                            </a>
                        </li>
                        <li>
                            <a href="<?php echo U('User/index',array('t'=>lx_encrypt(5)));?>">
                                <i class="el-adult"></i>
                                <span class="title">普通用户</span>
                            </a>
                        </li>
                        <li>
                            <a href="<?php echo U('User/index',array('t'=>lx_encrypt(4)));?>">
                                <i class="el-adult"></i>
                                <span class="title">游客管理</span>
                            </a>
                        </li>
                    </ul>
                </li>
                <li>
                    <a href="<?php echo U('question/index');?>">
                        <i class="fa-h-square"></i>
                        <span class="title">求助管理</span>
                    </a>
                </li>
                <li>
                    <a href="">
                        <i class="fa-youtube-play"></i>
                        <span class="title">视频管理</span>
                    </a>
                    <ul>
                        <li>
                            <a href="<?php echo U('video/index');?>">
                                <i class="fa-suitcase"></i>
                                <span class="title">视频管理</span>
                            </a>
                        </li>
                        <li>
                            <a href="<?php echo U('video/category');?>">
                                <i class="fa-suitcase"></i>
                                <span class="title">视频分类</span>
                            </a>
                        </li>
                    </ul>
                </li>
                <li>
                    <a href="<?php echo U('live/index');?>">
                        <i class="fa-youtube-play"></i>
                        <span class="title">直播管理</span>
                    </a>
                </li>
                <li>
                    <a href="">
                        <i class="fa-slack"></i>
                        <span class="title">运维管理</span>
                    </a>
                    <ul>
                        <li>
                            <a href="<?php echo U('User/major');?>">
                                <i class="fa-suitcase"></i>
                                <span class="title">专业领域</span>
                            </a>
                        </li>
                        <li>
                            <a href="<?php echo U('Laws/index');?>">
                                <i class="fa-institution"></i>
                                <span class="title">律所管理</span>
                            </a>
                        </li>
                        <li>
                            <a href="<?php echo U('Article/index');?>">
                                <i class="fa-slack"></i>
                                <span class="title">资讯管理</span>
                            </a>
                        </li>
                        <li>
                            <a href="<?php echo U('Activity/index');?>">
                                <i class="fa-glass"></i>
                                <span class="title">活动管理</span>
                            </a>
                        </li>
                        <li>
                            <a href="<?php echo U('Push/index');?>">
                                <i class="fa-paper-plane"></i>
                                <span class="title">首页推荐管理</span>
                            </a>
                        </li>
                        <li>
                            <a href="/admin/recommend">
                                <i class="fa-slack"></i>
                                <span class="title">精选推荐管理</span>
                            </a>
                        </li>
                        <li>
                            <a href="<?php echo U('Push/sys_msg');?>">
                                <i class="fa-comment"></i>
                                <span class="title">系统消息管理</span>
                            </a>
                        </li>
                    </ul>
                </li>
                <li>
                    <a href="">
                        <i class="fa-suitcase"></i>
                        <span class="title">资金管理</span>
                    </a>
                    <ul>
                        <li>
                            <a href="<?php echo U('Money/recordLog');?>">
                                <i class="fa-chain"></i>
                                <span>交易记录</span>
                            </a>
                        </li>
                        <li>
                            <a href="<?php echo U('Money/setting');?>">
                                <i class="fa-chain"></i>
                                <span>红包设置</span>
                            </a>
                        </li>
                        <li>
                            <a href="<?php echo U('Money/withdraw');?>">
                                <i class="fa-chain"></i>
                                <span>提现记录</span>
                            </a>
                        </li>
                        <li>
                            <a href="<?php echo U('Money/minimum');?>">
                                <i class="fa-chain"></i>
                                <span>提现额度</span>
                            </a>
                        </li>
                    </ul>
                </li>
                <li>
                    <a href="#">
                        <i class="fa-slack"></i>
                        <span class="title">统计管理</span>
                    </a>
                    <ul>
                        <li>
                            <a href="<?php echo U('Invite/users');?>">
                                <i class="entypo-flow-parallel"></i>
                                <span class="title">邀请注册新用户</span>
                            </a>
                        </li>
                        <li>
                            <a href="<?php echo U('Invite/invites');?>">
                                <i class="entypo-flow-parallel"></i>
                                <span class="title">邀请数统计</span>
                            </a>
                        </li>
                    </ul>
                </li>
                <li>
                    <a href="#">
                        <i class="entypo-flow-line fa-exclamation-triangle"></i>
                        <span class="title">反馈管理</span>
                    </a>
                    <ul>
                        <li>
                            <a href="<?php echo U('User/feedback');?>">
                                <i class="entypo-flow-parallel"></i>
                                <span class="title">意见反馈</span>
                            </a>
                        </li>
                        <li>
                            <a href="<?php echo U('User/user_report');?>">
                                <i class="entypo-flow-parallel"></i>
                                <span class="title">举报与投诉</span>
                            </a>
                        </li>
                    </ul>
                </li>
                <li>
                    <a href="#">
                        <i class="fa-gears"></i>
                        <span class="title">系统设置</span>
                    </a>
                    <ul>
                        <li>
                            <a href="#">
                                <i class="entypo-flow-line fa-lock"></i>
                                <span class="title">管理权限</span>
                            </a>
                            <ul>
                                <li>
                                    <a href="<?php echo U('Rbac/index');?>">
                                        <i class="entypo-flow-parallel"></i>
                                        <span class="title">管理员</span>
                                    </a>
                                </li>
                                <li>
                                    <a href="<?php echo U('Rbac/role');?>">
                                        <i class="entypo-flow-parallel"></i>
                                        <span class="title">角色</span>
                                    </a>
                                </li>
                                <li>
                                    <a href="<?php echo U('Rbac/node');?>">
                                        <i class="entypo-flow-parallel"></i>
                                        <span class="title">权限</span>
                                    </a>
                                </li>
                            </ul>
                        </li>

                        <li>
                            <a href="<?php echo U('Index/profile');?>">
                                <i class="fa-user"></i>
                                <span class="title">个人资料</span>
                            </a>
                        </li>
                        <li>
                            <a href="<?php echo U('Index/emailConfig');?>">
                                <i class="fa-envelope-o"></i>
                                <span class="title">邮箱设置</span>
                            </a>
                        </li>
                        <li>
                            <a href="<?php echo U('Ad/index');?>">
                                <i class="fa-chain"></i>
                                <span>常用网站</span>
                            </a>
                        </li>
                        <li>
                            <a href="<?php echo U('Ad/versionRefresh');?>">
                                <i class="fa-chain"></i>
                                <span>版本更新</span>
                            </a>
                        </li>
                        <!--<li>-->
                            <!--<a href="<?php echo U('Database/index',array('type'=>'export'));?>">-->
                                <!--<i class="fa-database"></i>-->
                                <!--<span class="title">备份数据库</span>-->
                            <!--</a>-->
                        <!--</li>-->
                        <!--<li>-->
                            <!--<a href="javascript:void(0)" id="clear-cache">-->
                                <!--<i class="fa-cloud"></i>-->
                                <!--<span class="title">清除缓存</span>-->
                            <!--</a>-->
                        <!--</li>-->
                    </ul>
                </li>
            </ul>
</div>

</div>

<div class="main-content">

    <!-- User Info, Notifications and Menu Bar -->
    <nav class="navbar user-info-navbar" role="navigation">

        <!-- Left links for user info navbar -->
        <ul class="user-info-menu left-links list-inline list-unstyled">

            <li class="hidden-sm hidden-xs">
                <a href="#" data-toggle="sidebar">
                    <i class="fa-bars"></i>
                </a>
            </li>
            <li class="dropdown hover-line">
                <a href="#" data-toggle="dropdown">
                    <i class="fa-bell-o"></i>
                    <span class="badge badge-purple"><?php echo ($auth_count); ?></span>
                </a>
                <ul class="dropdown-menu notifications">
                    <li class="top">
                        <p class="small">
                            用户申请认证的通知
                        </p>
                    </li>
                    <li>
                        <ul class="dropdown-menu-list list-unstyled ps-scrollbar">
                            <?php if(is_array($auth_data)): foreach($auth_data as $key=>$dat): ?><li class="active notification-success">
                                    <a href="<?php echo U('User/audit_identity',array('uid'=>lx_encrypt($dat['id'])));?>">
                                        <?php if(empty($dat['icon'])): ?><i><img src="/Public/Admin/images/user-4.png" class="img-responsive img-circle" width="28px" height="28px"/></i>
                                            <?php else: ?>
                                                <i><img src="<?php echo ($dat['icon']); ?>" class="img-responsive img-circle" width="28px" height="28px"/></i><?php endif; ?>
                                        <span class="line">
                                            <strong><?php echo ($dat["uname"]); ?>
                                                <?php switch($dat["type"]): case "1": ?>（律师）<?php break;?>
                                                    <?php case "2": ?>（法务）<?php break;?>
                                                    <?php case "3": ?>（学者）<?php break;?>
                                                    <?php case "5": ?>（其他）<?php break;?>
                                                    <?php default: ?>
                                                    （游客）<?php endswitch;?>
                                            </strong>
                                        </span>
                                        <span class="line small time">
                                            <?php echo (date('Y-m-d H:i',$dat["auth_time"])); ?>
                                        </span>
                                    </a>
                                </li><?php endforeach; endif; ?>
                        </ul>
                    </li>
                    <li class="external">
                        <a href="<?php echo U('User/index',array('all'=>lx_encrypt(1)));?>">
                            <span>查看所有通知</span>
                            <i class="fa-link-ext"></i>
                        </a>
                    </li>
                </ul>
            </li>

        </ul>
        <!-- Right links for user info navbar -->
        <ul class="user-info-menu right-links list-inline list-unstyled">


            <li class="dropdown user-profile">
                <a href="#" data-toggle="dropdown">
                    <img src="/Public/Admin/images/user-4.png" alt="user-image" class="img-circle img-inline userpic-32" width="28" />
							<span>
								<?php echo (session('username')); ?>
								<i class="fa-angle-down"></i>
							</span>
                </a>

                <ul class="dropdown-menu user-profile-menu list-unstyled">
                    <li>
                        <a href="<?php echo U('Index/profile');?>">
                            <i class="fa-edit"></i>
                            编辑资料
                        </a>
                    </li>
                    <!--<li>-->
                        <!--<a href="#settings">-->
                            <!--<i class="fa-wrench"></i>-->
                            <!--设置-->
                        <!--</a>-->
                    <!--</li>-->
                    <!--<li>-->
                        <!--<a href="#profile">-->
                            <!--<i class="fa-user"></i>-->
                            <!--简介-->
                        <!--</a>-->
                    <!--</li>-->
                    <!--<li>-->
                        <!--<a href="#help">-->
                            <!--<i class="fa-info"></i>-->
                            <!--帮助-->
                        <!--</a>-->
                    <!--</li>-->
                    <li class="last">
                        <a href="<?php echo U('Login/logout');?>">
                            <i class="fa-lock"></i>
                            退出
                        </a>
                    </li>
                </ul>
            </li>


        </ul>

    </nav>
    <!--Content -->
    <!-- Main Footer -->
    <!-- Choose between footer styles: "footer-type-1" or "footer-type-2" -->
    <!-- Add class "sticky" to  always stick the footer to the end of page (if page contents is small) -->
    <!-- Or class "fixed" to  always fix the footer to the end of page -->
    <!--<div class="page-title">-->

        <!--<div class="title-env">-->
            <!--<h1 class="title"><?php echo ($meta_title); ?></h1>-->
            <!--<p class="description"><?php echo ($meta_describe); ?></p>-->
        <!--</div>-->

    <!--</div>-->
<script type="text/javascript" src="/Public/Admin/js/bootstrapValidator.js"></script>
<div class="page-title">
    <div class="title-env">
        <h1 class="title"><?php echo ($meta_title); ?></h1>
        <p class="description"><?php echo ($meta_describe); ?></p>
    </div>
    <div class="breadcrumb-env">
        <ol class="breadcrumb bc-1">
            <li>
                <a href="<?php echo U('Index/index');?>"><i class="fa-home"></i>首页</a>
            </li>
            <li>
                <a>用户管理</a>
            </li>
            <li>
                <a href="#" onClick="javascript :history.back(-1);">视频管理</a>
            </li>
            <li class="active">
                <strong><?php echo ($meta_title); ?></strong>
            </li>
        </ol>
    </div>
</div>
<div class="row">
    <div class="panel panel-default">
        <table class="hrPageTable table table-hover table-bordered">
            <thead>
            <tr>
                <th>视频名称</th>
                <th>上传进度</th>
                <th>上传速度</th>
                <th>上传状态</th>
            </tr>
            </thead>
            <tbody>
            <tr>
                <td id="videoId"></td>
                <td id="videoProgress"></td>
                <td id="videoSpeed"></td>
                <td id="videoStatus"></td>
            </tr>
            </tbody>
        </table>
        <div>
            <input type="button" class="uploadBtn" id="uploadBtn" value="开始上传" />
            <input type="button" class="uploadBtn" id='fileSelecter' value="添加视频" />
            <span style="font-size: small; color: #00aa00">* 视频单次上传大小不能超过1G</span>
        </div>
        <script type="text/javascript" src="/Public/Admin/js/html5Upload.js"></script>
        <script type="text/javascript">
            $(function () {
                //添加视频
                $("#fileSelecter").addFile({
                    addFile: function (data) { //添加文件时的回调
                        $("#videoId").html(data.data.fileName);
                        $("#videoProgress").html("0");
                        $("#videoSpeed").html("0");
                        $("#videoStatus").html("等待上传");
                    },
                    addFileError: function (data) { //添加文件时发生错误的回调
                        alert("错误码：" + data.code + ";错误消息：" + data.msg);
                    }
                });

                //开始上传
                $("#uploadBtn").upload({
                    uploadInitUrl: "<?php echo U('Video/uploadInit');?>",//初始化上传地址
                    uploadProgress: function (data) { //进度回调
                        $("#videoProgress").html(data.progress);
                        $("#videoSpeed").html(data.speed);
                        $("#videoStatus").html("上传中");
                    },
                    uploadFinish: function (data) { //上传完成回调
                        $("#videoProgress").html("100%");
                        $("#videoSpeed").html("0");
                        $("#videoStatus").html("已上传");
                    },
                    uploadError: function (data) { //上传错误回调
                        $("#videoStatus").html("上传错误！错误码：" + data.code + ";错误消息：" + data.msg);
                    },
                    uploadAbort:function(data){ //中断

                    }
                });
            });
        </script>
        <div class="panel-body form-horizontal">
            <form id="For_video" action="<?php echo U('Video/submitCreate');?>" method="post" enctype="multipart/form-data">
                <div id="vidoe_info"></div>
                <input type="hidden" name="vidoe_info" value="">
                <!--视频信息-->
                <div class="form-group">
                    <label class="col-lg-3 control-label">封面图片</label>
                    <div class="col-lg-5 kv-main">
                        <input class="form-control" type="file" name="imgs">
                        <span style="font-size: small; color: #00aa00">* 图片的大小500kb,宽：600px  高253px</span>
                    </div>
                </div>
                <div class="form-group">
                    <label class="col-lg-3 control-label">标题</label>
                    <div class="col-lg-5 kv-main"><input class="form-control" type="text" name="title"></div>
                </div>
                <div class="form-group">
                    <label class="col-lg-3 control-label">专业领域 </label>
                    <div class="col-lg-5">
                        <select class="form-control" name="major_id">
                            <option value="" selected>请选择专业领域</option>
                            <?php if(is_array($major_tree)): $i = 0; $__LIST__ = $major_tree;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$major_vo): $mod = ($i % 2 );++$i;?><option value="<?php echo ($major_vo['id']); ?>"><?php echo ($major_vo["delimiter"]); echo ($major_vo["major_name"]); ?></option><?php endforeach; endif; else: echo "" ;endif; ?>
                        </select>
                    </div>
                </div>
                <div class="form-group">
                    <label class="col-lg-3 control-label">视频分类</label>
                    <div class="col-lg-5">
                        <select class="form-control" name="cate_id">
                            <option value="" selected>请选择视频分类</option>
                            <?php if(is_array($cate_tree)): $i = 0; $__LIST__ = $cate_tree;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$cate): $mod = ($i % 2 );++$i;?><option value="<?php echo ($cate['cate_id']); ?>"><?php echo ($cate["cate_name"]); ?></option><?php endforeach; endif; else: echo "" ;endif; ?>
                        </select>
                    </div>
                </div>
                <div class="form-group">
                    <label class="col-lg-3 control-label">视频描述</label>
                    <div class="col-lg-5"><textarea class="form-control autogrow" name="describe" style="min-height: 100px;"></textarea></div>
                </div>
                <div class="form-group">
                    <label class="col-lg-3 control-label">收费/免费</label>
                    <div class="col-lg-5 kv-main"><input class="form-control" type="text" name="amount" placeholder="0元免费/大于0收费，单位/元"></div>
                </div>
                <!--主讲人信息-->
                <div class="form-group">
                    <label class="col-lg-3 control-label">主讲人 </label>
                    <div class="col-lg-5">
                        <select class="form-control" name="speaker">
                            <option value="" selected>请选择主讲人</option>
                            <?php if(is_array($account_list)): $i = 0; $__LIST__ = $account_list;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$account_vo): $mod = ($i % 2 );++$i;?><option value="<?php echo ($account_vo['id']); ?>"><?php echo ($account_vo["uname"]); ?></option><?php endforeach; endif; else: echo "" ;endif; ?>
                        </select>
                    </div>
                </div>
                <div class="form-group">
                    <label class="col-lg-3 control-label">主讲人简介</label>
                    <div class="col-lg-5"><textarea class="form-control autogrow" name="speaker_info" style="min-height: 100px;"></textarea></div>
                </div>
                <div class="form-group">
                    <div class="col-lg-9 col-lg-offset-3">
                        <button type="submit" class="btn btn-success">确认保存</button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>
<script type="text/javascript">
    $(document).ready(function() {
        $('#For_video')
            .bootstrapValidator({
                message: '这个值无效',
                feedbackIcons: {
                    valid: 'glyphicon glyphicon-ok',
                    invalid: 'glyphicon glyphicon-remove',
                    validating: 'glyphicon glyphicon-refresh'
                },
                fields: {
                    major_id: {
                        message: '专业标签无效！',
                        validators: {
                            notEmpty: {
                                message: '专业标签不能为空！'
                            }
                        }
                    },
                    cate_id: {
                        message: '专业标签无效！',
                        validators: {
                            notEmpty: {
                                message: '分类id不能为空！'
                            }
                        }
                    },
                    cost: {
                        message: '专业标签无效！',
                        validators: {
                            notEmpty: {
                                message: '收费金额不能为空！'
                            }
                        }
                    },
                    imgs: {
                        validators: {
                            notEmpty: {
                                message: '封面图片不能为空！'
                            }
                        }
                    },
                    title: {
                        validators: {
                            notEmpty: {
                                message: '标题不能为空！'
                            }
                        }
                    },
                    describe: {
                        validators: {
                            notEmpty: {
                                message: '描述不能为空！'
                            }
                        }
                    }
                }
            })
    });
</script>
<footer class="main-footer sticky footer-type-1">
    <div class="footer-inner">
        <div class="footer-text">
            &copy; <?php echo (date('Y',(isset($data["time"]) && ($data["time"] !== ""))?($data["time"]):time())); ?>
            <strong>律携</strong> - 北京君时天下互动科技有限公司
        </div>
        <div class="go-up">
            <a href="#" rel="go-top">
                <i class="fa-angle-up"></i>
            </a>
        </div>
    </div>
</footer>

</div>
</div>

<div class="modal fade" id="exampleModal" tabindex="1" role="dialog" aria-labelledby="exampleModalLabel">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                <h4 class="modal-title" id="exampleModalLabel">新消息</h4>
            </div>
            <div class="modal-body">
                <form>
                    <div class="form-group">
                        <label for="message-text" class="control-label">回复:</label>
                        <textarea class="form-control" id="message-text" rows="5"></textarea>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">关闭</button>
                <button type="button" class="btn btn-primary submit-button" onclick="checkForm()">确定发送</button>
            </div>
        </div>
    </div>
</div>

<!-- Gallery Modal Image -->
<div class="modal fade" id="gallery-image-modal">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-gallery-image">
                <!-- <img src="/Uploads/<?php echo ($oneData["up_img"]); ?>" class="img-responsive" /> -->
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-md-12">
                        <div class="form-group">
                            <label for="field-1" class="control-label">Title</label>
                            <input type="text" class="form-control" id="field-1" placeholder="Enter image title" value="<?php echo ($oneData["title"]); ?>">
                        </div>

                    </div>
                </div>
                <div class="row">
                    <div class="col-md-12">
                        <div class="form-group">
                            <label for="field-1" class="control-label">Description</label>
                            <textarea class="form-control autogrow" id="field-2" placeholder="Enter image description" style="min-height: 80px;"><?php echo ($oneData["describe"]); ?></textarea>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer modal-gallery-top-controls">
                <button type="button" class="btn btn-xs btn-white" data-dismiss="modal">Close</button>
                <button type="button" class="btn btn-xs btn-info">Crop Image</button>
                <button type="button" class="btn btn-xs btn-secondary">Save</button>
            </div>
        </div>
    </div>
</div>
<!-- Gallery Delete Image (Confirm)-->
<div class="modal fade" id="gallery-image-delete-modal" data-backdrop="static">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title">确定删除数据</h4>
            </div>
            <div class="modal-body">你真的确定删除此数据吗？</div>
            <div class="modal-footer">
                <button type="button" class="btn btn-white" data-dismiss="modal">关闭</button>
                <button type="button" class="btn btn-danger">删除</button>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="avatar-modal" aria-hidden="true" aria-labelledby="avatar-modal-label" role="dialog" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <form id="Form1" class="avatar-form" action="<?php echo U('Index/uploadImg');?>" enctype="multipart/form-data" method="post">
                <div class="modal-header">
                    <button class="close" data-dismiss="modal" type="button">&times;</button>
                    <h4 class="modal-title" id="avatar-modal-label">修改头像</h4>
                </div>
                <div class="modal-body">
                    <div class="avatar-body">
                        <div class="avatar-upload">
                            <input class="avatar-src" name="avatar_src" type="hidden">
                            <input class="avatar-data" name="avatar_data" type="hidden">
                            <label for="avatarInput">图片上传</label>
                            <input class="avatar-input" id="avatarInput" name="avatar_file" type="file"></div>
                        <div class="row">
                            <div class="col-md-9">
                                <div class="avatar-wrapper"></div>
                            </div>
                            <div class="col-md-3">
                                <div class="avatar-preview preview-lg"></div>
                                <div class="avatar-preview preview-md"></div>
                                <div class="avatar-preview preview-sm"></div>
                            </div>
                        </div>
                        <div class="row avatar-btns">
                            <div class="col-md-9">
                                <div class="btn-group">
                                    <button class="btn" data-method="rotate" data-option="-90" type="button" title="Rotate -90 degrees"><i class="fa fa-undo"></i> 向左旋转</button>
                                </div>
                                <div class="btn-group">
                                    <button class="btn" data-method="rotate" data-option="90" type="button" title="Rotate 90 degrees"><i class="fa fa-repeat"></i> 向右旋转</button>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <button class="btn btn-success btn-block avatar-save" type="submit"><i class="fa fa-save"></i> 保存修改</button>
                            </div>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>
<div class="page-loading-overlay">
    <div class="loader-2"></div>
</div>
<!-- Imported styles on this page -->
<link rel="stylesheet" href="/Public/Admin/css/fonts/elusive/css/elusive.css">
<!-- Bottom Scripts -->
<script src="/Public/Admin/js/bootstrap.min.js"></script>
<script src="/Public/Admin/js/TweenMax.min.js"></script>
<script src="/Public/Admin/js/resizeable.js"></script>
<script src="/Public/Admin/js/joinable.js"></script>
<script src="/Public/Admin/js/xenon-api.js"></script>
<script src="/Public/Admin/js/xenon-toggles.js"></script>
<script src="/Public/Admin/js/public.js"></script>
<!-- Imported scripts on this page -->
<script src="/Public/Admin/js/jvectormap/jquery-jvectormap-1.2.2.min.js"></script>
<script src="/Public/Admin/js/jvectormap/regions/jquery-jvectormap-world-mill-en.js"></script>
<script src="/Public/Admin/js/xenon-widgets.js"></script>

<!-- JavaScripts initializations and stuff -->
<script src="/Public/Admin/js/xenon-custom.js"></script>
</body>
</html>