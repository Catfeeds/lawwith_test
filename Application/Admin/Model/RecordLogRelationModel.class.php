<?php
/**
 * Created by PhpStorm.
 * User: fangbiao
 * Date: 2016/10/27 0027
 * Time: 14:42
 */

namespace Admin\Model;

use Think\Model\RelationModel;

class RecordLogRelationModel extends RelationModel
{
    protected $tableName = 'record_log';
    protected $_link = array(
        // 红包来源帖子
        'resources' => array(
            'mapping_type'   => self::BELONGS_TO,
            'class_name'     => 'Resource',
            'foreign_key'    => 'trade_rource',
            'mapping_name'   => 'resources',
            'mapping_fields' => 'title',
        ),

        // 用户信息
        'user'     => array(
            'mapping_type'   => self::BELONGS_TO,
            'class_name'     => 'Account',
            'foreign_key'    => 'user_id',
            'mapping_name'   => 'user',
            'mapping_fields' => 'uname',
        ),

    );
}