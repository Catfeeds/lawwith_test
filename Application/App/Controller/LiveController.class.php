<?php
//+----------------------------------------------------------------------
// | Copyright (c) 2015-2017 http://lawyerscloud.cn All rights reserved.
//+----------------------------------------------------------------------
// | Author: 慢悠悠的丑小鸭 <1597305236@qq.com>
//+----------------------------------------------------------------------

namespace App\Controller;

use Think\Controller;

class LiveController extends Controller
{

    // 直播聊天室详情
    public function liveChatRoomDetail() {
        $userId = I('post.user_id');
        $liveId = I('post.live_id');
        $roomId = trim(I('post.roomid'));

        $channelInfo = D('Live')->where(['roomid' => $roomId])->find();

        //付费直播
        if($channelInfo['is_money']) {
            if(empty($userId)) {
                apiReturn('205',AJAX_FALSE,'未获取到用户id,请重新登录');
            }

            //检测订单支付状态
            $where = ['user_id'  => $userId, 'live_id' => $liveId, 'status' => 1];
            if(empty(M('order_live')->where($where)->find())) {
                apiReturn('504', AJAX_FALSE, '付款成功后才能观看直播');
            }
        }

        if(is_array($channelInfo)) {
            apiReturn('200', AJAX_TRUE, $channelInfo);
        } else {
            apiReturn('400', AJAX_FALSE, null);
        }
    }
}