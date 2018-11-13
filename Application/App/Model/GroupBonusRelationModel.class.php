<?php

namespace App\Model;

use Think\Model\RelationModel;

class GroupBonusRelationModel extends RelationModel
{
    //定义主表的名称
    protected $tableName = 'Group_bonus';

    //定义关联关系
    protected $_link = array(

        //所属律所
        'bonus' => array(
            'mapping_type' => self::HAS_MANY,
            'class_name'   => 'Group_bonus_result',
            'foreign_key' => 'bonus_id',
            'mapping_fields' => 'bonus_id,group_id,redpack_type,from_uid,to_uid,money,best_redpack,create_date',
            'mapping_name'  => 'bonus',
        ),
    );
}