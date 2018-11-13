<?php
/**
 * Created by PhpStorm.
 * User: Mrx
 * Date: 2016/5/21
 * Time: 18:09
 */

/**
 *返回接口数据格式规范
 * @param $code 状态码
 * @param $msg 状态信息
 * @param $data 返回数据
 *@author xue
 */

//定义常量
const AJAX_TRUE = 'success';
const AJAX_FALSE = 'error';
function apiReturn ($code='', $msg=self::AJAX_TRUE, $data='')
{
    header("Content-Type:text/html;charset=utf-8");
    $return['code'] = $code;
    $return['msg'] = $msg;
    $return['data'] = $data;
    //if(!empty($data)){
    //    $return['data'] = $data;
    //}
    echo json_encode($return);
    exit;
}
