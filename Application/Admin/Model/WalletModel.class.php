<?php
//+----------------------------------------------------------------------
// | Copyright (c) 2015-2017 http://lawyerscloud.cn All rights reserved.
//+----------------------------------------------------------------------
// | Author: 慢悠悠的丑小鸭 <1597305236@qq.com>
//+----------------------------------------------------------------------
// | Date: 2017/10/30 0027
//+----------------------------------------------------------------------

namespace Admin\Model;

use Think\Model;

class WalletModel extends Model
{
    public function test()
    {
        echo '4234';
    }
    /**获取用户钱包信息
     * @param $uid
     * @return mixed
     */
    public function getUserWallet($uid, $field)
    {
        if(!empty($uid)) {

            if(empty($field)) {
                $wallet = M('Wallet')->where(array('uid' => $uid))->field('pay_password', true)->find();
            }
            else {
                $wallet = M('Wallet')->where(array('uid' => $uid))->field($field)->find();
            }

            return $wallet;
        }
    }

    /**用户钱包余额加
     * @param $uid
     * @param $money
     * @param $type
     * @return mixed
     */
    public function updateWalletMoney($uid, $money, $type)
    {
        if(!empty($uid) || !empty($money) || !empty($type)) {

            if($type) {
                //type为 1 时，执行加运算，0 减运算
                $wallet = M('Wallet')->where(array('uid' => $uid))->setInc('money', $money);
            }
            else {
                $wallet = M('Wallet')->where(array('uid' => $uid))->setDec('money', $money);
            }

            return $wallet;
        }
    }

    /**新增用户钱包信息
     * @param $data
     * @return mixed
     */
    public function addUserWallet($data)
    {
        if(!empty($data)) {
            $wallet = M('Wallet')->add($data);

            return $wallet;
        }
    }

    /**更新用户钱包信息
     * @param $uid
     * @param $data
     * @return mixed
     */
    public function updateUserWallet($uid, $data)
    {
        if(!empty($uid) || !empty($data)) {
            $wallet = M('Wallet')->where(array('uid' => $uid))->save($data);

            return $wallet;
        }
    }
}