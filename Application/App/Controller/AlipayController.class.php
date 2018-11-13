<?php
//+----------------------------------------------------------------------
// | Copyright (c) 2015-2017 http://lawyerscloud.cn All rights reserved.
//+----------------------------------------------------------------------
// | Author: 慢悠悠的丑小鸭 <1597305236@qq.com>
//+----------------------------------------------------------------------

namespace App\Controller;

use Common\Api\MCrypt;

class AlipayController extends BasicController
{
    public function _initialize()
    {
        Vendor('Alipay.aop.AopClient');
        Vendor('Alipay.aop.request.AlipayTradeAppPayRequest');
    }

    /**支付宝支付
     * @param $body
     * @param $subject
     * @param $out_trade_no
     * @param $total_amount
     * @param $product_code
     * @return string
     */
    public function aliPay($body, $subject, $out_trade_no, $total_amount)
    {
        touchFile('alipay',json_encode($_POST));

        //构造业务请求参数的集合(订单信息)
        $content = array();
        $content['body'] = $body;
        $content['subject'] = $subject;//商品的标题/交易标题/订单标题/订单关键字等
        $content['out_trade_no'] = $out_trade_no;//商户网站唯一订单号
        $content['timeout_express'] = '90m';//该笔订单允许的最晚付款时间
        $content['total_amount'] = floatval($total_amount);//订单总金额(必须定义成浮点型)
        $content['product_code'] = 'QUICK_MSECURITY_PAY';//销售产品码，商家和支付宝签约的产品码，为固定值QUICK_MSECURITY_PAY
        $con = json_encode($content);//$content是biz_content的值,将之转化成字符串

        //公共参数
        $param = array();
        $Client = new \AopClient();//实例化支付宝sdk里面的AopClient类,下单时需要的操作,都在这个类里面
        $param['app_id'] = C('alipay_config')['appId'];//支付宝分配给开发者的应用ID
        $param['method'] = 'alipay.trade.app.pay';//接口名称
        $param['charset'] = 'UTF-8';//请求使用的编码格式
        $param['sign_type'] = 'RSA2';//商户生成签名字符串所使用的签名算法类型
        $param['timestamp'] = date("Y-m-d H:i:s", time());//发送请求的时间,格式："yyyy-MM-dd HH:mm:ss"
        $param['version'] = '1.0';//调用的接口版本，固定为：1.0
        $param['biz_content'] = $con;//业务请求参数的集合,长度不限,json格式
        $param['notify_url'] = 'http://' . $_SERVER['HTTP_HOST'] . '/Alipay/notify';//支付宝服务器主动通知地址


        //生成签名
        $paramStr = $Client->getSignContent($param);
        //return $paramStr;exit;
        $sign = $Client->alonersaSign($paramStr, C('alipay_config')['rsaPrivateKey'], 'RSA2');

        $param['sign'] = $sign;
        $str = $Client->getSignContentUrlencode($param);

        return $str;
    }

    //异步通知
    function notify()
    {
        //验证签名
        $client = new  \AopClient();
        $client->alipayrsaPublicKey = C('alipay_config')['alipayrsaPublicKey'];
        $verifyResult = $client->rsaCheckV1($_POST, C('alipay_config')['alipayrsaPublicKey'], "RSA2");

        //touchFile('alipay','接收通知成功');

        //验证签名
        if($verifyResult)
        {
            $out_trade_no = $_POST['out_trade_no'];      //商户订单号
            $trade_no = $_POST['trade_no'];             //支付宝交易号
            $trade_status = $_POST['trade_status'];      //交易状态
            $total_amount = $_POST['total_amount'];         //交易金额
            $notify_time = $_POST['notify_time'];       //通知的发送时间。格式为yyyy-MM-dd HH:mm:ss。
            $buyer_email = $_POST['buyer_logon_id'];       //买家支付宝帐号；

            $parameter = array(
                "out_trade_no" => $out_trade_no, //商户订单编号
                "pay_type"     => 'alipay',
                "trade_no"     => $trade_no,     //交易订单号
                "total_amount" => $total_amount, //交易金额
                "trade_status" => $trade_status, //交易状态
                "notify_time"  => $notify_time,  //付款时间
                "buyer_email"  => $buyer_email  //买家支付宝帐号
            );

            //插入支付记录
            M('Payment_log')->add($parameter);

            if($_POST['app_id'] !== C('alipay_config')['appId']) {
                //验证app_id是否为该商户本身
                exit;
            }
            if($_POST['trade_status'] !== 'TRADE_SUCCESS') {
                //判断订单支付状态
                exit;
            }
            if($this->checkOrderStatus($out_trade_no)) {
                //检测订单是否已经处理过
                exit;
            }

            //进行订单处理，并传送从支付宝返回的参数
            $record_sn = $out_trade_no;     //唯一订单号
            $orderData = M('order')->where(array('record_sn' => $record_sn))->find();   //关联订单信息
            $tableName = $orderData['order_type'];              //表名

            //更新订单关联表支付状态
            M('order')->where(['record_sn' => $record_sn])->setField('status', 1);


            if($tableName == 'article_reward_order')
            {
                //悬赏求助

                $articleOrder = M($tableName)->where(array('record_sn' => $orderData['record_sn']))->getField('resource_id,from_uid,money');   //订单的总金额
                $totalMoney = $articleOrder['money'];
                $resourceId = $articleOrder['resource_id'];
                $fromUid = $articleOrder['from_uid'];
                $tradeType = 2;
                $type = 4;

                if($total_amount !== $totalMoney) {
                    //比对金额不相等
                    touchFile('alipay','比对金额失败');
                    exit;
                }

                //修改订单支付状态
                $db = M('article_reward_order');
                $db->where(array('record_sn' => $record_sn))->setField('status', 1);
                M('resource')->where(['id' => $resourceId])->setField('status', 1);

            }

            elseif($tableName == 'answer_reward_order')
            {
                //打赏
                //修改订单支付状态
                $answerData = ['status' => 1, 'money' => $total_amount];
                M('answer_reward_order')->where(array('record_sn' => $record_sn))->save($answerData);

                //给该条回复增加金额
                $answerOrder = M('answer_reward_order')->where(array('record_sn' => $orderData['record_sn']))->field('comment_id,from_uid,to_uid')->find();
                $resourceId = $answerOrder['comment_id'];
                $fromUid = $answerOrder['from_uid'];
                $tradeType = 2;
                $type = 5;

                M('resource_comment')->where(array('id' => $answerOrder['comment_id']))->setInc('user_reward', $total_amount);

                //获得打赏用户钱包增加金额
                D('Wallet')->updateWalletMoney($answerOrder['to_uid'], $total_amount, 1);

            }


            elseif($tableName == 'group_bonus' || $tableName == 'person_bonus')
            {

                //更新红包订单状态
                $updateData = ['from_status' => 1];
                M($tableName)->where(array('record_sn' => $record_sn))->save($updateData);

                $fromUid = M($tableName)->where(array('record_sn' => $record_sn))->getField('from_uid');
                $resourceId = '';
                $tradeType = 2;
                $type = 3;
            }

            elseif($tableName == 'recharge')
            {
                //修改关联订单状态,在上面公共区
                //修改充值订单状态
                $fromUid = M('Recharge_log')->where(array('record_sn'=>$_POST['out_trade_no']))->getField('uid');
                $tradeType = 1;
                $type = 1;

                //插入充值记录日志
                M('Recharge_log')->where(array('record_sn'=>$_POST['out_trade_no']))->setField('status',1);
                //增加余额
                D('Wallet')->updateWalletMoney($fromUid,$_POST['total_amount'],1);
                //插入钱包变动日志
                $time = $_SERVER["REQUEST_TIME"];
                $userBalance = M('wallet')->where(array('uid' => $fromUid))->getField('money');//用户余额
                D('WalletLog')->addWalletRecord($out_trade_no, $fromUid, $total_amount, $userBalance+$total_amount, '充值', $time, 1);
            }

            elseif($tableName == 'train')
            {
                //更新订单状态为已支付
                //视频订单表,order关联表
                A('order')->updateOrder('video', $record_sn);
                $order = A('order')->getOrder('video', $record_sn);
                $resourceId = $order['video_id'];
                $fromUid = $order['user_id'];
                $tradeType = 2;
                $type = 6;
            }

            elseif($tableName == 'live')
            {
                //更新订单状态为已支付
                M('order_live')->where(['record_sn' => $record_sn])->setField('status', 1);
                $order = A('order')->getOrder('live', $record_sn);
                $resourceId = $order['live_id'];
                $fromUid = $order['user_id'];
                $tradeType = 2;
                $type = 7;
            }

            elseif($tableName == 'activity')
            {
                //更新订单状态为已支付
                M('order_activity')->where(['record_sn' => $record_sn])->setField('status', 1);
                $order = A('order')->getOrder('activity', $record_sn);
                $resourceId = $order['activity_id'];
                $fromUid = $order['user_id'];
                $tradeType = 2;
                $type = 8;
            }

            A('order')->updateRelationOrder($record_sn);

            //插入支出记录日志
            D('Pay')->addPayLog($fromUid, $total_amount, 2, $tradeType, $resourceId,1);
            //插入交易记录
            D('RecordLog')->addRecordLog($out_trade_no, $fromUid, $total_amount, $tradeType, $resourceId, $type, 1, 1, $notify_time);

            //返回成功
            echo "success";
            exit;

        } else {

            touchFile('alipay','验证签名失败');
        }
    }

    public function checkOrderStatus($out_trade_no)
    {
        $status = M('order')->where(['record_sn' => $out_trade_no])->getField('status');

        return $status;
    }
}