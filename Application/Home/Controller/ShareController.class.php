<?php
//+----------------------------------------------------------------------
// | Copyright (c) 2015-2017 http://lawyerscloud.cn All rights reserved.
//+----------------------------------------------------------------------
// | Author: 慢悠悠的丑小鸭 <1597305236@qq.com>
//+----------------------------------------------------------------------

namespace Home\Controller;

use Think\Controller;
use Common\Api\letvCloud;
use Common\Api\ChatApi;
use Common\Api\MCrypt;

class ShareController extends Controller
{

    public function _initialize() {
        $jssdk       = new JSSDK("wxc8e9ad9b69df18cc", "9439cb0e20531e07a3bc225e0cde8d82");
        $signPackage = $jssdk->GetSignPackage();
        $this->assign('signPackage', $signPackage);
    }


    //帖子详情
    public function posts() {
        $cid   = base64_decode(I('get.p'));
        $count = M('Resource')->where('id=' . $cid)->count();
        if(empty($cid) || $count <= 0) {
            $this->redirect(C('TMPL_EXCEPTION_FILE'));
        }
        $nowPage = 1; //页码
        $num     = 5;    //每页显示条数
        $model1  = D('Admin/ResourceRelation');
        $data
                 = $model1->relation(true)->where('id=' . $cid)->field('id,title,content,author,tag_major,imgs,views,send_time,tbd_id,type,status,is_nym,is_admin,is_money')->find(); //读取帖子的数据

        //读取回复信息列表
        $model2            = M('resource_comment');
        $count
                           = $model2->join('lx_account ON lx_resource_comment.uid = lx_account.id')->where('rid=' . $cid)->count();
        $data1             = $model2
            ->join('lx_account ON lx_resource_comment.uid = lx_account.id')
            ->where('rid=' . $cid)
            ->field('lx_resource_comment.id as cid,uid,rid,content,likes,dislikes,tbd,time,is_nym,uname,icon,type')
            ->page($nowPage, $num)
            ->select();
        $data['sums_page'] = $count / $num;   //数据总页数
        $data['comm_list'] = $data1;    //嵌入回复信息列表
        $data['my_id']     = session('my_id');  //当前用户id
        //附件图片的路径遍历嵌入数组中
        $imgs_id = explode(',', $data['imgs']);
        foreach($imgs_id as $k => $img) {
            $path                   = M('Picture')->where('id=' . $img)->getField('path');
            $data['img_path'][ $k ] = $path;
        }
        $this->majors = M('Major')->where('status=1')->select();
        $this->assign('data', $data);
        $this->assign('title', $data['title']);
        $this->assign('content', $data['content']);
        $this->display('quanzi');
    }


    //互助详情
    public function helps() {
        $cid   = base64_decode(I('get.p'));
        $count = M('Resource')->where('id=' . $cid)->count();
        if(empty($cid) || $count <= 0) {
            $this->redirect(C('TMPL_EXCEPTION_FILE'));
        }
        $nowPage = 1; //页码
        $num     = 5;     //每页显示条数
        $model1  = D('Admin/ResourceRelation');
        $data
                 = $model1->relation(true)->where('id=' . $cid)->field('id,title,content,author,tag_major,imgs,views,send_time,tbd_id,type,status,is_nym,is_admin,is_money')->find(); //读取帖子的数据

        //读取回复信息列表
        $model2            = M('resource_comment');
        $count
                           = $model2->join('lx_account ON lx_resource_comment.uid = lx_account.id')->where('rid=' . $cid)->count();
        $data1             = $model2
            ->join('lx_account ON lx_resource_comment.uid = lx_account.id')
            ->where('rid=' . $cid)
            ->field('lx_resource_comment.id as cid,uid,rid,content,likes,dislikes,tbd,time,is_nym,uname,icon,type')
            ->page($nowPage, $num)
            ->select();
        $data['sums_page'] = $count / $num;   //数据总页数
        $data['comm_list'] = $data1;    //嵌入回复信息列表
        $data['my_id']     = session('my_id');  //当前用户id

        //附件图片的路径遍历嵌入数组中
        $imgs_id = explode(',', $data['imgs']);
        foreach($imgs_id as $k => $img) {
            $path                   = M('Picture')->where('id=' . $img)->getField('path');
            $data['img_path'][ $k ] = $path;
        }
        $this->majors = M('Major')->where('status=1')->select();
        $this->assign('data', $data);
        $this->assign('title', $data['title']);
        $this->assign('content', $data['content']);
        $this->display('helps');
    }


    //资讯分享页
    public function news() {
        $cid   = base64_decode(I('get.p'));   //资讯id,base64加密
        $count = M('Resource')->where('id=' . $cid)->count();

        if(empty($cid) || $count <= 0) {
            $this->redirect(C('TMPL_EXCEPTION_FILE'));
        }

        //读取帖子的数据
        $model1 = D('Admin/NewsRelation');
        $data   = $model1
            ->relation(true)
            ->where('id=' . $cid)
            ->field('id,title,content,sort,title_img,author,tag_major,imgs,send_time,tbd_id,type,status,likes,views')
            ->find();

        $data['my_id'] = session('my_id');  //当前用户id
        $this->account = M('Account')->where('id=' . $data['author'])->field('uname,icon,position,remark')->find();

        //附件图片的路径遍历嵌入数组中
        $imgs_id = explode(',', $data['imgs']);
        foreach($imgs_id as $k => $img) {
            $path                   = M('Picture')->where('id=' . $img)->getField('path');
            $data['img_path'][ $k ] = $path;
        }

        M('Resource')->where('id=' . $cid)->setInc('views', 2);
        $this->majors = M('Major')->where('status=1')->select();
        $this->assign('data', $data);
        $this->content = htmlspecialchars_decode($data['content']);
        $this->display();
    }


    //活动详情
    public function activity() {
        $aid   = base64_decode(I('get.p'));
        $count = M('Activity')->where('id=' . $aid)->count();
        if(empty($aid) || $count <= 0) {
            $this->redirect(C('TMPL_EXCEPTION_FILE'));
        }
        $nowPage = 1; //页码
        $num     = 5;    //每页显示条数
        $model   = D('Admin/ActivityRelation');
        $data
                 = $model->relation(true)->where('id=' . $aid)->field('id,title,remark,address,number,price,star_time,end_time,sponsor,author,imgs,status,deadline,send_time,views,type')->find();
        //读取回复信息列表
        $model2            = M('activity_comment');
        $count
                           = $model2->join('lx_account ON lx_activity_comment.uid = lx_account.id')->where('aid=' . $aid)->count();
        $data1             = $model2
            ->join('lx_account ON lx_activity_comment.uid = lx_account.id')
            ->where('aid=' . $aid)
            ->field('lx_activity_comment.id as cid,uid,aid,content,likes,dislikes,time,is_nym,uname,icon,type')
            ->page($nowPage, $num)
            ->select();
        $data['sums_page'] = $count / $num;   //数据总页数
        $data['comm_list'] = $data1;    //嵌入回复信息列表
        $data['my_id']     = session('my_id');  //当前用户id
        foreach($data['part_info'] as $k => $v) {
            $user                             = M('Account')->where('id=' . $v['uid'])->field('icon,uname')->find();
            $data['part_info'][ $k ]['icon']  = $user['icon'];
            $data['part_info'][ $k ]['uname'] = $user['uname'];
        }

        $this->assign('data', $data);
        $this->assign('title', $data['title']);
        $this->assign('content', $data['remark']);
        $this->display();
    }


    //律所详情
    public function laws() {
        $lid   = base64_decode(I('get.p'));
        $count = M('Laws')->where('id=' . $lid)->count();
        if(empty($lid) || $count <= 0) {
            $this->redirect(C('TMPL_EXCEPTION_FILE'));
        }
        $model = D('App/LawsRelation');
        $data
               = $model->relation(true)->where('id=' . $lid)->field('id,law_name,profile,logo,uadmin,province,city,town,address,phone,email')->find();

        $data['title'] = $data['law_name'];
        $this->assign('data', $data);
        $this->assign('title', $data['law_name']);
        $this->display('law');
    }


    //律师详情
    public function lawyer() {
        $uid   = base64_decode(I('get.p'));
        $count = M('Account')->where('id=' . $uid)->count();
        if(empty($uid) || $count <= 0) {
            $this->redirect(C('TMPL_EXCEPTION_FILE'));
        }
        $where         = array(
            'id' => $uid,
        );
        $model         = D('App/AccountRelation');
        $data
                       = $model->relation(true)->where($where)->field('id,uname,mobile,gender,icon,remark,email,birth,province,city,town,majors,work_life,law,lawyer_num,company,position,school,hight_diploma,education,professional,prize,price,type,create_at,up_time,status')->find();
        $num           = date('y', $data['up_time']) - date('y', time());
        $data['years'] = $data['work_life'] + $num;     //执业年限
        $data['my_id'] = session('my_id');

        switch($data['type']) {
            case 1:
                $data['title'] = '律师';
                break;
            case 2:
                $data['title'] = '法务';
                break;
            case 3:
                $data['title'] = '专家';
                break;
            case 4:
                $data['title'] = '游客';
                break;
            default:
                $data['title'] = '其他';
        }
        $this->majors = M('Major')->where('status=1')->select();
        $this->assign('data', $data);
        $this->assign('title', $data['uname']);
        $this->display('account');
    }


    //视频详情
    public function train() {
        $nowPage = 1; //页码
        $num     = 5;    //每页显示条数
        $vid     = base64_decode(I('get.p'));  //视频id
        $count   = M('Train')->where('id=' . $vid)->count();
        if(empty($vid) || $count <= 0) {
            $this->redirect(C('TMPL_EXCEPTION_FILE'));
        }
        $uu        = "dwbppqvkxs"; //用户唯一标识码   dwbppqvkxs
        $pu        = "a2ee3b5de4"; //播放器唯一标识码  a2ee3b5de4
        $type      = 'js';  //接口类型
        $auto_play = 0; //是否自动播放
        $width     = auto;  //播放器宽度
        $height    = 300; //播放器高度
        $letv      = new LetvCloud;
        $model     = D('Admin/TrainRelation');
        $data      = $model->relation(true)->where('id=' . $vid)->find();
        //读取回复信息列表
        $model2            = M('train_comment');
        $count
                           = $model2->join('lx_account ON lx_train_comment.uid = lx_account.id')->where('vid=' . $vid)->count();
        $data1             = $model2
            ->join('lx_account ON lx_train_comment.uid = lx_account.id')
            ->where('vid=' . $vid)
            ->field('lx_train_comment.id as cid,uid,vid,content,ctime,is_nym,uname,icon,type')
            ->page($nowPage, $num)
            ->select();
        $data['sums_page'] = $count / $num;   //数据总页数
        $data['comm_list'] = $data1;    //嵌入回复信息列表
        $data['my_id']     = session('my_id');  //当前用户id
        $data['video_path']
                           = $letv->videoGetPlayinterface($uu, video_info($data['video_id'], 'video_unique'), $type, $pu, $auto_play, $width, $height);

        $this->majors = M('Major')->where('status=1')->select();
        $this->assign('data', $data);
        $this->assign('title', $data['title']);
        $this->display('shipin');
    }


    /*
     * Effect 播放分享的直播视频
     * author YangYunHao
     * time   2018-2-26 11:27:58
     * E-mail 1126420614@qq.com
     * Maintain
     * Maintain time
     * Maintain E-mail
     * */
    public function liveBroadcast() {
        $data = [
            'title' => 'AarthiModoo'
        ];
        $this->assign('data', $data); // 分配变量
        $this->assign('title', $data['title']); // 分配变量
        $this->display('live_broadcast'); // 显示模板
    }


    // 邀请注册活动
    public function invite() {
        //统计邀请人数
        $refer_id = I('get.refer_id');
        $actived_count = M('Invite')->where(["is_actived"=>"1","refer_id"=>$refer_id])->count();

        //统计参与活动人数
        $count = M('Invite')->count('id');

        $this->assign('count', $count);
        $this->assign('actived_count', $actived_count);
        $this->display();
    }


    //手机设备检测
    public function is_mobile() {
        if(strpos($_SERVER['HTTP_USER_AGENT'], 'iPhone') || strpos($_SERVER['HTTP_USER_AGENT'], 'iPad')) {
            echo 'systerm is IOS';
        } else if(strpos($_SERVER['HTTP_USER_AGENT'], 'Android')) {
            echo 'systerm is Android';
        } else {
            echo 'systerm is other';
        }
    }
}


//微信方法

class JSSDK
{

    private $appId;
    private $appSecret;


    public function __construct($appId, $appSecret) {
        $this->appId     = $appId;
        $this->appSecret = $appSecret;
    }


    public function getSignPackage() {
        $jsapiTicket = $this->getJsApiTicket();
        $url         = "http://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";
        $timestamp   = time();
        $nonceStr    = $this->createNonceStr();
        // 这里参数的顺序要按照 key 值 ASCII 码升序排序
        $string      = "jsapi_ticket=$jsapiTicket&noncestr=$nonceStr&timestamp=$timestamp&url=$url";
        $signature   = sha1($string);
        $signPackage = array(
            "appId"     => $this->appId,
            "nonceStr"  => $nonceStr,
            "timestamp" => $timestamp,
            "url"       => $url,
            "signature" => $signature,
            "rawString" => $string,
        );

        return $signPackage;
    }


    private function createNonceStr($length = 16) {
        $chars = "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789";
        $str   = "";
        for($i = 0; $i < $length; $i ++) {
            $str .= substr($chars, mt_rand(0, strlen($chars) - 1), 1);
        }

        return $str;
    }


    private function getJsApiTicket() {
        // jsapi_ticket 应该全局存储与更新，以下代码以写入到文件中做示例
        $data = json_decode(file_get_contents("jsapi_ticket.json"));
        if($data->expire_time < time()) {
            $accessToken = $this->getAccessToken();
            $url         = "https://api.weixin.qq.com/cgi-bin/ticket/getticket?type=jsapi&access_token=$accessToken";
            $res         = json_decode($this->httpGet($url));
            $ticket      = $res->ticket;
            if($ticket) {
                $data->expire_time  = time() + 7000;
                $data->jsapi_ticket = $ticket;
                $fp                 = fopen("jsapi_ticket.json", "w");
                fwrite($fp, json_encode($data));
                fclose($fp);
            }
        } else {
            $ticket = $data->jsapi_ticket;
        }

        return $ticket;
    }


    private function getAccessToken() {
        // access_token 应该全局存储与更新，以下代码以写入到文件中做示例
        $data = json_decode(file_get_contents("access_token.json"));
        if($data->expire_time < time()) {
            $url
                          = "https://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential&appid=$this->appId&secret=$this->appSecret";
            $res          = json_decode($this->httpGet($url));
            $access_token = $res->access_token;
            if($access_token) {
                $data->expire_time  = time() + 7000;
                $data->access_token = $access_token;
                $fp                 = fopen("access_token.json", "w");
                fwrite($fp, json_encode($data));
                fclose($fp);
            }
        } else {
            $access_token = $data->access_token;
        }

        return $access_token;
    }


    private function httpGet($url) {
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_TIMEOUT, 500);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($curl, CURLOPT_URL, $url);
        $res = curl_exec($curl);
        curl_close($curl);

        return $res;
    }
}