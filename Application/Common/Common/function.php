<?php
/**
 * Copyright (c) 2017. Lorem ipsum dolor sit amet, consectetur adipiscing elit.
 * Morbi non lorem porttitor neque feugiat blandit. Ut vitae ipsum eget quam lacinia accumsan.
 * Etiam sed turpis ac ipsum condimentum fringilla. Maecenas magna.
 * Proin dapibus sapien vel ante. Aliquam erat volutpat. Pellentesque sagittis ligula eget metus.
 * Vestibulum commodo. Ut rhoncus gravida arcu.
 */

/**
 * Created by PhpStorm.
 * User: Mrx
 * Date: 2016/3/21
 * Time: 12:37
 * 系统公共库文件
 * 主要定义系统公共函数库
 */
use Common\Api\JPush;

/**
 * 打印数组
 * @author xue
 */
function p($array)
{
    dump($array, 1, '<pre>', 0);
    exit();
}


/**
 * web版错误异常返回接口
 * @param $code
 * @param $message
 */
function apiResponse($code, $msg=true,$message='') {
    $data['code'] = $code;
    $data['msg'] = $msg;
    $data['message'] = $message;
    echo json_encode($data);
    exit();
}

/**
 * 递归重组节点信息为多维数组
 * @param [type] $node [要处理的节点数组]
 * @param integer $pid [父id]
 * @param [type]        [description]
 * @author xue
 */
function node_merge($node, $access = null, $pid = 0)
{
    $arr = array();
    foreach($node as $v) {
        if(is_array($access)) {
            $v['access'] = in_array($v['id'], $access) ? 1 : 0;
        }
        if($v['pid'] == $pid) {
            $v['child'] = node_merge($node, $access, $v['id']);
            $arr[] = $v;
        }
    }

    return $arr;
}

/**
 * 递归删除数据
 * @param  string $biao 操作的数据表
 * @param  number $mid 主键id
 * @param  number $fid 父id
 * @param  number $id
 * @return bool
 */
function delList($biao, $mid, $fid, $id)
{
    $res = M($biao)->select();
    //判断是否存在改列
    if(M($biao)->where($mid . " = " . $id)->count()) {
        //判断是否删除成功
        if(M($biao)->where($mid . " = " . $id)->delete()) {
            foreach($res as $v) {
                if($v[ $fid ] == $id) {
                    delList($biao, $mid, $fid, $v[ $mid ]);
                }
            }
        } else {
            return false;
        }
    }

    return true;
}

/**
 * 生成随机数
 * @param int $length
 * @param int $numeric
 * @return string
 */
function random($length = 6, $numeric = 0)
{
    PHP_VERSION < '4.2.0' && mt_srand((double)microtime() * 1000000);
    if($numeric) {
        $hash = sprintf('%0' . $length . 'd', mt_rand(0, pow(10, $length) - 1));
    } else {
        $hash = '';
        $chars = '1234567890';
        $max = strlen($chars) - 1;
        for($i = 0; $i < $length; $i ++) {
            $hash .= $chars[ mt_rand(0, $max) ];
        }
    }

    return $hash;
}

/**
 * 七牛token加密算法
 * @param $str
 * @return mixed
 */
function Qiniu_Encode($str) // URLSafeBase64Encode
{
    $find = array('+', '/');
    $replace = array('-', '_');

    return str_replace($find, $replace, base64_encode($str));
}

/**
 * 实例化阿里云oos
 * @return object 实例化得到的对象
 */
function new_oss()
{
    Vendor('Alioss.autoload');
    $config = C('ALIOSS_CONFIG');
    $oss = new \OSS\OssClient($config['KEY_ID'], $config['KEY_SECRET'], $config['END_POINT'], false);

    return $oss;
}

/**
 * 上传文件到oss并删除本地文件
 * @param  string $path 文件路径
 * @return bollear      是否上传
 */
function oss_upload($path)
{
    // 获取bucket
    $bucket = C('ALIOSS_CONFIG.BUCKET');
    // 先统一去除左侧的.或者/ 再添加./
    $oss_path = ltrim($path, './');
    $path = './' . $oss_path;
    if(file_exists($path)) {
        // 实例化oss类
        $oss = new_oss();
        // 上传到oss
        $oss->uploadFile($bucket, $oss_path, $path);
        // 如需上传到oss后 自动删除本地的文件 则删除下面的注释
        unlink($path);

        return true;
    }

    return false;
}

/**
 * 阿里云oos图片上传
 * @return array
 */
function aliyun_oss_upload()
{
    /* 返回标准数据 */
    $return = array('status' => 1, 'info' => '上传成功', 'data' => '');
    /* 调用文件上传组件上传文件 */
    $Picture = D('Picture');
    C('PICTURE_UPLOAD.maxSize', 500 * 1024);   //上传的文件大小限制 (0-不做限制)
    C('PICTURE_UPLOAD.exts', 'jpg,gif,png,jpeg');    //允许上传的文件后缀
    C('PICTURE_UPLOAD.savePath', 'Picture/');   //设置上传子目录
    $url = C('UPLOAD_SITEIMG_OSS.domain');  //拼接url头
    $info = $Picture->upload(
        $_FILES,
        C('PICTURE_UPLOAD'),
        C('PICTURE_UPLOAD_DRIVER'),
        C('UPLOAD_SITEIMG_OSS'),
        $url
    ); //TODO:上传到远程服务器

    if($info) {
        $return['status'] = 1;
        $return = array_merge($info['title_img'], $return);
    }
    else {
        $return['status'] = 0;
        $return['info'] = $Picture->getError();
    }

    return $return;
}

/**
 * 获取图片地址
 * @param int $id 图片id
 * @param     $id
 * @return mixed
 */
function img_path($id)
{
    $img = M('Picture');
    $path = $img->where('id=' . $id)->find();
    if(empty($path['url'])) {
        return $path['path'];
    } else {
        return $path['url'];
    }
}

/**
 * 获取用户信息
 * @param int    $id 用户id
 * @param string $val 字段
 */
function user_info($id, $val)
{
    $user = M('Account');
    $user_info = $user->where('id=' . $id)->find();

    return $user_info[ $val ];
}

/**
 * 获取视频信息
 * @param int    $id 视频id
 * @param string $map 字段
 */
function video_info($id, $map)
{
    $video = M('Video');
    $video_info = $video->where('id=' . $id)->find();

    return $video_info[ $map ];
}

/**
 * 获取城市名称
 * @param  number $cid 城市id
 * @return mixed
 */
function get_city($cid)
{
    $model = M('City');
    $name = $model->where('id=' . $cid)->getField('cityname');

    return $name;
}

/**
 * 系统加密方法
 * @param string $data 要加密的字符串
 * @param string $key 加密密钥
 * @param int    $expire 过期时间 单位 秒
 * @return string
 */
function lx_encrypt($data, $key = '', $expire = 0)
{
    $key = md5(empty($key) ? C('DATA_AUTH_KEY') : $key);
    $data = base64_encode($data);
    $x = 0;
    $len = strlen($data);
    $l = strlen($key);
    $char = '';

    for($i = 0; $i < $len; $i ++) {
        if($x == $l) {
            $x = 0;
        }
        $char .= substr($key, $x, 1);
        $x ++;
    }

    $str = sprintf('%010d', $expire ? $expire + time() : 0);

    for($i = 0; $i < $len; $i ++) {
        $str .= chr(ord(substr($data, $i, 1)) + (ord(substr($char, $i, 1))) % 256);
    }

    return str_replace(array('+', '/', '='), array('-', '_', ''), base64_encode($str));
}

/**
 * 系统解密方法
 * @param  string $data 要解密的字符串 （必须是lx_encrypt方法加密的字符串）
 * @param  string $key 加密密钥
 * @return string
 */
function lx_decrypt($data, $key = '')
{
    $key = md5(empty($key) ? C('DATA_AUTH_KEY') : $key);
    $data = str_replace(array('-', '_'), array('+', '/'), $data);
    $mod4 = strlen($data) % 4;
    if($mod4) {
        $data .= substr('====', $mod4);
    }
    $data = base64_decode($data);
    $expire = substr($data, 0, 10);
    $data = substr($data, 10);

    if($expire > 0 && $expire < time()) {
        return '';
    }
    $x = 0;
    $len = strlen($data);
    $l = strlen($key);
    $char = $str = '';

    for($i = 0; $i < $len; $i ++) {
        if($x == $l) {
            $x = 0;
        }
        $char .= substr($key, $x, 1);
        $x ++;
    }

    for($i = 0; $i < $len; $i ++) {
        if(ord(substr($data, $i, 1)) < ord(substr($char, $i, 1))) {
            $str .= chr((ord(substr($data, $i, 1)) + 256) - ord(substr($char, $i, 1)));
        } else {
            $str .= chr(ord(substr($data, $i, 1)) - ord(substr($char, $i, 1)));
        }
    }

    return base64_decode($str);
}

/**
 * 检测用户是否可以
 * @param  string $accid 用户名
 * @param  string $token 云信标识
 * @return bool
 */
function check_user($accid, $token)
{
    if(empty($accid) or empty($token)) {
        return false;
    } else {
        if($uid = M('Account')->where(array('mobile' => $accid, 'token' => $token))->getField('id')) {
            return $uid;
        } else {
            return false;
        }
    }
}

/**
 * 返回配置项对应值
 * @param string|integer $field 标识名,标识为空则返回所有配置项数组
 * @param string|integer $config_type 配置类型
 * @return string
 */
function getConfigValue($field)
{
    $Config = M('Email');
    if($field) {
        $value = $Config->where('id=1')->getField($field);

        return $value;
    }

}

/**
 * 发送邮件
 * @param  string $to 收件方邮箱
 * @param  string $title 邮件主题
 * @param  string $content 邮件内容
 * @return bool
 */
function sendMail($to, $title, $content)
{
    header("content-type:text/html;charset=utf-8");
    ini_set("magic_quotes_runtime", 0);
    //获取邮件设置信息
    $email_host = getConfigValue('email_host');
    $email_title = getConfigValue('email_title');
    $email_name = getConfigValue('email_name');
    $email_user = getConfigValue('email_user');
    $email_password = getConfigValue('email_pwd');
    Vendor('PHPMailer.PHPMailerAutoload');
    $mail = new PHPMailer(); //实例化
    $mail->IsSMTP(); // 启用SMTP
    $mail->Host = $email_host; //smtp服务器的名称（这里以QQ邮箱为例）
    $mail->SMTPAuth = C('MAIL_SMTPAUTH'); //启用smtp认证
    $mail->Username = $email_name; //你的邮箱名
    $mail->Password = lx_decrypt($email_password); //邮箱密码
    $mail->From = $email_user; //发件人地址（也就是你的邮箱地址）
    $mail->FromName = $email_title; //发件人姓名
    $mail->SMTPSecure = C('MAIL_SECURE');
    $mail->AddAddress($to, "您好");
    $mail->WordWrap = 50; //设置每行字符长度
    $mail->IsHTML(true); // 是否HTML格式邮件
    $mail->CharSet = C('MAIL_CHARSET'); //设置邮件编码
    $mail->Subject = $title; //邮件主题
    $mail->Body = $content; //邮件内容
    $mail->AltBody = "这是一个纯文本的身体在非营利的HTML电子邮件客户端"; //邮件正文不支持HTML的备用显示
    if($mail->Send()) {
        return true;
    } else {
        return false;
    }
}

/**
 * 获取邮箱地址
 * @param  string $email 邮箱地址
 * @return string            格式化后的带单位的大小
 */
function email_url($email)
{
    $ext = explode('@', $email);
    if($ext[1] == 'qq.com') {
        $href = 'mail.qq.com';
    } else if($ext == '163.com') {
        $href = 'mail.163.com';
    }

    return $href;
}

/**
 * 发送友盟推送消息
 * @param  integer $uid 用户id
 * @param  string  $title 推送的标题
 * @return boolear         是否成功
 */
function umeng_push($uid, $title)
{
    // 获取token
    $device_tokens = D('OauthUser')->getToken($uid, 2);
    // 如果没有token说明移动端没有登录；则不发送通知
    if(empty($device_tokens)) {
        return false;
    }
    // 导入友盟
    Vendor('Umeng.Umeng');
    // 自定义字段   根据实际环境分配；如果不用可以忽略
    $status = 1;
    // 消息未读总数统计  根据实际环境获取未读的消息总数 此数量会显示在app图标右上角
    $count_number = 1;
    $data = array(
        'key'          => 'status',
        'value'        => "$status",
        'count_number' => $count_number
    );
    // 判断device_token  64位表示为苹果 否则为安卓
    if(strlen($device_tokens) == 64) {
        $key = C('UMENG_IOS_APP_KEY');
        $timestamp = C('UMENG_IOS_SECRET');
        $umeng = new \Umeng($key, $timestamp);
        $umeng->sendIOSUnicast($data, $title, $device_tokens);
    } else {
        $key = C('UMENG_ANDROID_APP_KEY');
        $timestamp = C('UMENG_ANDROID_SECRET');
        $umeng = new \Umeng($key, $timestamp);
        $umeng->sendAndroidUnicast($data, $title, $device_tokens);
    }

    return true;
}

/**
 * 截取中文字符串
 * @param  string $str 需截取的字符串
 * @param  number $length 截取长度
 * @return string           截取后的字符串
 */
function msubstr($str, $start = 0, $length, $charset = "utf-8", $suffix = true)
{
    if(function_exists("mb_substr")) {

        if($suffix) {
            return mb_substr($str, $start, $length, $charset) . "...";
        } else {
            return mb_substr($str, $start, $length, $charset);
        }

    } elseif(function_exists('iconv_substr')) {

        if($suffix) {
            return iconv_substr($str, $start, $length, $charset) . "...";
        } else {
            return iconv_substr($str, $start, $length, $charset);
        }

    }

    $re['utf-8'] = "/[x01-x7f]|[xc2-xdf][x80-xbf]|[xe0-xef]
                  [x80-xbf]{2}|[xf0-xff][x80-xbf]{3}/";

    $re['gb2312'] = "/[x01-x7f]|[xb0-xf7][xa0-xfe]/";

    $re['gbk'] = "/[x01-x7f]|[x81-xfe][x40-xfe]/";

    $re['big5'] = "/[x01-x7f]|[x81-xfe]([x40-x7e]|xa1-xfe])/";

    preg_match_all($re[ $charset ], $str, $match);

    $slice = join("", array_slice($match[0], $start, $length));

    if($suffix) {
        return $slice . "…";
    }

    return $slice;

}

/**
 * 格式化字节大小
 * @param  number $size 字节数
 * @param  string $delimiter 数字和单位分隔符
 * @return string            格式化后的带单位的大小
 */
function format_bytes($size, $delimiter = '')
{
    $units = array('B', 'KB', 'MB', 'GB', 'TB', 'PB');
    for($i = 0; $size >= 1024 && $i < 5; $i ++) {
        $size /= 1024;
    }

    return round($size, 2) . $delimiter . $units[ $i ];
}

/**
 * 获取帖子标题
 * @param  number $type 帖子类型
 * @param  number $cid 帖子id
 * @return string            帖子标题
 */
function get_title($type, $cid)
{
    if($type == 2) {
        $title = M('Activity')->where('id=' . $cid)->getField('title');
    } else {
        $title = M('Resource')->where('id=' . $cid)->getField('title');
    }

    return $title;
}


/**
 * 生成随机字符串
 * @access public
 * @param integer $length 字符串长度
 * @return string
 */
function randString($length)
{
    $chars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789';
    $result = '';
    for($i = 0; $i < $length; $i ++) {
        $result .= $chars[ mt_rand(0, strlen($chars) - 1) ];
    }

    return $result;
}

/**
 * 获取URL参数
 * @access public
 * @param string $cparam 要获取的参数
 * @param string $url url链接
 * @return string
 */
function getParams($url)
{
    $refer_url = parse_url($url);

    $params = $refer_url['query'];

    $arr = array();
    if(!empty($params)) {
        $paramsArr = explode('&', $params);

        foreach($paramsArr as $k => $v) {
            $a = explode('=', $v);
            $arr[ $a[0] ] = $a[1];
        }
    }

    return $arr;
}

/**创建支付日志
 * @param $fileName
 * @param $text
 */
function touchFile($fileName, $text)
{
    $path = "Data/log/";
    if(!is_dir($path)) {
        //创建文件夹,并给777的权限（所有权限）
        mkdir($path);
    }
    $content = date("Y-m-d H:i:s") . ' => '.$text;
    $file = $path . $fileName . '_' . date("Ymd") . ".log";
    file_put_contents($file, $content . PHP_EOL, FILE_APPEND);
}

/**
 * 发送APP消息推送
 * @param string $platform
 * @param array  $alias
 * @param string $content
 * @param array  $m_type
 * @param array  $m_txt
 * @return object
 */
function send_message_push($platform = 'all', $alias, $content, $m_type, $m_txt)
{
    $Jpush = new Jpush();
    if(empty($m_type)) {
        $m_type = 'http';
    }
    if(empty($m_txt)) {
        $m_txt = [];
    }

    return $Jpush->push($platform, $alias, $content, $m_type, $m_txt);
}

/**生成商户订单号
 * @return string
 */
function createOrderSn()
{
    $recordSn = date('YmdHis',time()).mt_rand(1000,9999);
    return $recordSn;
}