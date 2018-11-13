<?php
/**
 * Created by PhpStorm.
 * User: Mrx
 * Date: 2016/4/27
 * Time: 11:27
 */

namespace Admin\Model;
use Think\Model\RelationModel;

class ResourceRelationModel extends RelationModel
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

//        //回复信息
//        'comment_info' => array(
//            'mapping_type' => self::HAS_MANY,
//            'class_name' => 'resource_comment',
//            'foreign_key' => 'rid',
//            //'mapping_fields' => 'id,uid, content as comm_content,likes,dislikes,tbd,time,post_amount,amount',
//            'mapping_name' => 'comment_info',
//        ),
        //回复信息
        'comment_info' => array(
            'mapping_type' => self::THREE_TABLE,
            'foreign_key' => 'rid',
            'relation_foreign_key' => 'uid',
            'relation_table' => 'lx_resource_comment',
            'relation_tableB' => 'lx_account',
            'mapping_fields' => 'a.id,a.uid,a.content as comm_content,a.likes,a.dislikes,a.tbd,a.time,a.post_amount,a.amount',
            'mapping_fieldsB' => 'b.icon',
            'mapping_name' => 'comment_info'
        ),

        //收藏数
        'favorite_sums' => array(
            'mapping_type' => self::HAS_ONE,
            'class_name' => 'resource_favorite',
            'foreign_key' => 'rid',
            'mapping_fields' => 'count(*) as counts',
            'mapping_name' => 'favorite_sums'
        ),

        //收藏信息
        'favorite_info' => array(
            'mapping_type' => self::HAS_MANY,
            'class_name' => 'resource_favorite',
            'foreign_key' => 'rid',
            'mapping_fields' => 'uid,time',
            'mapping_order' => 'time desc',
            'mapping_name' => 'favorite_info'
        ),

        //发布者信息
        'author_info' => array(
            'mapping_type' => self::BELONGS_TO,
            'class_name' => 'account',
            'foreign_key' => 'author',
            'mapping_name' => 'author_info',
            'mapping_fields' => 'id,uname,mobile,icon,law,company,position,school,type,status'
        ),

        //红包信息
        'redpack_info' => array(
            'mapping_type' => self::HAS_MANY,
            'class_name' => 'bonus_log',       //关联的从表
            'foreign_key' => 'res_id',              //关联主表的字段，注意：这里不是从表的主键
            'mapping_name' => 'redpack_info',       //mapping_name的名字和调用时的名字保持一致
            'mapping_fields' => 'rd_id,amount,payment_time,pay_user,get_user,type,commission'         //要关联从表的某些字段
        ),

        //红包设置项的时间
        'redpacks_conf' => array(
            'mapping_type' => self::BELONGS_TO,
            'class_name' => 'bonus_conf',
            'foreign_key' => 'redpack_id',
            'mapping_name' => 'redpacks_conf',
            //'mapping_fields' => 'redpack_id,total_amount,like_nums,end_time,start_time',
        ),

    );

}