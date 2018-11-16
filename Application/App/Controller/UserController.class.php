<?php
//+----------------------------------------------------------------------
// | Copyright (c) 2015-2017 http://lawyerscloud.cn All rights reserved.
//+----------------------------------------------------------------------
// | Author: 慢悠悠的丑小鸭 <1597305236@qq.com>
//+----------------------------------------------------------------------

namespace App\Controller;

use Common\Api\ChatApi;
use Common\Api\MCrypt;
use Admin\Model\PictureModel;
use Common\Api\letvCloud;
use Common\Api\ApiPage;
use Think\Controller;
use Think\Model;

class UserController extends BasicController
{
    //提交身份认证
    public function auth_check()
    {
        $AesMct = new MCrypt;
        $uid = session('my_id');    //当前用户id
        $num_img = $AesMct->decrypt(urldecode(I('post.num_img')));   //证件图片
        if(empty($num_img)) {
            if($uimg = M('Account')->where('id=' . $uid)->field('num_img')->find()) {
                apiReturn('1022', AJAX_TRUE, $uimg);  //获取证件照成功
            } else {
                apiReturn('1021', AJAX_FALSE);   //获取证件照失败
            }
        } else {
            $data = array(
                'num_img'   => $num_img,
                'auth_time' => time(),
                'status'    => '0'
            );
            if(M('Account')->where('id=' . $uid)->save($data)) {
                apiReturn('1024', AJAX_TRUE, $data);    //上传证件照成功
            } else {
                apiReturn('1023', AJAX_FALSE);   //上传证件照失败
            }
        }
    }

    //信息完善
    public function myProfile()
    {
        $accid = session('accid');      //用户手机号码
        //$p_token = session('token');      //用户标识
        $my_id = session('my_id');  //用户id
        $p_role = urldecode(I('post.role'));       //用户身份
        $p_name = I('post.uname');      //姓名
        $p_sex = I('post.gender');      //性别 1表示男，2表示女
        $p_city = explode(',', I('post.city'));       //所在城市
        $p_major = I('post.major');     //专业领域
        $p_law = I('post.law');         //所属律所
        $p_years = I('post.years');     //执业年限
        $p_number = I('post.id_number');//执业证号
        $p_price = I('post.price');     //价格区间
        $p_job = I('post.job');         //职务
        $p_firm = I('post.firm');       //所属公司
        $p_school = I('post.school');   //所属院校
        $p_age = I('post.age');       //年龄
        $p_diploma = I('post.diploma');       //学历
        $p_education = I('post.education');       //教育背景
        $p_professional = I('post.professional');       //执业经历
        $p_prize = I('post.prize');       //专业奖项
        $p_icon = I('post.icon');       //上传头像url
        $bj_img = I('post.bj_img');       //上传背景头像url
        $p_remark = I('post.remark');   //个人介绍
        $AesMcrypt = new MCrypt;    //调用AES加密接口
        $yunxin = new ChatApi;      //调用云信接口
        $role = $AesMcrypt->decrypt($p_role);
        //$accid = $p_accid;
        //$token = $p_token;
        //$where = array(
        //    'mobile' => $accid,
        //    'token'  => $token
        //);
        $u_type = M('Account')->where('id=' . $my_id)->getField('type');  //当前用户的身份
        if($p_city[0] == '全部地域') {
            $province = '';
        } else {
            $province = $p_city[0];
        }
        if(!empty($p_city[1]) && $p_city[1] != '(null)') {
            $city = $p_city[1];
        } else {
            $city = '';
        }
        if(!empty($p_city[2]) && $p_city[2] != '(null)') {
            $town = $p_city[2];
        } else {
            $town = '';
        }
        switch($role) {
            case 1:         //律师

                //获取所有数据
                $data = array(
                    'type'          => $role,
                    'uname'         => $p_name,
                    'icon'          => $p_icon,
                    'bj_img'        => $bj_img,
                    'gender'        => $p_sex,
                    'province'      => $province,
                    'city'          => $city,
                    'town'          => $town,
                    'specialty'     => $p_major,
                    'work_life'     => $p_years,
                    'lawyer_num'    => $p_number,
                    'price'         => $p_price,
                    'prize'         => $p_prize,
                    'professional'  => $p_professional,
                    'education'     => $p_education,
                    'hight_diploma' => $p_diploma,
                    'birth'         => $p_age,
                    'remark'        => $p_remark,
                    'up_time'       => time(),
                );
                if(!empty($p_law)) {
                    $data['law'] = $p_law;
                }

                //同步个人专业标签数据
                $majors = M('Account')->where('id=' . $my_id)->getField('majors');    //获取当前用户关注的专业标签id集
                $majors_arr = explode(',', $majors);     //专业标签id集转换为数组
                $specialty = explode(',', $p_major);     //用户所属专业id集转换为数组
                $diff_arr = array_diff($specialty, $majors_arr);     //获取两者的差集
                foreach($diff_arr as $v) {
                    array_unshift($majors_arr, $v);
                }
                $tag_major = implode(',', $majors_arr);
                M('Account')->where('id=' . $my_id)->setField('majors', $tag_major);

                //同步个人城市标签数据
                $res = M('Account')->where('id=' . $my_id)->getField('tag_citys');
                $res_arr = explode(',', $res);
                if(!in_array($province, $res_arr)) {
                    array_unshift($res_arr, $province);
                    $citys = implode(',', $res_arr);
                    M('Account')->where('id=' . $my_id)->setField('tag_citys', $citys);
                }
                //                //整理云信扩展数据
                //                $arr = array(
                //                    'province' => $p_city[0],
                //                    'city'  => $p_city[1],
                //                    'town'  => $town,
                //                    'majors'=> $p_major,
                //                    'work_life' => $p_years,
                //                    'lawyer_num' => $p_number,
                //                    'price' => $p_price,
                //                    'prize' => $p_prize,
                //                    'professional'  =>  $p_professional,
                //                    'education' =>  $p_education,
                //                    'hight_diploma' =>  $p_diploma,
                //                    'remark' => $p_remark,
                //                );
                //                if(!empty($p_law)){
                //                    $arr['law'] = $p_law;
                //                }
                //                //转换json数据
                //                $json_data = json_encode($arr);
                //更新云信数据
                $result = $yunxin->updateUinfo($accid, $name = $data['uname'], $icon = $data['icon'], $sign = '', $email = '', $birth = $data['birth'], $mobile = $accid, $gender = $data['gender']);
                if($result['code'] == 200) {    //更新云信数据成功
                    //管理员更改律所信息，清空管理员信息
                    $u_data = D('AccountRelation')
                        ->relation(true)
                        ->where('id=' . $my_id)
                        ->field('law')
                        ->find();
                    if($u_data['law_info']['uadmin'] = $my_id) {
                        if(!empty($p_law) && ($p_law != $u_data['law'])) {
                            M('Laws')->where('id=' . $u_data['law'])->setField('uadmin', 0);
                        }
                    }
                    //清空认证
                    if(!empty($role) && ($role != $u_type)) {
                        $save_data = array(
                            'lawyer_num' => '',
                            'num_img'    => '',
                            'status'     => 0
                        );
                        M('Account')->where('id=' . $my_id)->save($save_data);
                    }
                    if(M('Account')->where('id=' . $my_id)->save($data))   //同步数据到本地数据库
                    {
                        apiReturn('1018', AJAX_TRUE, $data);  //更新数据成功
                    } else {
                        apiReturn('1017', AJAX_FALSE);  //更新数据失败
                    }
                } else {
                    //更新云信数据成功
                    apiReturn('1017', AJAX_FALSE);
                }
                break;
            case 2:         //法务
                $data = array(
                    'type'          => $role,
                    'uname'         => $p_name,
                    'icon'          => $p_icon,
                    'bj_img'        => $bj_img,
                    'gender'        => $p_sex,
                    'province'      => $province,
                    'city'          => $city,
                    'town'          => $town,
                    'specialty'     => $p_major,
                    'position'      => $p_job,
                    'company'       => $p_firm,
                    'birth'         => $p_age,
                    'hight_diploma' => $p_diploma,
                    'remark'        => $p_remark,
                    'up_time'       => time(),
                );

                //同步个人专业标签数据
                $majors = M('Account')->where('id=' . $my_id)->getField('majors');    //获取当前用户关注的专业标签id集
                $majors_arr = explode(',', $majors);     //专业标签id集转换为数组
                $specialty = explode(',', $p_major);     //用户所属专业id集转换为数组
                $diff_arr = array_diff($specialty, $majors_arr);     //获取两者的差集
                foreach($diff_arr as $v) {
                    array_unshift($majors_arr, $v);
                }
                $tag_major = implode(',', $majors_arr);
                M('Account')->where('id=' . $my_id)->setField('majors', $tag_major);

                ////同步个人城市标签数据
                $res = M('Account')->where('id=' . $my_id)->getField('tag_citys');
                $res_arr = explode(',', $res);
                if(!in_array($province, $res_arr)) {
                    array_unshift($res_arr, $province);
                    $citys = implode(',', $res_arr);
                    M('Account')->where('id=' . $my_id)->setField('tag_citys', $citys);
                }
                //$arr = array(
                //    'province' => $p_city[0],
                //    'city'  => $p_city[1],
                //    'town'  => $town,
                //    'majors'=> $p_major,
                //    'position' => $p_job,
                //    'company' => $p_firm,
                //    'remark' => $p_remark,
                //    'hight_diploma' =>  $p_diploma,
                //);
                //$json_data = json_encode($arr);
                $result = $yunxin->updateUinfo($accid, $name = $data['uname'], $icon = $data['icon'], $sign = '', $email = '', $birth = $data['birth'], $mobile = $accid, $gender = $data['gender']);
                if($result['code'] == 200) {
                    //清空认证
                    if(!empty($role) && ($role != $u_type)) {
                        $save_data = array(
                            'lawyer_num' => '',
                            'num_img'    => '',
                            'status'     => 0
                        );
                        M('Account')->where('id=' . $my_id)->save($save_data);
                    }
                    if(M('Account')->where('id=' . $my_id)->save($data)) {

                        apiReturn('1018', AJAX_TRUE, $data);
                    } else {
                        apiReturn('1017', AJAX_FALSE);
                    }
                } else {
                    apiReturn('1017', AJAX_FALSE);
                }
                break;
            case 3:         //专家、学者
                $data = array(
                    'type'          => $role,
                    'uname'         => $p_name,
                    'icon'          => $p_icon,
                    'bj_img'        => $bj_img,
                    'gender'        => $p_sex,
                    'province'      => $province,
                    'city'          => $city,
                    'town'          => $town,
                    'specialty'     => $p_major,
                    'position'      => $p_job,
                    'school'        => $p_school,
                    'birth'         => $p_age,
                    'remark'        => $p_remark,
                    'hight_diploma' => $p_diploma,
                    'up_time'       => time(),
                );

                //同步个人专业标签数据
                $majors = M('Account')->where('id=' . $my_id)->getField('majors');    //获取当前用户关注的专业标签id集
                $majors_arr = explode(',', $majors);     //专业标签id集转换为数组
                $specialty = explode(',', $p_major);     //用户所属专业id集转换为数组
                $diff_arr = array_diff($specialty, $majors_arr);     //获取两者的差集
                foreach($diff_arr as $v) {
                    array_unshift($majors_arr, $v);
                }
                $tag_major = implode(',', $majors_arr);
                M('Account')->where('id=' . $my_id)->setField('majors', $tag_major);

                //同步个人城市标签数据
                $res = M('Account')->where('id=' . $my_id)->getField('tag_citys');
                $res_arr = explode(',', $res);
                if(!in_array($province, $res_arr)) {
                    array_unshift($res_arr, $province);
                    $citys = implode(',', $res_arr);
                    M('Account')->where('id=' . $my_id)->setField('tag_citys', $citys);
                }
                //$arr = array(
                //    'province' => $p_city[0],
                //    'city'  => $p_city[1],
                //    'town'  => $town,
                //    'majors'=> $p_major,
                //    'position' => $p_job,
                //    'school' => $p_school,
                //    'remark' => $p_remark,
                //    'hight_diploma' =>  $p_diploma,
                //);
                //$json_data = json_encode($arr);
                $result = $yunxin->updateUinfo($accid, $name = $data['uname'], $icon = $data['icon'], $sign = '', $email = '', $birth = $data['birth'], $mobile = $accid, $gender = $data['gender']);
                if($result['code'] == 200) {
                    //清空认证
                    if(!empty($role) && ($role != $u_type)) {
                        $save_data = array(
                            'lawyer_num' => '',
                            'num_img'    => '',
                            'status'     => 0
                        );
                        M('Account')->where('id=' . $my_id)->save($save_data);
                    }
                    if(M('Account')->where('id=' . $my_id)->save($data)) {
                        apiReturn('1018', AJAX_TRUE, $data);
                    } else {
                        apiReturn('1017', AJAX_FALSE);
                    }
                } else {
                    apiReturn('1017', AJAX_FALSE);
                }
                break;
            case 5:         //其他
                $data = array(
                    'type'          => $role,
                    'uname'         => $p_name,
                    'icon'          => $p_icon,
                    'bj_img'        => $bj_img,
                    'gender'        => $p_sex,
                    'province'      => $province,
                    'city'          => $city,
                    'town'          => $town,
                    'specialty'     => $p_major,
                    'birth'         => $p_age,
                    'remark'        => $p_remark,
                    'company'       => $p_firm,
                    'position'      => $p_job,
                    'hight_diploma' => $p_diploma,
                    'up_time'       => time(),
                );

                //同步个人专业标签数据
                $majors = M('Account')->where('id=' . $my_id)->getField('majors');    //获取当前用户关注的专业标签id集
                $majors_arr = explode(',', $majors);     //专业标签id集转换为数组
                $specialty = explode(',', $p_major);     //用户所属专业id集转换为数组
                $diff_arr = array_diff($specialty, $majors_arr);     //获取两者的差集
                foreach($diff_arr as $v) {
                    array_unshift($majors_arr, $v);
                }
                $tag_major = implode(',', $majors_arr);
                M('Account')->where('id=' . $my_id)->setField('majors', $tag_major);

                //同步个人城市标签数据
                $res = M('Account')->where('id=' . $my_id)->getField('tag_citys');
                $res_arr = explode(',', $res);
                if(!in_array($province, $res_arr)) {
                    array_unshift($res_arr, $province);
                    $citys = implode(',', $res_arr);
                    M('Account')->where('id=' . $my_id)->setField('tag_citys', $citys);
                }
                //$arr =array(
                //    'province' => $p_city[0],
                //    'city'  => $p_city[1],
                //    'town'  => $town,
                //    'majors'=> $p_major,
                //    'remark' => $p_remark,
                //    'company' => $p_firm,
                //    'position' => $p_job,
                //    'hight_diploma' =>  $p_diploma,
                //);
                //$json_data = json_encode($arr);
                $result = $yunxin->updateUinfo($accid, $name = $data['uname'], $icon = $data['icon'], $sign = '', $email = '', $birth = $data['birth'], $mobile = $accid, $gender = $data['gender']);
                if($result['code'] == 200) {
                    //清空认证
                    if(!empty($role) && ($role != $u_type)) {
                        $save_data = array(
                            'lawyer_num' => '',
                            'num_img'    => '',
                            'status'     => 0
                        );
                        M('Account')->where('id=' . $my_id)->save($save_data);
                    }
                    if(M('Account')->where('id=' . $my_id)->save($data)) {

                        apiReturn('1018', AJAX_TRUE, $data);
                    } else {
                        apiReturn('1017', AJAX_FALSE);
                    }
                } else {
                    apiReturn('1017', AJAX_FALSE);
                }
                break;
            default:
                apiReturn('1005', AJAX_FALSE);
                break;
        }
    }

    //用户资料
    public function user_info()
    {
        $AesMct = new MCrypt;
        $uid = $AesMct->decrypt(urldecode(I('post.uid'))); //用户id
        $type = $AesMct->decrypt(urldecode(I('post.type')));    //参数类型 默认用户id 1标识手机号码
//        $uid =urldecode(I('post.uid')); //用户id
//        $type =urldecode(I('post.type'));    //参数类型 默认用户id 1标识手机号码

        if(empty($uid)) {
            $where = array(
                'id' => session('my_id')
            );
        } elseif(intval($type) == 1) {
            $where = array(
                'mobile' => $uid
            );
        } else {
            $where = array(
                'id' => $uid
            );
        }
        $model = D('AccountRelation');
        $res = $model->relation(true)->where($where)->field('id,uname,mobile,gender,icon,bj_img,remark,email,birth,province,city,town,tag_citys,specialty,majors,work_life,law,lawyer_num,company,position,school,hight_diploma,education,professional,prize,price,type,create_at,up_time,status,num_img,is_hide,direct_price,case_price,direct_time')->select();
        $data = $res[0];
        $num = date('y', time()) - date('y', $data['up_time']);
        $data['years'] = $data['work_life'] + $num;     //执业年限
        $data['my_id'] = session('my_id');

        $resourceModel = M('resource_comment');
        $comm = $resourceModel->alias('rc')
            ->field('r.id,r.title')
            ->join('left join lx_resource  as r on rc.rid = r.id')
            ->where(['rc.uid'=>$data['id'],'rc.tbd'=>1,'r.status'=>1])
            ->group('rc.rid')
            ->order("time DESC")
            ->select();
        if($comm != null){
            foreach($comm as &$value){
                $array = array(
                    'rid'=>$value['id'],
                    'uid'=>$data['id'],
                    'rc.tbd'=>1 //查询优质回答
                );
                $content = $resourceModel->alias('rc')
                    ->field('id,content,type,likes,dislikes,tbd,time')
                    ->where($array)
                    ->order("time DESC")
                    ->select();
                foreach($content as &$val){
                    if($val['type'] == 1){
                        $uu = "dwbppqvkxs"; //用户唯一标识码   dwbppqvkxs
                        $pu = "a2ee3b5de4"; //播放器唯一标识码  a2ee3b5de4
                        $type = 'url';  //接口类型
                        $auto_play = 1; //是否自动播放
                        $width = $AesMct->decrypt(urldecode(I('post.width')));  //播放器宽度
                        $height = $AesMct->decrypt(urldecode(I('post.height'))); //播放器高度
                        $letv = new LetvCloud;
                        $val['content']
                            = $letv->videoGetPlayinterface($uu, video_info($val['content'], 'video_unique'), $type, $pu, $auto_play, $width, $height);
                    }

                    $commentCount = $resourceModel
                        ->field('count(id) as count')
                        ->where('pid='.$val['id'])
                        ->find();
                    $val['commentCount'] = $commentCount;
                }

                $value['commentInfo']=$content;
            }
        }
        $data['goodContent'] = $comm;
//        if(!empty($comm)){
//            $data['goodContent'] = $comm;
//        }else{
//            $data['goodContent'] = '';
//        }
        apiReturn('1020', AJAX_TRUE, $data);
    }

    //上传头像
    public function uploadPicture()
    {
        /* 返回标准数据 */
        $return = array('code' => 1, 'info' => '上传成功');
        /* 调用文件上传组件上传文件 */
        $Picture = D('Admin/Picture');
        C('PICTURE_UPLOAD.maxSize', 2 * 1024 * 1024);   //上传的文件大小限制 (0-不做限制)
        C('PICTURE_UPLOAD.exts', 'jpg,gif,png,jpeg');    //允许上传的文件后缀
        C('PICTURE_UPLOAD.savePath', 'Icon/');   //设置上传子目录
        $url = C('UPLOAD_SITEIMG_OSS.domain');  //拼接url头
        $info = $Picture->upload(
            $_FILES,
            C('PICTURE_UPLOAD'),
            C('PICTURE_UPLOAD_DRIVER'),
            C('UPLOAD_SITEIMG_OSS'),
            $url
        ); //TODO:上传到远程服务器

        /* 记录图片信息 */
        if($info) {
            $return['code'] = 1;
            $return = array_merge($info['filename'], $return);
            /* 返回JSON数据 */
            apiReturn('1022', AJAX_TRUE, $return);    //上传图片到云储存成功
        } else {
            $return['code'] = 0;
            $return['info'] = $Picture->getError();

            /* 返回JSON数据 */
            apiReturn('1021', AJAX_FALSE, $return);   //上传图片到云储存失败
        }
    }

    // 上传个人资料背景图
    public function upload_bgimg()
    {
        $return = array('code' => 1, 'info' => '上传成功');
        // 调用文件上传组件上传文件
        $Picture = D('Admin/Picture');
        C('PICTURE_UPLOAD.maxSize', 1 * 1024 * 1024);   //上传的文件大小限制 (0-不做限制)
        C('PICTURE_UPLOAD.exts', 'jpg,gif,png,jpeg');    //允许上传的文件后缀
        C('PICTURE_UPLOAD.savePath', 'Bgimg/');   //设置上传子目录
        $url = C('UPLOAD_SITEIMG_OSS.domain');  //拼接url头
        $info = $Picture->upload(
            $_FILES,
            C('PICTURE_UPLOAD'),
            C('PICTURE_UPLOAD_DRIVER'),
            C('UPLOAD_SITEIMG_OSS'),
            $url
        ); //TODO:上传到远程服务器

        // 记录图片信息
        if($info) {
            $return['code'] = 1;
            $return = array_merge($info['filename'], $return);
            apiReturn('1024', AJAX_TRUE, $return);    //上传图片到云储存成功
        } else {
            $return['code'] = 0;
            $return['info'] = $Picture->getError();
            apiReturn('1023', AJAX_FALSE, $return);   //上传图片到云储存失败
        }
    }

    //多图上传
    public function uploadImgs()
    {
        /* 返回标准数据 */
        $return = array('code' => 1, 'info' => '上传成功');
        /* 调用文件上传组件上传文件 */
        $Picture = D('Admin/Picture');
        C('PICTURE_UPLOAD.maxSize', 500 * 1024);   //上传的文件大小限制 (0-不做限制)
        C('PICTURE_UPLOAD.exts', 'jpg,gif,png,jpeg');    //允许上传的文件后缀
        C('PICTURE_UPLOAD.savePath', 'Picture/');   //设置上传子目录
        $url = C('UPLOAD_SITEIMG_OSS.domain');  //拼接url头
        $info = $Picture->upload(
            $_FILES,
            C('PICTURE_UPLOAD'),
            C('PICTURE_UPLOAD_DRIVER'),
            C('UPLOAD_SITEIMG_OSS'),
            $url
        ); //TODO:上传到远程服务器
        /* 记录图片信息 */
        if($info) {
            $return['code'] = 1;
            $data = array();
            foreach($info as $k => $v) {
                $data[ $k ]['file_path'] = $v['path'];
                $data[ $k ]['file_id'] = $v['id'];
            }
            $return['file_info'] = $data;
            /* 返回JSON数据 */
            apiReturn('1022', AJAX_TRUE, $return);    //上传图片到云储存成功
        } else {
            $return['code'] = 0;
            $return['info'] = $Picture->getError();
            /* 返回JSON数据 */
            apiReturn('1021', AJAX_FALSE, $return);   //上传图片到云储存失败
        }
    }

    //专业数据
    public function getMajor()
    {
        $AesMcrypt = new MCrypt;    //调用AES加密接口
        $mid = $AesMcrypt->decrypt(urldecode(I('post.mid'))); //专业id集
        $my_tag = $AesMcrypt->decrypt(urldecode(I('podt.is_my'))); //是否获取自己关注的专业 1是
        $model = M('Major');
        if($my_tag == 1) {
            $accid = session('accid'); //用户accid
            $token = session('token'); //用户云信token
            $majors = M('Account')->where(array('mobile' => $accid, 'token' => $token))->field('majors');
            $arr = explode(',', $majors);
            $data[] = '';
            foreach($arr as $k => $id) {
                $data[ $k ] = $model->where('id=' . $id)->field('id, major_name, pid')->find();
            }
            apiReturn('1020', AJAX_TRUE, $data);  //获取数据成功
        } else {
            if(empty($mid)) //获取所有数据
            {
                $data = $model->where('status=1')->order('sort')->field('id, major_name, pid')->select();
                apiReturn('1020', AJAX_TRUE, $data);  //获取数据成功
            } else { //获取指定专业的数据
                $arr = explode(',', $mid);
                $data[] = '';
                foreach($arr as $k => $id) {
                    $data[ $k ] = $model->where('id=' . $id)->field('id, major_name, pid')->find();
                }
                apiReturn('1020', AJAX_TRUE, $data);  //获取数据成功
            }
        }

    }

    //获取城市数据
    //public function getCity ()
    //{
    //    $AesMcrypt = new MCrypt;    //调用AES加密接口
    //    $my_tag = $AesMcrypt->decrypt(urldecode(I('is_my')));   //是否获取自己关注的专业 1是
    //    if($my_tag == 1) {  //获取用户关注的城市标签
    //        $accid = session('accid'); //用户accid
    //        $token = session('token'); //用户云信token
    //        $arr = M('Account')->where(array('mobile'=>$accid,'token'=>$token))->field('tag_citys')->find();
    //        $data = explode(',',$arr['tag_citys']);
    //        apiReturn('1020',AJAX_TRUE,$data);  //获取数据成功
    //    }else { //获取所有城市标签
    //        $data = M('City')->where('type=1')->field('cityname')->select();
    //        $get_dat = array();
    //        foreach ($data as $k => $v) {
    //            $get_dat[$k] = $v['cityname'];
    //        }
    //        apiReturn('1020',AJAX_TRUE,$get_dat);
    //    }
    //}

    //查询律所数据结果显示
    public function getLaw()
    {
        $keyword = I('post.keyword'); //客户端搜素的关键字
        $nowPage = I('post.nowpage'); //页码
        $num = I('post.num');    //每页显示条数
        $map['status'] = 1;
        $map['law_name'] = array('like', '%' . $keyword . '%');  //查询条件
        $config = array(
            'tablename' => 'LawsRelation', // 表名
            'where'     => $map, // 查询条件
            'relation'  => true, // 关联条件
            'order'     => 'sort,regtime desc', // 排序
            'page'      => $nowPage,  // 页码，默认为首页
            'num'       => $num,  // 每页条数
            'field'     => 'id,law_name,province,city,town,logo'    //返回指定信息
        );
        $page = new ApiPage($config);
        $data = $page->get();
        if($nowPage == 0) {
            apiReturn('1019', AJAX_FALSE);   //获取数据失败
        } else {
            $get_dat = array();
            foreach($data as $k => $v) {
                foreach($v as $m => $n) {
                    $get_dat[ $k ][ $m ] = $n;
                    $get_dat[ $k ]['sums'] = $data['data_sums'];   //数据总条数
                    $get_dat[ $k ]['sums_page'] = $data['total_page'];    //总页数
                }
            }
            apiReturn('1020', AJAX_TRUE, $get_dat);  //获取数据成功
        }
    }

    //创建律所
    public function create_law()
    {
        $AesMcrypt = new MCrypt;    //调用AES加密接口
        $law_name = $AesMcrypt->decrypt(urldecode(I('law_name')));      //律所名称
        $data['phone'] = $AesMcrypt->decrypt(urldecode(I('law_phone')));     //律所电话
        $data['address'] = $AesMcrypt->decrypt(urldecode(I('law_address')));   //律所详细地址
        $data['province'] = $AesMcrypt->decrypt(urldecode(I('province')));   //省
        $data['city'] = $AesMcrypt->decrypt(urldecode(I('city')));   //市
        $data['town'] = $AesMcrypt->decrypt(urldecode(I('town')));   //区
        $data['profile'] = $AesMcrypt->decrypt(urldecode(I('profile')));   //律所详情
        $data['regtime'] = time();  //加入时间
        $data['law_name'] = $law_name;
        if($res = M('Laws')->where("law_name='$law_name'")->select()) {
            apiReturn('1025', AJAX_FALSE);   //律所名称已存在
        } else {
            if($lid = M('Laws')->add($data)) {
                apiReturn('1026', AJAX_TRUE, $lid);   //创建律所成功，返回律所id
            } else {
                apiReturn('1027', AJAX_FALSE);   //创建律所失败
            }
        }
    }

    //我的提问
    public function my_question()
    {
        $tid = I('post.tid');   //别人id
        if(empty($tid)) {
            $uid = session('my_id');    //用户id
        } else {
            $uid = $tid;
        }
        $nowPage = I('post.nowpage'); //页码
        $num = I('post.num');    //每页显示条数
        $where = array(
            'author' => $uid,
            'type'   => 2,
            'status' => 1
        );
        $order = array(
            'send_time' => 'desc',
            'views'     => 'desc'
        );
        //数据分页
        $config = array(
            'tablename' => 'Admin/ResourceRelation', // 表名
            'relation'  => true, // 关联条件
            'order'     => $order, // 排序
            'where'     => $where,  //条件
            'page'      => $nowPage,  // 页码，默认为首页
            'num'       => $num,  // 每页条数
            'field'     => 'id,title,send_time,views,tag_major,imgs,status'
        );
        $page = new ApiPage($config);
        $data = $page->get(); //获取分页数据

        if($nowPage == 0) {
            apiReturn('1019', AJAX_FALSE);   //获取数据失败
        } else {
            $get_dat = array();
            foreach($data as $k => $v) {
                foreach($v as $m => $n) {
                    $get_dat[ $k ][ $m ] = $n;
                    $get_dat[ $k ]['sums_page'] = $data['total_page'];    //总页数
                }
            }
            apiReturn('1020', AJAX_TRUE, $get_dat);   //获取数据成功
        }
    }

    //我的回答
    public function my_reply()
    {
        $AesMct = new MCrypt;

        $tid =$AesMct->decrypt(urldecode(I('post.tid')));   //别人id
//        $tid = I('post.tid');   //别人id

        if(empty($tid)) {
            $uid = session('my_id');    //用户id
        } else {
            $uid = $tid;
        }
        $nowPage = I('post.nowpage'); //页码
        $num = I('post.num');    //每页显示条数
        $model = M('Resource_comment');
        $ids = $model->distinct(true)->where('uid=' . $uid)->getField('rid', true);
        $where = array(
            'id'     => array('in', $ids),
            'type'   => 2,
            'status' => 1
        );
        $order = array(
            'send_time' => 'desc',
            'views'     => 'desc'
        );
        //数据分页
        $config = array(
            'tablename' => 'Admin/ResourceRelation', // 表名
            'relation'  => 'comment_sums', // 关联条件
            'order'     => $order, // 排序
            'where'     => $where,  //条件
            'page'      => $nowPage,  // 页码，默认为首页
            'num'       => $num,  // 每页条数
            'field'     => 'id,title,author,send_time,views,tag_major,imgs,status'
        );
        $page = new ApiPage($config);
        $data = $page->get(); //获取分页数据
        if($nowPage == 0) {
            apiReturn('1019', AJAX_FALSE);   //获取数据失败
        } else {
            $get_dat = array();
            foreach($data as $k => $v) {
                $where1 = array(
                    'uid' => $uid,
                    'rid' => $data[ $k ]['id']
                );
                foreach($v as $m => $n) {
                    $get_dat[ $k ][ $m ] = $n;
                    $get_dat[ $k ]['sums_page'] = $data['total_page'];    //总页数
//                    $get_dat[ $k ]['comm_list'] = M('Resource_comment')->where($where1)->order('time desc')->limit(3)->field('content,time,tbd,is_nym,type')->select();
                    $res = M('Resource_comment')->where($where1)->order('time desc')->limit(3)->field('content,time,likes,tbd,is_nym,type')->select();
                    foreach($res as &$val){
                        if($val['type'] == 1){
                            $uu = "dwbppqvkxs"; //用户唯一标识码   dwbppqvkxs
                            $pu = "a2ee3b5de4"; //播放器唯一标识码  a2ee3b5de4
                            $type = 'url';  //接口类型
                            $auto_play = 1; //是否自动播放
                            $width = $AesMct->decrypt(urldecode(I('post.width')));  //播放器宽度
                            $height = $AesMct->decrypt(urldecode(I('post.height'))); //播放器高度
                            $letv = new LetvCloud;
                            $val['content']
                                = $letv->videoGetPlayinterface($uu, video_info($val['content'], 'video_unique'), $type, $pu, $auto_play, $width, $height);
                        }
                    }
                    $get_dat[ $k ]['comm_list'] = $res;
                }
            }
            apiReturn('1020', AJAX_TRUE, $get_dat);   //获取数据成功
        }
    }

    //我的资讯发帖
    public function my_posts()
    {
        $tid = I('post.tid');   //别人id
        if(empty($tid)) {
            $uid = session('my_id');    //用户id
        } else {
            $uid = $tid;
        }
        $nowPage = I('post.nowpage'); //页码
        $num = I('post.num');    //每页显示条数
        $where = array(
            'author' => $uid,
            'type'   => 3,  //内容类别：1话题2求助3资讯
            'status' => 1
        );
        $order = array(
            'send_time' => 'desc',
            'views'     => 'desc'
        );
        //数据分页
        $config = array(
            'tablename' => 'Admin/ResourceRelation', // 表名
            'relation'  => true, // 关联条件
            'order'     => $order, // 排序
            'where'     => $where,  //条件
            'page'      => $nowPage,  // 页码，默认为首页
            'num'       => $num,  // 每页条数
            'field'     => 'id,title,content,title_img,send_time,views,tag_major,imgs,status,is_money'
        );
        $page = new ApiPage($config);
        $data = $page->get(); //获取分页数据

        if($nowPage == 0) {
            apiReturn('1019', AJAX_FALSE);   //获取数据失败
        } else {
            $get_dat = array();
            foreach($data as $k => $v) {
                foreach($v as $m => $n) {
                    $get_dat[ $k ][ $m ] = $n;
                    $get_dat[ $k ]['sums_page'] = $data['total_page'];    //总页数
                }
            }
            apiReturn('1020', AJAX_TRUE, $get_dat);   //获取数据成功
        }
    }

    //我的评论
    public function my_comment()
    {
        $AesMct   = new MCrypt;
        $tid = I('get.tid');   //别人id
        if(empty($tid)) {
            $uid = session('my_id');    //用户id
        } else {
            $uid = $tid;
        }
        $nowPage = I('get.nowpage'); //页码
        $num = I('get.num');    //每页显示条数
        $model = M('Resource_comment');
        $ids = $model->distinct(true)->where('uid=' . $uid)->getField('rid', true);

        $where = array(
            'id'     => array('in', $ids),
            'type'   => 3,
            'status' => 1
        );
        $order = array(
            'send_time' => 'desc',
            'views'     => 'desc'
        );
        //数据分页
        $config = array(
            'tablename' => 'Admin/ResourceRelation', // 表名
            'relation'  => 'comment_sums', // 关联条件
            'order'     => $order, // 排序
            'where'     => $where,  //条件
            'page'      => $nowPage,  // 页码，默认为首页
            'num'       => $num,  // 每页条数
            'field'     => 'id,title,send_time,views,tag_major,imgs,status'
        );
        $page = new ApiPage($config);
        $data = $page->get(); //获取分页数据
        if($nowPage == 0) {
            apiReturn('1019', AJAX_FALSE);   //获取数据失败
        } else {
            $get_dat = array();
            foreach($data as $k => $v) {
                $where1 = array(
                    'uid' => $uid,
                    'rid' => $data[ $k ]['id']
                );
                foreach($v as $m => $n) {
                    $get_dat[ $k ][ $m ] = $n;
                    $get_dat[ $k ]['sums_page'] = $data['total_page'];    //总页数
//                    $get_dat[ $k ]['comm_list'] = M('Resource_comment')->where($where1)->order('time desc')->limit(3)->field('content,time,is_nym')->select();
                    $res = M('Resource_comment')->where($where1)->order('time desc')->limit(3)->field('content,time,is_nym,type')->select();
                    foreach($res as &$val){
                        if($val['type'] == 1){
                            $uu = "dwbppqvkxs"; //用户唯一标识码   dwbppqvkxs
                            $pu = "a2ee3b5de4"; //播放器唯一标识码  a2ee3b5de4
                            $type = 'url';  //接口类型
                            $auto_play = 1; //是否自动播放
//                            $width = $AesMct->decrypt(urldecode(I('post.width')));  //播放器宽度
//                            $height = $AesMct->decrypt(urldecode(I('post.height'))); //播放器高度
                            $width = I('post.width');  //播放器宽度
                            $height = I('post.height'); //播放器高度
                            $letv = new LetvCloud;
                            $val['content']
                                = $letv->videoGetPlayinterface($uu, video_info($val['content'], 'video_unique'), $type, $pu, $auto_play, $width, $height);
                        }
                    }
                    $get_dat[ $k ]['comm_list'] = $res;
                }
            }
            apiReturn('1020', AJAX_TRUE, $get_dat);   //获取数据成功
        }
    }

    //我的收藏
    public function my_favorite()
    {
        $tid = I('post.tid');   //别人id
        if(empty($tid)) {
            $uid = session('my_id');    //用户id
        } else {
            $uid = $tid;
        }
        $nowPage = I('post.nowpage'); //页码
        $num = I('post.num');    //每页显示条数
        //收藏的帖子和收藏的求助
        $rid = M('Resource_favorite')->where('uid=' . $uid)->field('rid')->order('time desc')->select();
        foreach($rid as $k => $v) {
            $data1[ $k ] = D('Admin/ResourceRelation')->relation(true)->where(array('id'     => $v['rid'],
                                                                                    'status' => 1))->field('id,title,send_time,views,tag_major,type,imgs,status')->find();

            // Type表示收藏对象的类型 1资讯,2求助,3活动,4视频
            if($data1[ $k ]['type'] == 3) {
                $data1[ $k ]['type'] = 1;
            }

            if($data1[ $k ]['type'] == 2) {
                $data1[ $k ]['type'] = 2;
            }
        }

        //收藏的活动
        $aid = M('Activity_favorite')->where('uid=' . $uid)->field('aid')->order('time desc')->select();
        foreach($aid as $k => $v) {
            $data2[ $k ] = D('Admin/ActivityRelation')->relation(true)->where(array('id' => $v['aid']))->field('id,title,send_time,views,status')->find();
            $data2[ $k ]['type'] = 3;
        }

        //收藏的视频
        $vid = M('Train_favorite')->where('uid=' . $uid)->field('vid')->order('time desc')->select();
        foreach($vid as $k => $v) {
            $data3[ $k ] = D('Admin/TrainRelation')->relation(true)->where(array('id' => $v['vid']))->field('id,title,create_at,views,tags,status')->find();
            $data3[ $k ]['type'] = 4;
        }

        if(empty($data1)) {
            $data1 = array();
        }
        if(empty($data2)) {
            $data2 = array();
        }
        if(empty($data3)) {
            $data3 = array();
        }
        $data = array_merge($data1, $data2, $data3);
        $count = count($data);
        foreach($data as $k => $v) {
            $data[ $k ]['sums_page'] = intval(ceil($count / $num));   //数据总页数
        }
        //分页
        $list = array_slice($data, ($nowPage - 1) * $num, $num);
        if($nowPage == 0) {
            apiReturn('1019', AJAX_FALSE);   //获取数据失败
        } else {
            apiReturn('1020', AJAX_TRUE, $list);  //获取数据成功
        }
    }

    //我的活动
    public function my_activity()
    {
        $tid = I('post.tid');   //别人id
        if(empty($tid)) {
            $uid = session('my_id');    //用户id
        } else {
            $uid = $tid;
        }
        $nowPage = I('post.nowpage');   //页码
        $num = I('post.num');   //每页条数
        $where = array(
            'status' => array('neq', 0),
            'author' => $uid
        );
        $order = array(
            'views'     => 'desc',
            'send_time' => 'desc',
        );
        //数据分页
        $config = array(
            'tablename' => 'Admin/ActivityRelation', // 表名
            'relation'  => true, // 关联条件
            'where'     => $where, //条件
            'order'     => $order, // 排序
            'page'      => $nowPage,  // 页码，默认为首页
            'num'       => $num,  // 每页条数
            'field'     => 'id,title,views,author,star_time,end_time,address,imgs,status,type,number,send_time,status'
        );
        $page = new ApiPage($config);
        $data = $page->get(); //获取分页数据
        if($nowPage == 0) {
            apiReturn('1019', AJAX_FALSE);   //获取数据失败
        } else {
            $get_dat = array();
            foreach($data as $k => $v) {
                foreach($v as $m => $n) {
                    $get_dat[ $k ][ $m ] = $n;
                    $get_dat[ $k ]['sums_page'] = $data['total_page'];    //总页数
                }
            }
            apiReturn('1020', AJAX_TRUE, $get_dat);   //获取数据成功
        }
    }

    //分享给律携好友
    public function shareTo_app()
    {
        $AesMct = new MCrypt;
        $Yun = new ChatApi;
        $form_tel = session('accid');   //当前用户手机号
        $tels_str = $AesMct->decrypt(urldecode(I('post.tels')));    //接受人手机号集
        $groups = $AesMct->decrypt(urldecode(I('post.groups')));    //接受高级群
        $content = I('post.url');   //分享链接
        $tels = explode(',', $tels_str);
        //        $groups = explode(',', $groups_str);
        if(empty($groups)) {
            $body = array(
                'msg' => $content,
            );
            $res = $Yun->sendMsgs($form_tel, $tels, 0, $body);
        } else {
            $body = array(
                'msg' => $content,
            );
            $res = $Yun->sendMsg($form_tel, 1, $groups, $type = 0, $body, $option = '{"push":false,"roam":true,"history":false,"sendersync":true, "route":false}', $pushcontent = '');
        }
        if($res['code'] == 200) {
            apiReturn('1024', AJAX_TRUE);    //发送成功
        } else {
            apiReturn('1023', AJAX_FALSE);   //发送失败
        }
    }

    //意见反馈
    public function feed_back()
    {
        $uid = session('my_id');    //当前用户id
        $content = I('post.content');   //反馈内容
        $data = array(
            'uid'       => $uid,
            'content'   => $content,
            'send_time' => time()
        );
        if(M('Feedback')->add($data)) {
            apiReturn('1024', AJAX_TRUE);
        } else {
            apiReturn('1023', AJAX_FALSE);
        }
    }

    //管理律所
    public function law_firms()
    {
        $AesMct = new MCrypt;
        $lid = $AesMct->decrypt(urldecode(I('post.lid')));  //律所id
        $logo = I('post.logo');     //律所logo图片
        $bg_img = I('post.bg_img');     //律所背景图片
        $area = I('post.area');     //所在城市
        $address = I('post.address');   //律所详细地址
        $phone = I('post.phone');   //律所电话
        $email = I('post.email');   //律所邮箱
        $profile = I('post.profile');   //律所简介
        if(!empty($area)) {
            $city = explode(',', $area);
            $data['province'] = $city[0];
            if(!empty($city[1])) {
                $data['city'] = $city[1];
            } else {
                $data['city'] = '';
            }
            if(!empty($city[2])) {
                $data['town'] = $city[2];
            } else {
                $data['town'] = '';
            }
        }

        $data['logo'] = $logo;
        $data['bg_img'] = $bg_img;
        $data['address'] = $address;
        $data['phone'] = $phone;
        $data['email'] = $email;
        $data['profile'] = $profile;
        $res = M('Laws')->where('id=' . intval($lid))->save($data);
        if($res > 0 || $res == 0) {
            apiReturn('1024', AJAX_TRUE);    //更新律所信息成功
        } else {
            apiReturn('1023', AJAX_FALSE);   //更新律所信息失败
        }
    }

    //更改律所管理员
    public function change_admin()
    {
        $AesMct = new MCrypt;
        $lid = $AesMct->decrypt(urldecode(I('post.lid')));  //律所id
        $uid = $AesMct->decrypt(urldecode(I('post.uid')));  //用户id
        $type = M('Account')->where('id=' . $uid)->getField('type');  //获取用户身份
        if($type == 1) {
            if(M('Laws')->where('id=' . $lid)->setField('uadmin', $uid)) {
                apiReturn('1024', AJAX_TRUE);    //更改管理员成功
            } else {
                apiReturn('1023', AJAX_FALSE);   //更改管理员失败
            }
        } else {
            apiReturn('1037', AJAX_FALSE);   //律师信息无效
        }
    }

    //踢出律师
    public function out_lawyer()
    {
        $AesMct = new MCrypt;
        $mid = session('my_id');    //当前用户id
        $lid = $AesMct->decrypt(urldecode(I('post.lid')));  //律所id
        $uids = $AesMct->decrypt(urldecode(I('post.uid')));  //用户id
        $uid = explode(',', $uids);
        $uadmin = M('Laws')->where('id=' . $lid)->getField('uadmin');
        if($mid == $uadmin) {
            foreach($uid as $u) {
                $where = array(
                    'id'  => $u,
                    'law' => $lid
                );
                M('Account')->where($where)->setField('law', 0);
                apiReturn('1024', AJAX_TRUE);    //踢出律师成功
            }
        } else {
            apiReturn('1039', AJAX_FALSE);   //当前用户不是管理员
        }
    }

    //隐藏手机号码
    public function hide_mobile()
    {
        $uid = session('my_id');
        $hide = (int)I('post.is_hide');
        if(empty($uid)) {
            apiReturn('1007', AJAX_FALSE);   //用户id不能为空
        } else {
            $User = new Model();
            $User->execute("update lx_account set is_hide=" . $hide . " where id=" . $uid);
            // $is_hide = $User->execute("select is_hide from lx_account where id=".$uid);
            $is_hide = M('Account')->where('id=' . $uid)->getField('is_hide');
            $data = array(
                'uid'     => $uid,
                'is_hide' => $is_hide
            );
            apiReturn('1024', AJAX_TRUE, $data);
        }
    }

    //律所照片上传
    public function laws_uploads()
    {
        $AesMct = new MCrypt;
        $model = M('Laws');
        $laws_id = $AesMct->decrypt(urldecode(I('post.laws_id')));      //律所id
        $laws_album = $AesMct->decrypt(urldecode(I('post.laws_album')));      //图片上传返回信息
        $img_id = $model->where('id=' . $laws_id)->getField('laws_album', $laws_album);    //律所图片列表id

        if($model->where('id=' . $laws_id)->setField('laws_album', $laws_album . ',' . $img_id)) {
            apiReturn('1024', AJAX_TRUE);    //插入图片id成功
        } else {
            apiReturn('1025', AJAX_FALSE);   //未找到该律所,或相同图片id已存在
        }
    }

    //律所相册删除
    public function laws_delete()
    {
        $AesMct = new MCrypt;
        $laws_id = $AesMct->decrypt(urldecode(I('post.laws_id')));      //律所id
        $img_id = $AesMct->decrypt(urldecode(I('post.img_id')));      //图片id
        $model = M('Laws');
        //删除前所有照片id
        $laws_albums = $model->where('id=' . $laws_id)->getField('laws_album');
        //删除后剩余所有照片id
        $albums_arr = explode(',', $laws_albums);

        $count = count($albums_arr);
        for($i = 0; $i <= $count; $i ++) {
            if($albums_arr[ $i ] == $img_id) {
                //删除指定img_id
                unset($albums_arr[ $i ]);
            }
        }
        $new_albums = trim(implode(',', $albums_arr));

        if($model->where('id=' . $laws_id)->setField('laws_album', $new_albums)) {     //删除图片id
            //M('Picture')->where('id='.$img_id)->delete();   //删除图片url
            apiReturn('1024', AJAX_TRUE);    // 删除成功
        } else {
            apiReturn('1023', AJAX_FALSE);
        }
    }

    //新添直连评价
    public function evaluate_add()
    {
        $AesMct = new MCrypt;
        $customer = $AesMct->decrypt(urldecode(I('post.customer')));      //评价人ID
        $uid = $AesMct->decrypt(urldecode(I('post.uid')));      //被评价人ID
        $content = $AesMct->decrypt(urldecode(I('post.content')));      //评价内容
        $type = $AesMct->decrypt(urldecode(I('post.type')));      //评价类型 0好评 1差评
        $direct = $AesMct->decrypt(urldecode(I('post.direct')));      //直连时间  单位/秒

        $data = array(
            'customer'        => $customer,
            'uid'       => $uid,
            'content'     => $content,
            'type'  => $type,
            'direct' => $direct,
            'time' => time()
        );
        if(M('evaluate')->add($data)) {     //添加评价记录
            M('account')->where(['id'=>$uid])->setInc('direct_time',$direct); //给被评价人更新连接时长
            apiReturn('1024', AJAX_TRUE);    // 添加成功
        } else {
            apiReturn('1023', AJAX_FALSE);
        }
    }
}