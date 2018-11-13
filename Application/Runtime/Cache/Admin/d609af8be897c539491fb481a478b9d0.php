<?php if (!defined('THINK_PATH')) exit();?><!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">

    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <meta name="description" content="Xenon Boostrap Admin Panel" />
    <meta name="author" content="" />
    <link rel="icon" href="http://lx2016.oss-cn-beijing.aliyuncs.com/Icon/2016-06-15/lx_logo80.png" type="image/x-icon"/>
    <title>律携 - 登入</title>

    <!--<link rel="stylesheet" href="http://fonts.useso.com/css?family=Arimo:400,700,400italic">-->
    <link rel="stylesheet" href="/Public/Admin/css/fonts/linecons/css/linecons.css">
    <link rel="stylesheet" href="/Public/Admin/css/fonts/fontawesome/css/font-awesome.min.css">
    <link rel="stylesheet" href="/Public/Admin/css/bootstrap.css">
    <link rel="stylesheet" href="/Public/Admin/css/xenon-core.css">
    <link rel="stylesheet" href="/Public/Admin/css/xenon-forms.css">
    <link rel="stylesheet" href="/Public/Admin/css/xenon-components.css">
    <link rel="stylesheet" href="/Public/Admin/css/xenon-skins.css">
    <link rel="stylesheet" href="/Public/Admin/css/custom.css">

    <script src="/Public/Admin/js/jquery-1.11.1.min.js"></script>

    <!-- HTML5 shim and Respond.js IE8 support of HTML5 elements and media queries -->
    <!--[if lt IE 9]>
    <!--<script src="https://oss.maxcdn.com/html5shiv/3.7.2/html5shiv.min.js"></script>-->
    <!--<script src="https://oss.maxcdn.com/respond/1.4.2/respond.min.js"></script>-->
    <!--[endif]-->

<style type="text/css">
    .changUser a{
        font-size : 13px;
        color : #fff1f0;
        text-decoration: none;
    }
    .changUser a:hover{
        color: #50D4FD;
        font-weight:bold
    }
</style>
</head>
<body class="page-body lockscreen-page">

<div class="login-container">

    <div class="row">

        <div class="col-sm-7">

            <script type="text/javascript">
                jQuery(document).ready(function($)
                {
                    // Reveal Login form
                    setTimeout(function(){ $(".fade-in-effect").addClass('in'); }, 1);


                    // Clicking on thumbnail will focus on password field
                    $(".user-thumb a").on('click', function(ev)
                    {
                        ev.preventDefault();
                        $("#passwd").focus();
                    });


                    // Form validation and AJAX request
                    $(".lockcreen-form").validate({
                        rules: {
                            passwd: {
                                required: true
                            }
                        },

                        messages: {
                            passwd: {
                                required: '请输入你的密码。'
                            }
                        },

                        submitHandler: function(form)
                        {
                            show_loading_bar(70); // Fill progress bar to 70% (just a given value)

                            var $passwd = $(form).find('#passwd'),
                                    opts = {
                                        "closeButton": true,
                                        "debug": false,
                                        "positionClass": "toast-top-full-width",
                                        "onclick": null,
                                        "showDuration": "300",
                                        "hideDuration": "1000",
                                        "timeOut": "5000",
                                        "extendedTimeOut": "1000",
                                        "showEasing": "swing",
                                        "hideEasing": "linear",
                                        "showMethod": "fadeIn",
                                        "hideMethod": "fadeOut"
                                    };

                            $.ajax({
                                url: "<?php echo U('Login/sub_login');?>",
                                method: 'POST',
                                dataType: 'json',
                                data: {
                                    do_login: true,
                                    username: $(form).find('#cookname').val(), // user is known in this case
                                    passwd: $passwd.val(),
                                },
                                success: function(resp)
                                {
                                    show_loading_bar({
                                        delay: .5,
                                        pct: 100,
                                        finish: function(){

                                            if(resp.accessGranted)
                                            {
                                                // Redirect after successful login page (when progress bar reaches 100%)
                                                window.location.href = "<?php echo U('Index/index');?>";
                                            }
                                            else
                                            {
                                                toastr.error("输入错误密码，请再试一次。", "无效登陆！", opts);
                                                $passwd.select();
                                            }
                                        }
                                    });
                                }
                            });
                        }
                    });

                    // Set Form focus
                    $("form#lockscreen .form-group:has(.form-control):first .form-control").focus();
                });
            </script>

            <form role="form" id="lockscreen" class="lockcreen-form fade-in-effect">

                <div class="user-thumb">
                    <a href="#">
                        <img src="/Public/Admin/images/user-4.png" class="img-responsive img-circle" />
                    </a>
                </div>

                <div class="form-group">
                    <h3>Welcome back, <?php echo (cookie('cook_name')); ?>!</h3>
                    <p>输入您的密码以访问后台管理。</p>

                    <div class="input-group">
                        <input type="hidden" value="<?php echo (cookie('cook_name')); ?>" name="username" id="cookname">
                        <input type="password" class="form-control input-dark" name="passwd" id="passwd" placeholder="密码" />
							<span class="input-group-btn">
								<button type="submit" class="btn btn-primary">登入</button>
							</span>
                    </div>
                </div>
                <div class="form-group">
                    <p class="changUser"><a href="<?php echo U('Login/login');?>">切换账号</a></p>
                </div>
            </form>

        </div>

    </div>

</div>



<!-- Bottom Scripts -->
<script src="/Public/Admin/js/bootstrap.min.js"></script>
<script src="/Public/Admin/js/TweenMax.min.js"></script>
<script src="/Public/Admin/js/resizeable.js"></script>
<script src="/Public/Admin/js/joinable.js"></script>
<script src="/Public/Admin/js/xenon-api.js"></script>
<script src="/Public/Admin/js/xenon-toggles.js"></script>
<script src="/Public/Admin/js/jquery-validate/jquery.validate.min.js"></script>
<script src="/Public/Admin/js/toastr/toastr.min.js"></script>


<!-- JavaScripts initializations and stuff -->
<script src="/Public/Admin/js/xenon-custom.js"></script>

</body>
</html>