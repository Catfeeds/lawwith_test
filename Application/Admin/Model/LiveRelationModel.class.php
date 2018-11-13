<?php 
namespace Admin\Model;

use Think\Model\RelationModel;

/**
* 
*/
class LiveRelationModel extends RelationModel
{
    protected $tableName = 'live';
    protected $_link = array(
        'userName' => array(
            'mapping_type' => self::HAS_ONE,
            'class_name' => 'account',
            'foreign_key' => 'uid',
            'mapping_fields' => 'id,uname,mobile,icon,company,position',
            'mapping_name' => 'userName'
            ),
    );
}
