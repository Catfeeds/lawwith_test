<?php
//+----------------------------------------------------------------------
// | Copyright (c) 2015-2017 http://lawyerscloud.cn All rights reserved.
//+----------------------------------------------------------------------
// | Author: 慢悠悠的丑小鸭 <1597305236@qq.com>
//+----------------------------------------------------------------------

namespace App\Controller;

use Common\Api\MCrypt;
use Common\Api\letvCloud;
use Think\Controller;
use Common\Api\ApiPage;

class DetailController extends Controller
{
    //求助详情
    public function posts_back() {

        $AesMct = new MCrypt;
        $cid = $AesMct->decrypt(urldecode(I('post.cid')));  //帖子id
        $is_read = I('post.is_read');
//        $cid = I('post.cid');
        $uid = session('my_id');    //当前用户id
        $nowPage = I('post.nowpage'); //页码
        $num = I('post.num');    //每页显示条数
        $width = I('post.width');   //播放器宽度
        $height = I('post.height'); //播放器高度
        $imageSize = I('post.image_size'); //视频截图尺寸

        //获取求助帖子的id
        if(empty($cid)) apiReturn('1022',AJAX_FALSE,'缺少必要参数');

        //回复列表是否已读
        if ($is_read ==1) M('resource_comment')->where('rid='.$cid)->setField('status',1);

        //此贴是否已解决
        $is_help = M("resource_comment")->where(['rid' => $cid, 'tbd' => 1]).count();
        if($is_help > 0){
            $data['is_help'] = true; //已解决
        }else{
            $data['is_help'] = false; //未解决
        }
        //根据帖子的id去查询
        $model1 = D('ResourceRelation');
        $tempData = $model1->relation(true)
            ->where('id='.$cid)
            ->field('id,title,content,author,tag_major,imgs,views,send_time,tbd_id,type,status,is_nym,is_admin,is_money,red_status,redpack_id,author_amount,post_amount,likes,is_reward,reward_money')
            ->select();


        //回答列表
        $model = D('ResourceComment');
        $order = ['tbd' => 'desc', 'time' => 'desc'];
        $where = ['rid' => $cid, 'pid'=>0];
        $model->_link['user_like']['condition'] = "lx_comm_like.uid=$uid";  //查询当前用户是否点赞此回复的条件
        $model->_link['user_dislike']['condition'] = "lx_comm_dislike.uid=$uid";    //查询当前用户是否点踩此回复的条件
        $count = $model->relation(true)->where($where)->count();   //获取回复总数据条数
        $tempdata = $model->relation(true)->order($order)->where($where)->page($nowPage,$num)->select();
//        dump($tempdata);exit;
        $arr = array();
        foreach($tempdata as $k => $v){
            foreach($v as $m => $n){
                $arr[$k][$m] = $n;
                $arr[$k]['my_id'] = $uid;   //当前用户id
                $arr[$k]['sums_page'] = intval(ceil($count/$num));;   //数据总页数

                //回复是否为视频回复
                if($arr[$k]['type'] == 1){
                    $uu = "dwbppqvkxs"; //用户唯一标识码   dwbppqvkxs
                    $pu = "a2ee3b5de4"; //播放器唯一标识码  a2ee3b5de4
                    $type = 'url';  //接口类型
                    $auto_play = 0; //是否自动播放
                    $letv = new LetvCloud;
                    //获取视频
//                    $arr[$k]['content'] = 'http://yuntv.letv.com/bcloud.html?uu=dwbppqvkxs&pu=a2ee3b5de4&vu='.video_info($v['content'], 'video_unique').'&width='.$width.'&height='.$height;
                    //获取视频截图
                    $image = $letv->imageGet(video_info($v['content'], 'video_id'), $imageSize);
                    $tmp_image = json_decode($image,true);
                    $arr[$k]['image'] =$tmp_image['data']['img1'];
                    $arr[$k]['content'] = $letv->videoGetPlayinterface($uu, video_info($v['content'], 'video_unique'), $type, $pu, $auto_play, $width, $height);

                }
                //未登录状态，是否点过赞为0
                if (empty($uid)) {
                    $arr[$k]['user_like']['counts'] = "0";
                    $arr[$k]['user_dislike']['counts'] = "0";
                }
                //
                if(!$arr[$k]['author_reward']){
                    $arr[$k]['author_reward'] = '';
                }
                if(!$arr[$k]['user_reward']){
                    $arr[$k]['user_reward'] = '';
                }
            }
        }
//        dump($arr);exit;
        $data = array_merge($data,$tempData[0]);
        $data['reply_list'] = $arr;
        //当前用户是否点赞
        $data['my_id'] = session('my_id');
        $data['user_likes'] = M('Likes')->where(['rid'=>$cid,'uid'=>$data['my_id']])->count();

        //当前用户是否收藏
        $res = M('resource_favorite')->where(['rid' => $cid, 'uid' => $data['my_id']])->count();
        $res ? $data['is_favorite']=1 : $data['is_favorite']=2;

        //作者所在律所名称
        if(!empty($data['author_info']['law'])) {
            $law_name = M('Laws')->where(['id' => $data['author_info']['law'], 'status' => 1])->getField('law_name');
            $data['author_info']['law_name'] = $law_name;
        }

        //附件图片的路径遍历嵌入数组中
        $imgs_id = explode(',',$data['imgs']);
        foreach($imgs_id as $k => $img){
            $path = M('Picture')->where('id='.$img)->getField('path');
            $data['img_path'][$k] = $path;
        }

        //阅读量增加1
        M('Resource')->where('id='.$cid)->setInc('views');

        //标记回复已读
        if(!empty(session('my_id')) && $data['author'] == session('my_id')) {
            M('Resource_comment')->where(array('rid' => $cid))->setField('status',1);
        }

        $data['sums_page'] = intval(ceil($count/$num));;   //数据总页数

        if(empty($data)){
            apiReturn('1019',AJAX_FALSE);   //获取数据失败
        }else{
            apiReturn('1020',AJAX_TRUE,$data);  //获取数据成功
        }
    }

    //资讯详情
    public function news_detail() {
        $AesMct = new MCrypt;
        $cid = $AesMct->decrypt(urldecode(I('post.cid')));  //帖子id

        $model1 = D('Admin/NewsRelation');
        $data = $model1->relation(true)->where('id='.$cid)->field('id,title,content,author,tag_major,imgs,views,send_time,type,status,is_admin,is_money,post_amount,sort,likes,dislikes')->find();

        //获取数据失败返回false
        if(!is_array($data)) apiReturn('1019',AJAX_FALSE);

        //当前用户是否收藏
        $data['my_id'] = session('my_id');
        $res = M('resource_favorite')->where(['rid' => $cid, 'uid' => $data['my_id']])->count();
        $res ? $data['is_favorite']=1 : $data['is_favorite']=2;

        //当前用户是否已点赞
        $user_like = M('Likes')->where(array('rid'=>$cid, 'uid'=>$data['my_id']))->count();
        $user_like ? $data['user_like'] = 1 : $data['user_like'] = 0;

        //当前用户是否已点踩
        $user_dislike = M('dislikes')->where(['rid' => $cid, 'uid' => session('my_id'),])->count();
        $user_dislike ? $data['user_dislike'] = 1 : $data['user_dislike'] = 0;

        //作者所在律所名称
        if(!empty($data['author_info']['law'])){
            $condit = array('id' => $data['author_info']['law'], 'status' => 1);
            $law_name = M('Laws')->where($condit)->getField('law_name');
            $data['author_info']['law_name'] = $law_name;
        }

        //附件图片的路径遍历嵌入数组中
        $imgs_id = explode(',',$data['imgs']);
        foreach($imgs_id as $k => $img){
            $path = M('Picture')->where('id='.$img)->getField('path');
            $data['img_path'][$k] = $path;
        }

        //解析html标签
        $data['content'] = htmlspecialchars_decode($data['content']);

        //获取内容中图片url存入数组
        $images = array();
        preg_match_all('/src=\"(.*?(jpg|jpeg|gif|png))/',$data['content'],$result,PREG_PATTERN_ORDER);
        for ($i = 0; $i < count($result[0]); $i++) {
            $str = trim(substr($result[0][$i],5));
            array_push($images,$str);
        }
        $data['img_arr'] = $images;
        //正则替换所有img标签
        $data['content_ios'] = preg_replace('/<\s*img\s+[^>]*?src\s*=\s*(\'|\")(.*?)\\1[^>]*?\/?\s*>/i','[#UIIMAGEVIEW#]',$data['content']);

        //阅读量递增
        M('Resource')->where('id='.$cid)->setInc('views',1);

        apiReturn('1020',AJAX_TRUE,$data);  //获取数据成功
    }

    //帖子回复列表
    public function commte_posts() {
        $AesMct = new MCrypt;
        $rid = $AesMct->decrypt(urldecode(I('post.cid')));  //帖子id
        $nowPage = I('post.nowpage'); //页码
        $num = I('post.num');    //每页显示条数
        $uid = session('my_id');    //当前用户id

        $width = I('post.width');
        $height = I('post.height');

        $model = D('ResourceComment');
        $order = ['tbd' => 'desc', 'time' => 'desc'];

        if(!empty($pid)){
            $where = ['pid' => $pid];
        }else{
            $where = ['rid' => $rid];
        }

        $model->_link['user_like']['condition'] = "lx_comm_like.uid=$uid";  //查询当前用户是否点赞此回复的条件
        $model->_link['user_dislike']['condition'] = "lx_comm_dislike.uid=$uid";    //查询当前用户是否点踩此回复的条件
        $count = $model->relation(true)->where($where)->count();   //获取回复总数据条数
        $data = $model->relation(true)->order($order)->where($where)->page($nowPage,$num)->select();

        $arr = array();
        foreach($data as $k => $v){
            foreach($v as $m => $n){
                $arr[$k][$m] = $n;
                $arr[$k]['my_id'] = $uid;   //当前用户id
                $arr[$k]['sums_page'] = intval(ceil($count/$num));   //数据总页数

                //是否为视频回复
                if($arr[$k]['type'] == 1){
                    $uu = "dwbppqvkxs"; //用户唯一标识码   dwbppqvkxs
                    $pu = "a2ee3b5de4"; //播放器唯一标识码  a2ee3b5de4
                    $type = 'url';  //接口类型
                    $auto_play = 0; //是否自动播放
                    $letv = new LetvCloud;
                    //获取视频
                    $arr[$k]['content']
                        = $letv->videoGetPlayinterface($uu, video_info($v['content'], 'video_unique'), $type, $pu, $auto_play, $width, $height);
                    //获取视频截图
//                    $arr[$k]['image'] = $letv->imageGet(video_info($arr[$k]['content'], 'video_id'), $imageSize);
                }

                //未登录状态，是否点过赞为0
                if (empty($uid)) {
                    $arr[$k]['user_like']['counts'] = "0";
                    $arr[$k]['user_dislike']['counts'] = "0";
                }
            }
        }
        $data['sums_page'] = intval(ceil($count/$num));;   //数据总页数
        if($nowPage == 0){
            apiReturn('1019',AJAX_FALSE);   //获取数据失败
        }else{
            apiReturn('1020',AJAX_TRUE,$arr);  //获取数据成功
        }
    }

    //帖子回复列表v4
    public function comment_posts_v4() {
        $AesMct = new MCrypt;
        $uid = session('my_id');    //当前用户id
        $rid = $AesMct->decrypt(urldecode(I('post.cid')));  //帖子id
        $pid = $AesMct->decrypt(urldecode(I('post.pid')));  //父级回复id
        $nowPage = I('post.nowpage'); //页码
        $num = I('post.num');    //每页显示条数

        $width = I('post.width');   //播放器宽度
        $height = I('post.height'); //播放器高度
        $imageSize = I('post.image_size'); //视频截图尺寸

        $model = D('ResourceComment');
        $order = ['tbd' => 'desc', 'time' => 'desc'];

        if($pid){
            $where = ['pid' => $pid];
        }else{
            $where = ['rid' => $rid, 'pid'=>0];
        }

        $model->_link['user_like']['condition'] = "lx_comm_like.uid=$uid";  //查询当前用户是否点赞此回复的条件
        $model->_link['user_dislike']['condition'] = "lx_comm_dislike.uid=$uid";    //查询当前用户是否点踩此回复的条件
        $count = $model->relation(true)->where($where)->count();   //获取回复总数据条数
        $data = $model->relation(true)->order($order)->where($where)->page($nowPage,$num)->select();
        $arr = array();
        foreach($data as $k => $v){
            foreach($v as $m => $n){
                $arr[$k][$m] = $n;
                $arr[$k]['my_id'] = $uid;   //当前用户id
                $arr[$k]['sums_page'] = intval(ceil($count/$num));;   //数据总页数

                //回复是否为视频回复
                if($arr[$k]['type'] == 1){
                    $uu = "dwbppqvkxs"; //用户唯一标识码   dwbppqvkxs
                    $pu = "a2ee3b5de4"; //播放器唯一标识码  a2ee3b5de4
                    $type = 'url';  //接口类型
                    $auto_play = 0; //是否自动播放
                    $letv = new LetvCloud;
                    //获取视频
//                    $arr[$k]['content'] = 'http://yuntv.letv.com/bcloud.html?uu=dwbppqvkxs&pu=a2ee3b5de4&vu='.video_info($v['content'], 'video_unique').'&width='.$width.'&height='.$height;
                    //获取视频截图
                    $image = $letv->imageGet(video_info($v['content'], 'video_id'), $imageSize);
                    $tmp_image = json_decode($image,true);
                    $arr[$k]['image'] =$tmp_image['data']['img1'];
                    $arr[$k]['content'] = $letv->videoGetPlayinterface($uu, video_info($v['content'], 'video_unique'), $type, $pu, $auto_play, $width, $height);

                }

                //未登录状态，是否点过赞为0
                if (empty($uid)) {
                    $arr[$k]['user_like']['counts'] = "0";
                    $arr[$k]['user_dislike']['counts'] = "0";
                }

                //
                if(!$arr[$k]['author_reward']){
                    $arr[$k]['author_reward'] = '';
                }
                if(!$arr[$k]['user_reward']){
                    $arr[$k]['user_reward'] = '';
                }
            }
        }
        $data['sums_page'] = intval(ceil($count/$num));;   //数据总页数
        if($nowPage == 0){
            apiReturn('1019',AJAX_FALSE);   //获取数据失败
        }else{
            apiReturn('1020',AJAX_TRUE,$arr);  //获取数据成功
        }
    }


    /**
     * 获取回答详情
     *
     * return string
     */
    public function comment_detail()
    {
        $commentId = I('get.comment_id');
        //获取播放器宽和长
        $width = I('get.width');
        $height = I('get.height');
        $imageSize = I('get.image_size');
        if(empty($commentId)) apiReturn('403', AJAX_FALSE, '回答id不能为空');
        $comment = M('resource_comment')->where(array('id'=>$commentId))->find();
        if ($comment['type'] == 1){
            $uu = "dwbppqvkxs"; //用户唯一标识码   dwbppqvkxs
            $pu = "a2ee3b5de4"; //播放器唯一标识码  a2ee3b5de4
            $type = 'url';  //接口类型
            $auto_play = 0; //是否自动播放
            $letv = new LetvCloud;
//            $image = $letv->imageGet(video_info($comment['content'], 'video_id'), "300_300");
//            $tmp_image = json_decode($image,true);
//            $comment['image'] =$tmp_image['data']['img1'];
            //获取视频
            $comment['content']
                = $letv->videoGetPlayinterface($uu, video_info($comment['content'], 'video_unique'), $type, $pu, $auto_play, $width, $height);
        }

        apiReturn('200', AJAX_TRUE, $comment);
//        if(empty($commentId)) apiReturn('403', AJAX_FALSE, '回答id不能为空');
//        $comment = M('resource_comment')->where(array('id'=>$commentId))->select();
//
//        apiReturn('200', AJAX_TRUE, $comment);
    }

    //律所详情
    public function laws() {
        $AesMct = new MCrypt;
        $lid = $AesMct->decrypt(urldecode(I('post.lid')));  //律所id

        if(empty($lid)) apiReturn('1022',AJAX_FALSE,'缺少必要参数');

        $model = D('LawsRelation');
        $data = $model->relation(true)->where('id='.$lid)->field('id,law_name,profile,logo,bg_img,uadmin,province,city,town,address,phone,email')->find();
        $data['profile'] = strval($data['profile']);

        if($data){
            $data['my_id'] = session('my_id');
            apiReturn('1020',AJAX_TRUE,$data);
        }else {
            apiReturn('1019',AJAX_FALSE);
        }
    }

    //律所相册列表
    public function laws_album() {
        $AesMct = new MCrypt;
        $laws_id = $AesMct->decrypt(urldecode(I('post.laws_id')));      //律所id
        $data = M('Laws')->where('id='.$laws_id)->field('laws_album')->find();

        //附件图片的路径遍历嵌入数组中
        $imgs_id = explode(',',$data['laws_album']);
        foreach($imgs_id as $k => $img){
            $path = M('Picture')->where('id='.$img)->getField('path');
            $data['img_path'][$k] = $path;
        }
        //律所相册不为空
        if(!empty($data['laws_album'])) {
            apiReturn('1024',AJAX_TRUE,$data);
        } else {
            $data['laws_album'] = '';
            $data['img_path'] = array();
            apiReturn('1023',AJAX_TRUE,$data);   //相册为空
        }
    }

    //活动详情
    public function activity() {
        $AesMct = new MCrypt;
        $nowPage = I('post.nowpage'); //页码
        $num = I('post.num');    //每页显示条数
        $userId = I('post.user_id');
        $aid = $AesMct->decrypt(urldecode(I('post.aid')));  //活动id
        $activityCount = M('Activity_part')->where('aid='.$aid)->count();    //统计当前报名人数
        $activity = M('Activity')->where('id='.$aid)->field('star_time,end_time,number,deadline')->find();   //活动限制人数
        $dead = $activity['deadline'];     //活动截止时间
        if($activityCount > $activity['number'] || $activity['end_time'] <time() || $dead<time()){
            M('Activity')->where('id='.$aid)->setField('status',2); //活动已结束
        }
        $model = D('Admin/ActivityRelation');
        $data = $model->relation(true)->where('id='.$aid)->field('id,title,remark,address,number,price,star_time,end_time,sponsor,author,imgs,status,send_time,views,type,deadline,group,is_money,amount')->select();
        //读取回复信息列表
        $model2 = M('activity_comment');
        $count = $model2->join('lx_account ON lx_activity_comment.uid = lx_account.id')->where('aid='.$aid)->count();
        $data1 = $model2
            ->join('lx_account ON lx_activity_comment.uid = lx_account.id')
            ->where('aid='.$aid)
            ->order('time desc')
            ->field('lx_activity_comment.id as cid,uid,aid,content,likes,dislikes,time,is_nym,uname,icon,type')
            ->page($nowPage,$num)
            ->select();
        $data[0]['sums_page'] = intval(ceil($count/$num));   //数据总页数
        $data[0]['comm_list'] = $data1;    //嵌入回复信息列表

        if($data[0]['is_money']) {
            if(M('order_train')->where(['activity_id'=>$aid, 'user_id'=>$userId, 'status'=>1])->find()) {
                $data['is_pay'] = 1;
            }
        }
        //未登录状态时不检测当前用户是否收藏此帖子
        if(!empty(session('my_id'))) {
            $data[0]['my_id'] = session('my_id');  //当前用户id
            $map = array(
                'aid' => $aid,
                'uid' => session('my_id')
            );
            $res = M('activity_favorite')->where($map)->select();   //查询当前用户是否收藏此帖子
            $res ? $data[0]['is_favorite']=1 : $data[0]['is_favorite']=2;
        }

        foreach($data[0]['part_info'] as $k => $v){
            $user = M('Account')->where('id='.$v['uid'])->field('icon,uname')->find();
            $data[0]['part_info'][$k]['icon'] = $user['icon'];
            $data[0]['part_info'][$k]['uname'] = $user['uname'];
        }

        if (Null == $data[0]['deadline']) {
            $data[0]['deadline'] = '';
        }

        $data[0]['remark'] = strip_tags(htmlspecialchars_decode($data[0]['remark']));
        if($nowPage == 0){
            apiReturn('1019',AJAX_FALSE);   //获取数据失败
        }else{
            M('Activity')->where('id='.$aid)->setInc('views');
            apiReturn('1020',AJAX_TRUE,$data[0]);  //获取数据成功
        }
    }

    //视频列表轮播图
    public function video_banner() {
        $where=array('mark' => 2, 'c_type' => 4);
        $field = 'cid,banner';
        $order = 'sort, push_time desc';
        $data = M('Push')->where($where)->field($field)->order($order)->select();

        foreach ($data as $k => $v) {
            $res[$k]['id'] = $v['cid'];
            $res[$k]['title_img'] = $v['banner'];
        }

        apiReturn('1020',AJAX_TRUE,$res);
    }

    //视频列表
    public function video_list() {
        $AesMct = new MCrypt;
        $mid = $AesMct->decrypt(urldecode(I('post.mid')));     //专业id
        $nowPage = I('post.nowpage');   //页码
        $num = I('post.num');   //每页条数
        if(!empty($mid)){
            $where['tags'] = $mid;
        }
        $where['status'] = 1;
        $where['is_private'] = 1;
        $order = array(
            'create_at'=>'desc',
            'views'=>'desc',
        );
        //数据分页
        $config = array(
            'tablename' => 'Admin/TrainRelation', // 表名
            'relation'  => true, // 关联条件
            'where'     => $where,  //查询条件
            'order'     => $order, // 排序
            'page'      => $nowPage,  // 页码，默认为首页
            'num'       => $num,  // 每页条数
            //'field'     => 'id,title,title_img,views,type,roomid,rtmpPullUrl'
        );
        $page = new ApiPage($config);
        $data = $page->get(); //获取分页数据
        if ($nowPage==0) {
            apiReturn('1019',AJAX_FALSE);   //获取数据失败
        }else {
            $get_dat = array();
            foreach ($data as $k => $v) {
                foreach ($v as $m => $n) {
                    $get_dat[$k][$m] = $n;
                    $get_dat[$k]['sums_page'] = $data['total_page'];    //总页数
                }
            }
            apiReturn('1020',AJAX_TRUE,$get_dat);   //获取数据成功
        }
    }


    //视频分类列表
    public function video_list_v4() {
        $mcrypt = new MCrypt();
        $userId = $mcrypt->encrypt(I('get.user_id'));
        $cate_id = I('get.cate_id');
        $nowPage = I('get.nowpage');
        $num = I('get.num');

        $where['cate_id'] = empty($cate_id) ? '':$cate_id;
        $where['status'] = 1;
        $where['is_private'] = 1;
        $order = array(
            'create_at'=>'desc',
            'views'=>'desc',
        );
        $config = array(
            'tablename' => 'Train',
            'where'     => $where,
            'order'     => $order,
            'page'      => $nowPage,
            'num'       => $num,
            'field'     => 'id,title,title_img,is_money,amount'
        );
        $page = new ApiPage($config);
        $data = $page->get();
        if($nowPage==0) {
            apiReturn('1019',AJAX_FALSE);
        } else {
            $returnData = array();
            foreach ($data as $k => $v) {
                foreach ($v as $m => $n) {
                    $returnData[$k][$m] = $n;
                    //$get_dat[$k]['sums_page'] = $data['total_page'];

                    if(M('order_train')->where(['user_id'  => $userId,
                            'video_id' => $data[ $k ]['id']])->count() > 0) {
                        $returnData[ $k ]['is_pay'] = true;
                    } else {
                        $returnData[ $k ]['is_pay'] = false;
                    }
                }
            }
            apiReturn('1020',AJAX_TRUE,$returnData);
        }
    }


    //视频列表头部的标签导航数据
    public function head_tags() {
        $data = M('Train')->Distinct(true)->where('status=1')->field('tags')->select();    //获取视频中的所有标签id
        $arr = array_column($data,'tags');  //转换一维数组

        //专业表中查询存在的数据
        $where = array('id' => array('in',$arr));
        $tags = M('Major')->where($where)->order('sort')->field('major_name,id')->select();
        apiReturn('1020',AJAX_TRUE,$tags);
    }

    //视频详情
    public function video() {
        $AesMct = new MCrypt;
        $letv = new LetvCloud;
        $uu = "dwbppqvkxs"; //用户唯一标识码   dwbppqvkxs
        $pu = "a2ee3b5de4"; //播放器唯一标识码  a2ee3b5de4
        $data['my_id'] = session('my_id');  //当前用户id
        $type = 'url';  //接口类型
        $auto_play = 1; //是否自动播放
        $width = I('post.width');  //播放器宽度
        $height = I('post.height'); //播放器高度
        $nowPage = I('post.nowpage'); //页码
        $num = I('post.num');    //每页显示条数
        $userId = I('post.user_id');
        $vid = $AesMct->decrypt(urldecode(I('post.vid')));  //视频id

        $model = D('Admin/TrainRelation');
        $data = $model->relation(true)->where('id='.$vid)->find();

        //付费视频
        if($data['is_money']) {

            if(empty($data['my_id']) and empty($userId)) {
                apiReturn('205',AJAX_FALSE,'未获取到用户id,请重新登录');
            }

            if(empty(M('order_train')->where(['video_id'=>$vid, 'user_id'=>$userId, 'status'=>1])->find())) {
                apiReturn('205',AJAX_FALSE,'请付费后再观看视频');
            }
            $data['is_pay'] = 1;
        }

        //读取回复信息列表
        $model2 = M('train_comment');
        $count = $model2->join('lx_account ON lx_train_comment.uid = lx_account.id')->where('vid='.$vid)->count();
        $data1 = $model2
            ->join('lx_account ON lx_train_comment.uid = lx_account.id')
            ->where('vid='.$vid)
            ->order('ctime desc')
            ->field('lx_train_comment.id as cid,uid,vid,content,ctime,is_nym,uname,icon,type')
            ->page($nowPage,$num)
            ->select();
        $data['sums_page'] = intval(ceil($count/$num));   //数据总页数
        $data['comm_list'] = $data1;    //嵌入回复信息列表

        //未登录时帖子收藏设为空
        if(empty(session('my_id'))) $data['is_favorite'] = '';

	    //查询当前用户是否收藏此帖子
	    $map = array('vid' => $vid, 'uid' => $data['my_id']);
	    $res = M('train_favorite')->where($map)->count();
//        $res ? $data['is_favorite'] = 1 : $data['is_favorite'] = 2;
        $data['is_favorite'] =  $res == 1 ?1 : 2;


        $data['video_path'] = $letv->videoGetPlayinterface($uu,video_info($data['video_id'],'video_unique'),$type,$pu,$auto_play,$width,$height);

        //主讲人介绍
        if($data['speaker_info'] == null) $data['speaker_info'] = '';

        //视频播放时长
        //if(!empty($get_dat[ $k ]['my_id']['video_id'])) {
        //    $video_id = $get_dat[ $k ]['my_id']['video_id'];
        //    $video_info = $letv->videoGet($video_id);
        //    $get_dat[ $k ]['video_duration'] = $video_info['video_duration'];
        //}

        if($nowPage == 0){
            apiReturn('1019',AJAX_FALSE);   //获取数据失败
        }else{
            M('Train')->where('id='.$vid)->setInc('views');
            apiReturn('1020',AJAX_TRUE,$data);  //获取数据成功
        }
    }

    //活动列表
    public function activity_list() {
        $AesMct = new MCrypt;

        $nowPage = $AesMct->decrypt(urldecode(I('get.nowpage')));   //页码
        $num = $AesMct->decrypt(urldecode(I('get.num')));   //每页条数
        $where = array('status' => array('neq',0));
        $order = array(
            'send_time'=>'desc',
            'views'=>'desc',
        );
        //数据分页
        $config = array(
            'tablename' => 'ActivityRelation', // 表名
            'relation'  => true, // 关联条件
            'where'     => $where, //条件
            'order'     => $order, // 排序
            'page'      => $nowPage,  // 页码，默认为首页
            'num'       => $num,  // 每页条数
            'field'     => 'id,title,address,number,price,star_time,end_time,views,status,send_time,imgs,type,author,
            deadline,is_money,amount'
        );
        $page = new ApiPage($config);
        $data = $page->get(); //获取分页数据
        if($data['now_page']==0)
        {
            apiReturn('1019',AJAX_FALSE);   //获取数据失败
        }else {
            $get_dat = array();
            foreach ($data as $k => $v) {
                foreach ($v as $m => $n) {
                    $get_dat[$k][$m] = $n;
                    if(session('my_id')) {
                        $get_dat[$k]['my_id'] = session('my_id');   //写入当前用户id
                    }
                    $get_dat[$k]['sums_page'] = $data['total_page'];    //总页数
                }
            }
            apiReturn('1020',AJAX_TRUE,$get_dat);   //获取数据成功
        }
    }

    //查询评价
    public function evaluate_view()
    {
        $AesMct = new MCrypt;
        $type = $AesMct->decrypt(urldecode(I('post.type')));      //评价类型 0好评 1差评
        $uid = $AesMct->decrypt(urldecode(I('post.uid')));      //律师ID
        $nowPage = $AesMct->decrypt(urldecode(I('post.nowpage')));   //页码
        $num = $AesMct->decrypt(urldecode(I('post.num')));   //每页条数
//        $nowPage =I('post.nowpage');   //页码
//        $num =I('post.num');   //每页条数
//        $type = I('post.type');      //评价类型 0好评 1差评
//        $uid = I('post.uid');      //律师ID

        if($type !== ""){
            $where = array(
                'type' => $type,
                'uid' => $uid
            );
        }else{
            $where = array(
                'uid' => $uid
            );
        }

        $order = array(
            'time'=>'desc'
        );
        $config = array(
            'tablename' => 'EvaluateRelation', // 表名
            'relation'  => true, // 关联条件
            'where'     => $where, //条件
            'order'     => $order, // 排序
            'page'      => $nowPage,  // 页码，默认为首页
            'num'       => $num,  // 每页条数
            'field'     => 'id,customer,uid,content,type,direct,time'
        );

        $page = new ApiPage($config);
        $data = $page->get(); //获取分页数据
        if($data['now_page']==0)
        {
            apiReturn('1019',AJAX_FALSE);   //获取数据失败
        }else {
            $get_dat = array();
            foreach ($data as $k => $v) {
                foreach ($v as $m => $n) {
                    $get_dat[$k][$m] = $n;
                    if(session('my_id')) {
                        $get_dat[$k]['my_id'] = session('my_id');   //写入当前用户id
                    }
                    $get_dat[$k]['sums_page'] = $data['total_page'];    //总页数
                }
            }
            apiReturn('1020',AJAX_TRUE,$get_dat);   //获取数据成功
        }
    }



    //他人资料
    public function other_info()
    {
        $AesMct = new MCrypt;
        $uid = $AesMct->decrypt(urldecode(I('post.uid'))); //用户id
        $type = $AesMct->decrypt(urldecode(I('post.type')));    //参数类型 默认用户id 1标识手机号码
        $width = I('post.width');  //播放器宽度
        $height =I('post.height'); //播放器高度
//        $uid =urldecode(I('post.uid')); //用户id
//        $type =urldecode(I('post.type'));    //参数类型 默认用户id 1标识手机号码

        if(intval($type) == 1) {
            $where = array(
                'mobile' => $uid
            );
        } else {
            $where = array(
                'id' => $uid
            );
        }

        $model = D('AccountRelation');
        $res = $model->relation(true)->where($where)->field('id,uname,mobile,gender,icon,bj_img,remark,email,birth,province,city,town,tag_citys,specialty,majors,work_life,law,lawyer_num,company,position,school,hight_diploma,education,professional,prize,price,type,create_at,up_time,status,num_img,is_hide,direct_price,case_price,direct_time,integral,credit,is_review,online_status')->select();
        $data = $res[0];
        $num = date('y', time()) - date('y', $data['up_time']);
        $data['years'] = $data['work_life'] + $num;     //执业年限
        $data['my_id'] = session('my_id');

        $resourceModel = M('resource_comment');
        $comm = $resourceModel->alias('rc')
            ->field('r.id,r.title')
            ->join('left join lx_resource  as r on rc.rid = r.id')
            ->where(['rc.uid'=>$data['id'],'rc.tbd'=>1,'r.status'=>1])
            ->group('rc.rid')
            ->order("time DESC")
            ->select();
        if($comm != null){
            foreach($comm as &$value){
                $array = array(
                    'rid'=>$value['id'],
                    'uid'=>$data['id'],
                    'rc.tbd'=>1 //查询优质回答
                );
                $content = $resourceModel->alias('rc')
                    ->field('id,content,type,likes,dislikes,tbd,time')
                    ->where($array)
                    ->order("time DESC")
                    ->select();
                foreach($content as &$val){
                    if($val['type'] == 1){
                        $uu = "dwbppqvkxs"; //用户唯一标识码   dwbppqvkxs
                        $pu = "a2ee3b5de4"; //播放器唯一标识码  a2ee3b5de4
                        $type = 'url';  //接口类型
                        $auto_play = 1; //是否自动播放
                        $letv = new LetvCloud;
                        $val['content']
                            = $letv->videoGetPlayinterface($uu, video_info($val['content'], 'video_unique'), $type, $pu, $auto_play, $width, $height);
                    }

                    $commentCount = $resourceModel
                        ->field('count(id) as count')
                        ->where('pid='.$val['id'])
                        ->find();
                    $val['commentCount'] = $commentCount;
                }

                $value['commentInfo']=$content;
            }
        }
        $data['goodContent'] = $comm;
//        if(!empty($comm)){
//            $data['goodContent'] = $comm;
//        }else{
//            $data['goodContent'] = '';
//        }
        apiReturn('1020', AJAX_TRUE, $data);
    }

}
