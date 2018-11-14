<?php
/**
 * Created by PhpStorm.
 * User: qinshidong
 * Date: 2018/11/14
 * Time: 13:56
 */

namespace App\Model;
use Think\Model\RelationModel;

class EvaluateRelationModel extends RelationModel{
    //定义主表的名称
    protected $tableName='evaluate';

    //定义从联关系
    protected $_link = array(
        //发布者信息
        'author_info' => array(
            'mapping_type' => self::BELONGS_TO,
            'class_name' => 'account',
            'foreign_key' => 'customer',
            'mapping_name' => 'author_info',
            'mapping_fields' => 'id,icon'
        ),
    );
}