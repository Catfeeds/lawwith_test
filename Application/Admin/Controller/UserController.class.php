<?php
/**
 * Created by PhpStorm.
 * User: Mrx
 * Date: 2016/3/30
 * Time: 10:00
 */

namespace Admin\Controller;

use Think\Controller;
use Common\Api\JPush;
use Common\Api\Category;
use Common\Api\letvCloud;

class UserController extends AdminController {

    //律师列表
    public function index()
    {
        $type     = lx_decrypt(I('get.t'));
        $all      = lx_decrypt(I('get.all'));
        $nickname = I('nickname');

        if ($all==1) {
            $map = array(
                'num_img'   => array('neq',''),
                'auth_time' => array('neq',''),
                'status'    => 0,
                //'is_review' => 0,
            );
        } else {
            $map['type'] = array('eq',$type);
        }

        if (is_numeric($nickname)) {
            $map['mobile|uname'] = array('like','%'.$nickname.'%');
        } else {
            $map['uname'] = array('like', '%'.(string)$nickname.'%');
        }

        $model = D('App/AccountRelation');
        $count = $model->relation(true)->where($map)->count();
        $Page = new \Think\Page($count,10);
        $Page -> setConfig('header','共%TOTAL_ROW%条');
        $Page -> setConfig('first','首页');
        $Page -> setConfig('last','共%TOTAL_PAGE%页');
        $Page -> setConfig('prev','上一页');
        $Page -> setConfig('next','下一页');
        $Page -> setConfig('link','indexpagenumb');//pagenumb 会替换成页码
        $Page -> setConfig('theme','%HEADER% %FIRST% %UP_PAGE% %LINK_PAGE% %DOWN_PAGE% %END%');
        $show = $Page->show();
        $data = $model->relation(true)->where($map)->order('status desc,create_at')->limit($Page->firstRow.','.$Page->listRows)->select();
        $this->meta_title    = '用户列表';
        $this->meta_describe = '查看用户基本数据';
        $this->majors = M('Major')->where('status=1')->select();    //专业数据
        $this->assign('u_type',$type);
        $this->assign('data',$data);
        $this->assign('page',$show); //赋值分页输出
        $this->display('account');
    }

    //取消推送banner首页
    public function cancelPush () {
        $id     = lx_decrypt(I('get.mid'));
        $type   = I('get.u_type');
        $status = lx_decrypt(I('get.is_push'));

        if (M('Account')->where('id='.$id)->setField('is_push',$status)) {
            M('Push')->where('c_type=6 AND cid='.$id)->delete();
            $this->redirect('User/index',array('t'=>$type));
        }else {
            $this->error('更新失败');
        }
    }

    //专业列表
    public function major()
    {
        $nickname          =   I('nickname');
        $map['major_name'] =   array('like', '%'.(string)$nickname.'%');
        $model = M('Major');
        $count = $model->where($map)->count();
        $Page = new \Think\Page($count,10);
        $Page -> setConfig('header','共%TOTAL_ROW%条');
        $Page -> setConfig('first','首页');
        $Page -> setConfig('last','共%TOTAL_PAGE%页');
        $Page -> setConfig('prev','上一页');
        $Page -> setConfig('next','下一页');
        $Page -> setConfig('link','indexpagenumb');//pagenumb 会替换成页码
        $Page -> setConfig('theme','%HEADER% %FIRST% %UP_PAGE% %LINK_PAGE% %DOWN_PAGE% %END%');
        $show = $Page->show();
        $data = $model
            ->where($map)
            ->order('sort')
            ->limit($Page->firstRow.','.$Page->listRows)
            ->select();
        $this->tree_list = Category::toLevel($data,"<div class='fa-hand-o-right'>----</div>");
        $this->assign('page',$show); //赋值分页输出
        $this->meta_title    = '专业列表';
        $this->meta_describe = '专业领域列表';
        $this->display();
    }

    //添加专业
    public function addMajor()
    {
        $mid = lx_decrypt(I('mid'));
        if(empty($mid))
        {
            $data = M('Major')->where('status=1')->order('sort')->select();
            $this->tree = Category::toLevel($data,"ㄴ");
            $this->meta_title = '添加专业';
            $this->meta_describe = '增添专业名称';
        }else {
            $data = M('Major')->where('status=1')->order('sort')->select();
            $this->tree = Category::toLevel($data,"ㄴ");
            $this->data1 = M('Major')->where('id='.$mid)->find();
            $this->meta_title = '编辑专业';
            $this->meta_describe = '修改编辑专业名称';
        }
        $this->display();
    }

    //提交添加
    public function subMajor()
    {
        $mid    = I('post.mid');
        $status = I('post.status');

        if (empty($status)) {
            $status = 0;
        }

        $data = array(
            'pid'        => I('post.pid'),
            'sort'       => I('post.sort'),
            'status'     => $status,
            'is_update'  => 1,
            'major_name' => I('post.majorName'),
        );
        if(empty($mid))
        {
            if (M('Major')->add($data))
            {
                $this->redirect('major');
            }else {
                $this->error('添加失败');
            }
        }else {
            if(M('Major')->where('id='.$mid)->save($data)){
                $this->redirect('major');
            }else {
                $this->error('修改失败');
            }
        }
    }


    //判断专业领域是否存在
    public function checkMajor ()
    {
        $post_val = I('post.majorName');
        $users = M('Major')->where("major_name='$post_val'")->select();

        if($users){
            $valid = false;
        }else {
            $valid = true;
        }
        echo json_encode(array(
            'valid' => $valid,
        ));
    }

    //修改状态
    public function changeStatus()
    {
        $id     = lx_decrypt(I('get.uid'));
        $status = lx_decrypt(I('get.status'));
        if(M('Major')->where('id='.$id)->setField('status',$status))
        {
            $this->redirect('major');
        }else {
            $this->error('更新失败');
        }
    }

    //验证密码
    public function checkPwd ()
    {
        $uname  = $_SESSION['username'];
        $pwd    = $_POST['old_pwd'];
        $passwd = M('User')->where("username='$uname'")->getField('passwd');
        if(md5($pwd) == $passwd){
            $valid = true;
            file_put_contents('test.txt','user:'.$uname.'<br />');
            file_put_contents('test.txt','pwd:'.$pwd);
        }else {
            $valid = false;
            file_put_contents('test1.txt','user:'.$uname.'<br />');
            file_put_contents('test1.txt','pwd:'.$pwd);
        }
        echo json_encode(array('valid'=>$valid,'uname'=>$uname,'pwd'=>$pwd));
    }
    

    //修改密码
    public function changePwd ()
    {
        $user    = $_SESSION['username'];  //用户
        $new_pwd = I('post.pwd1');      //新密码
        $model   = M('User');
        $id      = $model->where("username='$user'")->getField('id');
        $res     = $model->where('id='.$id)->setField('passwd',md5($new_pwd));
        if($res) {
            cookie('cook_id',session(C('USER_AUTH_KEY')));
            cookie('cook_name', session('username'));
            session_unset();
            session_destroy();
            if(!isset($_SESSION['uid'])){
                $this->redirect('Login/lockscreen');
            }
        }else {
            $this->error('密码修改失败');
        }
    }

    //查看审核
    public function audit_identity ()
    {
        $uid   = lx_decrypt(I('get.uid'));
        $model = D('App/AccountRelation');
        $data  = $model->relation(true)->where('id='.$uid)->find();
        $resourceModel = M('resource_comment');

           $comm = $resourceModel->alias('rc')
            ->field('r.id,r.title')
            ->join('left join lx_resource  as r on rc.rid = r.id')
            ->where('rc.uid='.$uid)
            ->group('rc.rid')
            ->order("time DESC")
            ->select();
        if($comm != null){
            foreach($comm as &$value){
                $array = array(
                    'rid'=>$value['id'],
                    'uid'=>$uid
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
                            $type = 'js';  //接口类型
                            $auto_play = 0; //是否自动播放
                            $width = 250;  //播放器宽度
                            $height = 100; //播放器高度
                            $letv = new LetvCloud;
                            $val['content']
                                = $letv->videoGetPlayinterface($uu, video_info($val['content'], 'video_unique'), $type, $pu, $auto_play, $width, $height);
                        }
                    }
                $value['commentInfo']=$content;
            }
        }

        $data['com'] = $comm;
        M('Account')->where('id='.$uid)->setField('is_review',1);
        $this->assign('data',$data);
//        dump($data); exit;
        $this->meta_title = '用户信息';
        $this->display('shenhe');
    }

    //审核认证
    public function change_audit ()
    {
        $sta = lx_decrypt(I('get.sta'));
        $uid = lx_decrypt(I('get.u'));
        $user_data = M('Account')->where('id='.$uid)->find();

        if ($sta != 4) {
            if($sta==1){
                $content = '恭喜您的认证审核已经通过！您有任何问题请联系info@lawyerscloud.cn，感谢您的支持。';
            }elseif($sta==3){
                $content = '抱歉，您的认证消息被驳回。您想继续认证或者有任何问题请联系info@lawyerscloud.cn。感谢您的支持。
';
            }
            $data = array(
                'title'     => '审核消息',
                'content'   => $content,
                'type'      => 2,
                'send_time' => time(),
                'push_obj'  => $user_data['uname'].'('.$user_data['mobile'].')',
            );
            $mid = M('Sys_messages')->add($data);
            $m_txt = array(
                'type' => 0,    //0系统消息 1回答我 2求助我 3评论我 4响应我
                'cid' =>$mid
            );
            $alias = array(
                'alias' => explode(',',$user_data['mobile'])
            );
            $Jpush = new Jpush(C('JPUSH_APPKEY'),C('JPUSH_SECRET'));
            $res = $Jpush->push('all',$alias,$content,$m_type='http',$m_txt);

            if ($res) {

                //$res_arr = json_decode($res, true);
                //if(isset($res_arr['error'])){                       //如果返回了error则证明失败
                //    M('Sys_messages')->where('id='.$mid)->delete();
                //    $this->error('发送失败');
                //}else{

                    $data = array(
                        'msgid' => $mid,
                        'uid' => $uid
                    );
                    M('Message')->add($data);
                    if(M('Account')->where('id='.$uid)->setField('status',$sta)){
                        $this->success('操作成功');
                    }else {
                        $this->error('操作失败');
                    }
               // }
            } else {
                //接口调用失败或无响应
                M('Sys_messages')->where('id='.$mid)->delete();
                $this->error('接口调用失败或无响应');
            }

        }else{
            if(M('Account')->where('id='.$uid)->setField('status',$sta)){
                $this->success('操作成功');
            }else {
                $this->error('操作失败');
            }
        }
    }

    //意见反馈
    public function feedback ()
    {
        $model = M('Feedback');
        $where['lx_feedback.status'] =  array('neq',0);
        $count = $model->join('lx_account on lx_feedback.uid = lx_account.id')->where($where)->count();
        $Page = new \Think\Page($count,12);
        $Page -> setConfig('header','共%TOTAL_ROW%条');
        $Page -> setConfig('first','首页');
        $Page -> setConfig('last','共%TOTAL_PAGE%页');
        $Page -> setConfig('prev','上一页');
        $Page -> setConfig('next','下一页');
        $Page -> setConfig('link','indexpagenumb');//pagenumb 会替换成页码
        $Page -> setConfig('theme','%HEADER% %FIRST% %UP_PAGE% %LINK_PAGE% %DOWN_PAGE% %END%');
        $show = $Page->show();
        $data = $model->join('lx_account on lx_feedback.uid = lx_account.id')
            ->order('is_read,send_time desc')
            ->where($where)
            ->limit($Page->firstRow.','.$Page->listRows)
            ->field('lx_account.icon,lx_account.uname,lx_account.mobile,lx_feedback.*')
            ->select();
        $this->meta_title = '用户反馈列表';
        $this->assign('page',$show);
        $this->assign('data',$data);
        $this->display();
    }

    //查看意见详情
    public function view_feedback()
    {
        $id      = lx_decrypt(I('get.id'));
        $model   = M('Feedback');

        $where = array(
            'lx_feedback.id' => $id
        );
        $data = $model
            ->join('lx_account on lx_feedback.uid = lx_account.id')
            ->where($where)
            ->field('lx_account.id as acc_id,lx_account.icon,lx_account.uname,lx_feedback.*')
            ->find();
        $replyData = M('Reply_feedback')->where('msg_id='.$id)->order('reply_time desc')->select();
        $this->assign('data',$data);
        $this->assign('replyData',$replyData);
        $this->meta_title = '反馈详情';
        $this->display();
    }

    //回复用户反馈
    public function replyFeedback()
    {
        $uid = I('post.uid');
        $msgid = I('post.msg_id');
        $replyContent = I('post.content');

        if (empty($uid) || empty($replyContent)) {
            //
            echo '发送失败：用户id或回复内容为空';exit();
        }

        $data = ['uid' => $uid,'msg_id' => $msgid,'reply_content' => $replyContent,'reply_time' => time()];
        M('Reply_feedback')->add($data);

        $model   = M('Feedback');
        $is_read = $model->where('id='.$msgid)->getField('is_read');
        if($is_read == 0){
            $model->where('id='.$msgid)->setField('is_read',1);
        }

        $acid = user_info($uid,'mobile');
        $content = '律携小秘书：感谢您提的反馈意见……';
        $m_txt   = array('type' => 0);
        sendPush($acid, $content, 'http', $m_txt);

        $messageData = [
            'title'     => '用户反馈',
            'content'   => $replyContent,
            'push_obj'  => '(' . $acid . ')',
            'type'      => 2,
            'send_time' => time(),
        ];
        //写入系统消息记录
        createMessage($messageData,$uid);

        echo 'success';
    }

    //用户举报
    public function user_report()
    {
        $model = M('Report');
        $where['lx_report.status'] =  array('neq',0);
        $count = $model
            ->join('lx_account on lx_report.uid = lx_account.id')
            ->where($where)
            ->count();
        $Page = new \Think\Page($count,10);
        $Page -> setConfig('header','共%TOTAL_ROW%条');
        $Page -> setConfig('first','首页');
        $Page -> setConfig('last','共%TOTAL_PAGE%页');
        $Page -> setConfig('prev','上一页');
        $Page -> setConfig('next','下一页');
        $Page -> setConfig('link','indexpagenumb');//pagenumb 会替换成页码
        $Page -> setConfig('theme','%HEADER% %FIRST% %UP_PAGE% %LINK_PAGE% %DOWN_PAGE% %END%');
        $show = $Page->show();
        $data = $model
            ->join('lx_account on lx_report.uid = lx_account.id')
            ->order('is_read,time desc')
            ->where($where)
            ->limit($Page->firstRow.','.$Page->listRows)
            ->field('lx_account.id as acc_id,lx_account.icon,lx_account.uname,lx_report.*')
            ->select();
        $this->meta_title = '举报列表';
        $this->assign('page',$show);
        $this->assign('data',$data);
        $this->display('user_report');
    }
}