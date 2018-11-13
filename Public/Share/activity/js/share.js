$(function(){
	$(".Top4").hide();
	if (browser.versions.mobile) {//判断是否是移动设备打开。browser代码在下面
		var ua = navigator.userAgent.toLowerCase();//获取判断用的对象
		if (ua.match(/MicroMessenger/i) == "micromessenger") {
			//在微信中打开
			console.log('在微信中打开');
			$(".Top4").show();
			$(".success-count").hide();
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
		$(".layui-input").hide();
		$(".Top4_password").hide();

		//输出反馈信息
		$(".invite-success").css("display","");
		$(".success-mobile").text(mobile);

		$(".submit-button").click(function(){
			window.location.href = 'http://a.app.qq.com/o/simple.jsp?pkgname=com.junhe.mobile';
		});
		return false;
	}

	//获取验证码
	$(".validate-button").click(function(){
		var mobile = $(".layui-input").val();
		if(mobile == ''){
			alert('请输入手机号');
			return false;
		}
		if(!(/^1[34578]\d{9}$/.test(mobile))){
			alert("手机号码格式不正确，请重试");
			return false;
		}
		getVerifyCode(mobile);
	});

	//提交手机号和验证码
	$(".submit-button").click(function(){
		var mobile = $(".layui-input").val();
		var code = $(".validate-code").val();
		var refer_id = getQueryString('refer_id');     //用户邀请标识
		var refer_code = getQueryString('refer_code');     //用户邀请标识

		if(mobile == ''){
			alert('请输入手机号');
			return false;
		}
		if(!(/^1[34578]\d{9}$/.test(mobile))){
			alert("手机号码有误，请重填");
			return false;
		}

		$.ajax({
			url:'/App/Login/register_by_invite',
			data:{mobile:mobile,code:code,refer_id:refer_id,refer_code:refer_code},
			type:"POST",
			success:function(response){
				//返回验证信息
				var res = JSON.parse(response);
				if(res.msg == 'success'){

					//删除表单
					$(".layui-input").hide();
					$(".Top4_password").hide();

					//输出反馈信息
					$(".invite-success").show();
					$(".success-mobile").text(mobile);

					var userCount = parseInt($(".user-count").text());
					userCount += 1;
					$(".user-count").text(userCount);

					$(".submit-button").css('background','../images/download-button.png');

					setCookie('mobile',mobile);
					setCookie('refer_code',refer_code);

					var r = confirm('手机号验证通过，是否去下载律携?');
					if(r){
						window.location.href = 'http://a.app.qq.com/o/simple.jsp?pkgname=com.junhe.mobile';
					}
				}else{
					alert(res.message);
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

//获取短信验证码
function getVerifyCode(mobile) {
	$.ajax({
		url:"/App/Login/sendValidateCode",
		data:{tel:mobile},
		type:"POST",
		success:function(response){

			if(JSON.parse(response).msg == 'success'){
				invokeSettime(".validate-button");
			}else{
				alert('获取验证码失败!');
				$(".validate-button").attr('disabled',false);
			}
		},
		error:function(){
			alert('获取验证码失败');
		}
	});
}

//倒计时
function invokeSettime(obj){
	var countdown=60;
	settime(obj);
	function settime(obj) {
		if (countdown == 0) {
			$(obj).attr("disabled",false);
			$(obj).val("获取验证码");
			countdown = 60;
			return;
		} else {
			$(obj).attr("disabled",true);
			$(obj).val(countdown + "S 重新发送");
			countdown--;
		}
		setTimeout(function(){
			settime(obj)
		},1000)
	}
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