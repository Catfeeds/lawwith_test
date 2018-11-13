<?php
/**
 * Created by PhpStorm.
 * User: Mrx
 * Date: 2016/7/27
 * Time: 20:27
 */

namespace Home\Controller;

use Think\Controller;

class LoginController extends Controller
{
    //登陆页面
    public function index ()
    {
        $this->meta_title = '登陆';
        $this->display('login');
    }

    //登录
    public function login()
    {
        header("Content-type: text/html; charset=utf-8");
        $mobile = urldecode(I('post.mobile'));
        $passwd = urldecode(I('post.passwd'));

        $where = array(
            'mobile' => $mobile,
            'passwd' => md5($passwd),
            'status' => array('neq',2)
	        //'type'  => array('neq',4),
        );
        if($data = M('Account')->where($where)->find())
        {
            session('app_id', $data['id'],3600*12);
	        session('mobile', $mobile,3600*12);
            $this->redirect('M/index');
        }else {
            echo "<script type=text/javascript>alert('账号或密码有误！！！');window.history.go(-1);</script>";
        }
    }

    //退出登录
    public function logout()
    {
        session_unset();
        session_destroy();
        $this->redirect('Login/index');
    }
}