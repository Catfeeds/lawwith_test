<?php
//+----------------------------------------------------------------------
// | Copyright (c) 2015-2017 http://lawyerscloud.cn All rights reserved.
//+----------------------------------------------------------------------
// | Author: 慢悠悠的丑小鸭 <1597305236@qq.com>
//+----------------------------------------------------------------------

namespace App\Controller;

use Think\Controller;
use Common\Api\MCrypt;

class BasicController extends Controller
{
    public function _initialize()
    {
        header("Content-Type:text/html;charset=utf-8");
        $AesMct = new MCrypt;
        $accid = $AesMct->decrypt(urldecode(I('post.accid')));    //用户
        $token = $AesMct->decrypt(str_replace(' ', '+', urldecode(I('post.token'))));    //用户云信标识

        if (!check_user($accid, $token)) {
            apiReturn('2001', AJAX_FALSE);
        } else {
            session('accid', $accid);
            session('token', $token);
            session('my_id', check_user($accid, $token));
        }
    }
}
