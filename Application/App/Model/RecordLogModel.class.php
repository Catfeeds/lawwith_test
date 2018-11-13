<?php
//+----------------------------------------------------------------------
// | Copyright (c) 2015-2017 http://lawyerscloud.cn All rights reserved.
//+----------------------------------------------------------------------
// | Author: 慢悠悠的丑小鸭 <1597305236@qq.com>
//+----------------------------------------------------------------------
// | Date: 2017/10/27 0027
//+----------------------------------------------------------------------

namespace App\Model;


use Think\Model;

class RecordLogModel extends Model
{
    /**
     * 插入交易记录
     * @param string $recordSn      商户订单号
     * @param int    $userId        用户id
     * @param int    $money         交易金额
     * @param int    $tradeType     收入支出类型
     * @param int    $tradeRource   收入支出来源id
     * @param int    $type          交易类型 1充值 2提现 3红包 4付费求助 5打赏
     * @param int    $payType       支付方式 0待定 1支付宝 2微信 3银行卡 4余额
     * @param int    $payStatus     支付状态 0待支付 -1成功 2失败
     * @param int    $payTime       交易时间
     * @return int|mixed|string
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

        //if($this->create($data)) {
        //    $logid = $this->add();
        //    return $logid ? $logid : 0;
        //} else {
        //    return $this->getError();
        //}
    }


    /**
     * 更新交易记录
     * @param  string    $record_sn     交易订单号
     * @param  array     $data          更新数据
     * @return bool      结果
     */
    public function updateRecordLog($record_sn, $data)
    {
        if(empty($record_sn) || empty($data)) {
            $this->error = '参数错误！';
            return false;
        }

        $data = $this->create($data);
        if($data){

            return $this->where(array('record_sn' => $record_sn))->save($data);
        }

        return false;
    }


    /**
     * 获取交易记录
     * @param $id
     */
    public function getRecordLog($id)
    {

    }


    /**
     * 批量获取交易记录
     *
     */
    public function getRecordLogAll()
    {

    }
}