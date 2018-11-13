<?php
/**
 * Created by PhpStorm.
 * User: Mrx
 * Date: 2016/5/4
 * Time: 19:05
 */

namespace Admin\Model;


use Think\Model\RelationModel;

class TrainRelationModel extends RelationModel
{
    //定义主表的名称
    protected $tableName = 'train';

    //定义关联关系
    protected $_link = array(
        //回复数
        'commt_sums' => array(
            'mapping_type' => self::HAS_ONE,
            'class_name' => 'train_comment',
            'foreign_key' => 'vid',
            'mapping_fields' => 'count(*) as counts',
            'mapping_name' => 'commt_sums'
        ),
         //视频分类信息
        'cate_name' => array(
            'mapping_type' => self::BELONGS_TO,
            'class_name' => 'train_cate',
            'foreign_key' => 'cate_id',
            'mapping_fields' => 'cate_name',
            'mapping_name' => 'cate_name'
        ),

        //回复信息
        'commt_info' => array(
            'mapping_type' => self::HAS_MANY,
            'class_name' => 'train_comment',
            'foreign_key' => 'vid',
            'mapping_fields' => 'id,uid, content as comm_content, ctime',
            'mapping_name' => 'commt_info'
        ),
        //收藏数
        'favorite_sums' => array(
            'mapping_type' => self::HAS_ONE,
            'class_name' => 'train_favorite',
            'foreign_key' => 'vid',
            'mapping_fields' => 'count(*) as counts',
            'mapping_name' => 'favorite_sums'
        ),

        //收藏信息
        'favorite_info' => array(
            'mapping_type' => self::HAS_MANY,
            'class_name' => 'train_favorite',
            'foreign_key' => 'vid',
            'mapping_fields' => 'uid,time',
            'mapping_name' => 'favorite_info'
        ),

        //主讲人介绍
        'speaker_user' => array(
            'mapping_type' => self::BELONGS_TO,
            'class_name' => 'account',
            'foreign_key' => 'speaker',
            'mapping_fields' => 'id,uname,icon,company,position',
            'mapping_name' => 'speaker_user'
        ),
    );
}