<?php
//+----------------------------------------------------------------------
// | Copyright (c) 2015-2017 http://lawyerscloud.cn All rights reserved.
//+----------------------------------------------------------------------
// | Author: 慢悠悠的丑小鸭 <1597305236@qq.com>
//+----------------------------------------------------------------------

namespace App\Controller;

use Common\Api\MCrypt;
use Common\Api\ApiPage;
use Common\Api\letvCloud;
use Think\Controller;

class IndexController extends Controller
{

    // 求助列表
    public function helps_list()
    {
        $nowPage = I('post.nowpage');   //页码
        $num = I('post.num');   //每页条数
        $where = array(
            'status' => 1,
            'type'   => 2       //数据类别1圈子2求助
        );
        $order = array(
            'is_stick'  => 'asc',
            'send_time' => 'desc',
        );
        $field = 'id,title,content,author,tag_city,tag_major,imgs,views,send_time,
        tbd_id,inviters,type,status,is_nym,is_admin,is_money,red_status,redpack_id,is_reward,reward_money';
        //数据分页
        $config = array(
            'tablename' => 'Admin/ResourceRelation', // 表名
            'where'     => $where, // 查询条件
            'relation'  => true, // 关联条件
            'order'     => $order, // 排序
            'page'      => $nowPage,  // 页码，默认为首页
            'num'       => $num,  // 每页条数
            'field'     => $field
        );
        $page = new ApiPage($config);
        $data = $page->get(); //获取分页数据

        if($data['now_page'] == 0) {
            apiReturn('1019', AJAX_FALSE);   //获取数据失败
        } else {
            $get_dat = array();
            foreach($data as $k => $v) {
                foreach($v as $m => $n) {
                    $get_dat[ $k ][ $m ] = $n;
                    $get_dat[ $k ]['my_id'] = session('my_id');   //写入当前用户id
                    $get_dat[ $k ]['sums_page'] = $data['total_page'];    //总页数
                    if(!empty($data[ $k ]['author_info']['law'])) {
                        $condit['id'] = $data[ $k ]['author_info']['law'];
                        $condit['status'] = 1;
                        $law_name = M('Laws')->where($condit)->getField('law_name');
                        $get_dat[ $k ]['author_info']['law_name'] = $law_name;
                        (string)$get_dat[ $k ]['is_reward'];
                        (string)$get_dat[ $k ]['reward_money'];
                    }
                }
            }
            apiReturn('1020', AJAX_TRUE, $get_dat);   //获取数据成功
        }
    }


    // 旧的: 1.帖子 2.求助 3.活动 4.视频 5.律所 6.用户 7直播
    //新的: 首页banner推荐 推送类别1.求助 2.免费直播 3.免费视频 4.活动 5.文章 6.律所 7.律师 8.学者 9.法务
    public function index_banner()
    {
        $data = M('Push')->where('mark=1')->field('cid,banner,c_type,author')->order('sort, push_time desc')->select();
        if(!empty($data)) {
            foreach($data as $k => $v) {
                $data[ $k ]['my_id'] = session('my_id');
                if($data[ $k ]['c_type'] == 7) {
                    $info = M('Live')->where('id=' . $data[ $k ]['cid'])->field('roomid,rtmpPullUrl')->find();
                    $data[ $k ]['roomid'] = $info['roomid'];
                    $data[ $k ]['rtmpPullUrl'] = $info['rtmpPullUrl'];
                }
            }
            apiReturn('1020', AJAX_TRUE, $data);
        } else {
            apiReturn('1019', AJAX_FALSE);
        }
    }


    //专业数据
    public function getMajor()
    {
        $AesMcrypt = new MCrypt;    //调用AES加密接口
        $mid = $AesMcrypt->decrypt(urldecode(I('post.mid'))); //专业id集
        $my_tag = $AesMcrypt->decrypt(urldecode(I('podt.is_my'))); //是否获取自己关注的专业 1是
        $model = M('Major');
        if($my_tag == 1) {
            $accid = session('accid'); //用户accid
            $token = session('token'); //用户云信token
            $majors = M('Account')->where(array('mobile' => $accid, 'token' => $token))->field('majors');
            $arr = explode(',', $majors);
            $data[] = '';
            foreach($arr as $k => $id) {
                $data[ $k ] = $model->where('id=' . $id)->field('id, major_name, pid')->find();
            }
            apiReturn('1020', AJAX_TRUE, $data);  //获取数据成功
        } else {
            if(empty($mid)) //获取所有数据
            {
                $data = $model->where('status=1')->order('sort')->field('id, major_name, pid')->select();
                apiReturn('1020', AJAX_TRUE, $data);  //获取数据成功
            } else {
                //获取指定专业的数据
                $arr = explode(',', $mid);
                $data[] = '';
                foreach($arr as $k => $id) {
                    $data[ $k ] = $model->where('id=' . $id)->field('id, major_name, pid')->find();
                }
                apiReturn('1020', AJAX_TRUE, $data);  //获取数据成功
            }
        }
    }


    // 资讯精选列表
    public function news_recommend()
    {
        $nowPage = I('post.nowpage');   //页码
        $num = I('post.num');   //每页条数
        $where = array(
            'status'     => 1,
            'is_private' => 1,
        );
        $order = array(
            'stick_date' => 'desc',
            'push_date'  => 'desc',
        );
        //数据分页
        $config = array(
            'tablename' => 'Admin/RecommendRelation', // 表名
            'where'     => $where,      // 查询条件
            'relation'  => true,        // 关联条件
            'order'     => $order,      // 排序
            'page'      => $nowPage,    // 页码，默认为首页
            'num'       => $num,        // 每页条数
            'field'     => 'id,type,news_id,sort,title,thumb_img,train_id,video_img,video_duration,activity_id,
            star_time,end_time,consumption,activity_type,deadline,status,author,tag_major,is_admin,is_stick,is_money,
            push_date,stick_date,channel_id'
        );
        $page = new ApiPage($config);
        $data = $page->get(); //获取分页数据

        if($data['now_page'] == 0) {
            apiReturn('1019', AJAX_FALSE);   //获取数据失败
        } else {
            $get_dat = array();
            foreach($data as $k => $v) {
                foreach($v as $m => $n) {
                    $get_dat[ $k ][ $m ] = $n;
                    $get_dat[ $k ]['my_id'] = session('my_id');       //写入当前用户id
                    $get_dat[ $k ]['sums_page'] = $data['total_page'];      //总页数

                    //未登录
                    if(empty($data[ $k ]['my_id'])) {
                        $get_dat[ $k ]['my_id'] = '';
                    }

                    //律所名称为空
                    if(!empty($data[ $k ]['author_info']['law'])) {
                        $condit = array(
                            'id'     => $data[ $k ]['author_info']['law'],
                            'status' => 1,
                        );
                        $law_name = M('Laws')->where($condit)->getField('law_name');
                        $get_dat[ $k ]['author_info']['law_name'] = $law_name;
                    }

                    //正文内容为空
                    if(empty($data[ $k ]['content'])) {
                        $get_dat[ $k ]['content'] = '';
                    }

                    //专业领域为空
                    if(empty($data[ $k ]['tag_major'])) {
                        $get_dat[ $k ]['tag_major'] = '';
                    }

                    //红包信息
                    $article
                        = M('Resource')->where('id=' . $data[ $k ]['news_id'])->field('send_time,is_money,post_amount')->find();
                    $get_dat[ $k ]['is_money'] = $article['is_money'];
                    $get_dat[ $k ]['post_amount'] = $article['post_amount'];

                    //内容类别：1资讯 2视频 3活动 4直播
                    switch($get_dat[ $k ]['type']) {
                        case 1:
                            $get_dat[ $k ]['send_time'] = $article['send_time'];
                            $comment_sums
                                = M('resource_comment')->where('rid=' . $data[ $k ]['news_id'])->count();
                            $get_dat[ $k ]['comment_sums']['counts'] = $comment_sums;

                            $views = M('resource')->where('id=' . $data[ $k ]['news_id'])->getField('views');
                            $get_dat[ $k ]['views'] = $views;
                            break;
                        case 2:
                            $time = M('Train')->where('id=' . $data[ $k ]['train_id'])->getField('create_at');
                            $get_dat[ $k ]['send_time'] = $time;
                            $comment_sums
                                = M('train_comment')->where('vid=' . $data[ $k ]['train_id'])->count();
                            $get_dat[ $k ]['comment_sums']['counts'] = $comment_sums;

                            $views = M('train')->where('id=' . $data[ $k ]['train_id'])->getField('views');
                            $get_dat[ $k ]['views'] = $views;
                            break;
                        case 3:
                            $time = M('Activity')->where('id=' . $data[ $k ]['activity_id'])->getField('send_time');
                            $get_dat[ $k ]['send_time'] = $time;
                            $comment_sums
                                = M('activity_comment')->where('aid=' . $data[ $k ]['activity_id'])->count();
                            $get_dat[ $k ]['comment_sums']['counts'] = $comment_sums;

                            $views = M('activity')->where('id=' . $data[ $k ]['activity_id'])->getField('views');
                            $get_dat[ $k ]['views'] = $views;
                            break;
                        case 4:
                            $info = M('Live')->where('id=' . $data[ $k ]['channel_id'])->find();
                            $get_dat[ $k ]['roomid'] = $info['roomid'];
                            $get_dat[ $k ]['rtmpPullUrl'] = $info['rtmpPullUrl'];
                            break;
                    }
                }
            }
            unset($data);
            apiReturn('1020', AJAX_TRUE, $get_dat);   //获取数据成功
        }
    }


    /**
     * 内容精选列表
     * version V4.0
     *
     * @param int nowpage
     * @param int num
     */
    public function recommend()
    {
        $userId = I('get.user_id');
        $nowPage = I('get.nowpage');   //页码
        $num = I('get.num');   //每页条数
        $where = array(
            'status'     => 1,
            'is_private' => 1,
        );
        $order = array(
            'stick_date' => 'desc',
            'push_date'  => 'desc',
        );
        $config = array(
            'tablename' => 'Recommend',
            //'relation'  => true,
            'where'     => $where,
            'order'     => $order,
            'page'      => $nowPage,
            'num'       => $num,
            'field'     => 'object_id,type,title,thumb_img,channel_id,is_cost,amount'
        );
        $page = new ApiPage($config);
        $data = $page->get();
        if($data['now_page'] == 0) {
            apiReturn('203', AJAX_FALSE);
        } else {
            foreach($data as $k => $v) {
                foreach($v as $key => $value) {
                    $returnData[ $k ] = $v;
                    $returnData[ $k ]['pages'] = $data['total_page'];    //总页数

                    //活动
                    if($data[ $k ]['type'] == 3) {
                        $returnData[ $k ]['activity'] = M('Activity')
                            ->cache(true,60)
                            ->where(['id' => $data[ $k ]['object_id']])
                            ->field('id,title,star_time,end_time,number,joined_number,views,status')
                            ->find();
                        $commentCount = M('activity_comment')->where('aid=' . $data[ $k ]['object_id'])->count();
                        $returnData[ $k ]['activity']['comment_counts'] = $commentCount;
                    } else {
                        $returnData[ $k ]['activity'] = null;
                    }

                    //直播
                    if($data[ $k ]['type'] == 4) {
                        $live = M('Live')->where('id=' . $data[ $k ]['channel_id'])->find();
                        $returnData[ $k ]['roomid'] = $live['roomid'];
                        $returnData[ $k ]['live_status'] = $live['status'];
                        $returnData[ $k ]['rtmpPullUrl'] = $live['rtmpPullUrl'];
                    }else{
                        $returnData[ $k ]['roomid'] = '';
                        $returnData[ $k ]['live_status'] = '';
                        $returnData[ $k ]['rtmpPullUrl'] = '';
                    }

                    if($data[ $k ]['is_cost'] && !empty($userId)) {
                        switch($data[ $k ]['type']) {
                            case 1:
                                break;
                            case 2:
                                if(M('order_train')->where(['user_id'  => $userId,
                                                            'video_id' => $data[ $k ]['object_id'], 'status' => 1])
                                                   ->count() > 0
                                ) {
                                    $returnData[ $k ]['is_pay'] = true;
                                } else {
                                    $returnData[ $k ]['is_pay'] = false;
                                }
                                break;
                            case 3:
                                if(M('order_activity')
                                       ->where(['user_id' => $userId, 'activity_id' => $data[ $k ]['object_id'], 'status' => 1])
                                       ->count() > 0
                                ) {
                                    $returnData[ $k ]['is_pay'] = true;
                                } else {
                                    $returnData[ $k ]['is_pay'] = false;
                                }
                                break;
                            case 4:
                                if(M('order_live')->where(['user_id' => $userId, 'live_id' => $data[ $k ]['object_id'], 'status' => 1])
                                                  ->count() > 0
                                ) {
                                    $returnData[ $k ]['is_pay'] = true;
                                } else {
                                    $returnData[ $k ]['is_pay'] = false;
                                }
                                break;
                        }

                    }else{
                        $returnData[ $k ]['is_pay'] = false;
                    }
                }
            }
            unset($data);
            apiReturn(200, AJAX_TRUE, $returnData);
        }
    }


    /**
     * 资讯分类列表
     */
    public function news_list()
    {
        $nowPage = I('post.nowpage');   //页码
        $num = I('post.num');   //每页条数
        $sort = I('post.sort');   //分类
        $where = array(
            'status' => 1,      //开启
            'type'   => 3,      //资讯
            'sort'   => $sort,   //分类
        );
        $order = array(
            'stick_date' => 'desc',
            'send_time'  => 'desc',
        );
        //数据分页
        $config = array(
            'tablename' => 'Admin/NewsRelation', // 表名
            'where'     => $where,  // 查询条件
            'relation'  => true,    // 关联条件
            'order'     => $order,  // 排序
            'page'      => $nowPage, // 页码，默认为首页
            'num'       => $num,    // 每页条数
            'field'     => 'id,title,content,title_img,author,tag_major,sort,views,send_time,status,is_admin,is_money,post_amount'
        );
        $page = new ApiPage($config);
        $data = $page->get(); //获取分页数据

        if($data['now_page'] == 0) {
            apiReturn('1019', AJAX_FALSE);   //获取数据失败
        } else {
            $get_dat = array();
            foreach($data as $k => $v) {
                foreach($v as $m => $n) {
                    $get_dat[ $k ][ $m ] = $n;
                    $get_dat[ $k ]['my_id'] = session('my_id');   //写入当前用户id
                    $get_dat[ $k ]['sums_page'] = $data['total_page'];    //总页数

                    //未登录
                    if(empty($get_dat[ $k ]['my_id'])) {
                        $get_dat[ $k ]['my_id'] = '';
                    }

                    //律所名称为空
                    if(!empty($data[ $k ]['author_info']['law'])) {
                        $condit = array(
                            'id'     => $data[ $k ]['author_info']['law'],
                            'status' => 1
                        );
                        $law_name = M('Laws')->where($condit)->getField('law_name');
                        $get_dat[ $k ]['author_info']['law_name'] = $law_name;
                    }
                }
            }
            apiReturn('1020', AJAX_TRUE, $get_dat);   //获取数据成功
        }
    }


    //视频页顶部导航
    public function category()
    {
        $category = M('Train_cate')->where(['status' => '1'])->field('cate_id,cate_name,sort')->select();
        if(empty($category)) {
            apiReturn(200, AJAX_TRUE, '');
        } else {
            apiReturn(200, AJAX_TRUE, $category);
        }
    }
}