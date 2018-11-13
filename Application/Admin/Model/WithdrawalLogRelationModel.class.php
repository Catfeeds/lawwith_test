<?php
/**
 * Created by PhpStorm.
 * User: fangbiao
 * Date: 2016/10/27 0027
 * Time: 14:42
 */

namespace Admin\Model;
use Think\Model\RelationModel;

class WithdrawalLogRelationModel extends RelationModel
{
    protected $tableName = 'withdrawal_log';
    protected $_link = array(
        // 红包来源帖子
        'resources' => array(
            'mapping_type' => self::BELONGS_TO,
            'class_name' => 'Resource',
            'foreign_key' => 'res_id',
            'mapping_name' => 'resources',
            'mapping_fields' => 'title',
        ),

        // 用户信息
        'userinfo'  =>array(
            'mapping_type'  =>  self::BELONGS_TO,
            'class_name' => 'Account',
            'foreign_key' => 'get_user',
            'mapping_name' => 'userinfo',
            'mapping_fields' => 'uname,id_card,mobile,withdraw_account,position,town',
        ),

    );
}