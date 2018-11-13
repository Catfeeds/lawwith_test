<?php
//+----------------------------------------------------------------------
// | Copyright (c) 2015-2017 http://lawyerscloud.cn All rights reserved.
//+----------------------------------------------------------------------
// | Author: 慢悠悠的丑小鸭 <1597305236@qq.com>
//+----------------------------------------------------------------------
// | Date: 2017/10/27 0027
//+----------------------------------------------------------------------

namespace Admin\Model;

use Think\Model;

class RecordLogModel extends Model
{
    /**插入交易记录
     *
     * @param $recordSn   string 商户订单号
     * @param $userId     int 用户id
     * @param $money      int 交易金额
     * @param $tradeType  int  收入支出类型
     * @param $type       int 交易类型 1充值 2提现 3红包 4付费求助 5打赏
     * @param $payType    int 支付方式 0待定 1支付宝 2微信 3银行卡 4余额
     * @param $payStatus  int 支付状态 0待支付 -1成功 2失败
     * @param $payTime    int 交易时间
     * @return mixed
     */
    public function addRecordLog($recordSn, $userId, $money, $tradeType, $tradeRource, $type, $payType, $payStatus, $payTime)
    {
        $data = [
            'record_sn'    => $recordSn,
            'user_id'      => $userId,
            'money'        => $money,
            'trade_type'   => $tradeType,
            'trade_rource' => $tradeRource,
            'type'         => $type,
            'pay_type'     => $payType,
            'pay_status'   => $payStatus,
            'pay_time'     => $payTime
        ];

        return M('Record_log')->add($data);
    }


    //更新交易记录
    public function updateRecordLog($record_sn, $data)
    {
        if(empty($record_sn) || empty($data)) {
            return false;
        }

        return M('Record_log')->where(array('record_sn' => $record_sn))->save($data);
    }


    //获取交易记录
    public function getRecordLog($id)
    {

    }


    //批量获取交易记录
    public function getRecordLogAll()
    {

    }
}