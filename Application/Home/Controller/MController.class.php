<?php
/**
 * Created by PhpStorm.
 * User: Mrx
 * Date: 2016/7/27
 * Time: 20:24
 */

namespace Home\Controller;

use Think\Controller;
use Common\Api\Category;

class MController extends BaseController {

    /**
     * 用户中心首页
     */
    public function index() {
        $uid=$_SESSION['app_id'];
        $where = array(
            'type' => 3,
            'status' => 1,
            'author' => $uid
        );
        $resource = M('Resource');
        $data = $resource
            ->where($where)
            ->order('send_time desc')
            ->limit(8)
            ->field('id,title')
            ->select();
        $counts = $resource->where($where)->count();
        $total_views = $resource->where($where)->sum('views');
        //$comment_sums = M('Resource_comment')->where($where)->
        $this->assign('article_list',$data);
        $this->assign('total_views',$total_views);
        $this->assign('count',$counts);
        $this->meta_title='资讯发布--律携APP';
        $this->display();
    }


    /**
     * 文章列表
     */
    public function article() {
        $uid=$_SESSION['app_id'];
        $where = array(
            'type' => 3,
            'status' => 1,
            'author' => $uid
        );
        $resource = M('Resource');
        $count = $resource->where($where)->field('id')->count(); //查询总记录数
        $Page  = new \Think\Page( $count,12);// 实例化分页类 传入总记录数和每页显示的记录数(25)
        $Page->setConfig( 'header', '共%TOTAL_ROW%条' );
        $Page->setConfig( 'first', '首页' );
        $Page->setConfig( 'last', '共%TOTAL_PAGE%页' );
        $Page->setConfig( 'prev', '上一页' );
        $Page->setConfig( 'next', '下一页' );
        $Page->setConfig( 'link', 'indexpagenumb' );//pagenumb 会替换成页码
        $Page->setConfig( 'theme', '%HEADER% %FIRST% %UP_PAGE% %LINK_PAGE% %DOWN_PAGE% %END%' );
        $show  = $Page->show();
        $data = $resource
            ->where($where)
            ->order('send_time desc')
            ->limit(8)
            ->field('id,title,send_time')
            ->limit($Page->firstRow.','.$Page->listRows )
            ->select();
        $this->assign('article_list',$data);
        $this->assign('page',$show);
        $this->meta_title='资讯发布--律携APP';
        $this->display();
    }

    //资讯图文新发布
    public function add() {
        $data = M('Major')
            ->where('status=1')
            ->order('sort')
            ->select();
        $this->major = Category::toLevel($data,"ㄴ"); //专业标签
        $this->meta_title='资讯发布--律携APP';
        $this->display();
    }

    public function sub_articleadd() {
        /* 返回标准数据 */
        $return = array( 'status' => 1, 'info' => '上传成功', 'data' => '' );
        /* 调用文件上传组件上传文件 */
        $Picture = D( 'Admin/Picture' );
        C( 'PICTURE_UPLOAD.maxSize', 500 * 1024 );   //上传的文件大小限制 (0-不做限制)
        C( 'PICTURE_UPLOAD.exts', 'jpg,gif,png,jpeg' );    //允许上传的文件后缀
        C( 'PICTURE_UPLOAD.savePath', 'Picture/' );   //设置上传子目录
        $url  = C( 'UPLOAD_SITEIMG_OSS.domain' );  //拼接url头
        $info = $Picture->upload(
            $_FILES,
            C( 'PICTURE_UPLOAD' ),
            C( 'PICTURE_UPLOAD_DRIVER' ),
            C( 'UPLOAD_SITEIMG_OSS' ),
            $url
        ); //TODO:上传到远程服务器

        /* 记录图片信息 */
        if ( $info ) {
            $return['status'] = 1;
            $return = array_merge( $info['title_img'], $return );
        } else {
            $return['status'] = 0;
            $return['info']   = $Picture->getError();
        }

        $data = array(
            'title'     => I('post.title'),
            'content'   => stripslashes(I('post.content')),
            'sort'      => I('post.sort'),
            'tag_major' => implode(',',I('post.major_id')),
            'title_img' => $return['path'],
            'send_time' => time(),
            'type'      => 3,
            'is_stick'  => 2,
            'author'    => $_SESSION['app_id'],
        );

        if (M('Resource')->add($data)){
            $this->redirect('article');
        }else {
            $this->error('添加失败');
        }
    }

    //资讯图文编辑
    public function edit() {

        if (IS_POST) {

            /* 返回标准数据 */
            $return = array( 'status' => 1, 'info' => '上传成功', 'data' => '' );
            /* 调用文件上传组件上传文件 */
            $Picture = D( 'Admin/Picture' );
            C( 'PICTURE_UPLOAD.maxSize', 500 * 1024 );   //上传的文件大小限制 (0-不做限制)
            C( 'PICTURE_UPLOAD.exts', 'jpg,gif,png,jpeg' );    //允许上传的文件后缀
            C( 'PICTURE_UPLOAD.savePath', 'Picture/' );   //设置上传子目录
            $url  = C( 'UPLOAD_SITEIMG_OSS.domain' );  //拼接url头
            $info = $Picture->upload(
                $_FILES,
                C( 'PICTURE_UPLOAD' ),
                C( 'PICTURE_UPLOAD_DRIVER' ),
                C( 'UPLOAD_SITEIMG_OSS' ),
                $url
            ); //TODO:上传到远程服务器

            /* 记录图片信息 */
            if ( $info ) {
                $return['status'] = 1;
                $return           = array_merge( $info['title_img'], $return );
            } else {
                $return['status'] = 0;
                $return['info']   = $Picture->getError();
            }

            $rid = $_POST['rid'];
            $data1 = array(
                'title'     => I('post.title'),
                'content'   => stripslashes(I('post.content')),
                'sort'      => I('post.sort'),
                'title_img' => $return['path'],
                'push_date' => $_SERVER['REQUEST_TIME']
            );

            if (M('Resource')->where('id='.$rid)->save($data1)) {

                $data2 = array(
                    'title'     => I('post.title'),
                    'content'   => stripslashes(I('post.content')),
                    'sort'      => I('post.sort'),
                    'thumb_img' => $return['path'],
                    'push_date' => $_SERVER['REQUEST_TIME']
                );
                M('Recommend')->where('news_id='.$rid)->save($data2);
                $this->redirect('article');

            } else {
                $this->error('修改失败');
            }

        } else {
            $major = M('Major')->where('status=1')->order('sort')->select();
            $article = M('Resource')->where('id='.lx_decrypt(I('get.id')))->field('id,title,content,sort,tag_major,imgs,author,title_img')->find();
            $this->assign('article',$article);
            $this->content = htmlspecialchars_decode($article['content']);
            $this->major = Category::toLevel($major,"ㄴ"); //专业标签
            $this->meta_title='资讯编辑--律携APP';
            $this->display();
        }
    }


    //用户删除文章
    public function delete() {
        $id = lx_decrypt(I('get.id'));

        if (!empty($id)) {
            $state = M('Resource')->where('id='.$id)->delete();

            if ($state) {
                $this->redirect('article');
            } else {
                $this->error('删除失败');
            }
        } else {
            $this->redirect('article');
        }
    }


    /**
     * 上传图片
     * @author huajie <banhuajie@163.com>
     */
    public function uploadPicture(){
        //TODO: 用户登录检测

        /* 返回标准数据 */
        $return  = array('status' => 1, 'info' => '上传成功', 'data' => '');
        /* 调用文件上传组件上传文件 */
        $Picture = D('Admin/Picture');
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
            //p($return);
        } else {
            $return['status'] = 0;
            $return['info']   = $Picture->getError();
        }

        /* 返回JSON数据 */
        $this->ajaxReturn($return);
    }
}