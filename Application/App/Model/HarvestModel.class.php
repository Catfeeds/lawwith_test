<?php

namespace App\Model;


use Think\Model;

class HarvestModel extends Model
{
    /**插入收入记录日志
     * @param $userId
     * @param $money
     * @param $paymentType
     * @param $tradeType
     * @param $tradeRource
     * @param $status
     * @return mixed
     */
    public function addHarvestLog($userId, $money, $paymentType, $tradeType, $tradeRource, $status)
    {
        $data = array(
            'user_id'      => $userId,
            'money'        => $money,
            'payment_type' => $paymentType,
            'trade_type'   => $tradeType,
            'trade_rource' => $tradeRource,
            'status'       => $status,
            'create_date'  => $_SERVER['REQUEST_TIME']
        );

        return M('Harvest')->add($data);
    }
}