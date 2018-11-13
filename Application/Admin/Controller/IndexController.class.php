<?php
/**
 * Created by PhpStorm.
 * User: Mrx
 * Date: 2016/3/11
 * Time: 14:30
 */

namespace Admin\Controller;

use Think\Controller;
use Common\Api\JPush;
use Common\Api\ChatApi;

class IndexController extends AdminController {
    public function _initialize()
    {
        if(!session(C('USER_AUTH_KEY'))){
            $this->redirect('Login/login');
        }
    }

    public function index ()
    {
        $account = M('Account');
        $resource = M('Resource');
        //用户总数
        $accounts = $account->where(array('status' => array('neq',2)))->count();
        //今日注册数

        $today_date = strtotime(date('Y-m-d', $_SERVER["REQUEST_TIME"]));  //获取当天凌晨的时间戳
        $last_date  = strtotime(date('Y-m-d', strtotime('+1 day'))); //获取次日凌晨的时间戳
        $map['create_at']=array('between',array($today_date,$last_date));

        $this->today = $account->where($map)->count();
        //律师数
        $this->lawyers = $account->where('type=1 and status!=2')->count();
        //已认证律师数量
        $this->law = $account->where('type = 1 and status!=2 and is_review = 1')->count();
        //法务数
        $this->justices = $account->where('type=2 and status!=2')->count();
        //已认证法务数量
        $this->jus = $account->where('type=2 and status!=2 and is_review = 1')->count();
        //学者数
        $this->scholars = $account->where('type=3 and status!=2')->count();
        //认证学者数量
        $this->sch = $account->where('type=3 and status!=2 and is_review = 1')->count();
        //游客数
        $this->tourists = $account->where('type=4 and status!=2')->count();
        //其他数?普通用户数
        $this->other = $account->where('type=5 and status!=2')->count();
        //帖子数,统计正常发帖数量
        $this->posts = $resource->count();
        //求助数
        $this->helps = $resource->where('type=2')->count();
        //昨日发帖数
        $yesterday_start=mktime(0,0,0,date('m'),date('d')-1,date('Y'));
        $yesterday_end=mktime(0,0,0,date('m'),date('d'),date('Y'))-1;
        $questionWhere['send_time'] = [['GT',$yesterday_start],['LT',$yesterday_end]];
        $questionWhere['type'] = 2;
        $this->question = $resource->where($questionWhere)->count();
        //律所总数
        $this->laws = M('Laws')->where(array('status' => array('neq',0)))->count();

        $map = array(
            'num_img' => array('neq',''),
            'auth_time' => array('neq',''),
            'status' => 0,
        );
        //$count = $account->where($map)->count();
        $data = $account->cache(true)->where($map)->order('auth_time desc')->field('id,uname,icon,mobile,type,auth_time')->select();
        //$this->assign('auth_count',$count);
        $this->assign('auth_data',$data);
        $this->assign('accounts',$accounts);    //用户总数
        $this->meta_title = '后台首页';
        $this->display();
    }

   /* 个人中心 */
    public function profile ()
    {
        $this->meta_title = '个人中心';
        $this->meta_describe = '可以看到用户的个人资料';
        $this->display('myProfile');
    }

    //邮箱配置
    public function emailConfig ()
    {
        $this->meta_title = '邮箱设置';
        $this->meta_decribe = '编辑邮箱配置';
        $data = M('Email')->where('id=1')->find();
        if(!empty($data)){
            $this->assign('data',$data);
        }
        $this->display('edit_email');
    }

    //提交邮箱配置
    public function sub_email ()
    {
        $data = array(
            'email_title' => I('post.email_title'),
            'email_host'  => I('post.email_host'),
            'email_name'  => I('post.email_name'),
            'email_user'  => I('post.email_user'),
            'email_pwd'   => lx_encrypt(I('post.email_pwd')),
            'test_content' => I('post.test_content'),
            'time'      => time()
        );
        if(M('Email')->where('id=1')->save($data)){
            $this->success('保存成功');
        }else {
            $this->error('保存失败');
        }
    }

    //清除缓存
    public function clearCache() {
        //echo json_encode('hahha');
        $cachedir=RUNTIME_PATH."/Cache/";   //Cache文件的路径；
        if ($dh = opendir($cachedir)) {     //打开Cache文件夹；
            while (($file = readdir($dh)) !== false) {    //遍历Cache目录，
                unlink($cachedir.$file);                //删除遍历到的每一个文件
            }
            closedir($dh);
        }
    }
}