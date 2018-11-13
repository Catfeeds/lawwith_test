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

class LawsController extends AdminController {

    /**
     * 律所列表
     * @return [type] [description]
     */
    public function index()
    {
        $model                   = D( 'Laws' );
        $nickname                = I( 'nickname' );
        $where['law_name']       = array( 'like', '%' . (string) $nickname . '%' );
        $where['lx_laws.status'] = array( 'neq', 0 );
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

        $this->major_list = M( 'major' )->where( 'status=1' )->select();    //专业标签
        $this->city_list  = M( 'City' )->field( 'id,cityname' )->select();  //城市
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
    public function subaddLaws()
    {
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
            $this->redirect( 'index' );
        } else {
            $this->error( '添加数据失败' );
        }
    }

    //编辑律所
    public function editLaws()
    {
        $lid        = lx_decrypt( I( 'get.lid' ) );
        $this->laws = M( 'Laws' )->where('id='.$lid)->find(); //律所数据
        $where      = array(
            'law'  => intval( $lid ),
            'type' => 1,
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
                    $this->redirect( 'index' );
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
                $this->redirect( 'index' );
            } else {
                $this->error( '无数据修改' );
            }
        }
    }

    /**
     * 取消推荐
     * @return [type] [description]
     */
    public function cancelPush()
    {
        $id     = lx_decrypt( I('get.mid') );
        $status = lx_decrypt( I('get.is_push') );

        $state = M('Laws')->where('id='.$id )->setField('is_push', $status);
        if ($state) {
            M('Push')->where('c_type=5 AND cid='.$id)->delete();
            $this->redirect('index');
        } else {
            $this->error( '更新失败' );
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
            $this->redirect( 'index' );
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
            $this->success( '数据导入成功', U( 'index' ) );
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
}