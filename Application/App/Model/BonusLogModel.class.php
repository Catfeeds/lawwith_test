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

class BonusLogModel extends Model
{
    /**
     * 新增红包记录(作临时用,后期重构后分离开)
     * @param int    $amount 金额
     * @param string $payUser 发红包者
     * @param int    $getUser 收红包者
     * @param int    $type 红包类型
     * @param int    $moneyType 资金操作类型  1活动奖励,1.2官方奖励,2提现,3充值
     * @param int    $time 时间
     * @param string $remark 备注
     */
    public function createBonusLog($amount, $takeAmount, $paymentTime, $payUser, $getUser, $type, $commission, $moneyType, $recordType)
    {
        $data = array(
            'amount'       => $amount,      //回复者红包总额
            'take_amount'  => $takeAmount, //申请提现金额
            'payment_time' => $paymentTime, //红包分成时间
            'pay_user'     => $payUser,     //发放红包者,默认为管理员
            'get_user'     => $getUser,     //回复者用户id
            'type'         => $type,        //求助类型,1为文章  2为求助
            'commission'   => $commission,  //分成比例
            'money_type'   => $moneyType,    //资金类型 1红包奖励(默认为1活动奖励 1.2为官方奖励),2提现,3充值,4为IM红包 5悬赏红包 6打赏红包
            'record_type'  => $recordType,
        );

        return M('Bonus_log')->add($data);
    }

    /**
     * 新增帖子相关红包记录
     * @param int    $amount 金额
     * @param string $payUser 发红包者
     * @param int    $getUser 收红包者
     * @param int    $type 红包类型
     * @param int    $moneyType 资金操作类型  1活动奖励,1.2官方奖励,2提现,3充值
     * @param int    $time 时间
     * @param string $remark 备注
     */
    public function createBonusArticleLog($rid, $postUser, $getUser, $money, $type, $time)
    {
        $log_data = array(
            'rid'         => $rid,
            'post_user'   => $postUser,
            'get_user'    => $getUser,
            'money'       => $money,
            'type'        => $type,
            'create_date' => $time
        );
        return M('Bonus_article_log')->add($log_data);
    }

    /**
     * 新增回复相关红包记录
     * @param int    $amount 金额
     * @param string $payUser 发红包者
     * @param int    $getUser 收红包者
     * @param int    $type 红包类型
     * @param int    $moneyType 资金操作类型  1活动奖励,1.2官方奖励,2提现,3充值
     * @param int    $time 时间
     * @param string $remark 备注
     */
    public function createBonusReplyLog($rid, $postUser, $getUser, $money, $type, $time)
    {
        $log_data = array(
            'rid'         => $rid,
            'post_user'   => $postUser,
            'get_user'    => $getUser,
            'money'       => $money,
            'type'        => $type,
            'create_date' => $time
        );
        return M('Bonus_reply_log')->add($log_data);
    }

    //获取个人红包信息
    public function getPersonBonusStatus($bonus_id)
    {
        if(empty($bonus_id)) {
            return false;
        }

        return M('person_bonus')->where(array('bonus_id' => $bonus_id))->find();
    }

    //获取群红包信息
    public function getGroupBonusStatus($bonus_id)
    {
        if(empty($bonus_id)) {
            return false;
        }

        return M('group_bonus')->where(array('bonus_id' => $bonus_id))->find();
    }
}