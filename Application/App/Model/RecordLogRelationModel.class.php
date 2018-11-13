<?php
namespace App\Model;
use Think\Model\RelationModel;

class RecordLogRelationModel extends RelationModel
{
    protected $tableName = 'record_log';
    protected $_link = array(
        // 红包来源帖子
        'trade_title' => array(
            'mapping_type' => self::BELONGS_TO,
            'class_name' => 'Resource',
            'foreign_key' => 'trade_rource',
            'mapping_name' => 'trade_title',
            'mapping_fields' => 'title',
        )
    );
}