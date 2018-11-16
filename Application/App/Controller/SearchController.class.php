<?php
//+----------------------------------------------------------------------
// | Copyright (c) 2015-2017 http://lawyerscloud.cn All rights reserved.
//+----------------------------------------------------------------------
// | Author: 慢悠悠的丑小鸭 <1597305236@qq.com>
//+----------------------------------------------------------------------

namespace App\Controller;
use Common\Api\MCrypt;
use Common\Api\ApiPage;
use Think\Controller;

class SearchController extends Controller
{
    //条件查询求助列表和帖子
    public function condition_list ()
    {
        $AesMct = new MCrypt;
        $type = $AesMct->decrypt(urldecode(I('post.t')));   //类别
        $nowPage = I('post.nowpage');   //页码
        $num = I('post.num');   //每页条数
        $major_id = $AesMct->decrypt(urldecode(I('post.mid')));    //专业id
        $city_name = $AesMct->decrypt(urldecode(I('post.city_name')));    //城市
        $keywords = $AesMct->decrypt(urldecode(I('post.keywords'))); //关键字检索
        $where = array(
            'type' => $type,
            'status' => 1,
            '_logic'=>'and',
        );
        if(!empty($major_id)){
            $where = array(
                '_string' => "FIND_IN_SET($major_id, tag_major)",
                'type' => $type,
                'status' => 1,
                '_logic'=>'and',
            );
        }
        if (!empty($keywords)){
            $where = array(
                'title' => array('like',"%".$keywords."%"),
                'type' => $type,
                'status' => 1,
                '_logic'=>'and',
            );
        }
        if (!empty($city_name)){
            $where = array(
                '_string' => "FIND_IN_SET('$city_name', tag_city)",
                'type' => $type,
                'status' => 1,
                '_logic'=>'and',
            );
        }
        $order = array(
            'is_stick' => 'asc',
            'send_time'=>'desc',
        );
        //数据分页
        $config = array(
            'tablename' => 'Admin/ResourceRelation', // 表名
            'where'     => $where, // 查询条件
            'relation'  => true, // 关联条件
            'order'     => $order, // 排序
            'page'      => $nowPage,  // 页码，默认为首页
            'num'       => $num,  // 每页条数
            'field'     => 'id,title,author,tag_city,tag_major,imgs,views,send_time,tbd_id,inviters,type,status,is_nym,is_admin,is_money,post_amount,red_status,redpack_id'
        );
        $page = new ApiPage($config);
        $data = $page->get(); //获取分页数据
        if($data['now_page']==0)
        {
            apiReturn('1019',AJAX_FALSE);
        }else {
            $get_dat = array();
            foreach ($data as $k => $v) {
                foreach ($v as $m => $n) {
                    $get_dat[$k][$m] = $n;
                    //$get_dat[$k]['my_id'] = session('my_id');   //写入当前用户id
                    $get_dat[$k]['sums_page'] = $data['total_page'];    //总页数
                    if(!empty($data[$k]['author_info']['law'])) {    //遍历律所名称
                        $condit = array(
                            'id' => $data[$k]['author_info']['law'],
                            'status' => 1
                        );
                        $law_name = M('Laws')->where($condit)->getField('law_name');
                        $get_dat[$k]['author_info']['law_name'] = $law_name;
                    }
                }
            }
            apiReturn('1020',AJAX_TRUE,$get_dat);
        }
    }

    //检索律所
    public function laws ()
    {
        $AesMct = new MCrypt;
        $city_key = $AesMct->decrypt(urldecode(I('post.city')));     //城市关键字(字符串)
        $city = explode(',',$city_key);     //字符串转换为数组
        $major_key = $AesMct->decrypt(urldecode(I('post.major')));     //城市关键字(id)
        $nowPage = intval(I('post.nowpage'));   //页码
        $num = intval(I('post.num'));   //每页显示页数
        $map = array(
            'status' => 1,
            '_logic' => 'and',
        );
        if(!empty($city_key)){
            if(!empty($city[1])){
                $map['city'] = $city[1];
            }
            if(!empty($city[2])){
                $map['town'] = $city[2];
            }
            $map = array(
                'province' => $city[0],
            );
        }
        if(!empty($major_key)){
            $map = array(
                '_string' => "FIND_IN_SET($major_key, majors)",
            );
        }
        $order = array(
            'sort'     => 'asc',
            'regtime'  => 'desc'
        );
        //数据分页
        $config = array(
            'tablename' => 'LawsRelation', // 表名
            'where'     => $map, // 查询条件
            'relation'  => true, // 关联条件
            'order'     => $order, // 排序
            'page'      => $nowPage,  // 页码，默认为首页
            'num'       => $num  // 每页条数
        );
        $page = new ApiPage($config);
        $data = $page->get(); //获取分页数据
        if($data['now_page']==0)
        {
            apiReturn('1019',AJAX_FALSE);   //获取数据失败
        }else {
            $get_dat = array();
            foreach ($data as $k => $v) {
                foreach ($v as $m => $n) {
                    $get_dat[$k][$m] = $n;
                    $get_dat[$k]['sums'] = $data['data_sums'];   //数据总条数
                    $get_dat[$k]['sums_page'] = $data['total_page'];    //总页数
                }
            }
            apiReturn('1020',AJAX_TRUE,$get_dat);   //获取数据成功
        }
    }

    //检索用户
    public function accounts ()
    {
        $AesMct = new MCrypt;
        $type = $AesMct->decrypt(urldecode(I('post.t')));    //用户身份
        $area = $AesMct->decrypt(urldecode(I('post.city'))); //城市
        $major = $AesMct->decrypt(urldecode(I('post.majors'))); //专业
//        $price = $AesMct->decrypt(urldecode(I('post.price'))); //价格
        $nowPage = intval(I('post.nowpage'));   //页码
        $num = intval(I('post.num'));   //每页显示页数
        $city = explode(',',$area);     //字符串转换数组
        switch ($type)
        {
            case 1:     //律师
                $map['type'] = 1;
                $map['status'] = array('neq',2);
                if(!empty($area)) {
                    $map['province'] = $city[0];
                    if(!empty($city[1])){
                        $map['city'] = $city[1];
                    }
                    if(!empty($city[2])){
                        $map['town'] = $city[2];
                    }
                }

                if(!empty($major)){
                    $map['_string'] = "FIND_IN_SET($major, specialty)";
                }

//                if($price != '全部价格'){
//                    $map['price'] = $price;
//                }
                $map['_logic'] = 'and';
                $filed = 'id,uname,icon,law,majors,work_life,status,create_at,specialty,direct_time';     //查询数据字段
                break;
            case 2:     //法务
                $map['type'] = 2;
                $map['status'] = array('neq',2);
                if(!empty($area)) {
                    $map['province'] = $city[0];
                    if(!empty($city[1])){
                        $map['city'] = $city[1];
                    }
                    if(!empty($city[2])){
                        $map['town'] = $city[2];
                    }
                }
                if(!empty($major)){
                    $map['_string'] = "FIND_IN_SET($major, specialty)";
                }
                $map['_logic'] = 'and';
                $filed = 'id,uname,icon,company,position,majors,status,specialty';    //查询数据字段
                break;
            case 3:     //学者
                $map['type'] = 3;
                $map['status'] = array('neq',2);
                if(!empty($area)) {
                    $map['province'] = $city[0];
                    if(!empty($city[1])){
                        $map['city'] = $city[1];
                    }
                    if(!empty($city[2])){
                        $map['town'] = $city[2];
                    }
                }
                if(!empty($major)){
                    $map['_string'] = "FIND_IN_SET($major, specialty)";
                }
                $map['_logic'] = 'and';
                $filed = 'id,uname,icon,school,position,majors,status,specialty';     //查询的字段
                break;
            default:
                apiReturn('1019',AJAX_FALSE);   //获取数据失败
        }
        //数据分页
        $config = array(
            'tablename' => 'AccountRelation', // 表名
            'where'     => $map, // 查询条件
            'relation'  => true, // 关联条件
            'field'     => $filed,
            'page'      => $nowPage,  // 页码，默认为首页
            'num'       => $num  // 每页条数
        );
        $page = new ApiPage($config);
        $data = $page->get(); //获取分页数据
        if($data['now_page']==0)
        {
            apiReturn('1019',AJAX_FALSE);   //获取数据失败
        }else {
            $get_dat = array();
            foreach ($data as $k => $v) {   //遍历用户数据
                foreach ($v as $m => $n) {

                    //计算律师的当前的执业年限
                    if(empty($v['up_time'])) {
                        $years = $v['work_life'];
                    }else {
                        $years = $v['work_life'] + (date('Y',time())-date('Y',$v['up_time']));
                    }
                    $get_dat[$k][$m] = $n;
                    $get_dat[$k]['sums'] = $data['data_sums'];   //数据总条数
                    $get_dat[$k]['years'] = $years;     //律师执业年限
                    $get_dat[$k]['sums_page'] = $data['total_page'];    //总页数
                }
            }
            apiReturn('1020',AJAX_TRUE,$get_dat);   //获取数据成功
        }
    }


    //关键字检索用户
    public function accounts_key ()
    {
        $AesMct = new MCrypt;
        $key_word = $AesMct->decrypt(urldecode(I('post.key_word'))); //关键字
        $type = $AesMct->decrypt(urldecode(I('post.t')));    //用户身份
        $nowPage = intval(I('post.nowpage'));   //页码
        $num = intval(I('post.num'));   //每页显示页数
        switch ($type) {
            case 1:
                $map['uname'] =  array('like','%'.$key_word.'%');   //模糊匹配用户姓名
                $map['type']  = $type;  //用户身份
                $map['status'] = array('neq',2);    //可用的用户
                $filed = 'id,uname,icon,law,majors,work_life,status,create_at,specialty,direct_time';     //查询数据字段
                break;
            case 2:
                $map['uname'] =  array('like','%'.$key_word.'%');   //模糊匹配用户姓名
                $map['type']  = $type;  //用户身份
                $map['status'] = array('neq',2);    //可用的用户
                $filed = 'id,uname,icon,company,position,majors,status,specialty';    //查询数据字段
                break;
            case 3:
                $map['uname'] =  array('like','%'.$key_word.'%');   //模糊匹配用户姓名
                $map['type']  = $type;  //用户身份
                $map['status'] = array('neq',2);    //可用的用户
                $filed = 'id,uname,icon,school,position,majors,status,specialty';     //查询的字段
                break;
            default:
                apiReturn('1019',AJAX_FALSE);   //获取数据失败
                break;
        }
        //数据分页
        $config = array(
            'tablename' => 'AccountRelation', // 表名
            'where'     => $map, // 查询条件
            'field'  =>  $filed, //查询数据字段
            'relation'  => true, // 关联条件
            'page'      => $nowPage,  // 页码，默认为首页
            'num'       => $num  // 每页条数
        );
        $page = new ApiPage($config);
        $data = $page->get(); //获取分页数据
        if($data['now_page']==0)
        {
            apiReturn('1019',AJAX_FALSE);   //获取数据失败
        } else {
            $get_dat = array();
            foreach ($data as $k => $v) {   //遍历用户数据
                foreach ($v as $m => $n) {

                    //计算律师的当前的执业年限
                    if(empty($v['up_time'])) {
                        $years = $v['work_life'];
                    }else {
                        $years = $v['work_life'] + (date('Y',time())-date('Y',$v['up_time']));
                    }
                    $get_dat[$k][$m] = $n;
                    $get_dat[$k]['sums'] = $data['data_sums'];   //数据总条数
                    $get_dat[$k]['years'] = $years;     //律师执业年限
                    $get_dat[$k]['sums_page'] = $data['total_page'];    //总页数
                }
            }
            apiReturn('1020',AJAX_TRUE,$get_dat);   //获取数据成功
        }
    }

    //关键词查询资讯列表
    public function news_search () {
        $AesMct   = new MCrypt;
        $major_id = $AesMct->decrypt(urldecode(I('post.mid')));    //专业id
        $sort     = $AesMct->decrypt( urldecode(I('post.sort')));   //所属类别
        $keywords = $AesMct->decrypt( urldecode(I('post.keywords'))); //关键字检索
        $nowPage  = I( 'post.nowpage' );   //页码
        $num      = I( 'post.num' );   //每页条数
        $where = array(
            'type' => 3,
            'status' => 1,
            '_logic'=>'and',
        );
        $order = array(
            'send_time' => 'desc',
            'views'=>'desc',
        );
        if (!empty($keywords)){
            $where = array(
                'title' => array('like',"%".$keywords."%"),
                'sort' => $sort,
                'type' => 3,
                'status' => 1,
                '_logic'=>'and',
            );
        }
        //资讯专业领域筛选
        if(!empty($major_id)){
            $where = array(
                '_string' => "FIND_IN_SET($major_id, tag_major)",
                'sort' => $sort,
                'status' => 1,
                '_logic'=>'and',
            );
        }
        $config = array(
            'tablename' => 'Admin/ResourceRelation', // 表名
            'where'     => $where, // 查询条件
            'relation'  => true, // 关联条件
            'order'     => $order, // 排序
            'page'      => $nowPage,  // 页码，默认为首页
            'num'       => $num,  // 每页条数
            'field'     => 'id,title,author,title_img,tag_city,tag_major,imgs,views,send_time,tbd_id,inviters,type,status,is_nym,is_admin,is_money,post_amount'
        );
        $page = new ApiPage($config);
        $data = $page->get(); //获取分页数据
        if($data['now_page']==0)
        {
            apiReturn('1019',AJAX_FALSE);
        }else {
            $get_dat = array();
            foreach ($data as $k => $v) {
                foreach ($v as $m => $n) {
                    $get_dat[$k][$m] = $n;
                    $get_dat[$k]['sums_page'] = $data['total_page'];    //总页数
                    if(!empty($data[$k]['author_info']['law'])) {    //遍历律所名称
                        $condit = array(
                            'id' => $data[$k]['author_info']['law'],
                            'status' => 1
                        );
                        $law_name = M('Laws')->where($condit)->getField('law_name');
                        $get_dat[$k]['author_info']['law_name'] = $law_name;
                    }
                }
            }
            apiReturn('1020',AJAX_TRUE,$get_dat);
        }
    }
}