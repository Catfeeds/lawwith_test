<?php
///**
// * Created by PhpStorm.
// * User: qinshidong
// * Date: 2018/11/13
// * Time: 14:18
// */
//namespace Admin\Model;
//use Think\Model\RelationModel;
//
//class AccountResouceRelationModel extends RelationModel
//{
//    //定义主表的名称
//    protected $tableName = 'account';
//
//    //定义关联关系
//    protected $_link = array(
//
//        //所属律所
//        'law_info' => array(
//            'mapping_type' => self::BELONGS_TO,
//            'class_name'   => 'laws',
//            'foreign_key' => 'law',
//            'mapping_fields' => 'law_name,uadmin',
//            'mapping_name'  => 'law_info',
//            'condition' =>  'lx_laws.status=1'
////        ),
////        //回复过的求助标题信息
////        'resourceInfo' => array(
////            'mapping_type' => self::HAS_MANY,
////            'class_name' => 'resource_comment',
////            'foreign_key' => 'uid',
////            'mapping_fields' => 'id,rid',
////            'mapping_order' => 'time desc',
////            'mapping_name' => 'resourceInfo'
////    ),
//    );
//
//
//}
//
//
//
//
//
//
//
//
