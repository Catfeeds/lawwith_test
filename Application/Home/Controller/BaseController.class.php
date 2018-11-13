<?php
/**
 * Created by PhpStorm.
 * User: Mrx
 * Date: 2016/7/27
 * Time: 21:54
 */

namespace Home\Controller;
use Think\Controller;

class BaseController extends Controller
{
    public function _initialize ()
    {
        if(!isset($_SESSION['app_id'])) {
            $this->redirect('Login/index');
        }else {
            $id = $_SESSION['app_id'];
            $data = M('Account')->where('id='.$id)->find();
            $this->type = $data['type'];
            $this->username = $data['uname'];
            $this->thumb_img = $data['icon'];
            $this->tel = $data['mobile'];
            $this->remark = $data['remark'];
        }
    }
}