<?php
//+----------------------------------------------------------------------
// | Copyright (c) 2015-2017 http://lawyerscloud.cn All rights reserved.
//+----------------------------------------------------------------------
// | Author: 慢悠悠的丑小鸭 <1597305236@qq.com>
//+----------------------------------------------------------------------

namespace Admin\Controller;

use Think\Controller;
use Common\Api\ChatApi;
use Common\Api\Category;
use Admin\Controller\File;

class LiveController extends AdminController {
    public function index() {
        $model = D('LiveRelation');
        //$where['status'] = array('eq',1);
        $count = $model->field('id')->count(); //查询总记录数
        $Page  = new \Think\Page( $count,12);// 实例化分页类 传入总记录数和每页显示的记录数(25)
        $Page->setConfig( 'header', '共%TOTAL_ROW%条' );
        $Page->setConfig( 'first', '首页' );
        $Page->setConfig( 'last', '共%TOTAL_PAGE%页' );
        $Page->setConfig( 'prev', '上一页' );
        $Page->setConfig( 'next', '下一页' );
        $Page->setConfig( 'link', 'indexpagenumb' );//pagenumb 会替换成页码
        $Page->setConfig( 'theme', '%HEADER% %FIRST% %UP_PAGE% %LINK_PAGE% %DOWN_PAGE% %END%' );
        $show  = $Page->show();
        $lists = $model
            ->relation(true)
            //->where($where)
            ->order('ctime desc' )
            ->limit($Page->firstRow.','.$Page->listRows )
            ->select();
        $this->majors = M('Major')->where('status=1')->select();    //专业数据
        $this->assign('data', $lists);
        $this->assign('page', $show);
        $this->meta_title = '直播列表';
        $this->display();
    }

    //创建一个直播频道,聊天室
    public function channelCreate() {
        if (IS_POST) {
            $chatApi = new ChatApi();
            $name = I('post.name');     //频道名称
            $mobile = I('post.mobile');   //主播手机号
            $robot = I('post.robot');    //机器人数
            $is_private = I('post.is_private');    //是否公开 0私密 1公开
            $announcement = I('post.announcement');     //聊天室公告

            // 创建频道
            $create = $chatApi->channelCreate($name, 0);
            /* 创建聊天室
            $mobile,                      聊天室属主的账号accid
            $name,                        聊天室名称
            $announcement,                公告
            $create['ret']['rtmpPullUrl'] 直播地址
            */
            $chatRoomInfo = $chatApi->chatRoomCreate($mobile, $name, $announcement, $create['ret']['rtmpPullUrl']);

            $roomid      = $chatRoomInfo['chatroom']['roomid'];     //聊天室id
            $name        = $create['ret']['name'];
            $httpPullUrl = $create['ret']['httpPullUrl'];
            $hlsPullUrl  = $create['ret']['hlsPullUrl'];
            $rtmpPullUrl = $create['ret']['rtmpPullUrl'];
            $pushUrl = $create['ret']['pushUrl'];
            $ctime = $_SERVER['REQUEST_TIME'];
            $cid = $create['ret']['cid'];

            if ($create['code'] == 200) {
                $return = array( 'status' => 1, 'info' => '上传成功', 'data' => '' );
                $upload = D( 'Picture' );
                C( 'PICTURE_UPLOAD.maxSize', 500 * 1024 );   //上传的文件大小限制 (0-不做限制)
                C( 'PICTURE_UPLOAD.exts', 'jpg,gif,png,jpeg' );    //允许上传的文件后缀
                C( 'PICTURE_UPLOAD.savePath', 'Picture/' );   //设置上传子目录
                $url  = C( 'UPLOAD_SITEIMG_OSS.domain' );  //拼接url头
                $info = $upload->upload(
                    $_FILES,
                    C( 'PICTURE_UPLOAD' ),
                    C( 'PICTURE_UPLOAD_DRIVER' ),
                    C( 'UPLOAD_SITEIMG_OSS' ),
                    $url
                ); //TODO:上传到远程服务器

                /* 记录图片信息 */
                if ( $info ) {
                    $return['status'] = 1;
                    $return = array_merge( $info['thumb_img'], $return );
                } else {
                    $this->error( $upload->getError() );
                }

                $data = array(
                    'tag_major'   => implode(',', I('post.major_id')),    //专业领域
                    'mobile'      => $mobile,                             //主播手机号
                    'robot'       => $robot,                              //机器人数
                    'thumb_img'   => $return['path'],                   //封面图片
                    'status'      => 0,                                 //状态 0：空闲； 1：直播； 2：禁用； 3：直播录制
                    'is_private'  => $is_private,                       //是否公开 0：私密； 1：公开
                    'type'        => 0,                                 //频道类型 ( 0 : rtmp, 1 : hls, 2 : http)
                    'name'        => $name,                             //频道名称
                    'announcement'=> $announcement,                     //聊天室公告
                    'roomid'      => $roomid,                           //聊天室ID
                    'httpPullUrl' => $httpPullUrl,                      //http拉流地址
                    'hlsPullUrl'  => $hlsPullUrl,                       //hls拉流地址
                    'rtmpPullUrl' => $rtmpPullUrl,                      //rtmp拉流地址
                    'pushUrl'     => $pushUrl,                          //推流地址
                    'ctime'       => $ctime,                            //创建频道的时间戳
                    'cid'         => $cid,                              //频道ID，32位字符串
                    'is_money'    => empty(I('post.amount')) ? 0 : 1,
                    'amount'      => I('post.amount')
                );

                if (M('Live')->add($data)) {
                    // 添加直播数据到视频列表
                    $trainData = array(
                        'title'     => $name,           //频道名称
                        'roomid'    => $roomid,         //聊天室ID
                        'title_img' => $return['path'], //封面图
                        'type'      => 2,               //1视频 2直播
                        'create_at' => $_SERVER['REQUEST_TIME'],    //创建时间
                        'rtmpPullUrl' => $rtmpPullUrl,  //拉流地址
                        'remark'    => $announcement,   //直播公告
                        'author'    => $_SESSION['username'],   //发布者
                        'tags'   => implode(',', I('post.major_id')),    //专业领域
                        'status' => 0,                  //状态 0：空闲； 1：直播； 2：禁用； 3：直播录制
                        'is_private' => $is_private,    //是否公开 0：私密； 1：公开
                    );
                    //p($data);p($chatRoomInfo);die;
                    if ( M('Train')->add($trainData) ) {
                        $this->redirect('index');
                    } else {
                        $this->error('创建频道成功,发送到视频列表失败!');
                    }
                    // $this->redirect('index');
                } else {
                    $this->error('创建失败');
                }

            } else {
                $this->error('该频道创建失败!');
            }
        }
        $data = M( 'Major' )->where( 'status=1' )->order( 'sort' )->select();
        $this->major = Category::toLevel( $data, "ㄴ" );
        $this->meta_title = '新建直播间';
        $this->display('create');
    }

    //发送自定义消息
    public function sendMsg() {
        if(IS_POST){
            $id = I('post.id');     //是否私密 0不私密 1私密
            $private = I('post.private');     //是否私密 0不私密 1私密
            $group = I('post.group');     //分享到群组群号
            $content = I('post.content');     //分享到群组的描述,分享到群组时的描述
            $liveInfo = M('Live')->where('id='.$id)->field('name,mobile,roomid')->find();
            $state = $this->sendCustomMsg($liveInfo['mobile'], $group, $liveInfo['roomid'], $liveInfo['name'], $content);
            if($state['code'] == 200){
                $this->redirect('index');
            }else{
                $this->error('发送失败');
            }
        }else{
            $id = I('get.id');
            $liveInfo = M('Live')->where('id='.$id)->field('id,mobile')->find();
            $this->assign('liveInfo',$liveInfo);
            $this->meta_title = '发送到群';
            $this->display();
        }
    }

    //发送自定义消息
    public function sendCustomMsg($from, $group, $roomid, $title, $content) {
        $chat = new ChatApi();
        $body = array(
            'data'  => array(
                'ctype' => 7,
                'id'=> $roomid,
                'title' => $title,
                'info'  => $content,
                'path'  => "",
            ),
            'type' => 5
        );
        $res = $chat->sendMsg($from,1,$group,100,$body);
        return $res;
    }

    /*
     * 修改聊天室信息
     * 此处注意修改频道名称时同时修改聊天室名称,直播地址不变
     */
    public function chatRoomUpdate() {
        if (IS_POST) {
            $roomid       = I('post.roomid');
            $cid          = I('post.cid');
            $name         = I('post.name');
            $robot        = I('post.robot');
            $announcement = I('post.announcement');

            if ( $_FILES['thumb_img']['name'] ) {
                $upload = D( 'Picture' );
                C( 'PICTURE_UPLOAD.maxSize', 500 * 1024 );   //上传的文件大小限制 (0-不做限制)
                C( 'PICTURE_UPLOAD.exts', 'jpg,gif,png,jpeg' );    //允许上传的文件后缀
                C( 'PICTURE_UPLOAD.savePath', 'Picture/' );   //设置上传子目录
                $url  = C( 'UPLOAD_SITEIMG_OSS.domain' );  //拼接url头
                $info = $upload->upload(
                    $_FILES,
                    C( 'PICTURE_UPLOAD' ),
                    C( 'PICTURE_UPLOAD_DRIVER' ),
                    C( 'UPLOAD_SITEIMG_OSS' ),
                    $url
                ); //TODO:上传到远程服务器

                /* 记录图片信息 */
                if ( $info ) {
                    $return['status'] = 1;
                    $return = array_merge( $info['thumb_img'], $return );

                    $liveData = array(
                        'name'         => $name,
                        'announcement' => $announcement,  //公告
                        'robot'        => $robot,
                        'thumb_img'    => $return['path'],
                        //'ctime'        => $_SERVER['REQUEST_TIME'],
                    );
                    $trainData = array(
                        'title'     => $name,
                        'remark'    => $announcement,
                        'title_img' => $return['path'],
                        'create_at' => $_SERVER['REQUEST_TIME'],
                    );

                    $where['roomid'] = $roomid;
                    $where['cid'] = $cid;
                    // 更新直播live表
                    $state = M('Live')->where($where)->save($liveData);
                    // 更新视频train表
                    if($state){
                        M('Train')->where('roomid='.$roomid)->save($trainData);
                        $this->redirect('index');
                    }else{
                        $this->error('修改聊天室信息错误');
                    }
                } else {
                    $return['status'] = 0;
                    $return['info']   = $upload->getError();
                    $this->error( $return['info'] );
                }
            }else{
                $liveData = array(
                    'name'         => $name,
                    'announcement' => $announcement,  //公告
                    'robot'        => $robot,
                    'ctime'        => $_SERVER['REQUEST_TIME'],
                );
                $trainData = array(
                    'title'     => $name,
                    'remark'    => $announcement,
                    'create_at' => $_SERVER['REQUEST_TIME'],
                );

                $where['roomid'] = $roomid;
                $where['cid'] = $cid;
                // 更新直播live表
                $state = M('Live')->where($where)->save($liveData);
                // 更新视频train表
                if($state){
                    M('Train')->where('roomid='.$roomid)->save($trainData);
                    $this->redirect('index');
                }else{
                    $this->error('修改聊天室信息错误');
                }
            }
        } else {
            $id = I('get.id');
            $getData = M('Live')->where('id='.$id)->find();
            $this->assign('data',$getData);
            $this->meta_title = '新建直播间';
            $this->display('update');
        }
    }

    //聊天室详情
    public function chatRoomDetail()
    {
        $id = I('get.id');
        $getData = M('Live')->where('id='.$id)->find();
        $this->assign('data',$getData);
        $this->meta_title = '直播间详情';
        $this->display('detail');
    }

    //结束暂停直播状态
    public function channelStop()
    {
        $chatApi = new ChatApi();
        $roomid = lx_decrypt(I('get.roomid'));
        $status = I('get.status');
        $live = M('Live');
        $where['roomid'] = $roomid;
        $state1 = M('Train')->where($where)->setField('status',$status);
        $state2 = $live->where($where)->setField('status',$status);

        $cid = $live->where($where)->getField('cid');
        if($status == 0){
            //结束直播
            $chatApi->channelPause($cid);
        }else{
            //恢复直播
            $chatApi->channelResume($cid);
        }
        if($state1 && $state2) {
            $this->redirect('index');
        }else{
            $this->error('结束直播失败!');
        }
    }

    //新建直播间时验证手机号
    public function verifyAccount()
    {
        $mobile = I('post.mobile');
        $where['mobile'] = $mobile;
        $accountId = M('Account')->where($where)->getField('id');
        $roomId = M('Live')->where($where)->getField('id');
        if (!is_string($accountId)) {
            $value = array('status'=>'0','msg'=>'请填写真实存在的用户手机号');
            echo json_encode($value);die;
        }

        if(!empty($roomId)) {
            $value = array('status'=>'0','msg'=>'该手机号已创建过直播间,请直接使用该直播间');
            echo json_encode($value);
        }else{
            $value = array('status'=>'1','msg'=>'该手机号可以使用');
            echo json_encode($value);
        }
    }

    public function verifyChannelExist()
    {
        $roomNumber = M('Train')->where('status=1 AND type=2')->field('roomid')->count();
        if ($roomNumber > 1) {
            $value = array('status'=>'0','msg'=>'直播间数量大于1');
            echo json_encode($value);
        }
    }

    //直播精选推荐
    public function channelRecommend() {
        $channel_id = lx_decrypt(I('get.channel_id'));
        $status = I('get.is_recommend');
        if($status == 1){
            //p($train);exit;
            //推荐到精选列表
            $live = M('Live')->where('id='.$channel_id)->find();
            $recommendData = array(
                'channel_id' => $channel_id,                       //直播id
                'type'     => 4,                                   //类别 1资讯 2视频 3活动 4直播
                'title'    => $live['name'],
                'content'  => $live['announcement'],
                'video_img'=> $live['thumb_img'],                  //封面图片url
                'thumb_img'=> $live['thumb_img'],                  //封面图片url
                'status'   => 1,                                   //状态 开启状态：1是、0否
                'author'   => $_SESSION['username'],               //创建者
                'tag_major'=> implode(',', I('post.major_id')),    //专业领域
                'is_stick' => 2,                                   //是否置顶：1是、2否
                'push_date'=> time(),                              //推送到精选时间
                'is_private'=> $live['is_private'],                 //是否私密
                'object_id' => $live['id'],
                'is_cost'   => $live['is_money'],
                'amount'    => $live['amount']
            );
            if (M('Recommend')->add($recommendData)) {
                M('Live')->where('id='.$channel_id)->setField('is_recommend',$status);
                $this->redirect('index');
            } else {
                $this->error('推荐到精选失败!');
            }
        }else{
            $where['channel_id'] = $channel_id;
            if(M('Recommend')->where($where)->delete()) {
                M('Live')->where('id='.$channel_id)->setField('is_recommend',$status);
                $this->redirect('index');
            } else {
                $this->error('取消推荐失败!');
            }
        }

    }

    /**
     * 取消首页banner推荐,直播推荐到首页banner图
     * @return [type] [description]
     */
    public function cancelPush() {
        $id     = lx_decrypt(I( 'get.id'));
        $is_push = lx_decrypt(I('get.is_push')); //是否推送至首页：1是、0否

        $state = M('Live')->where('id='.$id)->setField('is_push',$is_push);
        if ($state) {
            if ( M('Push')->where('cid='.$id)->delete()) {
                $this->redirect('index');
            } else {
                $this->error('更新失败');
            }
        } else {
            $this->error( '更新失败');
        }
    }

    //删除一个直播频道
     public function channelDelete()
     {
         $cid = I('get.cid');
         $ChatApi = new ChatApi();
         $state = $ChatApi->channelDelete($cid);
         $where['cid'] = $cid;
         $deleteState = M('Live')->where($where)->delete();
         if ($deleteState) {
             $this->redirect('index');
         } else {
             $this->error('删除失败');
         }
     }

    /*
     * 直播室列表,获取聊天室信息,发送自定义消息
     */
    //public function test() {
        //$chatApi = new ChatApi();
        //$info = $chatApi->channelList($records = 30, $pnum = 1, $ofield = 'ctime', $sort = 0);
        //$info = $chatApi->getChatRoom('7814727');
        //$info = $chatApi->channelStats('b29940460c9f404f8c02c4fc1ef73ac5');
        //$info = $this->sendCustomMsg('18710105752', '3731468', '8056776', '法务茶话会', '描述描述');
        //p($info);
    //}

    //删除直播间
    //public function delete() {
    //    $chatApi = new ChatApi();
    //    $info = $chatApi->channelDelete('b29940460c9f404f8c02c4fc1ef73ac5');
    //    p($info);
    //}

     //重新获取拉流地址
     //public function getList() {
     //    $chatApi = new ChatApi();
     //    $info = $chatApi->channelRefreshAddr('4fbac33190bb49a0a127f0e306380c01');
     //    p($info);
     //}

}
