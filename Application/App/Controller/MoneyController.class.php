<?php
//+----------------------------------------------------------------------
// | Copyright (c) 2015-2017 http://lawyerscloud.cn All rights reserved.
//+----------------------------------------------------------------------
// | Author: 慢悠悠的丑小鸭 <1597305236@qq.com>
//+----------------------------------------------------------------------

namespace App\Controller;

use Common\Api\MCrypt;
use Common\Api\ApiPage;
use Common\Api\ChatApi;
use Think\Model;

class MoneyController extends BasicController
{
    // 交易记录,3.2.9以下版本,后续会废弃
    public function BillingRecord() {
        $AesMct  = new MCrypt;
        $uid     = session('my_id');
        $nowPage = $AesMct->decrypt(urldecode(I('post.nowPage')));
        $num     = $AesMct->decrypt(urldecode(I('post.num')));

        if(empty(session('my_id'))) {
            apiReturn('1021', AJAX_FALSE, '您还是未登录状态');
        }

        $where                 = 'status != 2 AND (get_user=' . $uid . ' OR pay_user=' . $uid . ')';
        $order['payment_time'] = 'desc';

        //数据分页
        $config = array(
            'tablename' => 'BonusRelation', //表名或模型名
            'relation'  => true,
            'where'     => $where,
            'order'     => $order,
            'page'      => $nowPage,
            'num'       => $num,
            'field'     => 'take_amount,amount,pay_user,get_user,type,money_type,record_type,payment_time,status'
        );
        //获取分页数据
        $page = new ApiPage($config);
        $data = $page->get();

        if($data['now_page'] == 0) {
            //获取数据失败
            apiReturn('1019', AJAX_FALSE);
        }

        $getList = array();
        foreach($data as $k => $v) {
            foreach($v as $m => $n) {
                $getList[ $k ][ $m ] = $n;

                if($getList[ $k ]['resources'] == null) {
                    $getList[ $k ]['resources']['title'] = '';
                }

                (string)$getList[ $k ]['record_type'];
            }
        }

        apiReturn('1020', AJAX_TRUE, $getList);
    }


    //新版交易记录
    public function BillingRecordV4() {
        $userId           = I('post.user_id');
        $nowPage          = I('post.now_page');
        $num              = I('post.num');
        $where['user_id'] = $userId;
        $order            = 'pay_time desc';

        if(empty($userId)) {
            apiReturn('1021', AJAX_FALSE, 'user_id参数不能为空');
        }
        if(empty($nowPage) || empty($num)) {
            apiReturn('1021', AJAX_FALSE, '请传入分页参数');
        }

        $config = array(
            'tablename' => 'record_log',
            'where'     => $where,
            'order'     => $order,
            'page'      => $nowPage,
            'num'       => $num,
            'field'     => 'money,trade_type,type,pay_status,pay_time'
        );
        //获取分页数据
        $page = new ApiPage($config);
        $data = $page->get();

        if($data['now_page'] == 0) {
            apiReturn('1019', AJAX_FALSE);
        }
        $list = array();
        foreach($data as $k => $v) {
            foreach($v as $m => $n) {
                $list[ $k ]['sums_page'] = (string)$data['total_page'];
                $list[ $k ][ $m ]        = $n;
                //$list[ $k ]['trade_title'] ? $list[ $k ]['trade_title'] = $v['trade_title']['title'] : $list[ $k ]['trade_title'] = '';
                if($list[ $k ]['type'] == 2) {
                    $list[ $k ]['withdrawal_money'] = (string)($list[ $k ]['money'] - $list[ $k ]['money'] * 0.05);
                } else {
                    $list[ $k ]['withdrawal_money'] = '';
                }
            }
        }

        apiReturn('1024', AJAX_TRUE, $list);
    }


    // 钱包余额
    public function wallet() {
        if(empty(session('my_id'))) {
            apiReturn('1021', AJAX_FALSE, '您还是未登录状态');
        }

        // 获取用户信息
        $userInfo = D('Wallet')->getUserWallet(session('my_id'), 'uid,money,withdraw');

        // 提现最低额度
        $minimum             = file_get_contents('bonus_minimum.json');
        $userInfo['minimum'] = json_decode($minimum, true)['minimum'];

        //当天没有提现记录
        $paymentTime
            = M('Bonus_log')->where('money_type=2 AND get_user=' . $userInfo['uid'])->order('payment_time desc')->getField('payment_time');

        if(date('Ymd', $_SERVER["REQUEST_TIME"]) == date('Ymd', $paymentTime)) {
            // 当天已提过现(限每天一次)
            $userInfo['status'] = '1';
        } else {
            $userInfo['status'] = '0';
        }
        $users['uid']              = $userInfo['uid'];
        $users['wallet']           = $userInfo['money'];
        $users['withdraw_account'] = $userInfo['withdraw'];
        $users['minimum']          = $userInfo['minimum'];
        $users['status']           = $userInfo['status'];
        unset($userInfo);

        apiReturn('1024', AJAX_TRUE, $users);
    }


    // 用户提现
    public function take_account() {
        $AesMct      = new MCrypt;
        $userId      = session('my_id');
        $tel         = $AesMct->decrypt(urldecode(I('post.tel')));    //手机号
        $code        = $AesMct->decrypt(urldecode(I('post.code')));    //验证码
        $account     = $AesMct->decrypt(urldecode(trim(I('post.get_account')))); //提现账户
        $totalAmount = $AesMct->decrypt(urldecode(trim(I('post.amount'))));  // 提现金额
        $userInfo    = D('User')->getUser($userId, 'id,uname,type,mobile');// 用户信息

        // 游客身份不允许提现
        if($userInfo['type'] == 4) {
            apiReturn('1023', AJAX_FALSE, '游客身份不允许提现');
        }

        // 提现账号为空, 或提现金额为空
        if(empty($account) || empty($totalAmount)) {
            apiReturn('1025', AJAX_FALSE, '未获取到提现账户,请正确填写账号和提现金额');
        }

        // 限每天提现一次
        $oldCreateDate
            = M('withdrawal_log')->where(array('uid' => $userId))->order('create_date desc')->limit(1)->getField('create_date');
        if($_SERVER['REQUEST_TIME'] - $oldCreateDate < 86400) {
            apiReturn('1026', AJAX_FALSE, '您今天已提现过，限每天提现一次');
        }

        // 余额小于最低额度,提现金额小于最低额度
        $wallet  = D('wallet')->getUserWallet($userId);     //钱包信息详情
        $minimum = json_decode(file_get_contents('bonus_minimum.json'), true)['minimum'];    // 提现最低额度
        if($wallet['money'] < $minimum || $totalAmount < $minimum || $totalAmount > $wallet['money']) {
            apiReturn('1021', AJAX_FALSE, '余额不足，不能提现');
        }

        // 校验短信验证码通过
        $yunxin = new ChatApi;
        $res    = $yunxin->verifycode($tel, $code);
        if($res['code'] !== 200) {
            apiReturn('1022', AJAX_FALSE, '验证码验证失败');
        }

        //用户支付宝账号为空,第一次提交提现提现账户
        if(empty($wallet['withdraw'])) {
            D('wallet')->updateUserWallet($userId, array('withdraw' => $account));
        }

        //手续费0.5%
        $process = $totalAmount * 0.005;
        $amount  = $totalAmount - $process;
        //插入提现日志记录
        $recordSn     = A('order')->uniqueOrderSn($userId);
        $rechargeData = [
            'record_sn'        => $recordSn,
            'uid'              => $userId,
            'user_name'        => $userInfo['uname'],
            'withdraw_account' => $account,
            'total_money'      => $totalAmount,
            'money'            => $amount,
            'process'          => $process,
            'create_date'      => $_SERVER["REQUEST_TIME"],
            'payment_date'     => 0,
            'operator'         => 0,
            'status'           => 0
        ];
        M('withdrawal_log')->add($rechargeData);

        //插入钱包变动日志
        $recordSn = A('Order')->uniqueOrderSn($userId);
        D('WalletLog')->addWalletRecord($recordSn, $userId, $amount, ($wallet['money'] + $amount), '', $_SERVER["REQUEST_TIME"], 1);
        //插入支出日志记录
        D('pay')->addPayLog($userId, $totalAmount, 2, 1, 0, 0);
        //插入交易流水记录
        D('RecordLog')->addRecordLog($recordSn, $userId, $totalAmount, 2, '', 2, 4, 1, $_SERVER["REQUEST_TIME"]);

        // 扣除用户余额
        if(D('wallet')->updateWalletMoney($userId, $totalAmount, 0)) {
            apiReturn('1024', AJAX_TRUE);
        } else {
            apiReturn('1023', AJAX_FALSE, '申请提现失败');
        }
    }


    //余额充值
    public function recharge() {
        $AesMct   = new MCrypt;
        $uid      = $AesMct->decrypt(urldecode(I('post.uid')));
        $money    = $AesMct->decrypt(urldecode(trim(I('post.money'))));
        $recordSn = A('Order')->uniqueOrderSn($uid);

        if(empty($uid) || empty($money) || empty($recordSn)) {
            apiReturn('1040', AJAX_FALSE, '缺少必要参数');
        }
        //if($money<1) apiReturn('1030', AJAX_FALSE, '充值金额必须大于1元');

        $data = [
            'uid'         => $uid,
            'money'       => $money,
            'record_sn'   => $recordSn,
            'create_date' => $_SERVER["REQUEST_TIME"]
        ];

        //新增充值订单
        if($orderId = M('Recharge_log')->add($data)) {

            //新增订单关联数据
            $updateUnionData = [
                'record_sn'   => $recordSn,
                'order_type'  => 'recharge',
                'order_id'    => $orderId,
                'create_date' => $_SERVER["REQUEST_TIME"],
                'status'      => 0
            ];
            $unionOrderId    = A('Order')->insertOrder('order', $updateUnionData);

            if($unionOrderId) {
                apiReturn('1020', AJAX_TRUE, array('order_id' => (string)$unionOrderId));
            } else {
                apiReturn('1040', AJAX_FALSE);
            }
        } else {
            apiReturn('1040', AJAX_FALSE);
        }
    }


    //创建或重置支付密码
    public function updatePayPassword() {
        $yunxin    = new ChatApi;
        $AesMcrypt = new MCrypt;

        $accid        = $AesMcrypt->decrypt(urldecode(I('post.tel')));
        $code         = $AesMcrypt->decrypt(urldecode(I('post.code')));
        $pay_password = $AesMcrypt->decrypt(urldecode(I('post.pay_password')));

        if(empty($pay_password)) {
            apiReturn('1023', AJAX_FALSE, '支付密码参数不能为空');
        }

        //校验手机验证码
        $res = $yunxin->verifycode($accid, $code);
        if($res['code'] !== 200) {
            apiReturn('1003', AJAX_FALSE, '验证码验证失败');
        }

        //修改支付密码
        $uid = M('Account')->where(array('mobile' => $accid))->getField('id');
        if(empty($uid)) {
            apiReturn('1023', AJAX_FALSE, '用户id参数获取失败');
        }

        $pay_password = md5(md5($pay_password) . 'lvxie');
        $res          = D('Wallet')->updateUserWallet($uid, array('pay_password' => $pay_password));

        if(!$res) {
            apiReturn('1023', AJAX_FALSE, '支付密码修改失败');
        }

        apiReturn('1024', AJAX_TRUE);
    }


    //余额支付
    public function balancePay() {
        $AesMct        = new MCrypt();
        $uid           = $AesMct->decrypt(urldecode(I('post.uid')));
        $to_uid        = $AesMct->decrypt(urldecode(I('post.to_uid')));
        $resource_id   = $AesMct->decrypt(urldecode(I('post.resource_id')));  //资源id
        $resource_type = $AesMct->decrypt(urldecode(I('post.resource_type')));  //资源类型 1求助 2回答
        $money         = $AesMct->decrypt(urldecode(I('post.money')));
        $pay_password  = $AesMct->decrypt(urldecode(I('post.pay_password')));
        $payTime       = $_SERVER["REQUEST_TIME"];

        if(empty($uid) || empty($resource_id) || empty($resource_type) || empty($money) || empty($pay_password)) {
            apiReturn('1023', AJAX_FALSE, '必填参数不能为空');
        }

        //确认支付密码,是否设置过支付密码标识参数 在发布文章处标识
        $this->checkPayPassword($uid, $pay_password);

        //检测钱包余额不足
        $wallet = D('wallet')->getUserWallet($uid);
        $wallet = $wallet['money'];

        if($resource_type == 'article') {
            $record_sn = M('article_reward_order')->where('resource_id=' . $resource_id)->getField('record_sn');
        } elseif($resource_type == 'comment') {
            $record_sn = M('answer_reward_order')->where('comment_id=' . $resource_id)->getField('record_sn');
        }

        if($resource_type == 'article') {

            //悬赏求助余额支付
            if($wallet < $money) {
                M('Resource')->where('id=' . $resource_id)->setField('is_reward', 0);

                apiReturn('1023', AJAX_FALSE, '余额不足');
            }
            M('resource')->where('id=' . $resource_id)->save(['status' => 1,'is_reward' => 1, 'reward_money' => $money]);

            $articleRewardArr = [
                'record_id'   => '',
                'resource_id' => $resource_id,
                'from_uid'    => $uid,
                'to_uid'      => $to_uid,
                'money'       => $money,
                'create_date' => $payTime,
                'status'      => 0
            ];
            //$moneyType = 5; //5悬赏红包 6打赏红包
            //创建悬赏求助订单
            $this->createArticleRewardOrder($articleRewardArr);
            $type = 4;//交易类型
        } elseif($resource_type == 'comment') {

            //打赏余额支付
            if(empty($to_uid)) {
                apiReturn('1023', AJAX_FALSE, '必填参数不能为空');
            }
            if($wallet < $money) {
                apiReturn('1023', AJAX_FALSE, '余额不足');
            }
            if($money < 1) {
                apiReturn('1023', AJAX_FALSE, '打赏金额最低1块钱');
            }

            //修改打赏标识
            M('Resource_comment')->where('id=' . $resource_id)->setInc('user_reward', $money);
            //打钱到领赏用户钱包
            D('wallet')->updateWalletMoney($to_uid, $money, 1);

            $answerRewardOrderArr = [
                'record_id'   => '',
                'comment_id'  => $resource_id,
                'from_uid'    => $uid,
                'to_uid'      => $to_uid,
                'money'       => $money,
                'create_date' => $payTime,
                'status'      => 0
            ];

            //创建打赏回答订单
            $this->createAnswerRewardOrder($answerRewardOrderArr);
            //更新交易记录
            //D('BonusLog')->createBonusLog($money, '', $_SERVER['REQUEST_TIME'], $uid, $to_uid, 2, '', 6, 0);

            //推送通知
            $push          = A('PushMsg');
            $toMobile      = strval(user_info($to_uid, 'mobile'));
            $alias         = array('alias' => array($toMobile));
            $m_txt['type'] = 0;//0系统消息 1回答我 2求助我 3评论我 4响应我
            $n_content     = '你获得打赏' . $money . '元';
            $push->sendPush('all', $alias, $n_content, 'http', $m_txt);
            $push->addMessage($to_uid, $n_content, $n_content, $toMobile, 2, $_SERVER['REQUEST_TIME']);

            $type = 5;//交易类型
            D('RecordLog')->addRecordLog($record_sn, $to_uid, $money, 1, $resource_id, $type, 4, 1, $_SERVER['REQUEST_TIME']);
        } else {
            $tradeType = 0;
            apiReturn('1021', AJAX_FALSE);
        }

        //扣除用户钱包余额
        $res = D('wallet')->updateWalletMoney($uid, $money, 0);

        if(!$res) {
            M('Resource')->where('id=' . $resource_id)->setField('is_reward', 0);
            apiReturn('1023', AJAX_FALSE, '扣款失败');
        }

        //更新订单状态、绑定关联资源id
        if($resource_type == 'article') {
            M('article_reward_order')->where('resource_id=' . $resource_id)->save(array('status' => 1));
            M('resource')->where(['id' => $resource_id])->setField('status', 1);
        } elseif($resource_type == 'comment') {

            M('answer_reward_order')->where('comment_id=' . $resource_id)->save(array('money'  => $money,
                                                                                      'status' => 1));
        }

        //更新订单关联表状态
        M('order')->where(['record_sn' => $record_sn])->setField('status', 1);
        //插入钱包变动记录
        D('WalletLog')->addWalletRecord($record_sn, $uid, $money, $wallet - $money, '', $payTime, 1);
        //插入支出记录日志
        D('Pay')->addPayLog($uid, $money, 2, $tradeType, $resource_id, 1);
        //插入交易记录
        D('RecordLog')->addRecordLog($record_sn, $uid, $money, 2, $resource_id, $type, 4, 1, $_SERVER['REQUEST_TIME']);

        apiReturn('1024', AJAX_TRUE);
    }


    /**
     * 余额支付,通用接口
     */
    public function balancePayment() {
        $mcrypt        = new MCrypt();
        $userId        = $mcrypt->decrypt(urldecode(I('post.uid')));
        $payPassword  = $mcrypt->decrypt(urldecode(I('post.pay_password')));
        $orderSn       = $mcrypt->decrypt(urldecode(I('post.order_sn')));

        if(empty($userId) || empty($payPassword) || empty($orderSn)) {
            apiReturn('1023', AJAX_FALSE, '必填参数不能为空');
        }

        //校验支付密码
        $this->checkPayPassword($userId, $payPassword);

        $orderInfo = M('order')->where(array('record_sn'=>$orderSn))->find();

        if(empty($orderInfo)) {
            apiReturn('1011', AJAX_FALSE, '待支付的订单不存在');
        }

        if($orderInfo['status']) {
            apiReturn('1012', AJAX_FALSE, '此订单已经支付过');
        }

        //检测钱包余额不足
        $wallet = D('wallet')->getUserWallet($userId);
        $wallet = $wallet['money'];
        if($wallet < $orderInfo['amount']) {
            apiReturn('1013', AJAX_FALSE, '钱包余额不足,请用其他支付方式');
        }

        //扣除用户钱包余额
        D('wallet')->updateWalletMoney($userId, $orderInfo['amount'], 0);

        //更新关联订单、订单支付状态
        M('order')->where(['record_sn' => $orderSn])->setField('status', 1);

        switch($orderInfo['order_type']) {
            case 'train':
                M('order_train')->where(['record_sn' => $orderSn])->setField('status', 1);
                break;
            case 'live':
                M('order_live')->where(['record_sn' => $orderSn])->setField('status', 1);
                break;
            case 'activity':
                M('order_activity')->where(['record_sn' => $orderSn])->setField('status', 1);
                break;
        }

        //插入钱包变动记录
        D('WalletLog')->addWalletRecord($orderSn, $userId, $orderInfo['amount'], $wallet - $orderInfo['amount'], '',
                                        $_SERVER["REQUEST_TIME"], 1);

        //插入交易记录
        switch($orderInfo['order_type']) {
            case 'train':
                $payType = 6;
                break;
            case 'live':
                $payType = 7;
                break;
            case 'activity':
                $payType = 8;
                break;
        }

        D('RecordLog')->addRecordLog($orderSn, $userId, $orderInfo['amount'], 2, 0, $payType, 4, 1,
                                     $_SERVER['REQUEST_TIME']);

        apiReturn('1024', AJAX_TRUE);
    }


    //微信支付
    public function wxpay() {
        $mcrypt    = new MCrypt();
        $body      = $mcrypt->decrypt(urldecode(I('post.body')));    //商品描述
        $amount = $mcrypt->decrypt(urldecode(I('post.total_fee')));  //订单总金额，单位为元
        $order_id  = $mcrypt->decrypt(urldecode(I('post.order_id')));  //关联订单id
        $order_sn  = $mcrypt->decrypt(urldecode(I('post.order_sn')));  //关联订单id

        //file_put_contents('post.txt')
        if(empty($body) || empty($amount) || empty($order_id) || empty($order_sn)) {
            apiReturn('1023', AJAX_FALSE, '缺少必要参数');
        }

        //获取订单号
        if(empty($order_sn)) {
            $order = M('order')->where(['id' => $order_id])->find();
        }else{
            $order = M('order')->where(['record_sn' => $order_sn])->find();
        }

        //检测传参的金额与订单金额是否相同。
        //之所以需要前端传金额参数，是因为支付系统有两种：一是用户手动输入的金额；二是后台设定的金额
        //问答打赏的订单金额参数是在支付异步通知那里更新的,即这里金额校验需排除打赏支付类型
        if(($amount !== $order['amount']) and ($order['order_type'] !== 'answer_reward_order')) {
            apiReturn('505', AJAX_FALSE, '支付金额参数有误');
        }

        $wxpay  = A('Wxpay');
        $data   = $wxpay->getPrePayOrder($body, $order['record_sn'], $amount);
        $payArr = $wxpay->getOrder($data['prepay_id']);

        if(empty($payArr)) {
            apiReturn('1021', AJAX_FALSE);
        }

        apiReturn('1024', AJAX_TRUE, $payArr);
    }


    //支付宝支付
    public function alipay() {
        $AesMct       = new MCrypt();
        $body         = $AesMct->decrypt(urldecode(I('post.body')));    //对一笔交易的具体描述信息。如果是多种商品，请将商品描述字符串累加传给body。
        $subject      = $AesMct->decrypt(urldecode(I('post.subject')));  //商品的标题/交易标题/订单标题/订单关键字等。
        $total_amount = $AesMct->decrypt(urldecode(I('post.total_amount')));
        $orderId      = $AesMct->decrypt(urldecode(I('post.order_id')));  //关联订单id
        $order_sn     = $AesMct->decrypt(urldecode(I('post.order_sn')));  //关联订单id

        if(empty($total_amount) || empty($body) || empty($orderId)) {
            apiReturn('1023', AJAX_FALSE, '缺少必要参数');
        }
        //获取订单号
        if(empty($order_sn)) {
            $orderInfo = M('order')->where(['id' => $orderId])->find();
        }else{
            $orderInfo = M('order')->where(['record_sn' => $order_sn])->find();
        }

        $out_trade_no = $orderInfo['record_sn'];
        $orderType    = $orderInfo['order_type'];


        if($orderType == 'answer_reward_order') {
            //更新打赏订单的总金额
            M('answer_reward_order')->where(array('record_sn' => $out_trade_no))->setField('money', $total_amount);
        }

        $alipay = A('Alipay');
        $payStr = $alipay->aliPay($body, $subject, $out_trade_no, $total_amount);
        if(empty($payStr)) {
            apiReturn('1021', AJAX_FALSE);
        }

        apiReturn('1024', AJAX_TRUE, $payStr);
    }


    //取消支付
    public function cancelPay() {
        //帖子的悬赏标识改为0
        $AesMct   = new MCrypt();
        $rid      = $AesMct->decrypt(urldecode(I('post.rid')));
        $response = M('resource')->where('id=' . $rid)->save(['is_reward' => 0, 'reward_money' => 0]);

        if(!$response) {
            apiReturn('1021', AJAX_FALSE);
        }

        apiReturn('1024', AJAX_TRUE);
    }


    /**确认支付密码
     *
     * @param $uid
     * @param $pay_password
     * @return bool
     */
    public function checkPayPassword($uid, $pay_password) {
        if(empty($uid)) {
            apiReturn('1023', AJAX_FALSE, '缺少用户id参数');
        }
        if(empty($pay_password)) {
            apiReturn('1023', AJAX_FALSE, '支付密码不能为空');
        }

        //检测支付密码错误次数
        $wallet = M('Wallet')->where('uid=' . $uid)->field('pay_password')->find();
        if($wallet['password_error_num'] >= 3) {
            apiReturn('1023', AJAX_FALSE, '您输入密码错误次数超过3次，请修改支付密码');
        }

        //确认支付密码
        $pay_password = md5(md5($pay_password) . 'lvxie');
        if($pay_password !== $wallet['pay_password']) {
            M('Wallet')->where('uid=' . $uid)->setInc('password_error_num', 1);
            apiReturn('1023', AJAX_FALSE, '支付密码不正确');
        }
    }


    /**创建悬赏求助订单
     *
     * @param $arr
     */
    public function createArticleRewardOrder($arr) {
        M('article_reward_order')->add($arr);
    }


    /**创建打赏回答订单
     *
     * @param $arr
     */
    public function createAnswerRewardOrder($arr) {
        M('answer_reward_order')->add($arr);
    }
}