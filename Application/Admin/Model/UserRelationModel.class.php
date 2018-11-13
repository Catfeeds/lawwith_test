<?php
/**
 * 用户与角色模型
 * Created by PhpStorm.
 * User: Mrx
 * Date: 2016/3/18
 * Time: 17:29
 */
namespace Admin\Model;
use Think\Model\RelationModel;

class UserRelationModel extends RelationModel
{
    //定义主表的名称
    protected $tableName = 'user';

    //定义关联关系
    protected $_link = array(
        'role' => array( //关联的表
            'mapping_type' => self::MANY_TO_MANY,	//多对多关系
            'foreign_key' => 'user_id',	//主表在中间表中的字段字称
            'relation_key' => 'role_id',	//副表在中间表中的名称
            'relation_table' => 'lx_role_user',	//中间表名称
            'mapping_fields' => 'id, name, remark',	//显示的字段
        ),
    );
}