<?php
/**
 * Copyright (c) 2017. Lorem ipsum dolor sit amet, consectetur adipiscing elit.
 * Morbi non lorem porttitor neque feugiat blandit. Ut vitae ipsum eget quam lacinia accumsan.
 * Etiam sed turpis ac ipsum condimentum fringilla. Maecenas magna.
 * Proin dapibus sapien vel ante. Aliquam erat volutpat. Pellentesque sagittis ligula eget metus.
 * Vestibulum commodo. Ut rhoncus gravida arcu.
 */

/**
 *Author:慢悠悠的丑小鸭
 *CreateTime:2017/7/13 0013 17:42
 *Description:
 **/

namespace Home\Controller;
use Think\Controller;

define("TOKEN", "weixin");
define("APPID","wx19d9c412f2c743f1");
define("SECRET","18b7bdf4c2590b82ef641c97ae70fba7");

class WeixinController extends Controller
{
	const token = 'weixin';
	const appid = 'wx19d9c412f2c743f1';
	const secret = '18b7bdf4c2590b82ef641c97ae70fba7';

	public function valid() {
		$echoStr = $_GET["echostr"];

		//valid signature , option
		if($this->checkSignature()){
			echo $echoStr;
			exit;
		}
	}

	//验证数字签名
	private function checkSignature()
	{
		// you must define TOKEN by yourself
		if (!defined("TOKEN")) {
			throw new Exception('TOKEN is not defined!');
		}

		$signature = $_GET["signature"];
		$timestamp = $_GET["timestamp"];
		$nonce = $_GET["nonce"];

		$token = TOKEN;
		$tmpArr = array($token, $timestamp, $nonce);

		// use SORT_STRING rule
		sort($tmpArr, SORT_STRING);
		$tmpStr = implode( $tmpArr );
		$tmpStr = sha1( $tmpStr );

		if( $tmpStr == $signature ){
			return true;
		}else{
			return false;
		}
	}

	public function getAccessToken() {
		if($_SESSION['access_token']>time() && $_SESSION['access_token']) {
			$access_token = $_SESSION['access_token'];
		}else{
			$url = 'https://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential&appid='.self::appid.'&secret='.self::secret;
			//初始化curl
			$ch = curl_init();

			curl_setopt($ch, CURLOPT_URL, $url);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER,1);

			$res = curl_exec($ch);
			curl_close($ch);

			if(curl_errno($ch)){
				var_dump(curl_errno($ch));
			}

			$arr = json_decode($res,true);
			$access_token = $arr['access_token'];
			$_SESSION['access_token'] = $arr['access_token'];
			$_SESSION['access_token_exprice_time'] = time() + 7200;
		}

		return $access_token;
	}

	public function getJsApiTicket() {
		if($_SESSION['jsapi_ticket_exprice_time']>time() && $_SESSION['jsapi_ticket']) {
			$jsapi_ticket = $_SESSION['jsapi_ticket'];
		}else{
			$access_token = $this->getAccessToken();
			$url = "https://api.weixin.qq.com/cgi-bin/ticket/getticket?access_token=".$access_token."&type=jsapi";
			$res = $this->http_curl($url);
			$jsapi_ticket = $res['ticket'];
			$_SESSION['jsapi_ticket'] = $jsapi_ticket;
			$_SESSION['jsapi_ticket_exprice_time'] = time()+7200;
		}
		return $jsapi_ticket;
	}


	// 获取16位随机字符串
	public function getRandCode() {
		$array = ['A','B','C','D','E','F','G','H','I','J','K','L','M','N','O','P','Q','R','S','T','U','V','W','X','Y','Z','a','b','c','d','e','f','g','h','i','j','k','l','m','n','o','p','q','r','s','t','u','v','w','x','y','z','0','1','2','3','4','5','6','7','8','9'];
		$tmpStr = '';
		$max = count($array);
		for($i=1;$i<=16;$i++){
			$key = rand(0,$max-1);
			$tmpStr .= $array[$key];
		}
		return $tmpStr;
	}

	public function http_curl($url, $type='get',$res='json', $arr='') {
		//初始化curl
		$ch = curl_init();

		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER,1);

		if($type == 'post'){
			curl_setopt($ch,CURLOPT_POST,1);
			curl_setopt($ch,CURLOPT_POSTFIELDS,$arr);
		}
		$output = curl_exec($ch);
		curl_close($ch);

		if($res == 'json'){
			return json_decode($output,true);
		}
	}

	public function aaa() {
		$signature = "jsapi_ticket=sM4AOVdWfPE4DxkXGEs8VBU7Qq_zInm5EqOgaDxREp0dY2A3wCZ22wQBndq83jIVQufG3BMqyzrZJbymonflrQ&noncestr=KF5asyqaUuRMNB0G&timestamp=1499942424&url=http://www.downtonhd.com/home/Weixin/shareWx";
		echo sha1($signature);
	}

	//微信js分享
	public function shareWx() {
		$jsapi_ticket = $this->getJsApiTicket();
		$timestamp = time();
		$noncestr = $this->getRandCode();
		$url = 'http://www.downtonhd.com/home/Weixin/shareWx';
		$signature = "jsapi_ticket=".$jsapi_ticket."&noncestr=".$noncestr."&timestamp=".$timestamp."&url=".$url;
		$signature = sha1($signature);

		$this->assign('jsapi_ticket',$jsapi_ticket);
		$this->assign('signature',$signature);
		$this->assign('timestamp',$timestamp);
		$this->assign('noncestr',$noncestr);
		$this->assign('url',$url);
		$this->display();
	}

	//抢红包算法
	public function test() {
		header("Content-Type:text/html;charset=utf-8");//输出不乱码，你懂的
		$total = 10;//红包总额
		$num = 8;// 分成8个红包，支持8人随机领取
		$min = 0.01;//每个人最少能收到0.01元

		for ($i = 1; $i < $num; $i++) {
			$safe_total = ($total - ($num - $i) * $min) / ($num - $i);//随机安全上限
			$money = mt_rand($min * 100, $safe_total * 100) / 100;
			$total = $total - $money;
			echo '第'.$i.'个红包：'.$money.' 元，余额：'.$total.' 元'."<br>";
		}
		echo '第'.$num.'个红包：'.$total.' 元，余额：0 元';
	}

	//自动回复功能
	public function responseMsg()
	{
		//$this->valid();
		//get post data, May be due to the different environments
		$postStr = $GLOBALS["HTTP_RAW_POST_DATA"];

		//extract post data
		if (!empty($postStr)) {
			/* libxml_disable_entity_loader is to prevent XML eXternal Entity Injection,
			   the best way is to check the validity of xml by yourself */
			libxml_disable_entity_loader(true);
			$postObj = simplexml_load_string($postStr, 'SimpleXMLElement', LIBXML_NOCDATA);
			$fromUsername = $postObj->FromUserName;
			$toUsername = $postObj->ToUserName;
			$keyword = trim($postObj->Content);
			$msgType = $postObj->MsgType;
			$time = time();
			$textTpl = "<xml>
						<ToUserName><![CDATA[%s]]></ToUserName>
						<FromUserName><![CDATA[%s]]></FromUserName>
						<CreateTime>%s</CreateTime>
						<MsgType><![CDATA[%s]]></MsgType>
						<Content><![CDATA[%s]]></Content>
						</xml>";

			$musicTpl = "<xml>
						<ToUserName><![CDATA[%s]]></ToUserName>
						<FromUserName><![CDATA[%s]]></FromUserName>
						<CreateTime>%s</CreateTime>
						<MsgType><![CDATA[%s]]></MsgType>
						<Music>
						<Title><![CDATA[%s]]></Title>
						<Description><![CDATA[%s]]></Description>
						<MusicUrl><![CDATA[%s]]></MusicUrl>
						<HQMusicUrl><![CDATA[%s]]></HQMusicUrl>
						</Music>
						</xml>";

			$newsTpl = "<xml>
						<ToUserName><![CDATA[%s]]></ToUserName>
						<FromUserName><![CDATA[%s]]></FromUserName>
						<CreateTime>%s</CreateTime>
						<MsgType><![CDATA[%s]]></MsgType>
						<ArticleCount>%s</ArticleCount>
						<Articles>
						<item>
						<Title><![CDATA[微信开发教程]]></Title>
						<Description><![CDATA[微信开发教程]]></Description>
						<PicUrl><![CDATA[http://img.bss.csdn.net/201603251541119507.jpg]]></PicUrl>
						<Url><![CDATA[https://www.baidu.com/]]></Url>
						</item>
						<item>
						<Title><![CDATA[微信开发教程]]></Title>
						<Description><![CDATA[微信开发教程]]></Description>
						<PicUrl><![CDATA[http://img.bss.csdn.net/201603251541119507.jpg]]></PicUrl>
						<Url><![CDATA[https://www.baidu.com/]]></Url>
						</item>
						</Articles>
						</xml>";
			if($msgType == 'text'){
				if($keyword == '时间')
				{
					$msgType = "text";
					$contentStr = date("Y-m-d H:i:s",time());
					$resultStr = sprintf($textTpl, $fromUsername, $toUsername, $time, $msgType, $contentStr);
					echo $resultStr;
				}elseif($keyword == "?" || $keyword == "？"){
					$msgType = "text";
					$contentStr = "【1】特种服务号码\n【2】常用通讯号码\n【3】一般号码\n您还可以输入其他【】获取更多内容哦！";
					$resultStr = sprintf($textTpl, $fromUsername, $toUsername, $time, $msgType, $contentStr);
					echo $resultStr;
				}elseif($keyword == '1'){
					$msgType = "text";
					$contentStr = "特种服务号码\n火警:119\n匪警:110";
					$resultStr = sprintf($textTpl, $fromUsername, $toUsername, $time, $msgType, $postObj->FromUserName);
					echo $resultStr;
				}elseif($keyword == '2'){
					$msgType = "text";
					$contentStr = "常用通讯号码\n中国移动:10086\n中国联通:10010";
					$resultStr = sprintf($textTpl, $fromUsername, $toUsername, $time, $msgType, $contentStr);
					echo $resultStr;
				}elseif($keyword == '3'){
					$msgType = "text";
					$contentStr = "常用银行服务号码\n中国建设银行:95533\n中国工商银行:95588";
					$resultStr = sprintf($textTpl, $fromUsername, $toUsername, $time, $msgType, $contentStr);
					echo $resultStr;
				}elseif($keyword == '音乐'){
					$msgType = 'music';
					$title = '谢谢你的爱1999';
					$desc = '《谢谢你的爱1999》为谢霆锋的第一张国语专辑。发行于1999年9月19日....';
					$url = 'http://sc1.111ttt.com:8282/2015/1/06/06/99060941326.mp3';
					$hqurl = 'http://sc1.111ttt.com:8282/2015/1/06/06/99060941326.mp3';
					$resultStr = sprintf($musicTpl, $fromUsername, $toUsername, $time, $msgType, $title, $desc, $url, $hqurl);
					echo $resultStr;
				}elseif($keyword == '图文'){
					$msgType = 'news';
					$count = 2;
					//$str = '<Articles>';
					//for($i=1;$i<=$count;$i++){
					//	$str .= "<item>
					//				<Title><![CDATA[微信开发教程]]></Title>
					//				<Description><![CDATA[微信开发教程]]></Description>
					//				<PicUrl><![CDATA[http://img.bss.csdn.net/201603251541119507.jpg]]></PicUrl>
					//				<Url><![CDATA[https://www.baidu.com/]]></Url>
					//				</item>";
					//}
					//$str .= '</Articles>';
					$resultStr = sprintf($newsTpl, $fromUsername, $toUsername, $time, $msgType, $count);
					echo $resultStr;
				}elseif($msgType == 'image'){
					$msgType = "text";
					$contentStr = '你发布的这是图片消息';
					$resultStr = sprintf($textTpl, $fromUsername, $toUsername, $time, $msgType, $contentStr);
					echo $resultStr;
				}else{
					$msgType = 'text';
					$url = "http://www.tuling123.com/openapi/api?key=9009fc44f168cfc7055c8a469821ce9b&info=".$keyword;
					$str = file_get_contents($url);
					$json = json_decode($str);
					$contentStr = $json->text;
					$resultStr = sprintf($textTpl, $fromUsername, $toUsername, $time, $msgType, $contentStr);
					echo $resultStr;
				}

				//if(!empty( $keyword ))
				//{
				//	$msgType = "text";
				//	$contentStr = $keyword;
				//	$resultStr = sprintf($textTpl, $fromUsername, $toUsername, $time, $msgType, $contentStr);
				//	echo $resultStr;
				//}else{
				//	echo "Input something...";
				//}
			}

		}else {
			echo "";
			exit;
		}
	}

	public function sendTemplateMsg() {
		$access_token = $this->getAccessToken();
		$url = "https://api.weixin.qq.com/cgi-bin/message/template/send?access_token=".$access_token;
		//组装数组
		//$array = array(
		//	'touser'=>'ooS2huEeXxC1IOC04AuIvfIWzy_w',
		//	'template_id'=>'JjWpW83Gis66oVXHnYK8yAbR8EfdtCDwuiMz0jdUxLw',
		//	'url'=>'http://www.downtonhd.com',
		//	'data'=>array(
		//		'goodsName'=>array('value'=>'德芙巧克力','color'=>"#0000FF"),
		//		'goodsPrice'=>array('value'=>'168.5','color'=>"#0000FF"),
		//		'shopDate'=>array('value'=>date("Y-m-d H:i:s"),'color'=>"#0000FF"),
		//	)
		//);

		//$array = array(
		//	'touser'=>'ooS2huEeXxC1IOC04AuIvfIWzy_w',
		//	'template_id'=>'LiO5kj1WlEQz2YP1ibSsiZsZYVSTg8XIDusHYCMjvLI',
		//	'url'=>'http://www.downtonhd.com',
		//	'data'=>array(
		//		'User'=>array('value'=>'方先生','color'=>"#0000FF"),
		//		'Date'=>array('value'=>'07月13日 19时24分','color'=>"#0000FF"),
		//		'CardNumber'=>array('value'=>'0426','color'=>"#0000FF"),
		//		'Type'=>array('value'=>'消费','color'=>"#0000FF"),
		//		'Money'=>array('value'=>'人民币260.00元','color'=>"#0000FF"),
		//		'DeadTime'=>array('value'=>'07月13日 19时24分','color'=>"#0000FF"),
		//		'Left'=>array('value'=>'106504.09','color'=>"#0000FF")
		//	)
		//);
		////将数组->json
		//$postJson = json_encode($array);

		$postJson = '{
		"touser":"ooS2huEeXxC1IOC04AuIvfIWzy_w",

    "template_id":"LiO5kj1WlEQz2YP1ibSsiZsZYVSTg8XIDusHYCMjvLI",
	"url":"http://www.downtonhd.com",

    "data":{

            "User": {

                "value":"方先生",

                "color":"#0000FF"

            },

            "Date":{

                "value":"07月13日 19时24分",

                "color":"#0000FF"

            },

            "CardNumber": {

                "value":"0426",

                "color":"#0000FF"

            },

            "Type":{

                "value":"消费",

                "color":"#0000FF"

            },

            "Money":{

                "value":"人民币260.00元",

                "color":"#0000FF"

            },

            "DeadTime":{

                "value":"07月13日 19时24分",

                "color":"#0000FF"

            },

            "Left":{

                "value":"106504.09",

                "color":"#0000FF"

            }

    }

}';
		//调用curl函数
		$res = $this->http_curl($url, 'post', 'json', $postJson);
		print_r($res);
	}
	
	public function getUserInfo() {
		$access_token = $this->getAccessToken();
		$url = "https://api.weixin.qq.com/cgi-bin/user/info?access_token=$access_token&openid=ooS2huEeXxC1IOC04AuIvfIWzy_w&lang=zh_CN";
		$res = $this->http_curl($url,'json');

		$this->assign('nickname',$res['nickname']);
		$this->assign('headimgurl',$res['headimgurl']);
		$this->assign('info',$res);
		$this->display();
	}

	public function createMenu() {
		header("Contemt-type:text/html;charest=utf-8");
		$accessToken = 'yRi3E6q98X6_cwPH1qY2sCRK0Ks2q8_E6tthIeRODrI08xOXri0-G-NfPudPikg4YlWxH4UErSVXq_DJmsbkA0uPBzpoC42jVDBMoa4DFztnDzVI7TQCBi71o-RQQ-TWKHZeAJAAXP';
		$url = 'https://api.weixin.qq.com/cgi-bin/menu/create?access_token='.$accessToken;
		$postData = [
			[
				'name'=>'菜单一',
				'type'=>'click',
				'key'=>'item1',
			],
			[
				'name'=>'菜单二',
				'sub_button'=>[
					[
						'name'=>'歌曲',
						'type'=>'click',
						'key'=>'songs',
					],
					[
						'name'=>'电影',
						'type'=>'view',
						'url'=>'http://www.baidu.com',
					],
				],

			],
			[
				'name'=>'腾讯电影',
				'type'=>'view',
				'url'=>'http://www.qq.com',
			],
		];
		$postJson = json_encode($postData);
		$this->http_curl($url,'post','json',$postJson);
	}

}

