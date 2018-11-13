<?php
/**
 * Created by PhpStorm.
 * User: Mrx
 * Date: 2016/3/11
 * Time: 14:29
 */

return array(
    /* 模板相关配置 */
    'TMPL_PARSE_STRING'    => array(
        '__STATIC__' => __ROOT__ . '/Public/static',
        '__ADDONS__' => __ROOT__ . '/Public/' . MODULE_NAME . '/Addons',
        '__IMG__'    => __ROOT__ . '/Public/' . MODULE_NAME . '/images',
        '__CSS__'    => __ROOT__ . '/Public/' . MODULE_NAME . '/css',
        '__JS__'     => __ROOT__ . '/Public/' . MODULE_NAME . '/js',
        '__PUT__'    => __ROOT__ . '/Public/UpdateImg',
        '__VIDEO__'  => __ROOT__ . '/Public/Player',
        '__Date__'   => __ROOT__ . '/Public/Date_tool',
        '__Upload__' => __ROOT__ . '/Public/Jquery_Upload',
        '__upIMG__'  => __ROOT__ . '/Public/zyUpload',
        '__BUI__'    => __ROOT__ . '/Public/bui',
        '__DIST__'   => __ROOT__ . '/Public/dist',
    ),

    //超级管理员
    'RBAC_SUPERADMIN'      => 'admin',    //超级管理名称
    'ADMIN_AUTH_KEY'       => 'superadmin',    //超级管理识别
    'USER_AUTH_ON'         => true,        //开启验证
    'USER_AUTH_GATEWAY'    => 'Admin/Login/login', //默认网关
    'USER_AUTH_TYPE'       => 1,        //验证类型(1登录验证,2实时验证)
    'USER_AUTH_KEY'        => 'uid',        //用户认证识别号
    'NOT_AUTH_MODULE'      => 'Login,Index',            //不验证的控制器
    'NOT_AUTH_ACTION'      => 'getRegion,checkMajor',//不验证的action
    'RBAC_ROLE_TABLE'      => 'lx_role',    //角色表名称
    'RBAC_USER_TABLE'      => 'lx_role_user',    //用户关联表
    'RBAC_ACCESS_TABLE'    => 'lx_access', //权限表
    'RBAC_NODE_TABLE'      => 'lx_node',    //节点表

    /* Cookie设置 */
    'COOKIE_EXPIRE'        => 3600 * 6,    // Coodie有效期

    /* 后台错误页面模板 */
    'TMPL_ACTION_ERROR'    => MODULE_PATH . 'View/Public/error.html', // 默认错误跳转对应的模板文件
    'TMPL_ACTION_SUCCESS'  => MODULE_PATH . 'View/Public/success.html', // 默认成功跳转对应的模板文件
    // 'TMPL_EXCEPTION_FILE'   =>  MODULE_PATH.'View/Public/exception.html',// 异常页面的模板文件 

    //利用七牛云私有空间存储文件
    'UPLOAD_SITEIMG_QINIU' => array(
        'maxSize'      => 5 * 1024 * 1024,//文件大小
        'rootPath'     => '',
        'saveName'     => array('uniqid', ''),
        'driver'       => 'Qiniu',
        'driverConfig' => array(
            'secretKey' => 'gPUmgWg25Z0dvR77psVUZUVJcciRfBYCm4nsnPOS',
            'accessKey' => 'dT1eWtVpr9rRBs4p3Ju1HEiE4xpaom14cbE8Kiqj',
            'domain'    => 'http://7xso75.com2.z0.glb.clouddn.com',
            'bucket'    => 'junhe',
        )
    ),

    // 配置邮件发送服务器
    'MAIL_SMTPAUTH'        => true, //启用smtp认证
    'MAIL_CHARSET'         => 'utf-8',//设置邮件编码
    'MAIL_ISHTML'          => true, // 是否HTML格式邮件
    'MAIL_SECURE'          => 'tls',

    // 调试模式
    'SHOW_ERROR_MSG'       => true,    // 显示错误信息
    
    // 显示页面Trace信息
    'SHOW_PAGE_TRACE'      => true,

    // 程序运行状态
    'SHOW_RUN_TIME'        => true, // 运行时间显示
    'SHOW_ADV_TIME'        => true, // 显示详细的运行时间
    'SHOW_DB_TIMES'        => true, // 显示数据库查询和写入次数
    'SHOW_CACHE_TIMES'     => true, // 显示缓存操作次数
    'SHOW_USE_MEM'         => true, // 显示内存开销
    'SHOW_LOAD_FILE'       => true, // 显示加载文件数
    'SHOW_FUN_TIMES'       => true, // 显示函数调用次数

);