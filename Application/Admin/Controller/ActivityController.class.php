<?php
//+----------------------------------------------------------------------
// | Copyright (c) 2015-2017 http://lawyerscloud.cn All rights reserved.
//+----------------------------------------------------------------------
// | Author: 慢悠悠的丑小鸭 <1597305236@qq.com>
//+----------------------------------------------------------------------

namespace Admin\Controller;

use Common\Api\Category;
use Think\Controller;

class ActivityController extends AdminController
{

    /**
     * 活动列表
     *
     * @return [type] [description]
     */
    public function index()
    {
        $nickname = I('nickname');
        $where['status'] = array('neq', 0);
        $where['title'] = array('like', '%' . (string)$nickname . '%');
        $model = D('ActivityRelation');
        $count = $model->where($where)->field('id')->count(); //查询总记录数
        $Page = new \Think\Page($count, 8); // 实例化分页类 传入总记录数和每页显示的记录数(20)
        $Page->setConfig('header', '共%TOTAL_ROW%条');
        $Page->setConfig('first', '首页');
        $Page->setConfig('last', '共%TOTAL_PAGE%页');
        $Page->setConfig('prev', '上一页');
        $Page->setConfig('next', '下一页');
        $Page->setConfig('link', 'indexpagenumb');//pagenumb 会替换成页码
        $Page->setConfig('theme', '%HEADER% %FIRST% %UP_PAGE% %LINK_PAGE% %DOWN_PAGE% %END%');
        $show = $Page->show();
        $lists = $model
            ->relation(true)
            ->order('send_time desc,status')
            ->where($where)
            ->field('remark', true)
            ->limit($Page->firstRow . ',' . $Page->listRows)
            ->select();
        $this->assign('data', $lists);
        $this->assign('page', $show);
        $this->meta_title = '活动列表';
        $this->display();
    }


    /**
     * 发布活动
     *
     * @return [type] [description]
     */
    public function add()
    {
        $this->meta_title = '发布活动';
        $this->display();
    }


    /**
     * 修改活动
     *
     * @return [type] [description]
     */
    public function update()
    {
        if(IS_POST) {
            $activity_id = I('post.mid');
            $times = I('event_time');
            $arr_time = explode('-', $times);
            $star_time = strtotime($arr_time[0]); //活动开始时间戳
            $end_time = strtotime($arr_time[1]); //活动结束时间戳
            $deadline = strtotime(I('post.deadline')); //活动截止报名时间戳
            $data = array(
                'title'     => I('post.title'),     //标题
                'address'   => I('post.address'),     //活动地址
                'number'    => I('post.numbs'),        //限制人数
                'type'      => I('post.a_type'),       //活动方式
                'price'     => I('post.price'),     //人均消费
                'star_time' => $star_time,      //开始时间
                'end_time'  => $end_time,        //结束时间
                'deadline'  => $deadline,        //截止报名时间
                'group'     => I('post.group'),     //活动群号
                'sponsor'   => I('post.host_unit'),       //主办方
                'remark'    => I('post.content'),      //活动介绍
                'is_push'   => I('post.is_push'),     //是否首页推荐
                'send_time' => time()       //发布时间
            );

            $res = M('activity')->where(['id'=>$activity_id])->save($data);
            if($res){
                $this->redirect('index');
            }else{
                $this->error('修改失败');
            }
        }
        else {
            $activity_id = I('get.mid');
            if(empty($activity_id)) {
                $this->error('未找到这个网页');
            }
            $data_info = M('Activity')->where(['id' => $activity_id])->find();
            $this->assign('info', $data_info);
            $this->remark = htmlspecialchars_decode($data_info['remark']);
            $this->meta_title = '修改活动';
            $this->display();
        }
    }


    /**
     * 发布活动提交
     *
     * @return [type] [description]
     */
    public function sendActivity()
    {
        $times = I('event_time');
        $arr_time = explode('-', $times);
        $star_time = strtotime($arr_time[0]); //活动开始时间戳
        $end_time = strtotime($arr_time[1]); //活动结束时间戳
        $deadline = strtotime(I('post.deadline')); //活动截止报名时间戳

        /* 返回标准数据 */
        $return = array('status' => 1, 'info' => '上传成功', 'data' => '');
        /* 调用文件上传组件上传文件 */
        $Picture = D('Picture');
        C('PICTURE_UPLOAD.maxSize', 500 * 1024);   //上传的文件大小限制 (0-不做限制)
        C('PICTURE_UPLOAD.exts', 'jpg,gif,png,jpeg');    //允许上传的文件后缀
        C('PICTURE_UPLOAD.savePath', 'Picture/');   //设置上传子目录
        $url = C('UPLOAD_SITEIMG_OSS.domain');  //拼接url头
        $info = $Picture->upload(
            $_FILES,
            C('PICTURE_UPLOAD'),
            C('PICTURE_UPLOAD_DRIVER'),
            C('UPLOAD_SITEIMG_OSS'),
            $url
        ); //TODO:上传到远程服务器

        /* 记录图片信息 */
        if($info) {
            $return['status'] = 1;
            $return = array_merge($info['title_img'], $return);
            $return = array_merge($info['head_img'],$return);
        }
        else {
            $return['status'] = 0;
            $return['info'] = $Picture->getError();
        }
        $data = array(
            'title'     => I('post.title'),     //标题
            'imgs'      => $info['title_img']['path'],       //标题图片
            'head_img' =>  $info['head_img']['path'],          //详情头图
            'address'   => I('post.address'),     //活动地址
            'number'    => I('post.numbs'),        //限制人数
            'type'      => I('post.a_type'),       //活动方式
            'price'     => I('post.price'),     //人均消费
            'star_time' => $star_time,      //开始时间
            'end_time'  => $end_time,        //结束时间
            'deadline'  => $deadline,        //截止报名时间
            'group'     => I('post.group'),     //活动群号
            'sponsor'   => I('post.host_unit'),       //主办方
            'remark'    => I('post.content'),      //活动介绍
            'status'    => I('post.status'),       //活动状态
            'is_push'   => I('post.is_push'),     //是否首页推荐
            //'is_stick' => I('post.is_stick'),       //是否置顶
            'send_time' => time(),       //发布时间
            'is_money'    => empty(I('post.amount')) ? 0 : 1,
            'amount'      => I('post.amount')
        );

        if(M('Activity')->add($data)) {
            $this->redirect('index');
        }
        else {
            $this->error('插入数据失败');
        }
    }


    /**
     * 查看活动详情
     */
    public function views()
    {
        $id = lx_decrypt(I('get.mid'));
        if(empty($id)) {
            $this->error('未找到这个网页');
        }
        $data_info = D('ActivityRelation')->relation(true)->where('id=' . $id)->select();
        if ($data_info[0]['is_money'] == 1){
            $temp = M('Order_activity')->select(false);
            $data_list = M()->field('a.uname uname,a.icon icon,b.create_date time,b.amount')->table('lx_account a')->join('left join ('.$temp.')b on a.id = b.user_id')->where('b.activity_id = '.$id)->select();
            $data_info[0]['userList'] = $data_list;
        }else{
            $temp = M('Activity_part')->select(false);
            $data_list = M()->field('a.uname uname,a.icon icon,b.time time')->table('lx_account a')->join('left join ('.$temp.')b on a.id = b.uid')->where('b.aid = '.$id)->select();
            $data_info[0]['userList'] = $data_list;
        }
        $this->assign('info', $data_info[0]);
        $this->remark = htmlspecialchars_decode($data_info['remark']);
        $this->meta_title = '查看活动';
        $this->display();
    }


    /**
     * 活动状态,暂停开启
     *
     * @return [type] [description]
     */
    public function activityStop()
    {
        $status = lx_decrypt(I('get.status'));
        $id = lx_decrypt(I('get.mid'));

        $state = M('Activity')->where('id=' . $id)->setField('status', $status);
        if($state) {
            $this->redirect('index');
        }
        else {
            $this->error('更新失败');
        }
    }


    //删除单个活动
    public function deleteActivity()
    {
        $id = lx_decrypt(I('get.mid'));

        $state = M('Activity')->where('id=' . $id)->setField('status', 0);
        if($state) {
            $this->redirect('index');
        }
        else {
            $this->error('更新失败');
        }
    }


    /**
     * 批量删除
     *
     * @return [type] [description]
     */
    public function batchDelete()
    {
        $law_id = $_POST['get_id'];
        $law_idarr = explode(',', $law_id);

        foreach($law_idarr as $lid) {
            M('Activity')->where('id=' . $lid)->setField('status', 0);
        }

        $data = array(
            'state' => 1,
            'msg'   => '操作成功'
        );
        echo json_encode($data);

        return true;
    }


    /**
     * 首页banner推荐
     *
     * @return [type] [description]
     */
    public function push()
    {
        $id = lx_decrypt(I('get.mid'));
        $status = lx_decrypt(I('get.is_push'));

        $state = M('Activity')->where('id=' . $id)->setField('is_push', $status);
        if($state) {
            M('Push')->where('c_type=3 AND cid=' . $id)->delete();
            $this->redirect('index');
        }
        else {
            $this->error('更新失败');
        }
    }


    /**
     * 取消banner推荐
     *
     * @return [type] [description]
     */
    public function cancelPush()
    {
        $id = lx_decrypt(I('get.id'));
        $status = lx_decrypt(I('get.is_push')); //是否推送至首页：1是、0否

        $state = M('Activity')->where('id=' . $id)->setField('is_push', $status);
        if($state) {
            if(M('Push')->where('cid=' . $id)->delete()) {
                $this->redirect('index');
            }
            else {
                $this->error('更新失败');
            }
        }
        else {
            $this->error('更新失败');
        }
    }


    /**
     * 推荐到精选,取消推荐
     *
     * @return [type] [description]
     */
    public function recomPush()
    {
        $id = lx_decrypt(I('get.mid'));     //视频详情页id
        $status = lx_decrypt(I('get.status'));  //0取消精选推荐,1精选推荐

        if($status == 1) {
            $data = D('Activity')->where('id=' . $id)->find();
            $recom_data = array(
                'type'          => 3,               //内容类别：1资讯 2视频 3活动
                'status'        => $status,         //开启状态：1是、0否
                'activity_id'   => $data['id'],     //活动id
                'title'         => $data['title'],
                'thumb_img'     => $data['imgs'],
                'star_time'     => $data['star_time'],
                'end_time'      => $data['end_time'],
                'consumption'   => $data['price'],
                'activity_type' => $data['type'],
                'deadline'      => $data['deadline'],
                'author'        => $data['author'],
                'push_date'     => $_SERVER['REQUEST_TIME'],
                'views'         => $data['views'],
                'object_id'     => $data['id'],
                'is_cost'       => $data['is_money'],
                'amount'        => $data['amount']
            );

            if(M('Recommend')->add($recom_data)) {
                M('Activity')->where('id=' . $id)->setField('recom_push', $status);
                $this->redirect('index');
            }
            else {
                $this->error('推荐失败!');
            }

        }
        elseif($status == 0) {

            M('Activity')->where('id=' . $id)->setField('recom_push', $status);
            if(M('Recommend')->where('activity_id=' . $id)->delete()) {
                $this->redirect('index');
            }
            else {
                $this->error();
            }
        }
    }

    public function uploadUediImg(){

        date_default_timezone_set("Asia/chongqing");
        error_reporting(E_ERROR);
        header("Content-Type: text/html; charset=utf-8");

        //TODO: 用户登录检测

        /* 返回标准数据 */
        $return  = array('status' => 1, 'info' => '上传成功', 'data' => '');
        /* 调用文件上传组件上传文件 */
        $Picture = D('Picture');
        C('PICTURE_UPLOAD.maxSize',500*1024);   //上传的文件大小限制 (0-不做限制)
        C('PICTURE_UPLOAD.exts','jpg,gif,png,jpeg');    //允许上传的文件后缀
        C('PICTURE_UPLOAD.savePath','Picture/');   //设置上传子目录
        $url = C('UPLOAD_SITEIMG_OSS.domain');  //拼接url头
        $info = $Picture->upload(
            $_FILES,
            C('PICTURE_UPLOAD'),
            C('PICTURE_UPLOAD_DRIVER'),
            C('UPLOAD_SITEIMG_OSS'),
            $url
        ); //TODO:上传到远程服务器

        /* 记录图片信息 */
        if($info){
            $return['status'] = 1;
            $return = array_merge($info['fileList'], $return);
//            p($return);
        } else {
            $return['status'] = 0;
            $return['info']   = $Picture->getError();
        }

        /* 输出结果 */
        $data['state'] = "SUCCESS";
        $data['url'] = $return['path'];
        $data['title'] = $return['path'];
        $data['original'] = $return['path'];
        /**
         * {
        "state": "SUCCESS",
        "url": "upload/demo.jpg",
        "title": "demo.jpg",
        "original": "demo.jpg"
        }
         */
        echo json_encode($data);
        exit;
    }

}