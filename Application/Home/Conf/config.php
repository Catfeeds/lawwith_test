<?php
/**
 * Created by PhpStorm.
 * User: Mrx
 * Date: 2016/5/21
 * Time: 10:36
 */

return array(
    'TMPL_EXCEPTION_FILE'   =>  MODULE_PATH.'View/Public/exception1.html',// 异常页面的模板文件
    /* 模板相关配置 */
    'TMPL_PARSE_STRING' => array(
        '__SHARE__'     => __ROOT__ . '/Public/Share',       //分享样式
        '__PUBLIC__'    => __ROOT__ . '/Public/' . MODULE_NAME,       //分享样式
        '__upIMG__'     => __ROOT__ . '/Public/zyUpload',
        '__ASTYLE__'    => __ROOT__ . '/Public/Admin',
        '__EDITOR__'    => __ROOT__ . '/Public/Ueditor',
        '__HOME__'      => __ROOT__ . '/Public/Home',
        '__IMG__'    => __ROOT__ . '/Public/Home/Chat/images',
        '__CSS__'    => __ROOT__ . '/Public/Home/Chat/css',
        '__JS__'     => __ROOT__ . '/Public/Home/Chat/js',
        '__Theme__'  => __ROOT__ . '/Public/Home/Chat',
    ),

    /* Session设置 */
    'SESSION_EXPIRE'         => 3600*6,    // Coodie有效期
);