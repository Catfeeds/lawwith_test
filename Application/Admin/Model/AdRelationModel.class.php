<?php
/**
 * Created by PhpStorm.
 * User: Mrx
 * Date: 2016/5/20
 * Time: 19:47
 */

namespace Admin\Model;
use Think\Model\RelationModel;

class AdRelationModel extends RelationModel
{
    //定义主表的名称
    protected $tableName = 'ad';
    //定义关联关系
    protected $_link = array(
        //收藏数
        'favorite_sums' => array(
            'mapping_type' => self::HAS_ONE,
            'class_name' => 'ad_favorites',
            'foreign_key' => 'aid',
            'mapping_fields' => 'count(*) as counts',
            'mapping_name' => 'favorite_sums'
        ),

        //收藏信息
        'favorite_info' => array(
            'mapping_type' => self::HAS_MANY,
            'class_name' => 'ad_favorites',
            'foreign_key' => 'aid',
            'mapping_fields' => 'uid,time',
            'mapping_name' => 'favorite_info'
        ),
    );
}