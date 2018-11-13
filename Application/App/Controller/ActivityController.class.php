<?php
//+----------------------------------------------------------------------
// | Copyright (c) 2015-2017 http://lawyerscloud.cn All rights reserved.
//+----------------------------------------------------------------------
// | Author: 慢悠悠的丑小鸭 <1597305236@qq.com>
//+----------------------------------------------------------------------
// | Date: 2018/6/13 0013
//+----------------------------------------------------------------------

namespace App\Controller;


use Think\Controller;

class ActivityController extends Controller
{
    
    public function pop_ad() {
        apiReturn('200', AJAX_TRUE, [
            'is_pop'     => true,
            'pop_image'  => 'http://img.lawyerscloud.cn/activity/activity_content.png',
            'click_icon' => 'http://img.lawyerscloud.cn/activity/icon.png',
            'close_icon' => 'http://img.lawyerscloud.cn/activity/close.png'
        ]);
    }


    /**生成邀请注册的链接地址
     *
     * @param int refer_id 邀请者id
     */
    public function invite() {
        $refer_id = I('get.refer_id');  //当前用户id,即邀请者id

        if(!session('refer_code')) {
            $refer_code = randString(32);
            session('refer_code', $refer_code);
        } else {
            $refer_code = session('refer_code');
        }

        $url
            = 'http://' . $_SERVER["SERVER_NAME"] . '/home/share/invite?refer_id=' . $refer_id . '&refer_code=' . $refer_code;

        apiReturn('200', AJAX_TRUE, [
            'refer_id'          => $refer_id,
            'refer_code'        => $refer_code,
            'invite_url'        => $url,
            'share_title'       => '免费送，《2017君合业务研究报告》',
            'share_description' => '只要您邀请5个朋友注册律携，就能免费拿到书',
            'share_icon'        => 'http://img.lawyerscloud.cn/activity/share-icon.png',
            'join_button_icon'  => 'http://img.lawyerscloud.cn/activity/join_button.png'
        ]);
    }
}