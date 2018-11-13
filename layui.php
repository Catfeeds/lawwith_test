<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width,initial-scale=1,user-scalable=0">
        <title>WeUI</title>
        <!-- 引入 WeUI -->
        <link rel="stylesheet" href="//res.wx.qq.com/open/libs/weui/1.1.2/weui.min.css"/>
        <script type="text/javascript" src="http://res.wx.qq.com/open/js/jweixin-1.2.0.js"></script>
    </head>
    <style type="text/css">
        .weui-tab{position: relative;bottom: 0;}
    </style>
    <body>
        <!-- 使用 -->
        <!-- <a href="javascript:;" class="weui-btn weui-btn_primary">绿色按钮</a> -->
        <!-- <div class="weui-footer">
            <p class="weui-footer__text">Copyright &copy; 2008-2016 weui.io</p>
        </div>
        <div class="weui-footer">
            <p class="weui-footer__links">
                <a href="javascript:void(0);" class="weui-footer__link">底部链接</a>
            </p>
            <p class="weui-footer__text">Copyright &copy; 2008-2016 weui.io</p>
        </div>
        <div class="weui-footer">
            <p class="weui-footer__links">
                <a href="javascript:void(0);" class="weui-footer__link">底部链接</a>
                <a href="javascript:void(0);" class="weui-footer__link">底部链接</a>
            </p>
            <p class="weui-footer__text">Copyright &copy; 2008-2016 weui.io</p>
        </div>
        <div class="weui-footer weui-footer_fixed-bottom">
            <p class="weui-footer__links">
                <a href="javascript:home();" class="weui-footer__link">WeUI首页</a>
            </p>
            <p class="weui-footer__text">Copyright &copy; 2008-2016 weui.io</p>
        </div> -->

        <div class="weui-gallery">
            <span class="weui-gallery__img" style="background-image: url(https://weui.io/images/pic_article.png);"></span>
            <div class="weui-gallery__opr">
                <a href="javascript:" class="weui-gallery__del">
                    <i class="weui-icon-delete weui-icon_gallery-delete"></i>
                </a>
            </div>
        </div>

        <div class="weui-uploader">
            <div class="weui-uploader__hd">
                <p class="weui-uploader__title">图片上传</p>
                <div class="weui-uploader__info">0/2</div>
            </div>
            <div class="weui-uploader__bd">
                <ul class="weui-uploader__files" id="uploaderFiles">
                    <li class="weui-uploader__file" style="background-image:url(https://avatars1.githubusercontent.com/u/21169687?v=3&s=460)"></li>
                    <li class="weui-uploader__file" style="background-image:url(https://avatars1.githubusercontent.com/u/21169687?v=3&s=460)"></li>
                    <li class="weui-uploader__file" style="background-image:url(https://avatars1.githubusercontent.com/u/21169687?v=3&s=460)"></li>
                    <li class="weui-uploader__file weui-uploader__file_status" style="background-image:url(https://avatars1.githubusercontent.com/u/21169687?v=3&s=460)">
                        <div class="weui-uploader__file-content">
                            <i class="weui-icon-warn"></i>
                        </div>
                    </li>
                    <li class="weui-uploader__file weui-uploader__file_status" style="background-image:url(https://avatars1.githubusercontent.com/u/21169687?v=3&s=460)">
                        <div class="weui-uploader__file-content">50%</div>
                    </li>
                </ul>
                <div class="weui-uploader__input-box">
                    <input id="uploaderInput" class="weui-uploader__input" type="file" accept="image/*" multiple />
                </div>
            </div>
        </div>

        <div class="weui-tab">
            <div class="weui-tab__panel"></div>
            <div class="weui-tabbar">
                <a href="javascript:;" class="weui-tabbar__item weui-bar__item_on">
                    <img src="https://avatars1.githubusercontent.com/u/21169687?v=3&s=460" alt="" class="weui-tabbar__icon">
                    <p class="weui-tabbar__label">微信</p>
                </a>
                <a href="javascript:;" class="weui-tabbar__item">
                    <img src="https://avatars1.githubusercontent.com/u/21169687?v=3&s=460" alt="" class="weui-tabbar__icon">
                    <p class="weui-tabbar__label">通讯录</p>
                </a>
                <a href="javascript:;" class="weui-tabbar__item">
                    <img src="https://avatars1.githubusercontent.com/u/21169687?v=3&s=460" alt="" class="weui-tabbar__icon">
                    <p class="weui-tabbar__label">发现</p>
                </a>
                <a href="javascript:;" class="weui-tabbar__item">
                    <img src="https://avatars1.githubusercontent.com/u/21169687?v=3&s=460" alt="" class="weui-tabbar__icon">
                    <p class="weui-tabbar__label">我</p>
                </a>
            </div>
        </div>
        <script type="text/javascript">
            var time = new Date();
            wx.config({
                debug: true, // 开启调试模式,调用的所有api的返回值会在客户端alert出来，若要查看传入的参数，可以在pc端打开，参数信息会通过log打出，仅在pc端时才会打印。
                appId: 'wx19d9c412f2c743f1', // 必填，公众号的唯一标识
                timestamp: time.getTime(), // 必填，生成签名的时间戳
                nonceStr: '', // 必填，生成签名的随机串
                signature: '',// 必填，签名，见附录1
                jsApiList: [] // 必填，需要使用的JS接口列表，所有JS接口列表见附录2
            });

            wx.ready(function(){
                // content
                //
            });

            wx.checkJsApi({
                jsApiList: ['onMenuShareTimeline'], // 需要检测的JS接口列表，所有JS接口列表见附录2,
                success: function(res) {
                    // 以键值对的形式返回，可用的api值true，不可用为false
                    // 如：{"checkResult":{"chooseImage":true},"errMsg":"checkJsApi:ok"}
                }
            });
        </script>
    </body>
</html>