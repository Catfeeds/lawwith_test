<?php
/**
 * Created by PhpStorm.
 * User: Mrx
 * Date: 2016/5/20
 * Time: 14:36
 */

return array(

    /* 图片上传相关配置 */
    'PICTURE_UPLOAD' => array(
        'mimes'    => '', //允许上传的文件MiMe类型
        'maxSize'  => 2*1024*1024, //上传的文件大小限制 (0-不做限制)
        'exts'     => 'jpg,gif,png,jpeg', //允许上传的文件后缀
        'autoSub'  => true, //自动子目录保存文件
        'subName'  => array('date', 'Y-m-d'), //子目录创建方式，[0]-函数名，[1]-参数，多个参数使用数组
        'rootPath' => './', //保存根路径
        'savePath' => '', //保存路径
        'saveName' => array('uniqid', ''), //上传文件命名规则，[0]-函数名，[1]-参数，多个参数使用数组
        'saveExt'  => '', //文件保存后缀，空则使用原后缀
        'replace'  => false, //存在同名是否覆盖
        'hash'     => true, //是否生成hash编码
        'callback' => false, //检测文件是否存在回调函数，如果存在返回文件信息数组
    ), //图片上传相关配置（文件上传类配置）

    'PICTURE_UPLOAD_DRIVER'=>'Aliyun',


    //阿里云OSS配置
    'UPLOAD_SITEIMG_OSS' => array (
        'AccessKeyId' => 'meQ2SgsT9cDeZv68',    //AccessKeyId   meQ2SgsT9cDeZv68
        'AccessKeySecret' => 'gF4a1p1ivl8WE76v0WSLaRa6hoM9mP',//AccessKeySecret     gF4a1p1ivl8WE76v0WSLaRa6hoM9mP
        'domain' => 'http://img.lawyerscloud.cn',        //
        'Bucket' => 'lx2016',         //Bucket
        'Endpoint' => 'http://oss-cn-beijing.aliyuncs.com',
    ),


    'ALIOSS_CONFIG'          => array(
        'KEY_ID'             => 'meQ2SgsT9cDeZv68', // 阿里云oss key_id
        'KEY_SECRET'         => 'gF4a1p1ivl8WE76v0WSLaRa6hoM9mP', // 阿里云oss key_secret
        'END_POINT'          => 'http://oss-cn-beijing.aliyuncs.com', // 阿里云oss endpoint
        'Domain' => 'http://img.lawyerscloud.cn',        //OSS空间路径
        'BUCKET'             => 'lx2016'  // bucken 名称
    ),
    /** 七牛云储存 **/
    //    'UPLOAD_SITEIMG_QINIU' => array(
    //    'maxSize' => 30 * 1024 * 1024, //文件大小
    //    'exts' =>  array('mp4'),
    //    'rootPath' => './',
    //    'saveName' => array('uniqid', 'lx'),
    //    'driver' => 'Qiniu',
    //    'driverConfig' => array(
    //    'secretKey' => 'dT1eWtVpr9rRBs4p3Ju1HEiE4xpaom14cbE8Kiqj',//SK
    //    'accessKey' => 'gPUmgWg25Z0dvR77psVUZUVJcciRfBYCm4nsnPOS', //AK
    //    'domain' => '7xso75.com2.z0.glb.qiniucdn.com', //空间名称.qiniudn.com
    //    'bucket' => 'junhe',//空间名称
    //  )
    //  )
);