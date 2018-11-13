<?php
/**
 * Created by PhpStorm.
 * User: Mrx
 * Date: 2016/3/25
 * Time: 17:43
 */

/**
 *Memcache缓存的配置
 *@author xue
 */
return array(
    'TMPL_EXCEPTION_FILE'   =>  MODULE_PATH.'View/Public/exception1.html',// 异常页面的模板文件
    'TMPL_PARSE_STRING' => array(
     '__PUBLIC__'   =>  __ROOT__.'/Public/'.MODULE_NAME
    )
);