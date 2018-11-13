<?php
/**
 * Created by PhpStorm.
 * User: Mrx
 * Date: 2016/3/18
 * Time: 15:25
 */

namespace Admin\Controller;

use Think\Controller;
use Org\Util\Rbac;

class AdminController extends Controller
{
    public function _initialize() {
        $user = D('UserRelation')->where('id='.$_SESSION[C('USER_AUTH_KEY')])->field('passwd', true)->relation(true)->find();
        $this->myrole = $user['role'];
        $map = array(
            'num_img' => array('neq',''),
            'auth_time' => array('neq',''),
            'status' => 0,
            //'is_review'=>0
        );
        $model = M('Account');
        $count = $model->where($map)->count();
        $data = $model->where($map)->order('auth_time desc')->field('id,uname,icon,mobile,type,auth_time')->select();
        $this->assign('auth_count',$count);
        $this->assign('auth_data',$data);

        //if (!isset($_SESSION[C('USER_AUTH_KEY')])) {
        //    $this->redirect('Login/login');
        //}

        //if (!Rbac::getAccessList($_SESSION[C('USER_AUTH_KEY')])) {
        //    $this->error('没有权限操作');
        //}
    }
}