<?php
/**
 * Created by PhpStorm.
 * User: Mrx
 * Date: 2016/5/3
 * Time: 10:51
 */

namespace Admin\Model;


use Think\Model\RelationModel;

class ActivityRelationModel extends RelationModel
{
    //定义主表的名称
    protected $tableName = 'activity';

    //定义关联关系
    protected $_link = array(

        //回复数
        'commt_sums' => array(
            'mapping_type' => self::HAS_ONE,
            'class_name' => 'activity_comment',
            'foreign_key' => 'aid',
            'mapping_fields' => 'count(*) as counts',
            'mapping_name' => 'commt_sums'
        ),

        //回复信息
        'commt_info' => array(
            'mapping_type' => self::HAS_MANY,
            'class_name' => 'activity_comment',
            'foreign_key' => 'aid',
            'mapping_fields' => 'id,uid, content as comm_content,likes,dislikes,time',
            'mapping_name' => 'commt_info',
        ),

        //收藏数
        'favorite_sums' => array(
            'mapping_type' => self::HAS_ONE,
            'class_name' => 'activity_favorite',
            'foreign_key' => 'aid',
            'mapping_fields' => 'count(*) as counts',
            'mapping_name' => 'favorite_sums'
        ),

        //收藏信息
        'favorite_info' => array(
            'mapping_type' => self::HAS_MANY,
            'class_name' => 'activity_favorite',
            'foreign_key' => 'aid',
            'mapping_fields' => 'uid,time',
            'mapping_name' => 'favorite_info'
        ),

        //参加人数
        'part_sums' => array(
            'mapping_type' => self::HAS_ONE,
            'class_name' => 'activity_part',
            'foreign_key' => 'aid',
            'mapping_fields' => 'count(*) as counts',
            'mapping_name' => 'part_sums'
        ),

        //参加人信息
        'part_info' => array(
            'mapping_type' => self::HAS_MANY,
            'class_name' => 'activity_part',
            'foreign_key' => 'aid',
            'mapping_fields' => 'uid,time',
            'mapping_name' => 'part_info'
        ),

        //发布者信息
        'author_info' => array(
            'mapping_type' => self::BELONGS_TO,
            'class_name' => 'account',
            'foreign_key' => 'author',
            'mapping_name' => 'author_info',
            'mapping_fields' => 'id,uname,mobile,icon,type,status,company'
        ),
    );
}