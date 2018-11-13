<?php
/**
 * Created by PhpStorm.
 * User: Mrx
 * Date: 2016/4/22
 * Time: 17:23
 */

namespace Admin\Controller;
use Admin\Model\PictureModel;

class FileController extends AdminController
{
    /**
     * 上传图片
     * @author huajie <banhuajie@163.com>
     */
    public function uploadPicture(){


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

        /* 返回JSON数据 */
        $this->ajaxReturn($return);
    }

}