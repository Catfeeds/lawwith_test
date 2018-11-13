<?php
/**
 * Created by PhpStorm.
 * User: Mrx
 * Date: 2016/4/6
 * Time: 12:31
 */

namespace Admin\Controller;

use Common\Api\UploadImg;
use Common\Api\Category;
use Common\Api\letvCloud;

class VideoController extends AdminController
{
    public function _initialize() {
        $this->model = M('Train');
    }


    //视频管理
    public function index() {
        $nickname = I('nickname');
        $where['status'] = array('neq', 0);
        $where['title'] = array('like', '%' . (string)$nickname . '%');
        $where['type'] = 1;    //1视频 2直播
        $model = D('TrainRelation');
        $count = $model->relation(true)->where($where)->count();
        $Page = new \Think\Page($count, 10); // 实例化分页类 传入总记录数和每页显示的记录数(20)
        $Page->setConfig('header', '共%TOTAL_ROW%条');
        $Page->setConfig('first', '首页');
        $Page->setConfig('last', '共%TOTAL_PAGE%页');
        $Page->setConfig('prev', '上一页');
        $Page->setConfig('next', '下一页');
        $Page->setConfig('link', 'indexpagenumb');//pagenumb 会替换成页码
        $Page->setConfig('theme', '%HEADER% %FIRST% %UP_PAGE% %LINK_PAGE% %DOWN_PAGE% %END%');
        $show = $Page->show();
        $data = $model->relation(true)
                      //->join('train_cate on train.cate_id = train_cate.cate_id')
                      ->where($where)
                      ->field('remark', true)
                      ->order('create_at desc')
                      ->limit($Page->firstRow . ',' . $Page->listRows)
                      ->select();
        //p($data);
        $this->major_list = M('Major')->where('status=1')->order('sort')->select();
//        dump($data); exit;
        $this->assign('data', $data);
        $this->assign('page', $show);
        $this->meta_title = '视频管理';
        $this->display();
    }


    //添加视频
    public function create() {
        $major = M('Major')->where('status=1')->order('sort')->select();
        $account = M('Account')->where('status=1')->field('id,uname')->select();
        $this->major_tree = Category::toLevel($major, "ㄴ"); //专业列表
        $this->cate_tree
            = M('Train_cate')->where(['status' => '1'])->order('sort')->field('cate_id,cate_name')->select();
        $this->account_list = Category::toLevel($account, "ㄴ"); //专业列表
        $this->meta_title = '添加视频';
        $this->display();
    }


    //提交添加
    public function submitCreate() {
        $video_id = $_SESSION['video_sid'];
        if(IS_POST) {
            $return = array('status' => 1, 'info' => '上传成功');
            $upload = D('Picture');
            C('PICTURE_UPLOAD.maxSize', 2 * 1024 * 1024);   //上传的文件大小限制 (0-不做限制)
            C('PICTURE_UPLOAD.exts', 'jpg,gif,png,jpeg');    //允许上传的文件后缀
            C('PICTURE_UPLOAD.savePath', 'Picture/');   //设置上传子目录
            $url = C('UPLOAD_SITEIMG_OSS.domain');  //拼接url头
            $info = $upload->upload(
                $_FILES,
                C('PICTURE_UPLOAD'),
                C('PICTURE_UPLOAD_DRIVER'),
                C('UPLOAD_SITEIMG_OSS'),
                $url
            ); //TODO:上传到远程服务器

            /* 记录图片信息 */
            if($info) {
                $return['status'] = 1;
                $return = array_merge($info['imgs'], $return);
                //p($return['path']);die;
            } else {
                $return['status'] = 0;
                $return['info'] = $upload->getError();
                $this->error($return['info']);
            }

            $data = array(
                'title'        => I('post.title'),
                'title_img'    => $return['path'],
                'video_id'     => $video_id,
                'cate_id'      => I('post.cate_id'),
                'tags'         => I('post.major_id'),
                'remark'       => I('post.describe'),
                'amount'       => I('post.amount'),
                'author'       => session('username'),
                'speaker'      => I('post.speaker'),
                'speaker_info' => I('post.speaker_info'),
                'is_push'      => I('post.is_push'),
                'push_list'    => I('post.push_list'),
                'create_at'    => time(),
                'is_money'    => empty(I('post.amount')) ? 0 : 1,
                'amount'      => I('post.amount')
            );

            if($this->model->add($data)) {
                $this->redirect('index');
            } else {
                $this->error('添加数据失败');
            }
        }
    }


    //查看详情
    public function view() {
        //初始化播放器
        $uu = "dwbppqvkxs"; //用户唯一标识码   dwbppqvkxs
        $pu = "a2ee3b5de4"; //播放器唯一标识码  a2ee3b5de4
        $type = 'js';  //接口类型
        $auto_play = 0; //是否自动播放
        $width = 500;  //播放器宽度
        $height = 300; //播放器高度
        $size = '160_120'; //截图尺寸，每种尺寸各有8张图。 有以下尺寸供选择：100_100、200_200、300_300、120_90、128_96
        $letv = new LetvCloud;

        $id = I('get.vid');
        $model = D('TrainRelation');
        $res = $model->relation(true)->where('id=' . $id)->select();
        $data = $res[0];
        $data['video_path']
            = $letv->videoGetPlayinterface($uu, video_info($data['video_id'], 'video_unique'), $type, $pu, $auto_play, $width, $height);
        $imgs_str = $letv->imageGet(video_info($data['video_id'], 'video_id'), $size);
        $imgs_arr = json_decode($imgs_str, true);
        $data['imgs_cap'] = $imgs_arr['data'];
        $data1 = M('Major')->where('status=1')->order('sort')->select();
        $this->major = Category::toLevel($data1, "ㄴ"); //专业标签
//        dump($data); exit;
        $this->assign('info', $data);
        $this->meta_title = '查看详情';
        $this->display();
    }


    //视频编辑
    public function update() {
        $id = I('vid');
        $video = $this->model->where('id=' . $id)->find();
        $major = M('Major')->where('status=1')->select();
        $this->major_tree = Category::toLevel($major, "ㄴ"); //专业列表
        $this->assign('data', $video);
        $this->meta_title = '编辑视频';
        $this->display();
    }


    //提交编辑
    public function submitUpdate() {
        $id = I('post.vid');
        $path = I('post.path');
        $data = array(
            'path'      => $path,
            'title'     => I('post.title'),
            'tags'      => implode(',', I('post.major_id')),
            'remark'    => I('post.describe'),
            'author'    => session('username'),
            'create_at' => time()
        );
        if($this->model->where('id=' . $id)->save($data)) {
            $this->redirect('index');
        } else {
            $this->error('编辑信息失败');
        }
    }


    //删除视频
    public function delete() {
        if($this->model->where('id=' . I('get.vid'))->delete()) {
            $this->success('删除视频成功');
        } else {
            $this->error('删除视频失败');
        }
    }


    //上传视频初始化
    public function uploadInit() {
        $letv = new letvCloud;
        $video_name = trim(I('get.video_name'));

        if(empty($video_name)) {
            $video_name = '律携视频';
        }

        $client_ip = get_client_ip($type = 0);
        $file_size = isset($_GET['file_size']) ? intval($_GET['file_size']) : 0;
        $uploadtype = isset($_GET['uploadtype']) ? intval($_GET['uploadtype']) : 0;

        if(isset($_GET['token']) && !empty(trim($_GET['token']))) {
            $token = trim($_GET['token']);
            echo $letv_info = $letv->videoUploadResume($token, $uploadtype); //视频文件断点续传
        } else {
            echo $letv_info = $letv->videoUploadInit($video_name, $client_ip, $file_size, $uploadtype); //视频上传
        }

        $video_data = (json_decode($letv_info, true)); //转换上传视频返回的json数据为数组格式
        $video_data['data']['upload_time'] = time(); //插入数据库，添加时间字段

        if($video_data['code'] == 0) { //判断返回数据的状态码是否为成功，并插入数据库
            $id = M('Video')->add($video_data['data']);
            session('video_sid', $id); //保存数据id到session
        }
    }
    

    //视频分类管理
    public function category() {
        $cate = M('train_cate')->select();
        $this->assign('data', $cate);
        $this->meta_title = '视频分类管理';
        $this->display();
    }


    //添加视频分类
    public function categoryCreate() {
        if(!empty($_POST)) {
            $cate['cate_name'] = I('post.cate_name');
            $cate['sort'] = I('post.sort');
            $cate['status'] = I('post.status') ? I('post.status') : '0';
            $cate['author'] = session(C('USER_AUTH_KEY'));
            $cate['create_time'] = $_SERVER["REQUEST_TIME"];
            if(M('train_cate')->add($cate)) {
                $this->redirect('video/category');
            } else {
                $this->error('添加失败');
            }
        }
        $this->meta_title = '添加视频分类';
        $this->display();
    }


    //更新视频分类
    public function categoryUpdate() {
        $cate_id = I('get.cate_id');
        if(!empty($_POST)) {
            $cate['cate_id'] = I('post.cate_id');
            $cate['cate_name'] = I('post.cate_name');
            $cate['sort'] = I('post.sort');
            $cate['status'] = I('post.status') ? I('post.status') : '0';
            $cate['author'] = session(C('USER_AUTH_KEY'));
            if(M('train_cate')->where(['cate_id' => $cate['cate_id']])->save($cate)) {
                $this->redirect('video/category');
            } else {
                $this->error('添加失败');
            }
        } else {
            $cate = M('train_cate')->where(['cate_id' => $cate_id])->find();
            $this->assign('cate', $cate);
            $this->meta_title = '更新视频分类';
            $this->display();
        }
    }


    //删除视频分类
    public function categoryDelete() {
        $cate_id = I('post.cate_id');
        if(M('train')->where(['cate_id' => $cate_id])->count()) {
            apiResponse(500, false, '不能删除有视频的栏目分类');
        } else {
            if(M('train_cate')->where(['cate_id' => $cate_id])->delete()) {
                apiResponse(200, true, '删除成功');
            } else {
                apiResponse(500, false, '删除失败,请重试');
            }
        }
    }


    //更新分类状态
    public function updateCategoryStatus() {
        $cate_id = I('get.cate_id');
        $status = I('get.status') ? '0' : '1';
        if(isset($status)) {
            if(M('train_cate')->where(['cate_id' => $cate_id])->setField('status', $status)) {
                $this->redirect('video/category');
            } else {
                $this->error('更新状体失败');
            }
        } else {
            $this->error('更新状体失败');
        }
    }


    /**
     * 取消首页banner推荐
     *
     * @return [type] [description]
     */
    public function cancelPush() {
        $id = lx_decrypt(I('get.id'));
        $is_push = lx_decrypt(I('get.is_push')); //是否推送至首页：1是、0否

        $state = $this->model->where('id=' . $id)->setField('is_push', $is_push);
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


    //推送列表页
    public function push_list() {
        $status = lx_decrypt(I('get.push'));
        $id = lx_decrypt(I('get.mid'));
        if($this->model->where('id=' . $id)->setField('push_list', $status)) {
            $this->redirect('Video/index');
        } else {
            $this->error('更新失败');
        }
    }


    //推送列表页轮播图
    public function banner_list() {
        $id = lx_decrypt(I('get.mid')); //视频id
        $data = D('TrainRelation')->relation(true)->where('id=' . $id)->find();
        $this->p_type = 4;
        $this->mark = 2;    //标识推送列表
        $type_title = '视频';
        $majors = M('Major')->where('status=1')->select();
        $this->assign('major', $majors);
        $this->assign('content', $data);
        $this->meta_title = '推荐' . $type_title;
        $this->display('Push/des_push');
    }


    //推送视频到精选列表
    public function recom_push() {
        $id = lx_decrypt(I('get.mid')); //视频详情页id
        $status = lx_decrypt(I('get.status')); //0取消精选推荐,1精选推荐
        if($status == 1) {
            $data = D('Train')->where('id=' . $id)->find();
            $recom_data = array(
                'train_id'  => $data['id'],   //视频id
                'type'      => 2,             //内容类别：1资讯 2视频 3活动
                'title'     => $data['title'],
                'content'   => $data['content'],
                'video_img' => $data['title_img'],
                'thumb_img' => $data['title_img'],
                'status'    => $data['status'],
                'author'    => $data['speaker_user']['id'],
                'is_admin'  => $data['author'],
                'push_date' => time(),
                'views'     => $data['views'],
                'object_id' => $data['id'],
                'is_cost'   => $data['is_money'],
                'amount'    => $data['amount']
            );
            if(M('Recommend')->add($recom_data)) {
                $this->model->where('id=' . $id)->setField('recom_push', $status);
                $this->redirect('index');
            } else {
                $this->error();
            }

        } else {
            $this->model->where('id=' . $id)->setField('recom_push', $status);
            M('Recommend')->where('train_id=' . $id)->delete();
            $this->redirect('index');
        }
    }


    //更改多条数据状态
    public function delTrains() {
        $law_id = $_POST['train_id'];
        $law_idarr = explode(',', $law_id);
        foreach($law_idarr as $lid) {
            $this->model->where('id=' . $lid)->setField('status', 0);
        }
        $data = array(
            'state' => 1,
            'msg'   => '操作成功'
        );
        echo json_encode($data);

        return true;
    }
}