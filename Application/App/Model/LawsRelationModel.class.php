<?php

namespace App\Model;
use Think\Model\RelationModel;

class LawsRelationModel extends RelationModel
{
    //定义主表的名称
    protected $tableName = 'laws';

    //定义关联关系
    protected $_link = array(

        //律师数
        'lawyer_sums' => array(
            'mapping_type' => self::HAS_ONE,
            'class_name' => 'account',
            'foreign_key' => 'law',
            'mapping_fields' => 'count(*) as counts',
            'mapping_name' => 'lawyer_sums',
            'condition'    => 'lx_account.type=1'
        ),

        //律师信息
        'user_info' => array(
            'mapping_type' => self::HAS_MANY,
            'class_name' => 'account',
            'foreign_key' => 'law',
            'mapping_fields' => 'id,uname,mobile,icon',
            'mapping_name' => 'user_info',
            'condition'    => 'lx_account.type=1'
        ),

        //管理员信息
        'admin_info' => array(
            'mapping_type'  => self::BELONGS_TO,
            'class_name'    =>  'account',
            'foreign_key'   =>  'uadmin',
            'mapping_name'  =>  'admin_info',
            'mapping_fields' => 'id,uname,mobile,icon',
            'condition'    => 'lx_account.type=1'
        )
    );
}