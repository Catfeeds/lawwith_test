<?php
/**
 * Created by PhpStorm.
 * User: Mrx
 * Date: 2017/3/9
 * Time: 16:43
 */

namespace Home\Controller;


use Common\Api\ChatApi;
use Think\Controller;

class ChatController extends Controller
{
    public function test()
    {
        $chat = new ChatApi();
        $body = array(
            'data'  => array(
                'ctype' => 7,
                'id'    => 8120990,
                'info'  => "我买了一个二手房，后来房子涨价，业主违约。但是他帮他亲戚的公司担保在银行做了抵押贷款，现在银行同意只要我们帮忙还钱就可以配合解抵押。但是我们能不能在帮忙还清贷款后，要求这个钱直接",
                'path'  => "路径",
                'title' => "20170328法务茶话会"
            ),
            'type'  => 5
        );
        $res = $chat->sendMsg('15011236249',1,3731468,100,$body);
        p($res);
    }
    public function index()
    {

        $this->display();
    }

    public function doLogin()
    {
        $account = $_POST['login_tel'];
        $pwd = $_POST['login_password'];
        $passwd = md5($pwd);
        $data = M('Account')->where('mobile='.$account)->find();
        if($passwd == $data['passwd'])
        {
            $res = array(
                'status' => 1,
                'uname' => $data['uname'],  //用户昵称
                'token' => $data['token'], //用户唯一标识
                'icon'  => $data['icon'],   //头像
            );
            echo json_encode($res); exit;
        }else {
            $res = array(
                'status' => 0,
                'info' => '账号或密码错误！'
            );
            echo json_encode($res); exit;
        }

    }

    public function main()
    {
        $this->display();
    }

    public function rlist()
    {
        $this->display('list');
    }

    public function room()
    {
        $sy_yun_id = cookie('sy_yun_id');
        $accid = $sy_yun_id;
        $token   = md5($sy_yun_id.$this->salt);
        // 将得到的accid 和 token直接带到前台登录
        $this->assign('accid',$accid);
        $this->assign('token',$token);
        $this->display();
    }

    public function anchor()
    {
        $this->display();
    }

    public function roomManage()
    {
        $this->display('roomManage');
    }

    public function getRoomInfo(){
        // 获取直播的地址
        $info = M('Live')->where('status=1')->find();
        setcookie('roomavator',$info['thumb_img']);
        setcookie('videoUrl',$info['httpPullUrl']);
        $accid  = cookie('uid'); //13520088596
        $roomid = $info['roomid'];
        // 获取聊天室的直播地址 具体方法查看云信server接口文档
        $Chat = new ChatApi();
        $address = $Chat->getrequestAddr($roomid,$accid);
        $token   = cookie('sdktoken');
        $data = array('id'=>$roomid,'account'=>$accid,'address'=>$address['addr'],'token'=>$token,'appKey'=>'2efb9a02309e00ec9153324af405dea8');
        $this->ajaxReturn($data);
    }
}