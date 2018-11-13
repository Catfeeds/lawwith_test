/**
 * Created by Administrator on 2017/7/27 0027.
 */

$(function(){
	$(".Top4").hide();
	if (browser.versions.mobile) {//判断是否是移动设备打开。browser代码在下面
		var ua = navigator.userAgent.toLowerCase();//获取判断用的对象
		if (ua.match(/MicroMessenger/i) == "micromessenger") {
			//在微信中打开
			console.log('在微信中打开');
			$(".success-count").hide();
			$(".Top4").show();
		}
		if (ua.match(/WeiBo/i) == "weibo") {
			//在新浪微博客户端打开
			$(".Top4").show();
		}
		if (ua.match(/QQ/i) == "qq") {
			//在QQ空间打开
			$(".Top4").show();
		}
		if (browser.versions.ios) {
			//是否在IOS浏览器打开
		}
		if(browser.versions.android){
			//是否在安卓浏览器打开
		}
	} else {
		//否则就是PC浏览器打开
		$(".Top4").show();
	}

	var mobile = getCookie('mobile');
	if(mobile !== '' && mobile !== null){
		//删除表单
		$(".layui-input").remove();

		//输出反馈信息
		$(".invite-success").show();
		$(".success-mobile").text(mobile);

		$(".submit-button").click(function(){
			window.location.href = 'http://a.app.qq.com/o/simple.jsp?pkgname=com.junhe.mobile';
		});
		return false;
	}

	//if($("#success-count").text() == 0){
	//
	//	$(".success-count").hide();
	//}

	$(".submit-button").click(function(){
		var mobile = $(".layui-input").val();
		var code = getQueryString('code');          //用户邀请标识

		var Request = new Object();
		Request = getRequest();

		var pid = Request.pid;          //用户id
		var userCount = parseInt($(".user-count").text());
		if(mobile == ''){
			alert('请输入手机号');
			return false;
		}
		if(!(/^1[34578]\d{9}$/.test(mobile))){
			alert("手机号码有误，请重填");
			return false;
		}

		$.ajax({
			url:'/Home/Share/saveRegisterData',
			data:{mobile:mobile,pid:pid,code:code},
			type:"POST",
			success:function(reponse){
				//返回验证信息
				if(reponse !== ''){
					alert(reponse);
					return false;
				}
				//删除表单
				$(".layui-input").remove();
				$(this).removeClass();

				//输出反馈信息
				$(".invite-success").show();
				$(".success-mobile").text(mobile);
				userCount += 1;
				$(".user-count").text(userCount);

				$(".submit-button").css('background','../images/download-button.png');

				document.cookie = 'mobile=' + mobile;

				var r = confirm('手机号验证通过，是否去下载律携?');
				if(r){
					window.location.href = 'http://a.app.qq.com/o/simple.jsp?pkgname=com.junhe.mobile';
				}
			}
	})
	});
});

var browser = {
	versions: function () {
		var u = navigator.userAgent, app = navigator.appVersion;
		return {         //移动终端浏览器版本信息
			trident: u.indexOf('Trident') > -1, //IE内核
			presto: u.indexOf('Presto') > -1, //opera内核
			webKit: u.indexOf('AppleWebKit') > -1, //苹果、谷歌内核
			gecko: u.indexOf('Gecko') > -1 && u.indexOf('KHTML') == -1, //火狐内核
			mobile: !!u.match(/AppleWebKit.*Mobile.*/), //是否为移动终端
			ios: !!u.match(/\(i[^;]+;( U;)? CPU.+Mac OS X/), //ios终端
			android: u.indexOf('Android') > -1 || u.indexOf('Linux') > -1, //android终端或uc浏览器
			iPhone: u.indexOf('iPhone') > -1, //是否为iPhone或者QQHD浏览器
			iPad: u.indexOf('iPad') > -1, //是否iPad
			webApp: u.indexOf('Safari') == -1 //是否web应该程序，没有头部与底部
		};
	}(),
	language: (navigator.browserLanguage || navigator.language).toLowerCase()
};

//发送验证码
function getVerifyCode(mobile) {
	$.ajax({
		url:"/App/Login/sendVerifyCode",
		data:{tel:mobile},
		type:"POST",
		success:function(response){

			var time = 60;
			var dealline = time;

			if(response.msg != 'success'){
				//alert('获取验证码失败!');
				//console.log(response);
			}else{
				$("#getVerifyCode").attr('disabled',disabled);
			}
		},
		error:function(){
			alert('获取验证码失败');
		}
	});
}

//获取url参数
function getRequest() {
	var url = location.search; //获取url中"?"符后的字串
	var theRequest = new Object();
	if (url.indexOf("?") != -1) {
		var str = url.substr(1);
		strs = str.split("&");
		for(var i = 0; i < strs.length; i ++) {
			theRequest[strs[i].split("=")[0]] = unescape(strs[i].split("=")[1]);
		}
	}
	return theRequest;
}


//获取url参数
function getQueryString(key){
	var reg = new RegExp("(^|&)"+key+"=([^&]*)(&|$)");
	var result = window.location.search.substr(1).match(reg);
	return result ? decodeURIComponent(result[2]) : null;
}

//写cookies
function setCookie(name,value) {
	var Days = 30;
	var exp = new Date();
	exp.setTime(exp.getTime() + Days*24*60*60*1000);
	document.cookie = name + "="+ escape (value) + ";expires=" + exp.toGMTString();
}

//读取cookies
function getCookie(name) {
	var arr,reg=new RegExp("(^| )"+name+"=([^;]*)(;|$)");

	if(arr=document.cookie.match(reg))

		return unescape(arr[2]);
	else
		return null;
}

//删除cookies
function delCookie(name) {
	var exp = new Date();
	exp.setTime(exp.getTime() - 1);
	var cval=getCookie(name);
	if(cval!=null)
		document.cookie= name + "="+cval+";expires="+exp.toGMTString();
}

//倒计时
function countDwon(){
	var minute = 60;
	var mobile_timeout;
	var mobile_count = minute;
	var mobile_lock = 0;
	if (mobile_count == 0) {
		$("#getVerifyCode").addClass("on");
		$('#getVerifyCode').removeAttr("disabled");
		$('#getVerifyCode').text("重新获取");
		mobile_lock = 0;
		clearTimeout(mobile_timeout);
		$('#getVerifyCode').removeClass("on");
	}else {
		mobile_count--;
		$('#getVerifyCode').text( + dealline.toString() + "秒...");
		mobile_timeout = setTimeout(countDwon, 1000);
	}
}
