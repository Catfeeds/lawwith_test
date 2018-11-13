<?php
/**
 * 
 * @authors baby s:) (811800545@qq.com)
 * @date    2017-02-18 00:04:18
 * @version $Id$
 */

namespace Admin\Controller;

use Common\Api\Category;
use Common\Api\JPush;
use Think\Controller;

class PushController extends AdminController {

    /**
     * 首页banner推荐列表
     * @return [type] [description]
     */
    public function index()
    {
        //$model1 = M( 'Resource' );
        //$model2 = M( 'Activity' );
        //$model3 = M( 'Train' );
        //$model4 = M( 'Laws' );
        //$model5 = M( 'Account' );
        //
        ////推荐话题,在帖子表里资讯type为3,话题为1,求助为2
        //$this->data1 = $model1->where( 'is_push=1 AND type=3' )->order( 'push_date desc' )->field( 'id,title,is_push,push_date,type' )->select();
        ////推荐求助
        //$this->data2 = $model1->where( 'is_push=1 AND type=2' )->order( 'push_date desc' )->field( 'id,title,is_push,push_date,type' )->select();
        ////推荐活动
        //$this->data3 = $model2->where( 'is_push=1 AND status=1' )->order( 'push_date desc' )->field( 'id,title,is_push,push_date' )->select();
        ////推荐视频
        //$this->data4 = $model3->where( 'is_push=1 or push_list=1' )->order( 'push_date desc' )->field( 'id,title,is_push,push_list,push_date' )->select();
        ////推荐律所
        //$this->data5 = $model4->where( 'is_push=1' )->order( 'push_date desc' )->field( 'id,law_name,is_push,push_date' )->select();
        ////推荐用户
        //$this->data6 = $model5->where( 'is_push=1' )->order( 'push_date desc' )->field( 'id,type,uname,is_push,push_date' )->select();

        $data = M('Push')->where('mark=1')->select();
        $this->assign('data',$data);
        $this->meta_title = '首页banner推荐列表';
        $this->display();
    }

    /**
     * banner推荐
     * @return [type] [description]
     */
    public function addPush()
    {
        $id   = lx_decrypt( I( 'get.id' ) ); //内容id
        $type = lx_decrypt( I( 'get.t' ) ); //推送类别 1.资讯 2.求助 3.活动 4.视频 5.律所 6.用户 7.直播
        switch ( $type ) {
            case 1:
                $data = D( 'ResourceRelation' )->relation( true )->where('id='.$id)->find();
                $this->p_type = 1;
                $type_title   = '资讯';
                break;
            case 2:
                $data = D( 'ResourceRelation' )->relation( true )->where('id='.$id)->find();
                $this->p_type = 2;
                $type_title   = '求助';
                break;
            case 3:
                $data = D( 'ActivityRelation' )->relation( true )->where('id='.$id)->find();
                $this->p_type = 3;
                $type_title   = '活动';
                break;
            case 4:
                $data = D( 'TrainRelation' )->relation( true )->where('id='.$id)->find();
                $this->p_type = 4;
                $type_title = '视频';
                break;
            case 5:
                $data = M( 'Laws' )->where('id='.$id)->find();
                $this->p_type = 5;
                $type_title = '律所';
                break;
            case 6:
                $data  = M( 'Account' )->where('id='.$id )->find();
                $this->p_type = 6;
                $type_title   = '用户';
                break;
            case 7:
                $data  = M( 'Live' )->where('id='.$id )->find();
                $this->p_type = 7;
                $type_title   = '直播';
                break;
            default:
                $type_title = '详情';
                break;
        }

        $majors = M( 'Major' )->where('status=1')->select();
        $this->assign( 'major', $majors);
        $this->assign( 'content', $data);
        $this->assign( 'mark', 1 );
        $this->meta_title = '推荐' . $type_title;
        $this->display('Push/push');
    }

    /**
     * banner下架
     */
    public function deleteBanner()
    {
        $bannerId = I('get.id');
        if (empty($bannerId)) {
            $this->error('删除失败：id为空');exit();
        }
        $res = M('Push')->where('id='.$bannerId)->delete();

        if($res){
            $bannerInfo = M('Push')->where('id='.$bannerId)->getField('cid,c_type');

            switch ($bannerInfo['c_type']) {
                case 1:
                    M('Resource')->where('id='.$bannerInfo['cid'])->setField('is_push',0);
                    break;
                case 2:
                    M('Resource')->where('id='.$bannerInfo['cid'])->setField('is_push',0);
                    break;
                case 3:
                    M('Activity')->where('id='.$bannerInfo['cid'])->setField('is_push',0);
                    break;
                case 4:
                    M('Train')->where('id='.$bannerInfo['cid'])->setField('is_push',0);
                    break;
                case 5:
                    M('Laws')->where('id='.$bannerInfo['cid'])->setField('is_push',0);
                    break;
                case 6:
                    M('Account')->where('id='.$bannerInfo['cid'])->setField('is_push',0);
                    break;
            }
            $this->redirect('Push/index');
        }else{
            $this->error('操作失败');
        }
    }

    /**
     * 推荐到banner轮播提交
     * @return [type] [description]
     */
    public function subPush() {
        $p_type   = I( 'post.p_type' ); //推荐对象  推送类别 1.帖子 2.求助 3.活动 4.视频 5.律所 6.用户 7.直播
        $p_id     = I( 'post.res_id');   //对象id
        $p_sort   = I( 'post.sort');   //排序
        $p_banner = I( 'post.banner_img' );   //已存在的banner图
        $p_author = intval( I( 'post.author' ) ); //作者id
        $mark     = I( 'post.mark' );     //视频推送方式 1推送首页 2推送列表
        if ( !empty($_FILES['banner_img']['name']) ) {
            $return = array( 'status' => 1, 'info' => '上传成功' );
            $upload = D( 'Picture');
            C( 'PICTURE_UPLOAD.maxSize', 1 * 1024 * 1024 );   //上传的文件大小限制 (0-不做限制)
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
                $return = array_merge( $info['banner_img'], $return );
                //p($return['path']);die;
            } else {
                $return['status'] = 0;
                $return['info']   = $upload->getError();
                $this->error( $return['info'] );
            }
            //更新数据
            $data  = array(
                'title_img' => $p_banner,
                'is_push'   => 1,
                'push_date' => time()
            );
            $data2 = array(
                'cid'       => $p_id,
                'banner'    => $return['path'],
                'c_type'    => $p_type,
                'sort'      => $p_sort,
                'mark'      => $mark,
                'push_time' => time()
            );
        } else {
            //更新数据
            $data  = array(
                'title_img' => $return['path'],
                'is_push'   => 1,
                'push_date' => time()
            );
            $data2 = array(
                'cid'       => $p_id,
                'banner'    => $p_banner,
                'c_type'    => $p_type,
                'sort'      => $p_sort,
                'mark'      => $mark,
                'push_time' => time()
            );
        }
        if ( $p_author != 0 && ! is_string( $p_author ) ) {
            $data2['author'] = $p_author;
        }
        switch ( $p_type ) {
            case 1:
                if ( M( 'Resource' )->where( 'id='.$p_id )->save( $data ) ) {
                    $res = M( 'Push' )->where( 'c_type=1 AND cid='.$p_id )->count();
                    if ( $res ) {
                        M( 'Push' )->where( 'c_type=1 AND cid='.$p_id )->save( $data2 );
                    } else {
                        M( 'Push' )->add( $data2 );
                    }
                    $this->redirect( 'News/index' );
                } else {
                    $this->error( '更新数据失败' );
                }
                break;
            case 2:
                if ( M( 'Resource' )->where( 'id='.$p_id )->save( $data ) ) {
                    $res = M( 'Push' )->where( 'c_type=2 AND cid='.$p_id )->count();
                    if ( $res ) {
                        M( 'Push' )->where( 'c_type=2 AND cid='.$p_id )->save( $data2 );
                    } else {
                        M( 'Push' )->add( $data2 );
                    }
                    $this->redirect( 'Help/index' );
                } else {
                    $this->error( '更新数据失败' );
                }
                break;
            case 3:
                if ( M( 'Activity' )->where( 'id='.$p_id )->save( $data ) ) {
                    $res = M( 'Push' )->where( 'c_type=3 AND cid='.$p_id )->count();
                    if ( $res ) {
                        M( 'Push' )->where( 'c_type=3 AND cid='.$p_id )->save( $data2 );
                    } else {
                        M( 'Push' )->add( $data2 );
                    }
                    $this->redirect( 'Activity/index' );
                } else {
                    $this->error( '更新数据失败' );
                }
                break;
            case 4:
                if ( $mark == 2 ) {
                    $data = array(
                        //'title_img' => $return['path'],
                        'push_list' => 1,
                        'push_date' => time()
                    );
                }
                if ( M( 'Train' )->where( 'id=' . $p_id )->save( $data ) ) {
                    $where = array(
                        'c_type' => 4,
                        'cid'    => $p_id,
                        'mark'   => $mark,
                    );
                    $res   = M( 'Push' )->where( $where )->count();
                    if ( $res ) {
                        M( 'Push' )->where( $where )->save( $data2 );
                    } else {
                        M( 'Push' )->add( $data2 );
                    }
                    $this->redirect( 'Train/index' );
                } else {
                    $this->error( '更新数据失败' );
                }
                break;
            case 5:
                if ( M( 'Laws' )->where( 'id='.$p_id )->save( $data ) ) {
                    $res = M( 'Push' )->where( 'c_type=5 AND cid='.$p_id )->count();
                    if ( $res ) {
                        M( 'Push' )->where( 'c_type=5 AND cid='.$p_id )->save( $data2 );
                    } else {
                        M( 'Push' )->add( $data2 );
                    }
                    $this->redirect( 'Laws/index' );
                } else {
                    $this->error( '更新数据失败' );
                }
                break;
            case 6:
                if ( M( 'Account' )->where( 'id='.$p_id )->save( $data ) ) {
                    $res = M( 'Push' )->where( 'c_type=6 AND cid='.$p_id )->count();
                    if ( $res ) {
                        M( 'Push' )->where( 'c_type=6 AND cid='.$p_id )->save( $data2 );
                    } else {
                        M( 'Push' )->add( $data2 );
                    }
                    $this->redirect( 'User/index' );
                } else {
                    $this->error( '更新数据失败' );
                }
                break;
            case 7:
                if ( M( 'Live' )->where( 'id='.$p_id )->save(array('is_push'=>1)) ) {
                    $res = M( 'Push' )->where( 'c_type=7 AND cid='.$p_id )->count();
                    if ( $res ) {
                        M('Push')->where( 'c_type=7 AND cid='.$p_id )->save( $data2 );
                    } else {
                        M('Push')->add( $data2 );
                    }
                    $this->redirect('Live/index');
                } else {
                    $this->error( '更新数据失败' );
                }
                break;
            default:
                $this->error( '数据错误' );
                break;
        }
    }

    //系统消息
    public function sys_msg() {
        $model = M( 'sys_messages' );
        $count = $model->count(); //查询总记录数
        $Page  = new \Think\Page( $count, 20 ); // 实例化分页类 传入总记录数和每页显示的记录数(20)
        $Page->setConfig( 'header', '共%TOTAL_ROW%条' );
        $Page->setConfig( 'first', '首页' );
        $Page->setConfig( 'last', '共%TOTAL_PAGE%页' );
        $Page->setConfig( 'prev', '上一页' );
        $Page->setConfig( 'next', '下一页' );
        $Page->setConfig( 'link', 'indexpagenumb' );//pagenumb 会替换成页码
        $Page->setConfig( 'theme', '%HEADER% %FIRST% %UP_PAGE% %LINK_PAGE% %DOWN_PAGE% %END%' );
        $show  = $Page->show();
        $lists = $model
            ->order( 'send_time desc' )
            ->limit( $Page->firstRow . ',' . $Page->listRows )
            ->select();
        $this->assign( 'data', $lists );
        $this->assign( 'page', $show );
        $this->meta_title = '系统消息';
        $this->display();
    }

    //系统消息
    public function add_msg() {
        $this->meta_title = '发布系统消息';
        $this->display();
    }


    //发送系统消息
    public function send_msg ()
    {
        $pushObj =new Jpush(C('JPUSH_APPKEY'),C('JPUSH_SECRET'));
        $title = I('post.title');   //消息标题
        $content = I('post.content');   //消息内容
        $type = 'all';  //推送范围
        $alias = 'all';
        $data = array(
            'title' => $title,
            'content' => $content,
            //'push_obj' => $type,
            'send_time' => time()
        );

        if($mid = M('Sys_messages')->add($data)){
            $m_txt = array(
                'type' => 0,
                'cid' => $mid
            );
            $res = $pushObj->push($type,$alias,$content,$m_type='http',$m_txt);

            if($res){
                $res_arr = json_decode($res, true);
                if(isset($res_arr['error'])){                       //如果返回了error则证明失败
                    M('Sys_messages')->where('id='.$mid)->delete();
                    $this->error('发送失败');
                }else{
                    $uids = M('Account')->field('id')->select();
                    foreach($uids as $k => $v){
                        $data = array('msgid' => $mid, 'uid' => $v['id']);
                        M('Message')->add($data);
                    }
                    $this->success('发送成功',U('Push/sys_msg'));
                }
            }else{      //接口调用失败或无响应
                M('Sys_messages')->where('id='.$mid)->delete();
                $this->error('接口调用失败或无响应');
            }
        }
    }


    //删除回复
    public function del_comment() {
        $type   = lx_decrypt( I( 'get.t' ) );
        $cid    = lx_decrypt( I( 'get.cid' ) );
        $model1 = M( 'resource_comment' );
        $model2 = M( 'activity_comment' );
        $model3 = M( 'train_comment' );
        switch ( $type ) {
            case 1:
                if ( $model1->where( 'id='.$cid )->delete() ) {
                    M( 'comm_like' )->where( 'comm_id='.$cid )->delete();
                    M( 'comm_dislike' )->where( 'comm_id='.$cid )->delete();
                    $this->success( '删除成功' );
                } else {
                    $this->error( '删除失败' );
                }
                break;
            case 2:
                if ( $model2->where( 'id='.$cid )->delete() ) {
                    $this->success( '删除成功' );
                } else {
                    $this->error( '删除失败' );
                }
                break;
            case 3:
                if ( $model3->where( 'id='.$cid )->delete() ) {
                    $this->success( '删除成功' );
                } else {
                    $this->error( '删除失败' );
                }
                break;
            default:
                $this->error( '删除失败' );
                break;
        }
    }

}