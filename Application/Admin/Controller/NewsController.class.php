<?php
/**
 *
 * @authors 慢悠悠的丑小鸭
 * @date    2017-02-17 16:27:35
 * @version 3.0
 */

namespace Admin\Controller;

use Common\Api\Category;
use Think\Controller;

class NewsController extends AdminController {

    /**
     * 资讯列表
     * @return [type] [description]
     */
    public function index()
    {
        $nickname        = I( 'nickname' );
        $where['status'] = array( 'eq', 1 );
        $where['type']   = array('neq', 2);
        $where['title']  = array( 'like', '%' . (string) $nickname . '%' );
        $model = D( 'ResourceRelation' );
        $count = $model->where( $where )->field('id')->count(); //查询总记录数
        $Page = new \Think\Page( $count,12);// 实例化分页类 传入总记录数和每页显示的记录数(12)
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
            ->where( $where )
            ->field('content,tag_city',true)
            ->order( 'send_time desc' )
            ->limit( $Page->firstRow . ',' . $Page->listRows )
            ->select();
        $this->assign('data', $lists);
        $this->assign('page', $show);
        $this->meta_title = '资讯列表';
        $this->display();
    }

    /**
     * 资讯精选推荐列表
     * @return [type] [description]
     */
    public function recommenList()
    {
        $nickname        = I( 'nickname' );
        $where['status'] = array( 'eq', 1 );
        $where['title']  = array( 'like', '%' . (string) $nickname . '%' );
        $model = D( 'RecommendRelation' );
        $count = $model->where( $where )->field('id')->count(); //查询总记录数
        $Page = new \Think\Page( $count,12);// 实例化分页类 传入总记录数和每页显示的记录数(12)
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
            ->where( $where )
            ->field('content',true)
            ->order( 'push_date desc' )
            ->limit( $Page->firstRow . ',' . $Page->listRows )
            ->select();

        $this->assign( 'data', $lists );
        $this->assign( 'page', $show );
        $this->meta_title = '资讯精选推荐列表';
        $this->display();
    }


    /**
     * 取消首页banner推荐
     * @return [type] [description]
     */
    public function cancelPush()
    {
        $id     = lx_decrypt(I( 'get.id'));
        $status = lx_decrypt(I('get.is_push')); //是否推送至首页：1是、0否

        $state = M('Resource')->where('id='.$id)->setField('is_push',$status);
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


    /**
     * 资讯查看
     * @return [type] [description]
     */
    public function views()
    {
        $id = lx_decrypt( I( 'get.mid' ) );
        $data_info = D( 'ResourceRelation' )->relation(true)->where( 'id='.$id )->find();
        $data = M( 'Major' )->where( 'status=1' )->order( 'sort' )->select();

        if (empty($id)) {
            $this->error('此页面不存在!');
            $this->redirect('index');
        }

        $this->major = Category::toLevel( $data, "ㄴ" ); //专业标签
        $this->content = htmlspecialchars_decode($data_info['content']);
        $this->assign( 'info', $data_info );
        $this->meta_title = '查看详情';
        $this->display();
    }

    /**
     * 发布资讯
     * @return [type] [description]
     */
    public function add()
    {
        $data        = M( 'Major' )->where( 'status=1' )->order( 'sort' )->select();
        $this->city  = M( 'city' )->where( 'type=1' )->order( 'citySort' )->select(); //城市标签
        $this->major = Category::toLevel( $data, "ㄴ" ); //专业标签
        $this->meta_title = '发布资讯';
        $this->display();
    }

    /**
     * 修改资讯,暂时没有用
     * @return [type] [description]
     */
    public function edit()
    {
        $rid     = I( 'post.res_id' );
        $icon    = I( 'post.icon' );
        $content = I( 'post.content' );
        if ( empty( $icon ) ) {
            if ( M('Resource')->where('id='.$rid )->setField('content',$content) ) {
                $this->redirect( 'topic_list' );
            } else {
                $this->error( '编辑失败' );
            }
        } else {
            $data['imgs']    = implode( ',', $icon );
            $data['content'] = $content;
            if ( M('Resource')->where('id='.$rid )->save($data) ) {
                $this->redirect( 'index' );
            } else {
                $this->error( '编辑失败' );
            }
        }
    }

    /**
     * 删除资讯与恢复
     * @return [type] [description]
     */
    public function delete()
    {
        $id = lx_decrypt(I('get.id'));
        $status = lx_decrypt(I('get.status'));

        if (!empty($id)) {

            //删除资讯帖子时,同时删除帖子表,推荐表
            if (0 == $status) {
                M('Recommend')->where('news_id='.$id)->delete();
            }

            $state = M('Resource')->where('id='.$id )->setField('status',0);
            if ($state) {
                $this->redirect('index');
            } else {
                $this->error( '编辑失败' );
            }

        } else {
            $this->error('此页面不存在!');
            $this->redirect('index');
        }
    }

    /**
     * 提交更新
     * @return [type] [description]
     */
    public function send() {
        $type = I( 'post.type' );
        
        if ( $_FILES['title_img']['name'] ) {
            /* 返回标准数据 */
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
                $return = array_merge( $info['title_img'], $return );
            } else {
                $this->error( $upload->getError() );
            }
            $data = array(
                'title'     => I( 'post.title' ),
                'title_img' => $return['path'],
                'tag_major' => implode( ',', I( 'post.major_id' ) ),
                'content'   => I( 'post.content' ),
                'send_time' => $_SERVER['REQUEST_TIME'],
                'is_stick'  => 2,
                'type'      => $type,
                'sort'      => I('post.sort'),
                'is_admin'  => 1,
                'status'    => 1,
            );
            
            if ( M( 'Resource' )->add($data) ) {
                if ( $type == 2 ) {
                    $this->redirect( 'Help/index' );
                } else {
                    $this->redirect( 'News/index' );
                }
            } else {
                $this->error('添加失败');
            }

        } else {
            $data = array(
                'title'     => I( 'post.title'),
                'tag_major' => implode( ',', I( 'post.major_id' ) ),
                'content'   => I( 'post.content' ),
                'send_time' => $_SERVER['REQUEST_TIME'],
                'is_stick'  => 2,
                'type'      => $type,
                'sort'      => I('post.sort'),
                'is_admin'  => 1,
                'status'    => 1,
            );

            if ( M( 'Resource' )->add($data) ) {

                if ( $type == 2 ) {
                    $this->redirect('Help/index');
                } else {
                    $this->redirect('News/index');
                }

            } else {
                $this->error('添加失败');
            }
        }
    }

     /**
      * 资讯置顶
      * 资讯列表页设置置顶
      * @return [type] [description]
      */
    public function stick()
    {
        $id     = lx_decrypt( I('get.id') );
        $status = lx_decrypt( I('get.is_stick') );  //是否置顶：1是、2否

        if (!empty($id)) {

            if (2 == $status) {
                $data = array(
                    'is_stick' => $status,
                    'stick_date' => '',
                );
            } else {
                $data = array(
                    'is_stick' => $status,
                    'stick_date' => $_SERVER['REQUEST_TIME'],
                );
            }

            $state = M('Resource')->where('id='.$id)->save($data);
            if ($state) {
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
     * 资讯推荐
     * 普通资讯推荐至精选列表,不能搞错了
     * @return [type] [description]
     */
    public function recommen()
    {
        $id = lx_decrypt(I( 'get.id' ));
        $is_recommend = lx_decrypt( I('get.is_recommend') );     //0是取消推荐,1是推荐

        // 当资讯id为空时
        if (!empty($id)) {

            $state = M('Resource')->where('id='.$id)->setField('is_recommend', $is_recommend);
            if ($state) {

                // 当$state为1时为推荐
                if (1 == $is_recommend) {
                    $article = M('Resource')->where('id='.$id)->find();
                    $data = array(
                        'news_id' => $article['id'],
                        'type' => 1,    //内容类别：1资讯 2视频 3活动
                        'sort' => $article['type'],     //资讯分类,实务,人文,律圈
                        'title' => $article['title'],
                        'content' => $article['content'],
                        'thumb_img' => $article['title_img'],
                        'status' => 1,
                        'author' => $article['author'],
                        'tag_major' => $article['tag_major'],
                        'is_admin' => $article['is_admin'],
                        'is_money' => $article['is_money'],
                        'push_date' => $_SERVER['REQUEST_TIME'],
                        'views' => $article['views'],
                    );

                    if (M('Recommend')->add($data)) {
                        $this->redirect('index');
                    } else {
                        $this->error('推荐失败');
                    }
                } else {
                    M('Recommend')->where('news_id='.$id)->delete();
                    $this->redirect('index');
                }

            } else {
                $this->error('推荐失败');
            }

        } else {
            $this->error('此页面不存在!');
            $this->redirect('index');
        }
    }

    /**
     * 取消精选推荐(精选列表)
     * @return [type] [description]
     */
    public function cancelRecommen()
    {
        $id = lx_decrypt(I( 'get.id' ));
        $is_recommend = lx_decrypt( I('get.is_recommend') );     //0是取消推荐,1是推荐

        // 当资讯id为空时
        if (!empty($id)) {

            if (0 == $is_recommend) {
                M('Recommend')->where('id='.$id)->delete();
                $this->redirect('recommenList');
            } else {
                $this->redirect('recommenList');
            }
        } else {
            $this->error('此页面不存在!');
            $this->redirect('recommenList');
        }
    }

    /**
     * 精选置顶
     * 精选推荐,百里挑一那种,哈哈哈,模型为recommend
     * @return [type] [description]
     */
    public function recomStick()
    {
        $id     = lx_decrypt( I('get.id') );
        $status = lx_decrypt( I('get.is_stick') );  //是否置顶：1是、2否

        if (!empty($id)) {

            if (2 == $status) {
                $data = array(
                    'is_stick' => $status,
                    'stick_date' => '',
                );
            } else {
                $data = array(
                    'is_stick' => $status,
                    'stick_date' => $_SERVER['REQUEST_TIME'],
                );
            }

            $state = M('Recommend')->where('id='.$id)->save($data);
            if ($state) {
                $this->redirect('recommenList');
            } else {
                $this->error('置顶失败');
            }

        } else {
            $this->error('此页面不存在!');
            $this->redirect('recommenList');
        }
    }

}