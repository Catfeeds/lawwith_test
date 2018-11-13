<?php
/**
 * 资源管理
 * Created by PhpStorm.
 * User: Mrx
 * Date: 2016/4/7
 * Time: 16:33
 */

namespace Admin\Controller;

use Common\Api\Category;
use Think\Controller;

class ContentController extends AdminController {
    /**----------------律所------------------**/
    //律所列表
    public function laws_list() {
        $nickname                = I( 'nickname' );
        $where['lx_laws.status'] = array( 'neq', 0 );
        $where['law_name']       = array( 'like', '%' . (string) $nickname . '%' );
        $model                   = D( 'Laws' );
        $count  = $model->where($where)->field('id')->count(); //查询总记录数
        $Page   = new \Think\Page($count,12);// 实例化分页类 传入总记录数和每页显示的记录数(25)
        $Page->setConfig( 'header', '共%TOTAL_ROW%条' );
        $Page->setConfig( 'first', '首页' );
        $Page->setConfig( 'last', '共%TOTAL_PAGE%页' );
        $Page->setConfig( 'prev', '上一页' );
        $Page->setConfig( 'next', '下一页' );
        $Page->setConfig( 'link', 'indexpagenumb' );//pagenumb 会替换成页码
        $Page->setConfig( 'theme', '%HEADER% %FIRST% %UP_PAGE% %LINK_PAGE% %DOWN_PAGE% %END%' );
        $show  = $Page->show();
        $lists = $model
            ->join( 'LEFT JOIN lx_account on lx_laws.uadmin = lx_account.id' )
            ->order( 'sort,regtime desc' )
            ->where( $where )
            ->field( 'lx_laws.id as lid,lx_laws.*,lx_account.id as uid,lx_account.uname' )
            ->limit( $Page->firstRow . ',' . $Page->listRows )
            ->select();

        //专业标签
        $this->major_list = M( 'major' )->where( 'status=1' )->select();
        //城市
        $this->city_list  = M( 'City' )->field( 'id,cityname' )->select();
        $this->meta_title = '律所列表';
        $this->assign( 'data', $lists ); //赋值数据集
        $this->assign( 'page', $show ); //赋值分页输出
        $this->display();
    }

    //新增律所
    public function addLaws() {
        $this->province   = M( 'city' )->where( 'type=1' )->order( 'citySort' )->select();
        $data             = M( 'Major' )->where( 'status=1' )->order( 'sort' )->select();
        $this->major      = Category::toLevel( $data, "ㄴ" );
        $this->meta_title = '添加律所';
        $this->display();
    }

    //提交新增律所
    public function subaddLaws() {
        $upload = D( 'Picture' );
        C( 'PICTURE_UPLOAD.maxSize', 500 * 1024 );   //上传的文件大小限制 (0-不做限制)
        C( 'PICTURE_UPLOAD.exts', 'jpg,gif,png,jpeg' );    //允许上传的文件后缀
        C( 'PICTURE_UPLOAD.savePath', 'Logo/' );   //设置上传子目录
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
            $return           = array_merge( $info['banner_img'], $return );
        } else {
            $return['status'] = 0;
            $return['info']   = $upload->getError();
            $this->error( $return['info'] );
        }
        // 上传文件
        $mode = M( 'Laws' );
        $data = array(
            'logo'      => $return['path'],
            'law_name'  => I( 'post.lawName' ),
            'province'  => get_city( I( 'post.province' ) ),
            'city'      => get_city( I( 'post.city' ) ),
            'town'      => get_city( I( 'post.town' ) ),
            'address'   => I( 'post.address' ),
            'phone'     => I( 'post.phone' ),
            'profile'   => I( 'post.describe' ),
            'create_at' => strtotime( I( 'post.create_time' ) ),
            'majors'    => implode( ',', I( 'post.major_id' ) ),
            'status'    => I( 'post.status' ),
            'regtime'   => NOW_TIME
        );
        if ( $mode->add( $data ) ) {
            $this->redirect( 'laws_list' );
        } else {
            $this->error( '添加数据失败' );
        }
    }

    //编辑律所
    public function editLaws() {
        $lid        = lx_decrypt( I( 'get.lid' ) );
        $this->laws = M( 'Laws' )->where( 'id=' . $lid )->find(); //律所数据
        $where      = array(
            'law'  => intval( $lid ),
            'type' => 1
        );
        $this->lawyer     = M( 'Account' )->where( $where )->field( 'id,uname,mobile' )->select(); //律师列表
        $this->province   = M( 'City' )->where( 'type=1' )->order( 'citySort' )->select(); //城市列表
        $this->city       = M( 'City' )->where( 'type=2' )->order( 'citySort' )->select(); //城市列表
        $this->town       = M( 'City' )->where( 'type=3' )->order( 'citySort' )->select(); //城市列表
        $major            = M( 'Major' )->where( 'status=1' )->select();
        $this->major_tree = Category::toLevel( $major, "ㄴ" ); //专业列表
        $this->meta_title = '编辑律所';
        $this->display();
    }

    //提交编辑律所
    public function subeditLaws() {
        $lid = I( 'post.lid' );
        $arr = I( 'post.major_id' );
        $logo_path = I( 'post.logo_path' );
        $major_ids = implode( ',', array_unique( $arr ) );
        $mode = M( 'Laws' );
        if ( $_FILES['logo']['name'] ) {
            $upload = D( 'Picture' );
            C( 'PICTURE_UPLOAD.maxSize', 500 * 1024 );   //上传的文件大小限制 (0-不做限制)
            C( 'PICTURE_UPLOAD.exts', 'jpg,gif,png,jpeg' );    //允许上传的文件后缀
            C( 'PICTURE_UPLOAD.savePath', 'Logo/' );   //设置上传子目录
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
                $return = array_merge( $info['logo'], $return );
                $data   = array(
                    'logo'     => $return['path'],
                    'law_name' => I( 'post.lawName' ),
                    'province' => get_city( I( 'post.province' ) ),
                    'city'     => get_city( I( 'post.city' ) ),
                    'town'     => get_city( I( 'post.town' ) ),
                    'address'  => I( 'post.address' ),
                    'phone'    => I( 'post.phone' ),
                    'uadmin'   => I( 'post.uadmin' ),
                    'profile'  => I( 'post.describe' ),
                    'majors'   => $major_ids,
                    'status'   => I( 'post.status' )
                );
                if ( $mode->where( 'id=' . $lid )->save( $data ) ) {
                    unlink( $logo_path );
                    $this->redirect( 'laws_list' );
                } else {
                    $this->error( '添加数据失败' );
                }
            } else {
                $return['status'] = 0;
                $return['info']   = $upload->getError();
                $this->error( $return['info'] );
            }
        } else {
            $data = array(
                'law_name' => I( 'post.lawName' ),
                'province' => get_city( I( 'post.province' ) ),
                'city'     => get_city( I( 'post.city' ) ),
                'town'     => get_city( I( 'post.town' ) ),
                'address'  => I( 'post.address' ),
                'phone'    => I( 'post.phone' ),
                'uadmin'   => I( 'post.uadmin' ),
                'profile'  => I( 'post.describe' ),
                'majors'   => $major_ids,
                'status'   => I( 'post.status' )
            );
            if ( $mode->where( 'id=' . $lid )->save( $data ) ) {
                $this->redirect( 'laws_list' );
            } else {
                $this->error( '无数据修改' );
            }
        }
    }

    //判断律所名称是否可用
    public function checkLaws() {
        $post_val = I( 'post.lawName' );
        $users    = M( 'Laws' )->where( "law_name='$post_val'" )->select();

        if ( $users ) {
            $valid = false;
        } else {
            $valid = true;
        }

        echo json_encode( array(
            'valid' => $valid,
        ) );
    }

    //修改状态
    public function changeStatus() {
        $status = lx_decrypt( I( 'get.status' ) );
        $id     = lx_decrypt( I( 'get.lid' ) );
        if ( M( 'Laws' )->where( 'id=' . $id )->setField( 'status', $status ) ) {
            $this->redirect( 'laws_list' );
        } else {
            $this->error( '修改失败' );
        }
    }

    //更改多条数据状态
    public function delLaws() {
        $law_id = $_POST['get_id'];
        $law_idarr = explode( ',', $law_id );
        foreach ( $law_idarr as $lid ) {
            M( 'Laws' )->where( 'id='.$lid )->setField( 'status', 0 );
        }
        $data = array(
            'state' => 1,
            'msg'   => '操作成功'
        );
        echo json_encode( $data );

        return true;
    }

    //获取城市信息
    public function getRegion() {
        $Region      = M( "City" );
        $map['pid']  = $_REQUEST["pid"];
        $map['type'] = $_REQUEST["type"];
        $list        = $Region->where( $map )->select();
        echo json_encode( $list );
    }


    //导入数据提交方法
    public function subimport() {
        header( "Content-Type:text/html;charset=utf-8" );
        $upload = new \Think\Upload();// 实例化上传类
        $upload->maxSize  = 3145728;// 设置附件上传大小
        $upload->exts     = array( 'xls', 'xlsx' );// 设置附件上传类
        $upload->savePath = 'data/'; // 设置附件上传目录
        // 上传文件
        $info = $upload->uploadOne( $_FILES['file'] );
        $filename = './Uploads' . $info['savepath'] . $info['savename'];
        $exts     = $info['ext'];
        if ( ! $info ) {    // 上传错误提示错误信息
            $this->error( $upload->getError() );
        } else {    // 上传成功
            $this->excel_import( $filename, $exts );
        }
    }

    //导出数据提交方法
    public function subexport() {
        $data = M( 'Laws' )->select();
        $this->excel_export( $data );
    }

    //导入数据方法
    protected function excel_import( $filename, $exts = 'xls' ) {
        //导入PHPExcel类库，因为PHPExcel没有用命名空间，只能inport导入
        import( "Org.Util.PHPExcel" );
        //创建PHPExcel对象，注意，不能少了\
        $PHPExcel = new \PHPExcel();
        //如果excel文件后缀名为.xls，导入这个类
        if ( $exts == 'xls' ) {
            import( "Org.Util.PHPExcel.Reader.Excel5" );
            $PHPReader = new \PHPExcel_Reader_Excel5();
        } else if ( $exts == 'xlsx' ) {
            import( "Org.Util.PHPExcel.Reader.Excel2007" );
            $PHPReader = new \PHPExcel_Reader_Excel2007();
        }

        $laws = M( 'Laws' );
        //载入文件
        $PHPExcel = $PHPReader->load( $filename, $encode = 'utf-8' );
        //获取表中的第一个工作表，如果要获取第二个，把0改为1，依次类推
        $currentSheet = $PHPExcel->getSheet( 0 );
        //获取总列数
        $allColumn = $currentSheet->getHighestColumn();
        //获取总行数
        $allRow = $currentSheet->getHighestRow();
        //循环获取表中的数据，$currentRow表示当前行，从哪行开始读取数据，索引值从0开始
        for ( $i = 2; $i <= $allRow; $i ++ ) {
            $data['law_name'] = $PHPExcel->getActiveSheet()->getCell( 'A' . $i )->getValue();
            $data['phone']    = $PHPExcel->getActiveSheet()->getCell( 'B' . $i )->getValue();
            $data['province'] = $PHPExcel->getActiveSheet()->getCell( 'C' . $i )->getValue();
            $data['city']     = $PHPExcel->getActiveSheet()->getCell( 'D' . $i )->getValue();
            $data['town']     = $PHPExcel->getActiveSheet()->getCell( 'E' . $i )->getValue();
            $data['address']  = $PHPExcel->getActiveSheet()->getCell( 'F' . $i )->getValue();
            $data['email']    = $PHPExcel->getActiveSheet()->getCell( 'G' . $i )->getValue();
            $data['regtime']  = time();
            $result           = $laws->add( $data );
        }
        if ( false !== $result || 0 !== $result ) {
            $this->success( '数据导入成功', U( 'laws_list' ) );
        } else {
            $this->error( '数据导入失败' );
        }
    }


    //导出数据方法
    protected function excel_export( $data_list = array() ) {
        $data_list = $data_list;
        $data      = array();
        foreach ( $data_list as $k => $data_info ) {
            $data[ $k ][ id ]        = $data_info['id'];
            $data[ $k ][ law_name ]  = $data_info['law_name'];
            $data[ $k ][ province ]  = $data_info['province'];
            $data[ $k ][ city ]      = $data_info['city'];
            $data[ $k ][ towm ]      = $data_info['towm'];
            $data[ $k ][ address ]   = $data_info['address'];
            $data[ $k ][ create_at ] = date( 'Y-m-d', $data_info['create_at'] );
            $data[ $k ][ profile ]   = $data_info['profile'];
        }

        foreach ( $data as $field => $v ) {
            if ( $field == 'id' ) {
                $headArr[] = '律所ID';
            }

            if ( $field == 'law_name' ) {
                $headArr[] = '律所名称';
            }

            if ( $field == 'province' ) {
                $headArr[] = '省份/直辖市';
            }

            if ( $field == 'city' ) {
                $headArr[] = '市/县';
            }

            if ( $field == 'towm' ) {
                $headArr[] = '镇/区';
            }

            if ( $field == 'address' ) {
                $headArr[] = '详细地址';
            }

            if ( $field == 'create_at' ) {
                $headArr[] = '创建时间';
            }

            if ( $field == 'profile' ) {
                $headArr[] = '简介';
            }
        }

        $filename = "律所数据";

        $this->getExcel( $filename, $headArr, $data );
    }


    private function getExcel( $fileName, $headArr, $data ) {
        //导入PHPExcel类库，因为PHPExcel没有用命名空间，只能inport导入
        import( "Org.Util.PHPExcel" );
        import( "Org.Util.PHPExcel.Writer.Excel5" );
        import( "Org.Util.PHPExcel.IOFactory.php" );

        $date = date( "Y_m_d", time() );
        $fileName .= "_{$date}.xls";

        //创建PHPExcel对象，注意，不能少了\
        $objPHPExcel = new \PHPExcel();
        $objProps    = $objPHPExcel->getProperties();

        //设置表头
        $key = ord( "A" );
        //print_r($headArr);exit;
        foreach ( $headArr as $v ) {
            $colum = chr( $key );
            $objPHPExcel->setActiveSheetIndex( 0 )->setCellValue( $colum . '1', $v );
            $objPHPExcel->setActiveSheetIndex( 0 )->setCellValue( $colum . '1', $v );
            $key += 1;
        }

        $column = 2;
        $objActSheet = $objPHPExcel->getActiveSheet();

        //print_r($data);exit;
        foreach ( $data as $key => $rows ) { //行写入
            $span = ord( "A" );
            foreach ( $rows as $keyName => $value ) {// 列写入
                $j = chr( $span );
                $objActSheet->setCellValue( $j.$column, $value );
                $span ++;
            }
            $column ++;
        }

        $fileName = iconv( "utf-8", "gb2312", $fileName );

        //重命名表
        //$objPHPExcel->getActiveSheet()->setTitle('test');
        //设置活动单指数到第一个表,所以Excel打开这是第一个表
        $objPHPExcel->setActiveSheetIndex( 0 );
        ob_end_clean();//清除缓冲区,避免乱码
        header( 'Content-Type: application/vnd.ms-excel' );
        header( "Content-Disposition: attachment;filename=\"$fileName\"" );
        header( 'Cache-Control: max-age=0' );

        $objWriter = \PHPExcel_IOFactory::createWriter( $objPHPExcel, 'Excel5' );
        $objWriter->save( 'php://output' ); //文件通过浏览器下载
        exit;
    }

    //推荐首页
    public function changeLaws() {
        $status = lx_decrypt( I( 'get.is_push' ) );
        $id     = lx_decrypt( I( 'get.mid' ) );
        if ( M( 'Laws' )->where( 'id=' . $id )->setField( 'is_push', $status ) ) {
            M( 'Push' )->where( 'c_type=5 AND cid=' . $id )->delete();
            $this->redirect( 'laws_list' );
        } else {
            $this->error( '更新失败' );
        }
    }

    //资讯列表
    public function news_list() {
        $nickname        = I( 'nickname' );
        $where['status'] = array( 'eq', 1 );
        $where['type']   = array( 'eq', 3 );
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
            ->field('content,title_img,tag_city',true)
            ->order( 'send_time desc' )
            ->limit( $Page->firstRow . ',' . $Page->listRows )
            ->select();
        $this->assign( 'data', $lists );
        $this->assign( 'page', $show );
        $this->meta_title = '资讯列表';
        $this->display();
    }

    //资讯推荐列表
    public function news_recommend() {
        $nickname = I( 'nickname' );
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
//p($lists);die;
        $this->assign( 'data', $lists );
        $this->assign( 'page', $show );
        $this->meta_title = '资讯推荐列表';
        $this->display();
    }

    //发布话题
    public function sendTopic() {
        $type        = lx_decrypt( I( 'get.type' ) );
        $tid         = lx_decrypt( I( 'get.mid' ) );
        $this->city  = M( 'city' )->where( 'type=1' )->order( 'citySort' )->select(); //城市标签
        $data        = M( 'Major' )->where( 'status=1' )->order( 'sort' )->select();
        $this->major = Category::toLevel( $data, "ㄴ" ); //专业标签
        if ( empty( $tid ) ) {
            if ( $type == 2 ) {
                $this->meta_title = '发布求助';
            } else {
                $this->meta_title = '发布资讯';
            }
            $this->assign( 'type', $type );
            $this->display( 'addtopic' );
        } else {
           // $this->user_resource = D( 'ResourceRelation' )->relation( true )->where( 'id=' . $tid )->find();
            $this->meta_title    = '编辑内容';
            $this->display( 'edittopic' );
        }
    }

    //提交添加话题
    public function sub_sendTopic() {
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
                'send_time' => time(),
                'is_stick'  => 2,
                'type'      => $type,
                'sort'      => I('post.sort'),
                'is_admin'  => 1,
                'status'    => 1,
            );
            if ( M( 'Resource' )->add( $data ) ) {
                if ( $type == 2 ) {
                    $this->redirect( 'help_list' );
                } else {
                    $this->redirect( 'news_list' );
                }
            } else {
                $this->error( '添加失败' );
            }
        } else {
            $data = array(
                'title'     => I( 'post.title' ),
                'tag_major' => implode( ',', I( 'post.major_id' ) ),
                'content'   => I( 'post.content' ),
                'send_time' => time(),
                'is_stick'  => 2,
                'type'      => $type,
                'sort'      => I('post.sort'),
                'is_admin'  => 1,
                'status'    => 1,
            );
            if ( M( 'Resource' )->add( $data ) ) {
                if ( $type == 2 ) {
                    $this->redirect('help_list');
                } else {
                    $this->redirect('news_list');
                }
            } else {
                $this->error( '添加失败' );
            }
        }
    }

    //提交修改话题信息
    public function sub_editTopic() {
        $rid     = I( 'post.res_id' );
        $content = I( 'post.content' );
        $icon    = I( 'post.icon' );
        if ( empty( $icon ) ) {
            if ( M( 'Resource' )->where( 'id=' . $rid )->setField( 'content', $content ) ) {
                $this->redirect( 'topic_list' );
            } else {
                $this->error( '编辑失败' );
            }
        } else {
            $data['imgs']    = implode( ',', $icon );
            $data['content'] = $content;
            if ( M( 'Resource' )->where( 'id=' . $rid )->save( $data ) ) {
                $this->redirect( 'topic_list' );
            } else {
                $this->error( '编辑失败' );
            }
        }
    }

    //设置首页推荐
    public function changePush() {
        $status = lx_decrypt( I( 'get.is_push' ) );
        $id     = lx_decrypt( I( 'get.mid' ) );
        $type   = lx_decrypt( I( 'get.t' ) );
        if ( M( 'Resource' )->where( 'id='.$id )->setField( 'is_push', $status ) ) {
            if ( M( 'Push' )->where( 'cid='.$id )->delete() ) {
                $this->redirect( 'help_list' );
            } else {
                $this->error( '更新失败1' );
            }
        } else {
            $this->error( '更新失败' );
        }
    }

    //查看话题
    public function viewsTopic() {
        $id = lx_decrypt( I( 'get.mid' ) );
        $data_info   = D( 'ResourceRelation' )->relation(true)->where( 'id='.$id )->find();
        $data        = M( 'Major' )->where( 'status=1' )->order( 'sort' )->select();
        $this->major = Category::toLevel( $data, "ㄴ" ); //专业标签
        $this->assign( 'info', $data_info );
        $this->meta_title = '查看详情';
        $this->display();
    }

    public function news_viewsTopic() {
        $id = lx_decrypt( I( 'get.mid' ) );
        $data_info = D( 'ResourceRelation' )->relation(true)->where( 'id='.$id )->find();
        $data = M( 'Major' )->where( 'status=1' )->order( 'sort' )->select();
        $this->major = Category::toLevel( $data, "ㄴ" ); //专业标签
        $this->content = htmlspecialchars_decode($data_info['content']);
        $this->assign( 'info', $data_info );
        $this->meta_title = '查看详情';
        $this->display();
    }

    //修改话题状态(下架删除)
    public function changeTopic() {
        $status = lx_decrypt( I( 'get.status' ) );
        $id     = lx_decrypt( I( 'get.mid' ) );
        $type   = lx_decrypt( I( 'get.t' ) );
        if ( M( 'Resource' )->where( 'id=' . $id )->setField( 'status', $status ) ) {
            if ( $type == 2 ) {
                $this->redirect( 'help_list' );
            } else {
                $this->redirect( 'topic_list' );
            }
        } else {
            $this->error( '更新失败' );
        }
    }

    //修改资讯状态(下架删除)
    public function new_changeTopic() {
        $status = lx_decrypt( I( 'get.status' ) );
        $id = lx_decrypt(I( 'get.mid' ));
        //$type   = lx_decrypt( I( 'get.t' ) );
        if ( M( 'Resource' )->where( 'id='.$id )->setField('status', $status ) ) {
            $this->redirect( 'news_list' );
        } else {
            $this->error( '更新失败' );
        }
    }

    //设置求助置顶
    public function changeStick() {
        $status = lx_decrypt(I('get.is_stick'));
        $id = lx_decrypt(I( 'get.mid' ) );
        $type = lx_decrypt(I( 'get.t' ) );
        if(M('Resource')->where('id='.$id)->setField('is_stick',$status)){
            if ( $type == 2 ) {
                $this->redirect('help_list');
            } else {
                $this->redirect( 'topic_list' );
            }
        } else {
            $this->error( '更新失败' );
        }
    }

    //设置资讯置顶
    public function new_changeStick() {
        $status = lx_decrypt( I( 'get.is_stick' ) );
        $id     = lx_decrypt( I( 'get.mid' ) );
        $type   = lx_decrypt( I( 'get.t' ) );

        if($status == 1) {
            $data = array(
                'is_stick' => $status,
                'stick_date' => time(),
            );
            if(M('Resource')->where('id='.$id)->save($data)){
                if ( $type == 2 ) {
                    $this->redirect( 'news_list' );
                } else {
                    $this->redirect( 'news_list' );
                }
            } else {
                $this->error( '更新失败' );
            }
        } else {
            //取消置顶
            $data = array(
                'is_stick' => $status,
                'stick_date' => '',
            );
            if(M('Resource')->where('id='.$id)->save($data)){
                if ( $type == 2 ) {
                    $this->redirect( 'news_list' );
                } else {
                    $this->redirect( 'news_list' );
                }
            } else {
                $this->error( '更新失败' );
            }
        }

    }

    //设置资讯置顶
    public function recommendStick() {
        $status = lx_decrypt( I( 'get.is_stick' ) );
        $id     = lx_decrypt( I( 'get.mid' ) );

        if($status == 1) {
            $data = array(
                'is_stick' => $status,
                'stick_date' => time(),
            );
            if(M('Recommend')->where('id='.$id)->save($data)){
                $this->redirect( 'news_recommend' );
            } else {
                $this->error( '更新失败' );
            }
        } else {
            //取消置顶
            $data = array(
                'is_stick' => $status,
                'stick_date' => '',
            );
            if(M('Recommend')->where('id='.$id)->save($data)){

               $this->redirect( 'news_recommend' );
            } else {
                $this->error( '更新失败' );
            }
        }

    }

    //设置精选推荐
    public function new_changePush() {
        $status = lx_decrypt( I( 'get.is_recommend' ) );     //0是取消推荐,1是推荐
        $id = I( 'get.cid' );
        $model = M( 'Resource' );
        if($status == 1) {
            if ($model->where( 'id='.$id )->setField( 'is_recommend', $status )) {
                $article = $model->where('id='.$id)->find();
                $data = array(
                    'news_id' => $article['id'],
                    'type' => 1,    //内容类别：1资讯 2视频 3活动
                    'sort' => $article['type'],     //资讯分类,实务,人文,律圈
                    'title' => $article['title'],
                    'content' => $article['content'],
                    'thumb_img' => $article['title_img'],
                    'status' => 1,
                    'author' => $article['author'],
                    'is_admin' => $article['is_admin'],
                    'is_money' => $article['is_money'],
                    'push_date' => time(),
                    'views' => $article['views'],
                );

                if(M('Recommend')->add($data)) {
                    $this->redirect('news_list');
                } else {
                    $this->error('精选推荐失败');
                }
            } else {
                $this->error('精选推荐失败');
            }

        } else {
            if ($model->where( 'id='.$id)->setField( 'is_push', 2)) {
                M('Recommend')->where( 'news_id='.$id)->setField( 'status', 0);
                $this->redirect('news_list');
            } else {
                $this->error('更新失败');
            }
        }

    }

    /**----------------求助------------------**/

    //求助列表
    public function help_list() {
        $nickname = I( 'nickname' );
        //$where['status'] = array( 'neq', 0 );
        $where['type']   = array( 'eq', 2 );
        $where['title']  = array( 'like', '%'.(string)$nickname.'%' );
        $model = D( 'ResourceRelation' );
        $count = $model->where($where)->field('id')->count(); //查询总记录数
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
            ->where($where)
            ->field('id,title,author,views,send_time,status,is_stick')
            ->order('send_time desc' )
            ->limit($Page->firstRow.','.$Page->listRows )
            ->select();
        $this->assign( 'data', $lists );
        $this->assign( 'page', $show );
        $this->meta_title = '求助列表';
        $this->display();
//        p($lists);
    }

    //采纳问题
    public function adoptHelp() {
        $cid = lx_decrypt( I( 'get.cid' ) );
        $rid = lx_decrypt( I( 'get.rid' ) );
        M( 'Resource' )->where( 'id='.$rid )->setField( 'tbd_id', $cid );
        M( 'Resource_comment' )->where( 'id='.$cid )->setField( 'tbd', 1 );
        $this->redirect( 'Content/viewsTopic', array( 'mid' => $rid ) );
    }


    //更改多条数据状态
    public function delPosts() {
        $law_id = $_POST['get_id'];
        $law_idarr = explode( ',', $law_id );
        foreach ( $law_idarr as $lid ) {
            M( 'Resource' )->where( 'id='.$lid )->setField( 'status', 0 );
        }
        $data = array(
            'state'=> 1,
            'msg' => '操作成功'
        );
        echo json_encode( $data );
        return true;
    }

    /**----------------活动------------------**/

    //活动列表
    public function activity_list() {
        $nickname        = I( 'nickname' );
        $where['status'] = array( 'neq', 0 );
        $where['title']  = array( 'like', '%' . (string) $nickname . '%' );
        $model           = D( 'ActivityRelation' );
        $count           = $model->where( $where)->field('id')->count(); //查询总记录数
        $Page            = new \Think\Page( $count, 12 ); // 实例化分页类 传入总记录数和每页显示的记录数(20)
        $Page->setConfig( 'header', '共%TOTAL_ROW%条' );
        $Page->setConfig( 'first', '首页' );
        $Page->setConfig( 'last', '共%TOTAL_PAGE%页' );
        $Page->setConfig( 'prev', '上一页' );
        $Page->setConfig( 'next', '下一页' );
        $Page->setConfig( 'link', 'indexpagenumb' );//pagenumb 会替换成页码
        $Page->setConfig( 'theme', '%HEADER% %FIRST% %UP_PAGE% %LINK_PAGE% %DOWN_PAGE% %END%' );
        $show  = $Page->show();
        $lists = $model
            ->relation( true )
            ->order( 'send_time desc,status' )
            ->where( $where )
            ->field('remark,imgs',true)
            ->limit( $Page->firstRow . ',' . $Page->listRows )
            ->select();
        $this->assign( 'data', $lists );
        $this->assign( 'page', $show );
        $this->meta_title = '活动列表';
        $this->display();
    }

    //发布活动
    public function sendActivity() {
        $this->meta_title = '发布活动';
        $this->display( 'addactivity' );
    }

    //提交发布活动
    public function sub_sendActivity() {
        $times     = I( 'event_time' );
        $arr_time  = explode( '-', $times );
        $star_time = strtotime( $arr_time[0] ); //活动开始时间戳
        $end_time  = strtotime( $arr_time[1] ); //活动结束时间戳
        $deadline  = strtotime( I( 'post.deadline' ) ); //活动截止报名时间戳

        /* 返回标准数据 */
        $return = array( 'status' => 1, 'info' => '上传成功', 'data' => '' );
        /* 调用文件上传组件上传文件 */
        $Picture = D( 'Picture' );
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
        $data = array(
            'title'     => I( 'post.title' ),     //标题
            'imgs'      => $return['path'],       //标题图片
            'address'   => I( 'post.address' ),     //活动地址
            'number'    => I( 'post.numbs' ),        //限制人数
            'type'      => I( 'post.a_type' ),       //活动方式
            'price'     => I( 'post.price' ),     //人均消费
            'star_time' => $star_time,      //开始时间
            'end_time'  => $end_time,        //结束时间
            'deadline'  => $deadline,        //截止报名时间
            'group'     => I('post.group'),     //活动群号
            'sponsor'   => I( 'post.host_unit' ),       //主办方
            'remark'    => I( 'post.content' ),      //活动介绍
            'status'    => I( 'post.status' ),       //活动状态
            'is_push'   => I( 'post.is_push' ),     //是否首页推荐
            //                'is_stick' => I('post.is_stick'),       //是否置顶
            'send_time' => time()       //发布时间
        );
        if ( M( 'Activity' )->add( $data ) ) {
            $this->redirect( 'activity_list' );
        } else {
            $this->error( '插入数据失败' );
        }
    }

    //查看活动详情
    public function viewsActivity() {
        $id = lx_decrypt( I( 'get.mid' ) );
        if(IS_POST) {
            $mid = I('post.mid');
            $activity_data = array(
                'title' => I('post.title'),
                'remark'=> I('post.remark')
            );
            $res = M('Activity')->where('id='.$mid)->setField($activity_data);

            if($res) {
                $this->redirect('activity_list');
            }else{
                $this->error('操作失败');
            }
        }else{
            $data_info = D('ActivityRelation')->relation(true)->where('id='.$id)->find();
            $this->assign( 'info', $data_info );
        }

        $this->meta_title = '查看活动';
        $this->display();
    }

    //修改活动状态
    public function changeActivity() {
        $status = lx_decrypt( I( 'get.status' ) );
        $id = lx_decrypt( I( 'get.mid' ) );
        if ( M( 'Activity' )->where( 'id=' . $id )->setField( 'status', $status ) ) {
            $this->redirect( 'activity_list' );
        } else {
            $this->error( '更新失败' );
        }
    }

    //设置首页推荐
    public function pushIndex() {
        $status = lx_decrypt( I( 'get.is_push' ) );
        $id     = lx_decrypt( I( 'get.mid' ) );
        if ( M( 'Activity' )->where( 'id='.$id )->setField( 'is_push', $status ) ) {
            M( 'Push' )->where( 'c_type=3 AND cid=' . $id )->delete();
            $this->redirect( 'activity_list' );
        } else {
            $this->error( '更新失败' );
        }
    }

    //推送活动到精选列表
    public function recom_push() {
        $id = lx_decrypt(I('get.mid')); //视频详情页id
        $status = lx_decrypt(I('get.status')); //0取消精选推荐,1精选推荐
        if($status == 1) {
            $data = D('Activity')->where('id='.$id)->find();
            $recom_data = array(
                'activity_id'=> $data['id'],   //活动id
                'type'    => 3,             //内容类别：1资讯 2视频 3活动
                'title'   => $data['title'],
                'thumb_img'=>$data['imgs'],
                'star_time' => $data['star_time'],
                'end_time'=> $data['end_time'],
                'consumption'  => $data['price'],
                'activity_type' => $data['type'],
                'deadline'=> $data['deadline'],
                'status'=> 1,   //开启状态：1是、0否
                'author'   => $data['author'],
                'push_date' => time(),
                'views' => $data['views'],
            );
            if(M('Recommend')->add($recom_data)) {
                M('Activity')->where('id='.$id)->setField('recom_push',$status);
                $this->redirect('activity_list');
            } else {
                $this->error();
            }

        } else {
            M('Activity')->where('id='.$id)->setField('recom_push',$status);
            if(M('Recommend')->where('activity_id='.$id)->delete()) {
                $this->redirect('activity_list');
            } else {
                $this->error();
            }
        }
    }

    //更改多条数据状态
    public function delActivity() {
        $law_id = $_POST['get_id'];
        $law_idarr = explode( ',', $law_id );
        foreach ( $law_idarr as $lid ) {
            M( 'Activity' )->where( 'id='.$lid )->setField( 'status', 0 );
        }
        $data = array(
            'state' => 1,
            'msg'   => '操作成功'
        );
        echo json_encode( $data );

        return true;
    }
    /**----------------推荐------------------**/

    //推送列表
    public function push_list() {
        $model1 = M( 'Resource' );
        $model2 = M( 'Activity' );
        $model3 = M( 'Train' );
        $model4 = M( 'Laws' );
        $model5 = M( 'Account' );

        //推荐话题
        $this->data1 = $model1->where( 'is_push=1 AND type=1' )->order( 'push_date desc' )->field( 'id,title,is_push,push_date,type' )->select();
        //推荐求助
        $this->data2 = $model1->where( 'is_push=1 AND type=2' )->order( 'push_date desc' )->field( 'id,title,is_push,push_date,type' )->select();
        //推荐活动
        $this->data3 = $model2->where( 'is_push=1' )->order( 'push_date desc' )->field( 'id,title,is_push,push_date' )->select();
        //推荐视频
        $this->data4 = $model3->where( 'is_push=1 or push_list=1' )->order( 'push_date desc' )->field( 'id,title,is_push,push_list,push_date' )->select();
        //推荐律所
        $this->data5 = $model4->where( 'is_push=1' )->order( 'push_date desc' )->field( 'id,law_name,is_push,push_date' )->select();
        //推荐用户
        $this->data6 = $model5->where( 'is_push=1' )->order( 'push_date desc' )->field( 'id,type,uname,is_push,push_date' )->select();

        $this->meta_title = '推荐列表';
        $this->display( 'Push/push_list' );
    }

    //推荐详情
    public function addPush() {
        $id   = lx_decrypt( I( 'get.cid' ) ); //内容id
        $type = lx_decrypt( I( 'get.t' ) ); //推送类别 1.帖子 2.求助 3.活动 4.视频 5.律所 6.用户
        switch ( $type ) {
            case 1:
                $data = D( 'ResourceRelation' )->relation( true )->where( 'id=' . $id )->find();
                $this->p_type = 1;
                $type_title   = '资讯';
                break;
            case 2:
                $data = D( 'ResourceRelation' )->relation( true )->where( 'id=' . $id )->find();
                $this->p_type = 2;
                $type_title   = '求助';
                break;
            case 3:
                $data = D( 'ActivityRelation' )->relation( true )->where( 'id=' . $id )->find();
                $this->p_type = 3;
                $type_title   = '活动';
                break;
            case 4:
                $data = D( 'TrainRelation' )->relation( true )->where( 'id=' . $id )->find();
                $this->p_type = 4;
                $type_title = '视频';
                break;
            case 5:
                $data = M( 'Laws' )->where( 'id=' . $id )->find();
                $this->p_type = 5;
                $type_title = '律所';
                break;
            case 6:
                $data  = M( 'Account' )->where( 'id=' . $id )->find();
                $this->p_type = 6;
                $type_title   = '用户';
                break;
            default:
                $type_title = '详情';
                break;
        }
        //p($data);die;
        $majors = M( 'Major' )->where('status=1')->select();
        $this->assign( 'major', $majors);
        $this->assign( 'content', $data);
        $this->assign( 'mark', 1 );
        $this->meta_title = '推荐' . $type_title;
        $this->display( 'Push/des_push' );
    }

    public function subPush() {
        $p_type   = I( 'post.p_type' ); //推荐对象  推送类别 1.帖子 2.求助 3.活动 4.视频 5.律所 6.用户
        $p_id     = I( 'post.res_id' );   //对象id
        $p_sort   = I( 'post.sort' );   //排序
        $p_banner = I( 'post.banner_img' );   //已存在的banner图
        $p_author = intval( I( 'post.author' ) ); //作者id
        $mark     = I( 'post.mark' );     //视频推送方式 1推送首页 2推送列表
        if ( ! empty( $_FILES['banner_img']['name'] ) ) {
            $return = array( 'status' => 1, 'info' => '上传成功' );
            $upload = D( 'Picture' );
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
                $return           = array_merge( $info['banner_img'], $return );
                //            p($return['path']);die;
            } else {
                $return['status'] = 0;
                $return['info']   = $upload->getError();
                $this->error( $return['info'] );
            }
            //更新数据
            $data  = array(
                //                'title_img' => $return['path'],
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
                //                'title_img' => $return['path'],
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
                    $this->redirect( 'Content/push_list' );
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
                    $this->redirect( 'Content/push_list' );
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
                    $this->redirect( 'Content/push_list' );
                } else {
                    $this->error( '更新数据失败' );
                }
                break;
            case 4:
                if ( $mark == 2 ) {
                    $data = array(
                        //                'title_img' => $return['path'],
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
                    $this->redirect( 'Content/push_list' );
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
                    $this->redirect( 'Content/push_list' );
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
                    $this->redirect( 'Content/push_list' );
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