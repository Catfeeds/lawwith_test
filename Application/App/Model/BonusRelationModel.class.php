<?php
/**
 * Created by PhpStorm.
 * User: fangbiao
 * Date: 2016/10/27 0027
 * Time: 14:42
 */

namespace App\Model;
use Think\Model\RelationModel;

class BonusRelationModel extends RelationModel
{
    protected $tableName = 'bonus_log';
    protected $_link = array(
        // 红包来源帖子
        'resources' => array(
            'mapping_type' => self::BELONGS_TO,
            'class_name' => 'Resource',
            'foreign_key' => 'res_id',
            'mapping_name' => 'resources',
            'mapping_fields' => 'title',
        )
    );
}