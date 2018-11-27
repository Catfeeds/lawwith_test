<?php
//+----------------------------------------------------------------------
// | Copyright (c) 2015-2017 http://lawyerscloud.cn All rights reserved.
//+----------------------------------------------------------------------
// | Author: 慢悠悠的丑小鸭 <1597305236@qq.com>
//+----------------------------------------------------------------------

namespace App\Controller;

use Common\Api\ApiPage;
use Common\Api\MCrypt;
use Common\Api\JPush;

class PushMsgController extends BasicController
{
    //回答我
    public function replyTome()
    {
        $uid = session('my_id');
        $nowPage = I('post.nowpage');   //页码
        $num = I('post.num');   //每页显示数据
        $where = array(
            'author' => $uid,
            'type'   => 2,
            'status' => 1
        );
        $model = M('Resource');
        $count = $model->where($where)->count();
        $data = $model->where($where)->page($nowPage, $num)->field('id,title,send_time,status')->order('send_time desc')->select();
        $data_list = $data;
        foreach($data as $k => $v) {
            $map = array('rid' => $v['id'], 'status' => 0);
            $data_list[ $k ]['new'] = M('Resource_comment')->where($map)->count();
            $data_list[ $k ]['sums_page'] = intval(ceil($count / $num));   //数据总页数
        }
        if($nowPage == 0) {
            apiReturn('1019', AJAX_FALSE);   //获取数据失败
        } else {
            apiReturn('1020', AJAX_TRUE, $data_list);  //获取数据成功
        }
    }

    //求助我
    public function helpTome()
    {
        $uid = session('my_id');
        $nowPage = I('post.nowpage');
        $num = I('post.num');
        $where = array(
            '_string' => "FIND_IN_SET($uid, inviters)",
            'type'    => 2,
            '_logic'  => 'and'
        );
        $filed = 'id,title,content,author,imgs,send_time,tbd_id,is_nym,views,status,tag_major,reward_money';
        $order = array(
            'send_time' => 'desc'
        );
        //数据分页
        $config = array(
            'tablename' => 'Admin/ResourceRelation', // 表名
            'where'     => $where,    // 查询条件
            'relation'  => array('comment_sums','author_info'),      // 关联条件
            'field'     => $filed,
            'order'     => $order,
            'page'      => $nowPage,  // 页码，默认为首页
            'num'       => $num       // 每页条数
        );
        $page = new ApiPage($config);
        $data = $page->get(); //获取分页数据
        if($nowPage == 0) {
            apiReturn('1019', AJAX_FALSE);   //获取数据失败
        } else {
            $get_dat = array();
            foreach($data as $k => $v) {
                foreach($v as $m => $n) {
                    $get_dat[ $k ][ $m ] = $n;
                    $get_dat[ $k ]['my_id'] = session('my_id');   //写入当前用户id
                    $get_dat[ $k ]['sums_page'] = $data['total_page'];    //总页数

                    //if(!empty($data[$k]['author_info']['law'])){
                    //    $condit = array(
                    //        'id' => $data[$k]['author_info']['law'],
                    //        'status' => 1
                    //    );
                    //    $law_name = M('Laws')->where($condit)->getField('law_name');
                    //    $get_dat[$k]['author_info']['law_name'] = $law_name;
                    //}
                }
            }
            foreach($get_dat as &$val){
                $model = M('major');
                $arr = explode(',', $val['tag_major']);
                $where['id'] = array('in',$arr);
                $str = implode(',',$arr);
                $res =$model->where('id IN ('.$str.')')->field('major_name')->select();
                $val['major'] = $res;
            }
            apiReturn('1020', AJAX_TRUE, $get_dat);   //获取数据成功
        }
    }

    //评论我
    public function commTome()
    {
        $uid = session('my_id');
        $nowPage = I('post.nowpage');
        $num = I('post.num');
        $where = array(
            'author' => $uid,
            'type'   => 1,
            'status' => 1
        );
        $model = M('Resource');
        $count = $model->where($where)->count();
        $data = $model->where($where)->page($nowPage, $num)->field('id,title,send_time,status')->order('send_time desc')->select();
        $data_list = $data;
        foreach($data as $k => $v) {
            $map = array(
                'rid'    => $v['id'],
                'status' => 0
            );
            $data_list[ $k ]['new'] = M('Resource_comment')->where($map)->count();    //未读回复数
            $data_list[ $k ]['sums_page'] = intval(ceil($count / $num));   //数据总页数
        }
        if($nowPage == 0) {
            apiReturn('1019', AJAX_FALSE);   //获取数据失败
        } else {
            apiReturn('1020', AJAX_TRUE, $data_list);  //获取数据成功
        }
    }

    //响应我
    public function responseTome()
    {
        $uid = session('my_id');
        $nowPage = I('post.nowpage');
        $num = I('post.num');
        $where = array(
            'author' => $uid,
        );
        $filed = 'id,title,author,number,imgs,send_time,type,status';
        $order = array(
            'send_time' => 'desc'
        );
        //数据分页
        $config = array(
            'tablename' => 'Admin/ActivityRelation', // 表名
            'where'     => $where, // 查询条件
            'relation'  => 'part_sums', // 关联条件
            'field'     => $filed,
            'order'     => $order,
            'page'      => $nowPage,  // 页码，默认为首页
            'num'       => $num  // 每页条数
        );
        $page = new ApiPage($config);
        $data = $page->get(); //获取分页数据
        if($nowPage == 0) {
            apiReturn('1019', AJAX_FALSE);   //获取数据失败
        } else {
            $get_dat = array();
            foreach($data as $k => $v) {
                foreach($v as $m => $n) {
                    $get_dat[ $k ][ $m ] = $n;
                    $get_dat[ $k ]['my_id'] = session('my_id');   //写入当前用户id
                    $get_dat[ $k ]['sums_page'] = $data['total_page'];    //总页数
                }
            }
            apiReturn('1020', AJAX_TRUE, $get_dat);   //获取数据成功
        }
    }

    //响应我的人列表
    public function user_list()
    {
        $AesMct = new MCrypt;
        $aid = $AesMct->decrypt(urldecode(I('post.aid')));
        $nowPage = I('post.nowpage');
        $num = I('post.num');
        $model = M('activity_part');
        $count = $model->join('lx_account ON lx_activity_part.uid = lx_account.id')->where('aid=' . $aid)->count();
        $data = $model
            ->join('lx_account ON lx_activity_part.uid = lx_account.id')
            ->where('aid=' . $aid)
            ->order('time desc')
            ->field('uid,aid,time,uname,icon,type')
            ->order('send_time desc')
            ->page($nowPage, $num)
            ->select();
        foreach($data as $k => $v) {
            $data[ $k ]['sums_page'] = intval(ceil($count / $num));;   //数据总页数
        }
        if($nowPage == 0) {
            apiReturn('1019', AJAX_FALSE);
        } else {
            apiReturn('1020', AJAX_TRUE, $data);
        }
    }

    //用户系统消息列表
    public function sys_msg()
    {
        $uid = session('my_id');
        $nowPage = I('post.nowpage');
        $num = I('post.num');

        $field = 'id,title,content,send_time';
        $order = array(
            'send_time' => 'desc'
        );
        $msg_id = M('Message')->distinct(true)->where('uid=' . $uid)->getField('msgid', true);
        $where = array(
            'id' => array('in', $msg_id)
        );
        //数据分页
        $config = array(
            'tablename' => 'sys_messages', // 表名
            'where'     => $where,
            'field'     => $field,
            'order'     => $order,
            'page'      => $nowPage,  // 页码，默认为首页
            'num'       => $num       // 每页条数
        );
        $page = new ApiPage($config);
        $data = $page->get(); //获取分页数据
        $get_dat = array();
        foreach($data as $k => $v) {
            foreach($v as $n => $m) {
                $get_dat[ $k ][ $n ] = $m;
                $get_dat[ $k ]['sums_page'] = $data['total_page'];    //总页数
                $get_dat[ $k ]['title'] = mb_substr($get_dat[ $k ]['title'], 0, 9, 'UTF-8');
                $get_dat[ $k ]['content'] = mb_substr($get_dat[ $k ]['content'], 0, 50, 'UTF-8');
            }
        }

        if($nowPage == 0) {
            apiReturn('1019', AJAX_FALSE);
        } else {
            apiReturn('1020', AJAX_TRUE, $get_dat);
        }
    }

    //删除系统消息
    public function del_msg()
    {
        $uid = session('my_id');
        if(M('Message')->where('uid=' . $uid)->delete()) {
            apiReturn('1024', AJAX_TRUE);
        } else {
            apiReturn('1023', AJAX_FALSE);
        }
    }

    /**
     * 发送APP消息推送
     * @param string $platform
     * @param array  $alias
     * @param string $content
     * @param array  $m_type
     * @param array  $m_txt
     * @return object
     */
    public function sendPush($platform = 'all', $alias, $content, $m_type, $m_txt)
    {
        $Jpush = new Jpush();
        if(empty($m_type)) {
            $m_type = 'http';
        }
        if(empty($m_txt)) {
            $m_type = '';
        }

        return $Jpush->push($platform, $alias, $content, $m_type, $m_txt);
    }

    /**新增用户系统消息记录
     * @param $uid
     * @param $title
     * @param $content
     * @param $obj
     * @param $type
     * @param $time
     * @return mixed
     */
    public function addMessage($uid, $title, $content, $obj, $type, $time)
    {
        $data = [
            'title'     => $title,
            'content'   => $content,
            'push_obj'  => $obj,
            'type'      => $type,
            'send_time' => $time
        ];
        $mid = M('Sys_messages')->add($data);
        $messageData = array('msgid' => $mid, 'uid' => $uid);

        return M('Message')->add($messageData);
    }
}