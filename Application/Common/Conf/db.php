<?php
/**
 * Created by PhpStorm.
 * User: Mrx
 * Date: 2016/5/20
 * Time: 14:34
 */

//数据库配置文件
return array(
    /* 数据库配置 */
    'DB_TYPE'   => 'mysqli', // 数据库类型
    'DB_HOST'   => '120.27.42.182', // 服务器地址
    'DB_NAME'   => 'lawwith_test', // 数据库名
	'DB_USER'   => 'root', // 用户名
	'DB_PWD'    => 'ZfTx169@2018',  // 密码xue123、lingaltech512..  ZfTx169@2018
    'DB_PORT'   => '3306', // 端口
    'DB_PREFIX' => 'lx_', // 数据库表前缀

    'DB_PATH_NAME'=> 'db',        //备份目录名称,主要是为了创建备份目录；
    'DB_PATH'     => './db/',     //数据库备份路径必须以 / 结尾；
    'DB_PART'     => '20971520',  //该值用于限制压缩后的分卷最大长度。单位：B；建议设置20M
    'DB_COMPRESS' => '1',         //压缩备份文件需要PHP环境支持gzopen,gzwrite函数        0:不压缩 1:启用压缩
    'DB_LEVEL'    => '9',         //压缩级别   1:普通   4:一般   9:最高


//	/* 数据库配置 */
//	'DB_TYPE'   => 'mysqli', // 数据库类型
//	'DB_HOST'   => 'localhost', // 服务器地址
//	'DB_NAME'   => 'lawwith_test', // 数据库名
//	'DB_USER'   => 'root', // 用户名
//	'DB_PWD'    => 'root',  // 密码xue123、lingaltech512..  ZfTx169@2018
//	'DB_PORT'   => '3306', // 端口
//	'DB_PREFIX' => 'lx_', // 数据库表前缀
//
//	'DB_PATH_NAME'=> 'db',        //备份目录名称,主要是为了创建备份目录；
//	'DB_PATH'     => './db/',     //数据库备份路径必须以 / 结尾；
//	'DB_PART'     => '20971520',  //该值用于限制压缩后的分卷最大长度。单位：B；建议设置20M
//	'DB_COMPRESS' => '1',         //压缩备份文件需要PHP环境支持gzopen,gzwrite函数        0:不压缩 1:启用压缩
//	'DB_LEVEL'    => '9',         //压缩级别   1:普通   4:一般   9:最高
);
