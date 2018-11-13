<?php
namespace Admin\Model;
use Think\Model\RelationModel;

class RecommendRelationModel extends RelationModel
{
    //定义主表的名称
    protected $tableName = 'Recommend';

    //定义关联关系
    protected $_link = array(
        //发布者信息
        'author_info' => array(
            'mapping_type' => self::BELONGS_TO,
            'class_name' => 'account',
            'foreign_key' => 'author',
            'mapping_name' => 'author_info',
            'mapping_fields' => 'id,uname,mobile,icon,type,status,law,company,position,school'
        ),

        //回复数
        //'comment_sums' => array(
        //    'mapping_type' => self::HAS_ONE,
        //    'class_name' => 'resource_comment',
        //    'foreign_key' => 'rid',
        //    'mapping_fields' => 'count(*) as counts',
        //    'mapping_name' => 'comment_sums'
        //),
    );

}