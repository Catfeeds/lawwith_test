<?php
//+----------------------------------------------------------------------
// | Copyright (c) 2015-2017 http://lawyerscloud.cn All rights reserved.
//+----------------------------------------------------------------------
// | Author: 慢悠悠的丑小鸭 <1597305236@qq.com>
//+----------------------------------------------------------------------

namespace App\Controller;

use Think\Controller;
use Common\Api\MCrypt;
use Think\Model;
use Think\Exception;

class TestController extends Controller
{
    //public function _initialize()
    //{
    //    Vendor('Alipay.aop.AopClient');
    //    Vendor('Alipay.aop.request.AlipayTradeAppPayRequest');
    //}
    
    
    public function crypt()
    {
        $mcrypt = new MCrypt();
        echo 'accid:' . $mcrypt->encrypt(urlencode('13930188593')) . "<br>";
        echo 'token:' . $mcrypt->encrypt(urlencode('73202ecee646912f334e45dafcb80bd3')) . "<br>";
        echo 'to_uid:' . $mcrypt->encrypt(urlencode('3454')) . "<br/>";
        echo 'money:' . $mcrypt->encrypt(urlencode('3')) . "<br/>";
        echo 'nowPage:' . $mcrypt->encrypt(urlencode('1')) . "<br/>";
        echo 'num:' . $mcrypt->encrypt(urlencode('5')) . "<br/>";
        echo 'total_fee:' . $mcrypt->encrypt(urlencode('0.05')) . "<br/>";
        echo 'order_id:' . $mcrypt->encrypt(urlencode('40')) . "<br/>";
        echo 'body:' . $mcrypt->encrypt(urlencode('test')) . "<br/>";
        echo 'object_id' . $mcrypt->encrypt(urlencode(113)) . "<br/>";
        echo $mcrypt->encrypt(urlencode(3454)) . '<br/>';
        echo $mcrypt->encrypt(urlencode('2018081517372834547187')) . '<br/>';

        //echo $mcrypt->encrypt(urlencode('15098005754')) . '<br/>';
        //echo $mcrypt->encrypt('a1d12541ac082674129db3bd2a2befa9') . '<br/>';
    }


    public function test()
    {
        //$model = M();
        //$model->startTrans();
        //
        //$model1 = M('order_train');
        //$model2 = M('order');
        //$res1 = $model1->where(array('record_sn'=>'201710201652151410179602'))->setField('status','b');
        //$res2 = $model2->where(array('record_sn'=>'201710201652151410179602'))->setField('status',1);
        //
        //if($res1 && $res2){
        //    $model->commit();
        //}else{
        //    $model->rollback();
        //}

        A('order')->updateRelationOrder('2018081617085457955412');
    }
}