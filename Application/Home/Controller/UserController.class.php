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
class UserController extends BaseController
{
    public function index() {
        $uid=3;
        $where = array(
            //'type' => 3,
            'status' => 1,
            'author' => $uid,
        );
        $data = M('Resource')->where($where)->order('send_time desc')->field('id,title')->select();
        $this->assign('resource_list',$data);
        $this->meta_title='律携APP--资讯发布';
        $this->display();
    }

    //发布帖子
    public function article_edit ()
    {
        $this->city = M('city')->where('type=1')->order('citySort')->select(); //城市标签
        $data = M('Major')->where('status=1')->order('sort')->select();
        $this->major = Category::toLevel($data,"ㄴ"); //专业标签
        $this->meta_title='律携APP--资讯发布';
        $this->display();
    }

    public function update_data ()
    {
        if(!empty(I('post.icon'))){
            $imgs_arr = array_filter(I('post.icon'));
            if(count($imgs_arr)>4){
                $res = array_slice($imgs_arr,0,4);
                $imgs = implode(',',$res);
            }else {
                $imgs=implode(',',I('post.icon'));
            }
        }

        $data = array(
            'title' => I('post.title'),
            'tag_city' => implode(',',I('post.city_id')),
            'tag_major' => implode(',',I('post.major_id')),
            'content' => strip_tags(htmlspecialchars_decode(I('post.content'))),
            'imgs' => $imgs,
            'send_time' => time(),
            'type' => 1,
            'is_stick' => 2,
            'author' => I('post.author'),
        );

        p($data);die;
        if (M('Resource')->add($data)){
            $this->success('发布成功');
        }else {
            $this->error('添加失败');
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
//            p($return);
        } else {
            $return['status'] = 0;
            $return['info']   = $Picture->getError();
        }

        /* 返回JSON数据 */
        $this->ajaxReturn($return);
    }
}