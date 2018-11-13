<?php
//+----------------------------------------------------------------------
// | Copyright (c) 2015-2017 http://lawyerscloud.cn All rights reserved.
//+----------------------------------------------------------------------
// | Author: 慢悠悠的丑小鸭 <1597305236@qq.com>
//+----------------------------------------------------------------------
// | Date: 2017/11/1 0001
//+----------------------------------------------------------------------

namespace App\Model;

use Think\Model;

class RedpackageModel extends Model
{
    /**
     * 新增红包记录
     * @param int    $amount 金额
     * @param string $payUser 发红包者
     * @param int    $getUser 收红包者
     * @param int    $type 红包类型
     * @param int    $moneyType 资金操作类型  1活动奖励,1.2官方奖励,2提现,3充值
     * @param int    $time 时间
     * @param string $remark 备注
     */
    function createRedpack($amount, $payUser, $getUser, $type, $moneyType, $time, $remark)
    {
        $log_data = array(
            'amount'       => $amount,
            'pay_user'     => $payUser,
            'get_user'     => $getUser,
            'type'         => $type,
            'money_type'   => $moneyType,
            'payment_time' => $time,
            'remark'       => $remark
        );
        M('Redpackets_log')->add($log_data);
    }

    //获取个人红包信息
    public function getPersonBonusStatus($bonus_id)
    {
        if(empty($bonus_id)) {
            return false;
        }

        return M('person_bonus')->where(array('bonus_id'=>$bonus_id))->find();
    }

    //获取群红包信息
    public function getGroupBonusStatus($bonus_id)
    {
        if(empty($bonus_id)) {
            return false;
        }

        return M('group_bonus')->where(array('bonus_id'=>$bonus_id))->find();
    }
}