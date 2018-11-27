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
    /**
     * 身份验证
     */
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

//    /**
//     * 本地测试,身份验证不加密
//     */
//    public function _initialize()
//    {
//        header("Content-Type:text/html;charset=utf-8");
//        $AesMct = new MCrypt;
//        $accid = I('post.accid');    //用户
//        $token = str_replace(' ', '+', urldecode(I('post.token')));    //用户云信标识
//
//        if (!check_user($accid, $token)) {
//            apiReturn('2001', AJAX_FALSE);
//        } else {
//            session('accid', $accid);
//            session('token', $token);
//            session('my_id', check_user($accid, $token));
//        }
//    }
}
