<?php
//+----------------------------------------------------------------------
// | Copyright (c) 2015-2017 http://lawyerscloud.cn All rights reserved.
//+----------------------------------------------------------------------
// | Author: 慢悠悠的丑小鸭 <1597305236@qq.com>
//+----------------------------------------------------------------------

namespace App\Controller;

use Common\Api\MCrypt;
use Common\Api\WxPay;

class WxpayController extends BasicController
{
    public function _initialize()
    {
        Vendor('Wxpay.lib.WxPay#Api');
        Vendor('Wxpay.lib.WxPay#Notify');
    }
    
    /**获取预支付订单
     * @param $body
     * @param $out_trade_no
     * @param $total_fee
     * @param $ip
     * @return array
     * @throws \WxPayException
     */
    public function getPrePayOrder($body, $out_trade_no, $total_fee)
    {
        $wxPayApi = new \WxPayApi();
        $wxPay = new WxPay();
        //将参与签名的数据保存到数组
        $data = [
            'appid'            => C('wxpay_config')['APPID'],
            'body'             => $body,
            'mch_id'           => strval(C('wxpay_config')['MCHID']),
            'nonce_str'        => $wxPayApi->getNonceStr(32),
            'notify_url'       => 'http://' . $_SERVER['HTTP_HOST'] . '/Wxpay/notify',
            'out_trade_no'     => $out_trade_no,
            'spbill_create_ip' => get_client_ip(),
            'total_fee'        => $total_fee * 100,
            'trade_type'       => 'APP',
        ];

        $xmlTpl = "<xml>
                        <appid><![CDATA[%s]]></appid>
                        <body><![CDATA[%s]]></body>
                        <mch_id><![CDATA[%s]]></mch_id>
                        <nonce_str><![CDATA[%s]]></nonce_str>
                        <notify_url><![CDATA[%s]]></notify_url>
                        <out_trade_no><![CDATA[%s]]></out_trade_no>
                        <spbill_create_ip><![CDATA[%s]]></spbill_create_ip>
                        <total_fee><![CDATA[%d]]></total_fee>
                        <trade_type><![CDATA[%s]]></trade_type>
                        <sign><![CDATA[%s]]></sign>
                    </xml>";                          //xml数据模板

        // 注意：以上几个参数是追加到$data中的，$data中应该同时包含开发文档中要求必填的剔除sign以外的所有数据
        $sign = $wxPay->getSign($data);        //获取签名

        //拼装生成xml数据格式请求的数据
        $xmlData = sprintf($xmlTpl, $data['appid'], $data['body'], $data['mch_id'], $data['nonce_str'], $data['notify_url'], $data['out_trade_no'], $data['spbill_create_ip'], $data['total_fee'], $data['trade_type'], $sign);

        //p($xmlData);
        //exit();

        //请求数据,统一下单
        $url = "https://api.mch.weixin.qq.com/pay/unifiedorder";

        $res = wxPay::postXmlCurl($xmlData, $url);

        if(!$res) {
            apiReturn('1023', AJAX_FALSE, 'Can\'t connect the server');
        }

        // 这句file_put_contents是用来查看服务器返回的结果 测试完可以删除了
        //file_put_contents('./log.txt', $res . PHP_EOL, FILE_APPEND);


        $content = $wxPay->xml_to_array($res);
        if(strval($content['return_code']) == 'FAIL') {

            apiReturn('1023', AJAX_FALSE, 'return fail');
        }
        if(strval($content['result_code']) == 'FAIL') {

            apiReturn('1023', AJAX_FALSE, 'result fail');
        }

        return $content;
    }

    //执行第二次签名，才能返回给客户端使用
    public function getOrder($prepayId)
    {
        $wxPay = new WxPay();
        $data["appid"] = C('wxpay_config')['APPID'];
        $data["noncestr"] = randString(32);;
        $data["package"] = "Sign=WXPay";
        $data["partnerid"] = strval(C('wxpay_config')['MCHID']);
        $data["prepayid"] = $prepayId;
        $data["timestamp"] = strval(time());
        $data["sign"] = $wxPay->getSign($data);

        return $data;
    }
    
    /**接收支付结果通知参数
     * @return array|bool
     */
    protected function getNotifyData()
    {
        $wxPay = new WxPay();
        $postXml = $GLOBALS["HTTP_RAW_POST_DATA"];    // 接受通知参数；
        if(empty($postXml)) {
            return false;
        }
        $postArr = $wxPay->xml_to_array($postXml);
        if($postArr === false) {
            return false;
        }
        if(!empty($postArr['return_code'])) {
            if($postArr['return_code'] == 'FAIL') {
                return false;
            }
        }

        //返回结果数组
        return $postArr;
    }

    //接收异步通知
    public function notify()
    {
        //这里没有去做回调的判断，可以参考手机做一个判断。
        //下面开始你可以把回调的数据存入数据库，或者和你的支付前生成的订单进行对应了。
        //需要记住一点，就是最后在输出一个success.要不然微信会一直发送回调包的，只有需出了succcess微信才确认已接收到信息不会再发包.

        // 接收数组
        $arr = $this->getNotifyData();
        touchFile('weixin',json_encode($arr));

        $wxPay = new WxPay();
        // 拼装数据进行第三次签名
        $sign = $wxPay->getSign($arr);        // 获取签名

        //比对签名值sign
        if($sign !== $arr['sign']) {
            $reply = "<xml><return_code><![CDATA[FAIL]]></return_code>";
            $reply .= "<return_msg><![CDATA[签名失败]]></return_msg></xml>";
            echo $reply;

            touchFile('weixin','签名错误');
            exit;
        }

        $record_sn = $arr['out_trade_no'];      //唯一订单号
        $orderData = M('order')->where(array('record_sn' => $record_sn))->find();       //关联订单信息
        $tableName = $orderData['order_type'];      //表名
        $totalFee = $arr['total_fee'] / 100;    //微信返回金额,单位元

        //比对金额
        //if($orderData[''] !== ($arr['total_fee'] / 100)) {
        //    $reply = "<xml><return_code><![CDATA[FAIL]]></return_code>";
        //    $reply .= "<return_msg><![CDATA[比对金额]]></return_msg></xml>";
        //    echo $reply;
        //
        //    touchFile('weixin','比对金额');
        //    exit;
        //}

        $parameter = array(
            "out_trade_no" => $arr['out_trade_no'],  //商户订单编号
            "pay_type"     => 'weixin',
            "trade_no"     => $arr['transaction_id'],//交易订单号
            "total_amount" => $totalFee,            //交易金额
            "trade_status" => $arr['result_code'],  //交易状态
            "notify_time"  => $arr['time_end'],     //付款时间
            "buyer_email"  => $arr['openid']        //买家支付宝帐号
        );
        //插入支付记录
        M('payment_log')->add($parameter);


        //更新订单关联表支付状态
        M('order')->where(['record_sn' => $record_sn])->setField('status', 1);

        if($tableName == 'article_reward_order'){
            //悬赏求助
            $articleOrder = M($tableName)->where(array('record_sn' => $orderData['record_sn']))->getField('resource_id,from_uid,money');
            $totalMoney = $articleOrder['money'];//订单的总金额,单位元
            $resourceId = $articleOrder['resource_id'];
            $fromUid = $articleOrder['from_uid'];
            $tradeType = 2;
            $type = 4;

            if((string)$totalFee !== $totalMoney) {
                touchFile('weixin','比对金额失败');   //比对金额不相等
                exit;
            }

            //修改悬赏订单支付状态
            $db = M('article_reward_order');
            $db->where(['record_sn' => $record_sn])->setField('status', 1);
            M('resource')->where(['id' => $resourceId])->setField('status', 1);
        }

        elseif($tableName == 'answer_reward_order')
        {
            //打赏
            //修改订单支付状态,打赏金额本就是用户自己随意输入的,所以金额不在下单那里入库,在支付成功后入库
            $answerData = ['status' => 1, 'money' => $totalFee];
            M('answer_reward_order')->where(array('record_sn' => $record_sn))->save($answerData);

            //给该条回复增加金额
            $answerOrder = M('answer_reward_order')->where(array('record_sn' => $orderData['record_sn']))->field('comment_id,from_uid,to_uid')->find();
            $resourceId = $answerOrder['comment_id'];
            $fromUid = $answerOrder['from_uid'];
            $tradeType = 2;
            $type = 5;

            M('resource_comment')->where(array('id' => $answerOrder['comment_id']))->setInc('user_reward', $totalFee);

            //获得打赏用户钱包增加金额
            D('Wallet')->updateWalletMoney($answerOrder['to_uid'], $totalFee, 1);
        }


        //类型为IM红包
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
            $fromUid = M('Recharge_log')->where(array('record_sn'=>$record_sn))->getField('uid');
            $tradeType = 1;
            $type = 1;
            //插入充值记录日志
            M('Recharge_log')->where(array('record_sn'=>$record_sn))->setField('status',1);
            //增加余额
            D('Wallet')->updateWalletMoney($fromUid,$totalFee,1);
            $time = $_SERVER["REQUEST_TIME"];
            $userBalance = M('wallet')->where(array('uid' => $fromUid))->getField('money');//用户余额
            D('WalletLog')->addWalletRecord($record_sn, $fromUid, $totalFee, $userBalance+$totalFee, '充值', $time, 1);
        }

        elseif($tableName == 'train')
        {
            //更新订单状态为已支付
            //视频订单表,order关联表
            M('Order_train')->where(['record_sn'=>$record_sn])->setField('status',1);
            $order = A('order')->getOrder('video', $record_sn);
            $resourceId = $order['video_id'];
            $fromUid = $order['user_id'];
            $tradeType = 2;
            $type = 6;
        }

        elseif($tableName == 'live')
        {
            //更新订单状态为已支付
            M('Order_live')->where(['record_sn'=>$record_sn])->setField('status',1);
            $order = A('order')->getOrder('live', $record_sn);
            $resourceId = $order['live_id'];
            $fromUid = $order['user_id'];
            $tradeType = 2;
            $type = 7;
        }

        elseif($tableName == 'activity')
        {
            //更新订单状态为已支付
            M('Order_activity')->where(['record_sn'=>$record_sn])->setField('status',1);
            $order = A('order')->getOrder('activity', $record_sn);
            $resourceId = $order['activity_id'];
            $fromUid = $order['user_id'];
            $tradeType = 2;
            $type = 8;
        }

        M('Order')->where(['record_sn'=>$record_sn])->setField('status',1);
        //插入支出记录日志
        D('Pay')->addPayLog($fromUid, $totalFee, 2, $tradeType, $resourceId,1);
        //插入交易记录
        D('RecordLog')->addRecordLog($record_sn, $fromUid, $totalFee, $tradeType, $resourceId, $type, 1, 1, $arr['time_end']);

        //向微信返回结果
        return '<xml><return_code><![CDATA[SUCCESS]]></return_code><return_msg><![CDATA[OK]]></return_msg></xml>';
    }

    /**查询订单状态
     * @param  string $out_trade_no 订单号
     * @return xml 订单查询结果
     */
    public function queryOrder($out_trade_no)
    {
        $AesMct = new MCrypt();
        $transaction_id = $AesMct->decrypt(urldecode(I('post.transaction_id')));
        $nonce_str = randString(32);
        $data = array(
            'appid'          => C('wxpay_config')['APPID'],
            'mch_id'         => C('wxpay_config')['MCHID'],
            'out_trade_no'   => $out_trade_no,
            'transaction_id' => $transaction_id,
            'nonce_str'      => $nonce_str
        );
        $wxPay = new WxPay();
        $sign = $wxPay->getSign($data);
        $xml_tpl = '<xml><appid>%s</appid><mch_id>%s</mch_id><nonce_str>%s</nonce_str><out_trade_no>%s</out_trade_no><sign>%s</sign></xml>';
        $xml_data = sprintf($xml_tpl, $data['appid'], $data['mch_id'], $nonce_str, $out_trade_no, $sign);
        $url = "https://api.mch.weixin.qq.com/pay/orderquery";
        $response = postXmlCurl($xml_data, $url);
        $response = $wxPay->xml_to_array($response);

        if($response['return_code'] == 'SUCCESS') {
            if($data['result_code'] == 'FAIL') {

                touchFile('weixin',json_encode($response));

                apiReturn('1021', AJAX_FALSE);
            }

            apiReturn('1024', AJAX_TRUE, $response);

        } else {

            touchFile('weixin',json_encode($response));

            apiReturn('1021', AJAX_FALSE);
        }
    }
}