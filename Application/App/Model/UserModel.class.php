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

class UserModel extends Model
{
    //获取用户信息
    public function getUser($uid,$field='')
    {
        if(empty($field)){

            $data = M('Account')->where(array('id'=>$uid))->find();
        }else{

            $data = M('Account')->where(array('id'=>$uid))->field($field)->find();
        }
        return $data;
    }

    //更新用户信息
    public function updateUser($uid,$data)
    {

    }
}