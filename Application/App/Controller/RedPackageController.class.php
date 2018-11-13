<?php
//+----------------------------------------------------------------------
// | Copyright (c) 2015-2017 http://lawyerscloud.cn All rights reserved.
//+----------------------------------------------------------------------
// | Author: 慢悠悠的丑小鸭 <1597305236@qq.com>
//+----------------------------------------------------------------------

namespace App\Controller;

use Common\Api\ChatApi;
use Common\Api\ApiPage;
use Common\Api\MCrypt;
use Common\Api\JPush;

class RedPackageController extends BasicController
{
    //发个人一对一红包,确认订单
    public function sendPersonBonus()
    {
        $AesMcrypt = new MCrypt;

        $from_uid = session('my_id');
        $to_uid = $AesMcrypt->decrypt(urldecode(I('post.to_uid')));
        $money = $AesMcrypt->decrypt(urldecode(I('post.money')));
        $remark = I('post.remark');

        $orderObj = A('Order');
        $record_sn = $orderObj->setOrderSn();//同步存储到 红包订单表 钱包交易记录表 钱包变动日志

        if(empty($to_uid) || empty($money) || empty($remark) || empty($from_uid)) {
            apiReturn('1023', AJAX_FALSE, '必要参数不能为空');
        }
        if($money > 200) {
            apiReturn('1023', AJAX_FALSE, '红包金额不能大于200元');
        }
        //获取用户id
        $to_uid = M('Account')->where('mobile=' . $to_uid)->getField('id');

        //下红包订单
        $order = [
            'record_sn'   => $record_sn,
            'from_uid'    => $from_uid,
            'to_uid'      => $to_uid,
            'status'      => 0,
            'money'       => $money,
            'remark'      => $remark,
            'create_date' => time()
        ];
        //p($order);exit;
        $bonus_id = M('Person_bonus')->add($order);

        if(empty($bonus_id)) {
            apiReturn('1023', AJAX_FALSE, '发红包订单确认失败');
        }

        $data = [
            'record_sn'   => $record_sn,
            'order_type'  => 'person_bonus',
            'order_id'    => $bonus_id,
            'create_date' => $_SERVER[ REQUEST_TIME ],
            'status'      => 0
        ];
        //新增订单关联数据
        $unionOrderId = $orderObj->insertOrder('Order', $data);
        if(!$unionOrderId || empty($unionOrderId)) {

            apiReturn('1022', AJAX_FALSE);
        }

        $wallet = M('Wallet')->where('uid=' . $from_uid)->field('money,pay_password')->find();
        $money = $wallet['money'];      //余额
        if(empty($wallet['pay_password'])) {
            $pay_password = '0';
        }
        else {
            $pay_password = '1';
        }

        $arr = array(
            'bonus_id'     => strval($bonus_id),
            'order_id'     => strval($unionOrderId),
            'money'        => strval($money),
            'is_paypasswd' => $pay_password
        );
        apiReturn('1024', AJAX_TRUE, $arr);
    }

    //发群红包,确认订单
    public function sendGroupBonus()
    {
        $AesMcrypt = new MCrypt;
        $from_uid = session('my_id');          //红包发起者
        $group_id = $AesMcrypt->decrypt(urldecode(I('post.group_id')));     //群id
        $people_num = $AesMcrypt->decrypt(urldecode(I('post.people_num'))); //群人数
        $redpack_type = I('post.bonus_type');                             //红包类型 1拼手气红包 2普通红包
        $redpack_count = $AesMcrypt->decrypt(urldecode(I('post.bonus_count')));   //红包个数
        $money = $AesMcrypt->decrypt(urldecode(I('post.money')));           //红包金额
        $remark = I('post.remark');         //祝福语

        $orderObj = A('Order');
        $record_sn = $orderObj->setOrderSn();//同步存储到 红包订单表 钱包交易记录表 钱包变动日志

        if(empty($from_uid) || empty($group_id) || empty($people_num) || empty($redpack_type) || empty($redpack_count) || empty($money) || empty($remark)) {
            apiReturn('1023', AJAX_FALSE, '必要参数不能为空');
        }

        //下红包订单
        $order = [
            'from_uid'       => $from_uid,
            'group_id'       => $group_id,
            'people_num'     => $people_num,
            'redpack_type'   => $redpack_type,
            'redpack_count'  => $redpack_count,
            'money'          => $money,
            'remark'         => $remark,
            'record_sn'      => $record_sn,
            'status'         => 0,
            'surplus_count'  => $redpack_count,
            'surplus_amount' => $money,
            'create_date'    => time()
        ];
        $bonus_id = M('Group_bonus')->add($order);

        if(empty($bonus_id)) {
            apiReturn('1023', AJAX_FALSE, '发红包订单确认失败');
        }

        $updateUnionData = [
            'record_sn'   => $record_sn,
            'order_type'  => 'group_bonus',
            'order_id'    => $bonus_id,
            'create_date' => $_SERVER[ REQUEST_TIME ],
            'status'      => 0
        ];
        //新增订单关联数据
        $unionOrderId = $orderObj->insertOrder('order', $updateUnionData);
        if(!$unionOrderId || empty($unionOrderId)) {

            apiReturn('1022', AJAX_FALSE);
        }

        $wallet = M('Wallet')->where('uid=' . $from_uid)->field('money,pay_password')->find();
        if(empty($wallet['pay_password'])) {
            $pay_password = '0';
        }
        else {
            $pay_password = '1';
        }

        $money = M('Wallet')->where('uid=' . $from_uid)->getField('money');
        $arr = array(
            'bonus_id'     => strval($bonus_id),
            'order_id'     => strval($unionOrderId),
            'money'        => strval($money),
            'is_paypasswd' => $pay_password
        );
        apiReturn('1024', AJAX_TRUE, $arr);
    }

    //余额支付
    public function walletPayment()
    {
        $AesMcrypt = new MCrypt;
        $bonus_type = I('post.bonus_type');
        $uid = $AesMcrypt->decrypt(urldecode(I('post.uid')));
        $bonus_id = $AesMcrypt->decrypt(urldecode(I('post.bonus_id')));
        $pay_password = $AesMcrypt->decrypt(urldecode(I('post.pay_password')));

        if(empty($bonus_type)) {
            apiReturn('1023', AJAX_FALSE, '红包类型参数不能为空');
        }
        if(empty($bonus_id)) {
            apiReturn('1023', AJAX_FALSE, '红包id参数不能为空');
        }

        //确认支付密码
        $money = A('Money');
        $money->checkPayPassword($uid, $pay_password);

        //判断个人红包，群红包
        if($bonus_type == 'person') {
            $order = M('person_bonus')->where('bonus_id=' . $bonus_id)->find();

        }
        elseif($bonus_type == 'group') {
            $order = M('group_bonus')->where('bonus_id=' . $bonus_id)->find();

        }
        else {
            $order = false;
        }

        //钱包余额不足
        $userWallet = D('wallet')->getUserWallet($uid);
        if($userWallet < $order['money']) {
            apiReturn('1022', AJAX_FALSE, '余额不足');
        }

        //扣除红包发起者钱包余额
        if(!D('wallet')->updateWalletMoney($uid, $order['money'], 0)) {
            apiReturn('1023', AJAX_FALSE, '扣款失败');
        }

        //插入交易记录
        D('BonusLog')->createBonusLog($order['money'], '', $_SERVER['REQUEST_TIME'], $uid, '', '', '', 4, 0);
        //插入交易记录
        D('record_log')->addRecordLog($order['record_sn'], $order['from_uid'], $order['money'], 2,'', 3, 4, 1, $_SERVER["REQUEST_TIME"]);

        //支付后的操作：1修改订单状态  2增加钱包交易记录
        $this->orderPaid($order, $bonus_type, 4);

        //下单流程成功结束
        apiReturn('1024', AJAX_TRUE);
    }

    //红包订单支付后的操作
    protected function orderPaid($order, $bonus_type, $pay_type)
    {
        //支付订单,修改订单状态
        if($bonus_type == 'person') {
            $res2 = M('Person_bonus')->where('bonus_id=' . $order['bonus_id'])->setField('from_status', 1);
        }
        elseif($bonus_type == 'group') {
            $res2 = M('Group_bonus')->where('bonus_id=' . $order['bonus_id'])->setField('from_status', 1);
        }

        //更新订单关联表状态
        M('order')->where('record_sn=' . $order['record_sn'])->setField('status', 1);

        if(!$res2) {
            apiReturn('1023', AJAX_FALSE, '修改订单状态失败');
        }
    }

    //获取红包状态
    public function getBonusStatus()
    {
        $crypt = new MCrypt;
        $bonusId = I('post.bonus_id');  //红包id
        $uid = $crypt->decrypt(urldecode(I('post.uid')));  //用户id
        $bonusType = I('post.bonus_type');  //红包类型，person group

        if(empty($bonusId) || empty($bonusType)) {
            apiReturn('1023', AJAX_FALSE, '必要参数不能为空');
        }

        if($bonusType == 'person') {

            $result = D('BonusLog')->getPersonBonusStatus($bonusId);
        }

        elseif($bonusType == 'group') {

            $getBonus = M('group_bonus_result')->where(array('bonus_id' => $bonusId, 'to_uid' => $uid))->find();
            if(!empty($getBonus)) {

                //当前用户是否抢过该红包 0未抢 1已抢
                $data['is_get_bonus'] = '1';
            }
            else {
                $data['is_get_bonus'] = '0';
            }

            $result = D('BonusLog')->getGroupBonusStatus($bonusId);
        }

        else {
            apiReturn('1023', AJAX_FALSE, '红包类型参数错误');
        }

        $data['bonus_id'] = $bonusId;
        $data['status'] = (int)$result['status'];

        apiReturn('1024', AJAX_TRUE, $data);
    }

    //领取个人红包
    public function getPersonBonus()
    {
        $AesMcrypt = new MCrypt;
        $uid = $AesMcrypt->decrypt(urldecode(I('post.uid')));   //红包拆开者
        $bonus_id = $AesMcrypt->decrypt(urldecode(I('post.bonus_id')));

        if(empty($uid) || empty($bonus_id)) {
            apiReturn('1023', AJAX_FALSE, '缺少必要参数');
        }
        $bonus = M('Person_bonus')->where('bonus_id=' . $bonus_id)->find();
        if($bonus['status'] == 1) {
            apiReturn('1023', AJAX_TRUE, [
                'status'   => '1',
                'uid'      => strval($uid),
                'bonus_id' => strval($bonus_id),
                'money'    => $bonus['money']
            ]);     //已领
        }
        if($bonus['status'] == 2) {
            apiReturn('1023', AJAX_TRUE, [
                'status'   => '2',
                'uid'      => strval($uid),
                'bonus_id' => strval($bonus_id),
                'money'    => ''
            ]);  //已过期
        }
        if($bonus['status']) {
            apiReturn('1023', AJAX_TRUE, [
                'status'   => '3',
                'uid'      => strval($uid),
                'bonus_id' => strval($bonus_id),
                'money'    => ''
            ]);      //该红包不能领取
        }
        if($uid !== $bonus['to_uid']) {
            apiReturn('1023', AJAX_TRUE, [
                'status'   => '4',
                'uid'      => strval($uid),
                'bonus_id' => strval($bonus_id),
                'money'    => ''
            ]);    //该红包领取者不是您
        }

        //写入红包领取结果记录
        $personModel = M('person_bonus');
        $bonus = $personModel->where('bonus_id=' . $bonus_id)->find();
        $bonus_result = [
            'bonus_id'    => $bonus_id,
            'from_uid'    => $bonus['from_uid'],
            'to_uid'      => $uid,
            'money'       => $bonus['money'],
            'create_date' => time()
        ];
        M('person_bonus_result')->add($bonus_result);

        //修改订单状态
        $res = $personModel->where('bonus_id=' . $bonus_id)->setField('status', 1);
        if(empty($res)) {
            apiReturn('1023', AJAX_FALSE, '修改订单状态失败');
        }

        //更新钱包余额
        $res = D('Wallet')->updateWalletMoney($uid, $bonus['money'], 1);
        if(!$res) {
            apiReturn('1023', AJAX_FALSE, '更新钱包余额失败');
        }

        //插入钱包变动记录
        $wallet = D('wallet')->getUserWallet($uid);
        D('WalletLog')->addWalletRecord($bonus['record_sn'],$uid,$bonus['money'],$wallet+$bonus['money'], '',$_SERVER["REQUEST_TIME"],1);
        //插入收入记录日志
        D('Harvest')->addHarvestLog($uid, $bonus['money'], 1, 2,'',1);
        //插入交易记录
        D('RecordLog')->addRecordLog($bonus['record_sn'], $uid, $bonus['money'], 1,0, 3, 0, 1,$_SERVER['REQUEST_TIME']);

        apiReturn('1024', AJAX_TRUE, [
            'status'   => '0',
            'uid'      => strval($uid),
            'bonus_id' => strval($bonus_id),
            'money'    => ''
        ]);
    }

    //抢群红包
    public function getGroupBonus()
    {
        $AesMcrypt = new MCrypt;
        $uid = $AesMcrypt->decrypt(urldecode(I('post.uid')));   //红包拆开者
        $bonus_id = $AesMcrypt->decrypt(urldecode(I('post.bonus_id')));
        $group_id = $AesMcrypt->decrypt(urldecode(I('post.group_id')));
        $redpack_type = $AesMcrypt->decrypt(urldecode(I('post.redpack_type')));

        if(empty($uid) || empty($bonus_id) || empty($group_id) || empty($redpack_type)) {
            apiReturn('1025', AJAX_FALSE, '必要参数不能为空');
        }

        $group_bonus = M('group_bonus')->where(['bonus_id' => $bonus_id, 'group_id' => $group_id])->find();

        //是否有此红包
        if(empty($group_bonus) || !$group_bonus['from_status']) {
            apiReturn('1023', AJAX_FALSE, '红包不存在');
        }

        //红包状态
        $arr = ['uid'    => strval($uid), 'bonus_id' => strval($bonus_id), 'money' => '',
                'status' => (string)$group_bonus['status']];
        if($group_bonus['surplus_count'] <= 0 || $group_bonus['surplus_amount'] <= 0) {
            apiReturn('1023', AJAX_TRUE, $arr);
        }
        switch($group_bonus['status']) {
            case 1:
                apiReturn('1021', AJAX_TRUE, $arr);
            case 2:
                apiReturn('1022', AJAX_TRUE, $arr);
        }

        //当前用户是否领取过该红包
        $getBonus = M('group_bonus_result')->where(['bonus_id' => $bonus_id, 'to_uid' => $uid])->find();
        if(!empty($getBonus)) {
            apiReturn('1023', AJAX_TRUE, [
                'is_get_bonus' => '1',
                'uid'          => strval($uid),
                'bonus_id'     => strval($bonus_id),
                'money'        => strval($getBonus['money'])
            ]);    //当前用户是否抢过该红包 0未抢 1已抢
        }

        $totalMoney = $group_bonus['money'];
        $totalCount = $group_bonus['redpack_count'];
        $surplusCount = $group_bonus['surplus_count'];
        $surplusMoney = $group_bonus['surplus_amount'];
        $bestRedpack = 0;
        $status = 0;

        //红包类型 1拼手气红包 2普通红包
        if($redpack_type == 2) {

            $money = $totalMoney / $totalCount;
            if($surplusCount == 1) {
                $status = 1;
            }

            $bestRedpack = 0;
            $surplusCount --;
            $surplusMoney -= $money;

            //红包类型 1拼手气红包 2普通红包
        }
        elseif($redpack_type == 1) {

            //红包总数为1
            if($totalCount == 1) {
                $status = 1;
                $money = $totalMoney;
                $bestRedpack = 1;

            }
            elseif($totalCount >= 2) {

                //红包总数为2
                $money = $this->getRandMoney($surplusCount, $surplusMoney);

                if(empty($money)) {
                    apiReturn('1023', AJAX_FALSE, '红包抢失败了1');
                }
                $bestRedpack = 0;

                //最后一个红包
                $status = $surplusCount == 1 ? 1 : 0;
            }
            $surplusCount --;
            $surplusMoney -= $money;
        }

        $res = D('Wallet')->updateWalletMoney($uid, $money, 1);
        if(!$res) {
            apiReturn('1023', AJAX_FALSE, '更新钱包余额失败');
        }
        //插入钱包变动记录
        $walletMoney = D('wallet')->getUserWallet($uid,'money');
        $recordSn = A('Order')->setOrderSn();
        $walletMoney = $walletMoney['money']+$money;
        D('WalletLog')->addWalletRecord($recordSn,$uid,$money,$walletMoney, '',$_SERVER["REQUEST_TIME"],1);
        //插入收入记录日志
        D('Harvest')->addHarvestLog($uid, $money, 1, 2,'',1);
        //插入交易记录
        D('RecordLog')->addRecordLog($recordSn, $uid, $money, 1,0, 3, 0, 1,$_SERVER['REQUEST_TIME']);

        $results = [
            'bonus_id'     => $bonus_id,
            'group_id'     => $group_id,
            'redpack_type' => $redpack_type,
            'from_uid'     => $group_bonus['from_uid'],
            'to_uid'       => $uid,
            'money'        => $money,
            'best_redpack' => $bestRedpack,
            'create_date'  => $_SERVER['REQUEST_TIME']
        ];
        M('Group_bonus_result')->add($results);

        if(!$surplusCount && $redpack_type == 1) {
            // 最佳手气王,随机红包才有最佳手气王,剩余红包个数为0
            $where = ['bonus_id' => $bonus_id, 'group_id' => $group_id];

            $bonusInfo = M('group_bonus_result')->where($where)->order('money desc')->limit(1)->find();

            M('group_bonus_result')->where('id=' . $bonusInfo['id'])->setField('best_redpack', 1);
        }

        //记录入库
        $data = [
            'surplus_count'  => $surplusCount,      //剩余红包个数
            'surplus_amount' => $surplusMoney,      //剩余红包金额
            'status'         => $status             //红包状态
        ];
        M('Group_bonus')->where(['bonus_id' => $bonus_id, 'group_id' => $group_id])->save($data);

        apiReturn('1024', AJAX_TRUE, [
            'status'   => '0',
            'uid'      => strval($uid),
            'bonus_id' => strval($bonus_id),
            'money'    => strval($money)
        ]);
    }

    /**得随机红包
     * @param $surplusCount 剩余红包个数
     * @param $surplusMoney 剩余红包余额
     * @return float
     */
    public function getRandMoney($surplusCount, $surplusMoney)
    {
        if($surplusCount == 1) {
            $surplusCount --;

            return $surplusMoney;
        }

        $min = 0.01;
        $max = ($surplusMoney / $surplusCount) * 2;

        $money = mt_rand($min * 100, $max * 100) / 100;
        $money = $money <= $min ? 0.01 : $money;

        return $money;
    }

    //领取通知
    public function receiveNotice()
    {
        $AesMcrypt = new MCrypt;
        $uid = $AesMcrypt->decrypt(urldecode(I('post.uid')));   //红包拆开者
        $bonus_id = $AesMcrypt->decrypt(urldecode(I('post.bonus_id')));
        $bonus_type = I('post.bonus_type');

        if(empty($uid) || empty($bonus_id) || empty($bonus_type)) {
            apiReturn('1023', AJAX_FALSE, '必要参数不能为空');
        }
        if($bonus_type == 'person') {
            $bonusResult = M('Person_bonus')->where('bonus_id=' . $bonus_id)->find();
        }
        elseif($bonus_type == 'group') {
            $bonusResult = M('Group_bonus')->where('bonus_id=' . $bonus_id)->find();
        }
        else {
            apiReturn('1023', AJAX_FALSE, '红包类型参数不正确');
        }
    }

    //红包领取结果
    public function bonusResult()
    {
        $AesMcrypt = new MCrypt;
        $uid = $AesMcrypt->decrypt(urldecode(I('post.uid')));   //红包拆开者
        $bonus_id = $AesMcrypt->decrypt(urldecode(I('post.bonus_id')));
        $bonus_type = I('post.bonus_type');

        //$uid = 3454;
        //$bonus_id = 2;
        //$bonus_type = 'group';

        if(empty($uid) || empty($bonus_id) || empty($bonus_type)) {
            apiReturn('1023', AJAX_FALSE, '必要参数不能为空');
        }

        if($bonus_type == 'person') {

            $bonusResult = M('Person_bonus_result')->where('bonus_id=' . $bonus_id)->find();
            $bonus = M('Person_bonus')->where('bonus_id=' . $bonus_id)->find();
            $bonusResult['status'] = $bonus['status'];
            $bonusResult['total_money'] = $bonus['money'];
            $bonusResult['from_uid'] = $bonus['from_uid'];
            //$bonusResult['is_get_bonus'] = '1';

            apiReturn('1024', AJAX_TRUE, $bonusResult); //获取数据成功

        }
        elseif($bonus_type == 'group') {

            $nowPage = I('post.nowpage');   //页码
            $num = I('post.num');   //每页条数

            //$nowPage = 1;
            //$num = 5;

            $where = ['bonus_id' => $bonus_id];
            $order = ['create_date' => 'desc', 'best_redpack' => 'desc'];
            //数据分页
            $config = array(
                'tablename' => 'GroupBonusRelation', // 表名
                'relation'  => true,
                'where'     => $where,  //查询条件
                'order'     => $order, // 排序
                'page'      => $nowPage,  // 页码，默认为首页
                'num'       => $num,  // 每页条数
                'field'     => 'bonus_id,group_id,from_uid,redpack_type,redpack_count,money,from_status,surplus_count,surplus_amount,status,remark,create_date'
            );

            $page = new ApiPage($config);
            $data = $page->get(); //获取分页数据
            if($nowPage == 0) {
                apiReturn('1019', AJAX_FALSE);   //获取数据失败
            }
            else {
                $bonusResult = array();
                foreach($data as $k => $v) {
                    foreach($v as $m => $n) {
                        $bonusResult['sums_page'] = $data['total_page'];    //总页数
                        $nowUserMoney = M('Group_bonus_result')->where(['bonus_id=' . $bonus_id,
                                                                        'to_uid' => $uid])->getField('money');    //当前用户抢到多少钱

                        if($nowUserMoney) {
                            $bonusResult['nowUserMoney'] = $nowUserMoney;
                        }
                        else {
                            $bonusResult['nowUserMoney'] = '';
                        }
                        $bonusResult[ $m ] = $n;

                        foreach($bonusResult[ $m ] as $a => $b) {
                            $userInfo = M('Account')->where('id=' . $bonusResult[ $m ][ $a ]['to_uid'])->field('uname,icon')->find();
                            $bonusResult[ $m ][ $a ]['uname'] = $userInfo['uname'];
                            $bonusResult[ $m ][ $a ]['icon'] = $userInfo['icon'];
                        }
                    }
                }

                apiReturn('1020', AJAX_TRUE, $bonusResult);   //获取数据成功
                //p($bonusResult);
            }
        }
        else {
            apiReturn('1023', AJAX_FALSE, '红包类型参数不正确');
        }
    }
}