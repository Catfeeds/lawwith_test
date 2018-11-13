<?php
/**
 * Created by PhpStorm.
 * User: Mrx
 * Date: 2016/4/27
 * Time: 11:27
 */

namespace Admin\Model;
use Think\Model\RelationModel;

class NewsRelationModel extends RelationModel
{
    //定义主表的名称
    protected $tableName = 'resource';

    //定义关联关系
    protected $_link = array(
        //回复数
        'comment_sums' => array(
            'mapping_type' => self::HAS_ONE,
            'class_name' => 'resource_comment',
            'foreign_key' => 'rid',
            'mapping_fields' => 'count(*) as counts',
            'mapping_name' => 'comment_sums'
        ),

        //发布者信息
        'author_info' => array(
            'mapping_type' => self::BELONGS_TO,
            'class_name' => 'account',
            'foreign_key' => 'author',
            'mapping_name' => 'author_info',
            'mapping_fields' => 'id,uname,mobile,icon,law,company,position,school,type,status'
        ),
    );

}