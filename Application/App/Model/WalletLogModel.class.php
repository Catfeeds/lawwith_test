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

class WalletLogModel extends Model
{
    //插入钱包变动日志
    public function addWalletRecord($recordSn, $uid, $changeMoney, $money, $remark, $time, $display)
    {
        $data = [
            'record_sn'    => $recordSn,
            'uid'          => $uid,
            'change_money' => $changeMoney,
            'money'        => $money,
            'remark'       => $remark,
            'create_time'  => $time,
            'display'      => $display
        ];

        return M('Wallet_log')->add($data);
    }
}