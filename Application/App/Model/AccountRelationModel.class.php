<?php

namespace App\Model;


use Think\Model\RelationModel;

class AccountRelationModel extends RelationModel
{
    //定义主表的名称
    protected $tableName = 'account';

    //定义关联关系
    protected $_link = array(

        //所属律所
        'law_info' => array(
            'mapping_type' => self::BELONGS_TO,
            'class_name'   => 'laws',
            'foreign_key' => 'law',
            'mapping_fields' => 'law_name,uadmin',
            'mapping_name'  => 'law_info',
            'condition' =>  'lx_laws.status=1'
        )
    );
}