<?php

namespace Admin\Controller;


class InviteController extends AdminController
{
    //新用户统计
    public function users() {
        $model = M('Invite');
        $count = $model->count();
        $Page  = new \Think\Page($count, 15);
        $show  = $Page->show();
        $users = $model
            ->order('create_time desc')
            ->limit($Page->firstRow . ',' . $Page->listRows)
            ->select();
        foreach($users as $k => $v) {
            $users[ $k ]['user_name'] = M('Account')->where('mobile=' . $v['mobile'])->getField('uname');
        }

        $this->assign('users', $users);
        $this->assign('page', $show);
        $this->assign('meta_title', '新用户统计');
        $this->display();
    }


    //用户邀请数统计
    public function invites() {
        $model  = M('Invite');
        $count  = $model->count();
        $Page   = new \Think\Page($count, 15);
        $show   = $Page->show();
        $invites = $model
            ->field("*,count(refer_id) as count")
            ->group('refer_id')
            ->order('create_time desc')
            ->limit($Page->firstRow . ',' . $Page->listRows)
            ->select();
        foreach($invites as $k => $v) {
            $invites[ $k ]['user_name'] = user_info($v['refer_id'], 'uname');
            $invites[ $k ]['mobile']    = user_info($v['refer_id'], 'mobile');
            $invites[ $k ]['actived_count']    = $model->where(["is_actived"=>"1","refer_id"=>$v['refer_id']])->count();
        }

        $this->assign('page', $show);
        $this->assign('lists', $invites);
        $this->assign('page', $show);
        $this->assign('meta_title', '用户邀请数');
        $this->display();
    }
}