<?php
/**
 * Created by PhpStorm.
 * User: Mrx
 * Date: 2016/4/5
 * Time: 9:44
 */

namespace Admin\Controller;

use Admin\Model\PictureModel;
use Common\Api\MCrypt;
use Think\Model;

class AdController extends AdminController
{

    //友情链接
    public function index() {
        $model = D('AdRelation');
        $count = $model->count(); //查询总记录数
        $Page  = new \Think\Page($count, 20); // 实例化分页类 传入总记录数和每页显示的记录数(20)
        $Page->setConfig('header', '共%TOTAL_ROW%条');
        $Page->setConfig('first', '首页');
        $Page->setConfig('last', '共%TOTAL_PAGE%页');
        $Page->setConfig('prev', '上一页');
        $Page->setConfig('next', '下一页');
        $Page->setConfig('link', 'indexpagenumb');//pagenumb 会替换成页码
        $Page->setConfig('theme', '%HEADER% %FIRST% %UP_PAGE% %LINK_PAGE% %DOWN_PAGE% %END%');
        $show  = $Page->show();
        $lists = $model
            ->relation(true)
            ->limit($Page->firstRow . ',' . $Page->listRows)
            ->select();
        $this->assign('data', $lists);
        $this->assign('page', $show);
        $this->meta_title = '常用网站';
        $this->display();
    }


    //添加链接
    public function addUrl() {
        $this->meta_title = '添加链接';
        $this->display();
    }


    //提交添加数据
    public function upData() {
        $mode = M('Ad');
        $data = array(
            'title'     => I('post.title'),
            'url'       => I('post.url'),
            'create_at' => NOW_TIME,
        );
        if($mode->add($data)) {
            $this->redirect('index');
        } else {
            $this->error('添加数据失败');
        }
    }


    //修改链接
    public function editUrl() {
        $id               = lx_decrypt(I('get.aid'));
        $this->data       = M('Ad')->where('id=' . $id)->find();
        $this->meta_title = '修改链接';
        $this->display();
    }


    //提交修改数据
    public function sub_edit() {
        $id = I('post.aid');
        $_FILES['up_img']['name'];

        $data = array('url' => I('post.url'), 'title' => I('post.title'));
        if(M('Ad')->where('id=' . $id)->save($data)) {
            $this->redirect('index');
        } else {
            $this->error('数据更新失败');
        }
    }


    //删除常用网站
    public function delUrl() {
        $id = lx_decrypt(I('get.aid'));
        if(M('Ad')->where('id=' . $id)->delete()) {
            $this->redirect('index');
        } else {
            $this->error('删除失败');
        }
    }


    //删除多条数据
    public function delUrls() {
        $law_id    = $_POST['url_id'];
        $law_idarr = explode(',', $law_id);
        foreach($law_idarr as $lid) {
            M('Ad')->where('id=' . $lid)->delete();
        }
        $data = array(
            'state' => 1,
            'msg'   => '操作成功',
        );
        echo json_encode($data);

        return true;
    }


    //增加新版本APP
    public function addVersion() {
        if($_POST) {
            $versionName = I('post.version_name');
            $versionCode = I('post.version_code');
            $description = urlencode(I('post.description'));
            $downloadUrl = urlencode(I('post.download_url'));
            $data        = [
                'version_name' => $versionName,
                'version_code' => $versionCode,
                'description'  => $description,
                'download_url' => $downloadUrl,
                'create_date'  => (string)$_SERVER["REQUEST_TIME"]
            ];

            file_put_contents('version_refresh.json', json_encode($data)) ? $this->redirect('versionRefresh') : $this->error();
        } else {
            $this->meta_title = '版本更新提示设置';
            $this->display();
        }
    }


    //版本更新提示
    public function versionRefresh() {
        $result               = file_get_contents('version_refresh.json');
        $data                 = json_decode($result, true);
        $data['description']  = urldecode($data['description']);
        $data['download_url'] = urldecode($data['download_url']);

        $this->assign('meta_title', '版本更新提示');
        $this->assign('data', $data);
        $this->display('versionlist');
    }
}