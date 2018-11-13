<?php
//+----------------------------------------------------------------------
// | Copyright (c) 2015-2017 http://lawyerscloud.cn All rights reserved.
//+----------------------------------------------------------------------
// | Author: 慢悠悠的丑小鸭 <1597305236@qq.com>
//+----------------------------------------------------------------------

namespace App\Controller;

use Common\Api\MCrypt;
use Think\Controller;

class OrderController extends BasicController
{
    /**
     * 生成唯一商户订单号
     *
     * @param $userId int
     *
     * @return string
     */
    public static function uniqueOrderSn($userId)
    {
        $recordSn = date('YmdHis', $_SERVER["REQUEST_TIME"]) . $userId . mt_rand(1000, 9999);

        return empty($userId) ? false : $recordSn;
    }


    /**
     * 创建关联订单
     *
     * @param $orderId
     * @param $orderSn
     * @param $amount
     * @param $type
     *
     * @return bool
     */
    protected function createRelationOrder($orderId, $orderSn, $amount, $type)
    {
        $data = [
            'order_id'    => $orderId,
            'record_sn'   => $orderSn,
            'order_type'  => $type,
            'amount'      => $amount,
            'create_date' => $_SERVER["REQUEST_TIME"]
        ];
        if($orderId = M('order')->add($data)) {
            return $orderId;
        } else {
            return false;
        }
    }


    /**
     * 创建视频付费订单
     *
     * @param $videoId
     * @param $userId
     * @param $amount
     *
     * @return bool|string
     */
    public function createTrainOrder($videoId, $userId, $amount)
    {
        $orderSn = $this->uniqueOrderSn($userId);
        $data = [
            'record_sn'   => $orderSn,
            'video_id'    => $videoId,
            'user_id'     => $userId,
            'amount'      => $amount,
            'create_date' => $_SERVER["REQUEST_TIME"],
        ];
        $result = M('order_train')->add($data);
        if(!$result) {
            return false;
        }

        $orderId = $this->createRelationOrder($result, $orderSn, $amount, 'train');
        if(empty($orderId)) {
            return false;
        }

        return ['order_id' => $orderId, 'order_sn' => $orderSn];
    }


    /**
     * 创建直播付费订单
     *
     * @param $liveId
     * @param $userId
     * @param $amount
     *
     * @return bool|string
     */
    public function createLiveOrder($liveId, $userId, $amount)
    {
        $orderSn = $this->uniqueOrderSn($userId);
        $data = [
            'record_sn'   => $orderSn,
            'live_id'     => $liveId,
            'user_id'     => $userId,
            'amount'      => $amount,
            'create_date' => $_SERVER["REQUEST_TIME"],
        ];
        $result = M('order_train')->add($data);
        if(!$result) {
            return false;
        }

        $orderId = $this->createRelationOrder($result, $orderSn, $amount, 'live');
        if(empty($orderId)) {
            return false;
        }

        return ['order_id' => $orderId, 'order_sn' => $orderSn];
    }


    /**
     * 创建付费活动订单
     *
     * @param $activityId
     * @param $userId
     * @param $amount
     *
     * @return bool|string
     */
    public function createActivityOrder($activityId, $userId, $amount)
    {
        $orderSn = $this->uniqueOrderSn($userId);
        $data = [
            'record_sn'   => $orderSn,
            'activity_id' => $activityId,
            'user_id'     => $userId,
            'amount'      => $amount,
            'create_date' => $_SERVER["REQUEST_TIME"],
        ];
        $result = M('order_train')->add($data);
        if(!$result) {
            return false;
        }

        $orderId = $this->createRelationOrder($result, $orderSn, $amount, 'activity');
        if(empty($orderId)) {
            return false;
        }

        return ['order_id' => $orderId, 'order_sn' => $orderSn];
    }


    /**
     * 创建业务关联订单
     * 早期的创建关联订单,还在沿用
     *
     * @param $table
     * @param $data
     *
     * @return bool
     */
    public function insertOrder($table, $data)
    {
        $orderId = M($table)->add($data);

        return $orderId;
    }


    /**
     * 提交订单接口
     *
     * @return bool/array
     */
    public function createOrder()
    {
        $mcrypt = new MCrypt();
        $userId = I('post.user_id');
        $type = I('post.type');
        $objectId = $mcrypt->decrypt(urldecode(I('post.object_id')));

        if(empty($userId) || empty($type) || empty($objectId)) {
            apiReturn('503', AJAX_FALSE, '参数不能为空');
        }

        $wallet = D('wallet')->getUserWallet($userId,'uid,money,pay_password');

        //验证重复下订单
        //直接拿出该订单继续使用,不重新创建订单
        if($existOrder = $this->validateOrder($userId, $type, $objectId)) {

            $order['order_id'] = $existOrder['order_id'];
            $order['order_sn'] = $existOrder['record_sn'];
            $order['is_paypasswd'] = empty($wallet['pay_password']) ? false : true;
            $order['money'] = $wallet['money'];

            apiReturn('200', AJAX_TRUE, $order);
        }

        //获取对象金额
        $object = $this->getAmount($type, $objectId);
        $amount = $object['amount'];
        if(empty($object)) {
            apiReturn('505', AJAX_FALSE, '商品对象不存在');
        }
        if(!$object['is_money']) {
            apiReturn('506', AJAX_FALSE, '此商品对象免费');
        }
        if(empty($amount)) {
            apiReturn('507', AJAX_FALSE, '商品对象价格不能为空');
        }

        switch($type) {
            case 'video':
                $order = $this->createTrainOrder($objectId, $userId, $amount);
                break;
            case 'live':
                $order = $this->createLiveOrder($objectId, $userId, $amount);
                break;
            case 'activity':
                $order = $this->createActivityOrder($objectId, $userId, $amount);
                break;
            default:
                return false;
                break;
        }

        $order['is_paypasswd'] = empty($wallet['pay_password']) ? false : true;
        $order['money'] = $wallet['money'];
        $order['order_id'] = (string)$order['order_id'];

        if($order) {
            apiReturn('200', AJAX_TRUE, $order);
        } else {
            apiReturn('504', AJAX_FALSE, '创建订单失败');
        }
    }
    

    /**
     * 验证重复提交订单
     *
     * @param $userId
     * @param $type
     * @param $objectId
     *
     * @return bool|array
     */
    protected function validateOrder($userId, $type, $objectId)
    {
        $where = ['user_id' => $userId, 'video_id' => $objectId, 'status' => 0];
        switch($type) {
            case 'video':
                $order = M('order_train')->where($where)->find();
                break;
            case 'live':
                $order = M('order_live')->where($where)->find();
                break;
            case 'activity':
                $order = M('order_activity')->where($where)->find();
                break;
            default:
                return false;
                break;
        }

        return $order ? $order : false;
    }
    

    /**
     * 查询关联订单明细
     *
     * @param        $userId
     * @param        $type
     * @param        $orderId
     * @param string $status
     *
     * @return bool
     */
    public function getRelationOrder($userId, $type, $orderId, $status = '')
    {
        $where = ['user_id' => $userId, 'order_type' => $type, 'order_id' => $orderId, 'status' => $status];
        $order = M('order')->where($where)->find();

        return empty($order) ? false : $order;
    }


    /**
     * 查询订单详细
     *
     * @param $type
     * @param $recordSn
     *
     * @return bool
     */
    public function getOrder($type, $recordSn)
    {
        $where = ['order_type' => $type, 'record_sn' => $recordSn];
        switch($type) {
            case 'article_reward_order':
                break;
            case 'answer_reward_order':
                break;
            case 'person_bonus':
                break;
            case 'group_bonus':
                break;
            case 'recharge':
                break;
            case 'withdraw':
                break;
            case 'video':
                $order = M('order_train')->where($where)->find();
                break;
            case 'live':
                $order = M('order_live')->where($where)->find();
                break;
            case 'activity':
                $order = M('order_activity')->where($where)->find();
                break;
            default:
                return false;
                break;
        }

        return $order;
    }


    /**
     * 查询对象金额
     *
     * @param $type
     * @param $objectId
     *
     * @return bool
     */
    public function getAmount($type, $objectId)
    {
        $where = ['id'=>$objectId];
        switch($type) {
            case 'video':
                $data = M('train')->where($where)->find();
                break;
            case 'live':
                $data = M('live')->where($where)->find();
                break;
            case 'activity':
                $data = M('activity')->where($where)->find();
                break;
            default:
                return false;
                break;
        }

        return $data;
    }


    /**
     * 更新订单支付状态为已支付
     *
     * @param $type
     * @param $recordSn
     *
     * @return bool
     */
    public function updateOrder($type, $recordSn)
    {
        $where = ['order_type' => $type, 'record_sn' => $recordSn];
        switch($type) {
            case 'article_reward_order':
                break;
            case 'answer_reward_order':
                break;
            case 'person_bonus':
                break;
            case 'group_bonus':
                break;
            case 'recharge':
                break;
            case 'withdraw':
                break;
            case 'video':
                $result = M('order_train')->where($where)->setField('status', 1);
                break;
            case 'live':
                $result = M('order_live')->where($where)->setField('status', 1);
                break;
            case 'activity':
                $result = M('order_activity')->where($where)->setField('status', 1);
                break;
            default:
                return false;
                break;
        }

        return $result;
    }


    /**
     * 更新关联订单状态为已支付
     *
     * @param $orderSn
     *
     * @return bool
     */
    public function updateRelationOrder($orderSn)
    {
        if(M('order')->where(['record_sn' => $orderSn])->setField('status', 1)) {
            return true;
        } else {
            return false;
        }
    }
}