<?php

namespace App\Model;


use Think\Model\RelationModel;

class ResourceCommentModel extends RelationModel
{
    //定义关联关系
    public $_link = array(

        //用户信息
        'user_info'    => array(
            'mapping_type'  => self::BELONGS_TO,
            'class_name'    => 'account',
            'foreign_key'   => 'uid',
            'mapping_fields'=> 'id,uname,mobile,icon,type,status',
            'mapping_name'  => 'user_info',
        ),

        //当前用户点赞
        'user_like'    => array(
            'mapping_type'  => self::HAS_ONE,
            'class_name'    => 'comm_like',
            'foreign_key'   => 'comm_id',
            'mapping_name'  => 'user_like',
            'mapping_fields'=> 'count(*) as counts'
        ),

        //当前用户点踩
        'user_dislike' => array(
            'mapping_type'  => self::HAS_ONE,
            'class_name'    => 'comm_dislike',
            'foreign_key'   => 'comm_id',
            'mapping_name'  => 'user_dislike',
            'mapping_fields'=> 'count(*) as counts',
        ),

	    //回复列表每条回复的下级回复数量
	    'comments'     => array(
		    'mapping_type'  => self::HAS_ONE,
		    'class_name'    => 'resource_comment',
		    'foreign_key'   => 'pid',
		    'mapping_name'  => 'comments',
		    'mapping_fields'=> 'count(*) as counts',
	    ),
    );
}