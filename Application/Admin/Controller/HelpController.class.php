<?php
//+----------------------------------------------------------------------
// | Copyright (c) 2015-2017 http://lawyerscloud.cn All rights reserved.
//+----------------------------------------------------------------------
// | Author: 慢悠悠的丑小鸭 <1597305236@qq.com>
//+----------------------------------------------------------------------

namespace Admin\Controller;

use Common\Api\Category;
use Think\Controller;
use Common\Api\letvCloud;

class HelpController extends AdminController
{

    /**
     * 求助列表
     * @return [type] [description]
     */
    public function index()
    {
        $nickname = I('nickname');
        //$where['status'] = array( 'neq', 0 );
        $where['type'] = array('eq', 2);
        $where['title'] = array('like', '%' . (string)$nickname . '%');
        $model = D('ResourceRelation');
        $count = $model->where($where)->field('id')->count(); //查询总记录数
        $Page = new \Think\Page($count, 12);// 实例化分页类 传入总记录数和每页显示的记录数(25)
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
            ->where($where)
            ->field('id,title,author,views,send_time,status,is_push,is_stick,is_reward,reward_money')
            ->order('send_time desc')
            ->limit($Page->firstRow . ',' . $Page->listRows)
            ->select();

        $this->assign('data', $lists);
        $this->assign('page', $show);
        $this->meta_title = '求助列表';
        $this->display();
    }

    /**
     * 求助详情
     * @return [type] [description]
     */
    public function views()
    {
        $id = lx_decrypt(I('get.id'));
        $model = D('ResourceRelation');
        $article = $model ->relation(true)->where('id=' . $id)->select();
        $data = M('Major')->where('status=1')->order('sort')->select();

        if(empty($id)) {
            $this->error('此页面不存在!');
            $this->redirect('index');
        }

        $this->major = Category::toLevel($data, "ㄴ"); //专业标签
        $this->content = htmlspecialchars_decode($article['content']);

        foreach($article[0]['comment_info'] as &$val){
            if($val['type'] == 1){
                $uu = "dwbppqvkxs"; //用户唯一标识码   dwbppqvkxs
                $pu = "a2ee3b5de4"; //播放器唯一标识码  a2ee3b5de4
                $type = 'js';  //接口类型
                $auto_play = 0; //是否自动播放
                $width = 250;  //播放器宽度
                $height = 100; //播放器高度
                $letv = new LetvCloud;
                $val['comm_content']
                    = $letv->videoGetPlayinterface($uu, video_info($val['comm_content'], 'video_unique'), $type, $pu, $auto_play, $width, $height);
            }
        }
//        dump($article[0]);exit;
        $this->assign('info', $article[0]);
        $this->meta_title = '查看详情';
        $this->display();
    }

    /**
     * 采纳问题
     * @return [type] [description]
     */
    public function adoptHelp()
    {
        $cid = lx_decrypt(I('get.cid'));
        $rid = lx_decrypt(I('get.rid'));
        M('Resource')->where('id=' . $rid)->setField('tbd_id', $cid);
        M('Resource_comment')->where('id=' . $cid)->setField('tbd', 1);
        $this->redirect('Help/views', array('mid' => $rid));
    }

    /**
     * 删除求助
     * @return [type] [description]
     */
    public function delete()
    {
        $id = lx_decrypt(I('get.id'));
        $status = lx_decrypt(I('get.status'));

        if(!empty($id)) {
            $state = M('Resource')->where('id=' . $id)->setField('status', $status);

            if($state) {
                $this->redirect('index');
            } else {
                $this->error('编辑失败');
            }
        } else {
            $this->error('此页面不存在!');
            $this->redirect('index');
        }
    }

    /**
     * 批量删除
     * @return [type] [description]
     */
    public function delPosts()
    {
        $law_id = $_POST['get_id'];
        $law_idarr = explode(',', $law_id);

        foreach($law_idarr as $lid) {
            M('Resource')->where('id=' . $lid)->setField('status', 0);
        }

        $data = array(
            'state' => 1,
            'msg'   => '操作成功',
        );
        echo json_encode($data);

        return true;
    }

    /**
     * 求助置顶
     * 求助列表页设置置顶
     * @return [type] [description]
     */
    public function stick()
    {
        $id = lx_decrypt(I('get.id'));
        $status = lx_decrypt(I('get.is_stick'));  //是否置顶：1是、2否

        if(!empty($id)) {
            $data = array(
                'is_stick'   => $status,
                'stick_date' => $_SERVER['REQUEST_TIME'],
            );
            $state = M('Resource')->where('id=' . $id)->save($data);
            if($state) {
                $this->redirect('index');
            } else {
                $this->error('置顶失败');
            }

        } else {
            $this->error('此页面不存在!');
            $this->redirect('index');
        }
    }


    /**
     * 取消首页banner推荐
     * @return [type] [description]
     */
    public function cancelPush()
    {
        $id = lx_decrypt(I('get.id'));
        $status = lx_decrypt(I('get.is_push')); //是否推送至首页：1是、0否

        $state = M('Resource')->where('id=' . $id)->setField('is_push', $status);
        if($state) {
            if(M('Push')->where('cid=' . $id)->delete()) {
                $this->redirect('index');
            } else {
                $this->error('更新失败');
            }
        } else {
            $this->error('更新失败');
        }
    }
}