<?php
/**
 * Created by PhpStorm.
 * User: Mrx
 * Date: 2016/5/3
 * Time: 10:51
 */

namespace App\Model;


use Think\Model\RelationModel;

class ActivityRelationModel extends RelationModel
{
    //定义主表的名称
    protected $tableName = 'activity';

    //定义关联关系
    protected $_link = array(

        //回复数
        'commt_sums' => array(
            'mapping_type' => self::HAS_ONE,
            'class_name' => 'activity_comment',
            'foreign_key' => 'aid',
            'mapping_fields' => 'count(*) as counts',
            'mapping_name' => 'commt_sums'
        )
    );
}