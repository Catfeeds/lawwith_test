<?php
//+----------------------------------------------------------------------
// | Copyright (c) 2015-2017 http://lawyerscloud.cn All rights reserved.
//+----------------------------------------------------------------------
// | Author: 慢悠悠的丑小鸭 <1597305236@qq.com>
//+----------------------------------------------------------------------

namespace App\Controller;

use Common\Api\MCrypt;
use Common\Api\ApiPage;
use Common\Api\JPush;

class ContentController extends BasicController
{
    //全部求助列表
    public function helps_list() {
        $nowPage = I('post.nowpage');   //页码
        $num     = I('post.num');   //每页条数
        $where   = array(
            'status' => 1,
            'type'   => 2
        );

        $order = array(
            'is_stick'  => 'asc',
            'send_time' => 'desc',
        );
        //数据分页
        $config = array(
            'tablename' => 'Admin/ResourceRelation', // 表名
            'where'     => $where, // 查询条件
            'relation'  => true, // 关联条件
            'order'     => $order, // 排序
            'page'      => $nowPage,  // 页码，默认为首页
            'num'       => $num,  // 每页条数
            'field'     => 'id,title,content,author,tag_city,tag_major,imgs,views,send_time,tbd_id,inviters,type,status,is_nym,is_admin,is_money,red_status,redpack_id,is_reward,reward_money'
        );
        $page   = new ApiPage($config);
        $data   = $page->get(); //获取分页数据

        if($data['now_page'] == 0) {
            apiReturn('1019', AJAX_FALSE);   //获取数据失败
        } else {
            $get_dat = array();
            foreach($data as $k => $v) {
                foreach($v as $m => $n) {
                    $get_dat[ $k ][ $m ]        = $n;
                    $get_dat[ $k ]['my_id']     = session('my_id');   //写入当前用户id
                    $get_dat[ $k ]['sums_page'] = $data['total_page'];    //总页数
                    if(!empty($data[ $k ]['author_info']['law'])) {
                        $condit                                   = array(
                            'id'     => $data[ $k ]['author_info']['law'],
                            'status' => 1
                        );
                        $law_name                                 = M('Laws')->where($condit)->getField('law_name');
                        $get_dat[ $k ]['author_info']['law_name'] = $law_name;
                        (string)$get_dat[ $k ]['is_reward'];
                        (string)$get_dat[ $k ]['reward_money'];
                    }
                }
            }
            apiReturn('1020', AJAX_TRUE, $get_dat);   //获取数据成功
        }
    }


    //首页banner推荐  推送类别 1.帖子 2.求助 3.活动 4.视频 5.律所 6.用户
    public function index_banner() {
        $data = S('data');
        if(empty($data)) {
            $data
                = M('Push')->where('mark=1')->cache('data', 60)->field('cid,banner,c_type,author')->order('sort, push_time desc')->select();
            S('data', $data, 60);
        } else {
            $data = S('data');
        }

        if(!empty($data)) {
            foreach($data as $k => $v) {
                $data[ $k ]['my_id'] = session('my_id');
            }
            apiReturn('1020', AJAX_TRUE, $data);
        } else {
            apiReturn('1019', AJAX_FALSE);
        }
    }


    //互助热点
    public function help_hot($nowPage, $num) {
        $time_str = date('Y-m-d', strtotime("-3 day"));
        $interval = strtotime($time_str);   //间隔两天
        //合并数据查询条件
        $where = array(
            'send_time' => array('egt', $interval),
            'type'      => 2,
            'status'    => 1
        );
        $order = array(
            'is_stick'  => 'asc',
            'views'     => 'desc',
            'send_time' => 'desc',
        );
        //数据分页
        $config = array(
            'tablename' => 'Admin/ResourceRelation', // 表名
            'where'     => $where, // 查询条件
            'relation'  => true, // 关联条件
            'order'     => $order, // 排序
            'page'      => $nowPage,  // 页码，默认为首页
            'num'       => $num,  // 每页条数
            'field'     => 'id,title,content,author,tag_city,tag_major,imgs,views,send_time,tbd_id,inviters,type,status,is_nym,is_admin,is_money'
        );
        $page   = new ApiPage($config);
        $data   = $page->get(); //获取分页数据
        if($nowPage == 0) {
            apiReturn('1019', AJAX_FALSE);
        } else {
            $get_dat = array();
            foreach($data as $k => $v) {
                foreach($v as $m => $n) {
                    $get_dat[ $k ][ $m ]        = $n;
                    $get_dat[ $k ]['my_id']     = session('my_id');   //写入当前用户id
                    $get_dat[ $k ]['sums_page'] = $data['total_page'];    //总页数
                    if(!empty($data[ $k ]['author_info']['law'])) {    //遍历律所名称
                        $condit                                   = array(
                            'id'     => $data[ $k ]['author_info']['law'],
                            'status' => 1
                        );
                        $law_name                                 = M('Laws')->where($condit)->getField('law_name');
                        $get_dat[ $k ]['author_info']['law_name'] = $law_name;
                    }

                }
            }
            //p($get_dat);die;
            apiReturn('1020', AJAX_TRUE, $get_dat);
        }
    }


    //首页圈子数据列表
    public function index_hot() {
        $AesMct   = new MCrypt;
        $uid      = session('my_id');   //当前用户id
        $c_type   = $AesMct->decrypt(urldecode(I('post.t'))); //解密内容类别，1、帖子 2、求助
        $nowPage  = $AesMct->decrypt(urldecode(I('post.nowpage'))); //页码
        $num      = $AesMct->decrypt(urldecode(I('post.num')));    //每页显示条数
        $is_index = $AesMct->decrypt(urldecode(I('post.is_index')));    //是否是首页列表 1是
        $is_help  = $AesMct->decrypt(urldecode(I('post.is_help')));   //是否是互助热点 1是
        $is_money = $AesMct->decrypt(urldecode(I('post.is_money')));   //是否是红包互助热点 1是
        if($is_help == 1) {
            $this->help_hot($nowPage, $num);
        } else {
            $tags
                          = M('Account')->where('id=' . $uid)->field('province,tag_citys,majors,specialty')->find();  //获取用户关注的标签
            $major_ar     = explode(',', $tags['majors']); //专业标签集
            $specialty_ar = explode(',', $tags['specialty']); //所属专业
            $province     = $tags['province'];  //所属城市
            if($c_type == 1) {
                //遍历专业标签的查询条件
                if(!empty($tags['majors'])) {
                    foreach($major_ar as $k => $major) {
                        $where1[ $k ]['_string'] = "FIND_IN_SET($major, tag_major)";
                        $where1['_logic']        = 'or';
                    }
                }
                //帖子  整理专业和城市标签查询条件，二者满足其一即可
                $map = array(
                    // '_complex' => $where1,
                    'tag_city' => array('in', $tags['tag_citys']),
                    '_logic'   => 'or'
                );
                if(!empty($tags['majors'])) {
                    $map['_complex'] = $where1;
                }
            } else {
                //遍历专业标签的查询条件
                if(!empty($tags['specialty'])) {
                    foreach($specialty_ar as $k => $specialty) {
                        $where1[ $k ]['_string'] = "FIND_IN_SET($specialty, tag_major)";
                        $where1['_logic']        = 'or';
                    }
                }
                //求助
                $map = array(
                    // '_complex' => $where1,
                    'tag_city' => $province,
                    '_logic'   => 'or'
                );
                if(!empty($tags['specialty'])) {
                    $map['_complex'] = $where1;
                }
            }
            $time_str = date('Y-m-d', strtotime("-3 day"));
            // $time_str = date('Y-m-d',strtotime("-10 day"));
            $interval = strtotime($time_str);   //间隔两天
            //合并数据查询条件
            $where = array(
                'type'     => $c_type,
                '_complex' => $map,
                'status'   => 1,
                '_logic'   => 'and',
            );
            if(intval($is_index) == 1) {
                $where['send_time'] = array('egt', $interval);
                $order              = array(
                    'is_stick' => 'asc',
                    'views'    => 'desc',
                );
            } else {
                $order = array(
                    'is_stick'  => 'asc',
                    'send_time' => 'desc'
                );
            }
            //数据分页
            $config = array(
                'tablename' => 'Admin/ResourceRelation', // 表名
                'where'     => $where, // 查询条件
                'relation'  => true, // 关联条件
                'order'     => $order, // 排序
                'page'      => $nowPage,  // 页码，默认为首页
                'num'       => $num,  // 每页条数
                'field'     => 'id,title,content,author,tag_city,tag_major,imgs,views,send_time,tbd_id,inviters,type,status,is_nym,is_admin'
            );
            $page   = new ApiPage($config);
            $data   = $page->get(); //获取分页数据
            if($nowPage == 0) {
                apiReturn('1019', AJAX_FALSE);
            } else {
                $get_dat = array();
                foreach($data as $k => $v) {
                    foreach($v as $m => $n) {
                        $get_dat[ $k ][ $m ]        = $n;
                        $get_dat[ $k ]['my_id']     = session('my_id');   //写入当前用户id
                        $get_dat[ $k ]['sums_page'] = $data['total_page'];    //总页数
                        if(!empty($data[ $k ]['author_info']['law'])) {    //遍历律所名称
                            $condit                                   = array(
                                'id'       => $data[ $k ]['author_info']['law'],
                                'status'   => 1,
                                'is_money' => $is_money
                            );
                            $law_name                                 = M('Laws')->where($condit)->getField('law_name');
                            $get_dat[ $k ]['author_info']['law_name'] = $law_name;
                        }
                    }
                }
                apiReturn('1020', AJAX_TRUE, $get_dat);
            }
        }
    }


    //添加保存标签
    public function save_tags() {
        $uid       = session('my_id');
        $major_tag = I('post.major_tag');
        $city_tag  = I('post.city_tag');
        $major_arr = explode(',', $major_tag);
        $city_arr  = explode(',', $city_tag);
        $new_major = implode(',', array_filter($major_arr));
        $new_city  = implode(',', array_filter($city_arr));
        $data      = array(
            'tag_citys' => $new_city,
            'majors'    => $new_major
        );
        $res       = M('Account')->where('id=' . $uid)->setField($data);
        if($res !== false) {
            apiReturn('1024', AJAX_TRUE);
        } else {
            apiReturn('1023', AJAX_FALSE);
        }
    }


    //条件查询求助列表和帖子
    public function condition_list() {
        $AesMct    = new MCrypt;
        $type      = $AesMct->decrypt(urldecode(I('post.t')));   //类别
        $nowPage   = I('post.nowpage');   //页码
        $num       = I('post.num');   //每页条数
        $major_id  = $AesMct->decrypt(urldecode(I('post.mid')));    //专业id
        $city_name = $AesMct->decrypt(urldecode(I('post.city_name')));    //城市
        $keywords  = $AesMct->decrypt(urldecode(I('post.keywords'))); //关键字检索
        $where     = array(
            'type'   => $type,
            'status' => 1,
            '_logic' => 'and',
        );
        if(!empty($major_id)) {
            $where = array(
                '_string' => "FIND_IN_SET($major_id, tag_major)",
                'type'    => $type,
                'status'  => 1,
                '_logic'  => 'and',
            );
        }
        if(!empty($keywords)) {
            $where = array(
                'title'  => array('like', "%" . $keywords . "%"),
                'type'   => $type,
                'status' => 1,
                '_logic' => 'and',
            );
        }
        if(!empty($city_name)) {
            $where = array(
                '_string' => "FIND_IN_SET('$city_name', tag_city)",
                'type'    => $type,
                'status'  => 1,
                '_logic'  => 'and',
            );
        }
        $order = array(
            'is_stick'  => 'asc',
            'send_time' => 'desc',
        );
        //数据分页
        $config = array(
            'tablename' => 'Admin/ResourceRelation', // 表名
            'where'     => $where, // 查询条件
            'relation'  => true, // 关联条件
            'order'     => $order, // 排序
            'page'      => $nowPage,  // 页码，默认为首页
            'num'       => $num,  // 每页条数
            'field'     => 'id,title,content,author,tag_city,tag_major,imgs,views,send_time,tbd_id,inviters,type,status,is_nym,is_admin,is_money,red_status,redpack_id'
        );
        $page   = new ApiPage($config);
        $data   = $page->get(); //获取分页数据
        if($data['now_page'] == 0) {
            apiReturn('1019', AJAX_FALSE);
        } else {
            $get_dat = array();
            foreach($data as $k => $v) {
                foreach($v as $m => $n) {
                    $get_dat[ $k ][ $m ]        = $n;
                    $get_dat[ $k ]['my_id']     = session('my_id');   //写入当前用户id
                    $get_dat[ $k ]['sums_page'] = $data['total_page'];    //总页数
                    if(!empty($data[ $k ]['author_info']['law'])) {    //遍历律所名称
                        $condit                                   = array(
                            'id'     => $data[ $k ]['author_info']['law'],
                            'status' => 1
                        );
                        $law_name                                 = M('Laws')->where($condit)->getField('law_name');
                        $get_dat[ $k ]['author_info']['law_name'] = $law_name;
                    }
                }
            }
            apiReturn('1020', AJAX_TRUE, $get_dat);
        }
    }


    //发布话题
    public function topic_send() {
        $AesMct  = new MCrypt;
        $user_id = session('my_id');  //用户id
        $uid     = M('Account')->where('id=' . $user_id)->field('id,type')->find();    //获取用户id
        if(empty($uid['type']) or $uid['type'] == 4) {
            apiReturn('1029', AJAX_FALSE);   //游客没有权限
        } else {
            $data['title']     = $AesMct->decrypt(urldecode(I('post.title')));   //标题
            $data['content']   = $AesMct->decrypt(urldecode(I('post.content')));   //内容
            $data['tag_major'] = $AesMct->decrypt(urldecode(I('post.majors')));  //专业标签集
            $data['tag_city']  = $AesMct->decrypt(urldecode(I('post.citys')));   //城市标签集
            $data['is_nym']    = $AesMct->decrypt(urldecode(I('post.is_nym'))); //是否匿名 1表示是
            $data['imgs']      = $AesMct->decrypt(urldecode(I('post.imgs')));    //图片id集
            $data['author']    = $uid['id'];
            $data['type']      = 1;
            $data['send_time'] = time();
            if(M('Resource')->add($data)) {
                apiReturn('1024', AJAX_TRUE);    //发布成功
            } else {
                apiReturn('1023', AJAX_FALSE);   //发布失败
            }
        }
    }


    //发布求助
    public function help_send() {
        $AesMct  = new MCrypt;
        $user_id = session('my_id');  //用户id
        $usr     = M('Account')->where('id=' . $user_id)->field('id,type,status')->find();    //获取用户id和身份

        if(empty($usr)) {
            apiReturn('1025', AJAX_FALSE, '用户不存在');
        }
        //游客没有权限
        if($usr['type'] == 4 || $usr['status'] == 2) {
            apiReturn('1029', AJAX_FALSE, '您的账号没有权限进行此操作，请申请认证');
        }

        $friends_tel = $AesMct->decrypt(urldecode(I('post.tels'))); //用户手机号码集
        $friends_arr = explode(',', $friends_tel);
        $uid_arr     = array();
        //根据用户手机号码，遍历获取用户id
        foreach($friends_arr as $k => $val) {
            $uid           = M('Account')->where(array('mobile' => $val))->getField('id'); //获取用户id
            $uid_arr[ $k ] = $uid;
        }
        $data['title']     = $AesMct->decrypt(urldecode(I('post.title')));   //标题
        $data['content']   = $AesMct->decrypt(urldecode(I('post.content')));   //内容
        $data['tag_major'] = $AesMct->decrypt(urldecode(I('post.majors')));  //专业标签集
        $data['tag_city']  = $AesMct->decrypt(urldecode(I('post.citys')));   //城市标签集
        $data['inviters']  = implode(',', $uid_arr);  //被邀请人id集
        $data['is_nym']    = $AesMct->decrypt(urldecode(I('post.is_nym'))); //是否匿名 1表示是
        $data['imgs']      = $AesMct->decrypt(urldecode(I('post.imgs')));    //图片id集
        $data['author']    = $usr['id'];   //发布者
        $data['type']      = 2;      //内容类别：1话题2求助
        $data['send_time'] = $_SERVER[ REQUEST_TIME ];    //发布时间

        //红包配置详情
        $end_time = M('Bonus_conf')->max('end_time');
        $redpackets_conf
                  = M('Bonus_conf')->where('end_time=' . $end_time)->where('status=1')->field('conf_id,like_nums,total_amount,start_time,end_time,type,commission')->find();

        //判断求助帖子类型,是否在设置时间区间内
        if(($_SERVER[ REQUEST_TIME ] >= $redpackets_conf['start_time']) && ($_SERVER[ REQUEST_TIME ] <= $redpackets_conf['end_time'])) {
            $data['is_money']   = 1;                          //标记为红包求助 1表示是
            $data['red_status'] = 0;                          //红包是否领取 0表示未领
            $data['redpack_id'] = $redpackets_conf['conf_id'];  //属于哪个红包配置项id
        }

        //更新帖子资源表
        if($cid = M('Resource')->add($data)) {
            if(empty($friends_tel)) {
                apiReturn('1024', AJAX_TRUE);   //发布成功
            } else {
                $m_txt     = array(
                    'type' => 2,    //0系统消息 1回答我 2求助我 3评论我 4响应我
                    'cid'  => $cid
                );
                $alias     = array(
                    'alias' => $friends_arr
                );
                $n_content = '@你:' . $data['title'];
                $Jpush     = new Jpush(C('JPUSH_APPKEY'), C('JPUSH_SECRET'));
                $res       = $Jpush->push('all', $alias, $n_content, $m_type = 'http', $m_txt);
                if($res) {
                    $res_arr = json_decode($res, true);
                    if(isset($res_arr['error'])) {                       //如果返回了error则证明失败
                        M('Resource')->where('id=' . $cid)->delete();
                        apiReturn('1031', AJAX_FALSE, $res_arr);
                    } else {
                        //处理成功的推送......
                        apiReturn('1030', AJAX_TRUE);
                    }
                } else {      //接口调用失败或无响应
                    M('Resource')->where('id=' . $cid)->delete();
                    apiReturn('1033', AJAX_FALSE);
                }
            }
        } else {
            apiReturn('1023', AJAX_FALSE);   //发布失败
        }
    }


    //发布求助V4版本
    public function help_send_v4() {
        $AesMct   = new MCrypt;
        $userId   = session('my_id');
        $userInfo = M('Account')->where('id=' . $userId)->field('id,type,status')->find();    //获取用户id和身份
        if(empty($userInfo)) {
            apiReturn('1025', AJAX_FALSE, '用户不存在');
        }

        //游客没有发帖权限
        if($userInfo['type'] == 4 || $userInfo['status'] == 2) {
            apiReturn('1029', AJAX_FALSE, '您的账号没有权限进行此操作，请申请认证');
        }

        $friends_tel = $AesMct->decrypt(urldecode(I('post.tels'))); //用户手机号码集
        $friends_arr = explode(',', $friends_tel);
        $userIdArr   = array();
        //根据用户手机号码，遍历获取用户id
        foreach($friends_arr as $k => $val) {
            $uid             = M('Account')->where(array('mobile' => $val))->getField('id'); //获取用户id
            $userIdArr[ $k ] = $uid;
        }
        $data['inviters'] = implode(',', $userIdArr);  //被邀请人id集

        //红包配置详情
        $field     = 'conf_id,like_nums,total_amount,start_time,end_time,type,commission';
        $end_time  = M('Bonus_conf')->max('end_time');
        $bonusConf = M('Bonus_conf')->where('end_time=' . $end_time)->where('status=1')->field($field)->find();

        //判断求助帖子类型,是否在设置时间区间内
        if(($_SERVER['REQUEST_TIME'] >= $bonusConf['start_time']) && ($_SERVER['REQUEST_TIME'] <= $bonusConf['end_time'])) {
            $data['is_money']   = 1;                          //标记为红包求助 1表示是
            $data['red_status'] = 0;                          //红包是否领取 0表示未领
            $data['redpack_id'] = $bonusConf['conf_id'];  //属于哪个红包配置项id
        }

        //发布悬赏贴时先设为隐藏状态
        if($data['is_reward']) {
            $data['status'] = 0;
        }

        $data['title']        = trim($AesMct->decrypt(urldecode(I('post.title'))));   //标题
        $data['content']      = trim($AesMct->decrypt(urldecode(I('post.content'))));   //内容
        $data['tag_major']    = $AesMct->decrypt(urldecode(I('post.majors')));  //专业标签集
        $data['is_nym']       = $AesMct->decrypt(urldecode(I('post.is_nym'))); //是否匿名 1表示是
        $data['author']       = $userId;   //发布者
        $data['type']         = 2;      //内容类别：1话题2求助
        $data['send_time']    = $_SERVER["REQUEST_TIME"];    //发布时间
        $data['reward_money'] = $AesMct->decrypt(urldecode(I('post.reward_money'))); //悬赏金额
        $data['is_reward']    = $AesMct->decrypt(urldecode(I('post.is_reward'))); //是否悬赏 0否 1是
        $data['status']       = $data['is_reward'] ? 0 : 1;

        //更新帖子资源表
        if($cid = M('Resource')->add($data)) {
            //是否是付费提问
            if($data['is_reward']) {
                $order     = A('Order');
                $orderSn = $order->uniqueOrderSn($userId);

                //新增悬赏业务订单
                $paymentData['record_sn']   = $orderSn;
                $paymentData['resource_id'] = $cid;
                $paymentData['from_uid']    = $userId;
                $paymentData['to_uid']      = '';
                $paymentData['money']       = $data['reward_money'];
                $paymentData['create_date'] = $_SERVER["REQUEST_TIME"];
                $paymentData['status']      = 0;
                $orderId                    = $this->createPaymentQuestionOrder($paymentData);

                if(empty($orderId)) {
                    M('resource')->where('id=' . $cid)->save(['is_reward' => 0, 'reward_money' => 0]);
                    apiReturn('1021', AJAX_FALSE, '创建付费提问订单失败');

                }

                //新增订单关联数据
                $unionData['record_sn']   = $orderSn;
                $unionData['order_type']  = 'article_reward_order';
                $unionData['order_id']    = $orderId;
                $unionData['create_date'] = $_SERVER[ REQUEST_TIME ];
                $unionData['status']      = 0;
                $unionOrderId             = $this->createPaymentQuestionUnionOrder($unionData);

                if(empty($unionOrderId)) {
                    M('resource')->where('id=' . $cid)->save(['is_reward' => 0, 'reward_money' => 0]);
                    apiReturn('1022', AJAX_FALSE);
                }
            }

            //@邀请人
            if(!empty($friends_tel)) {
                $pushText    = array('type' => 2, 'cid' => $cid);//0系统消息 1回答我 2求助我 3评论我 4响应我
                $alias       = array('alias' => $friends_arr);
                $push        = new Jpush(C('JPUSH_APPKEY'), C('JPUSH_SECRET'));
                $pushContent = '@你:' . $data['title'];
                $res         = $push->push('all', $alias, $pushContent, $m_type = 'http', $pushText);

                //接口调用失败或无响应
                //$res = json_decode($res, true);
                //if(empty($res) || isset($res['error'])) {
                //
                //    M('Resource')->where('id=' . $cid)->delete();
                //    apiReturn('1033', AJAX_FALSE);
                //}
            }

            $wallet = M('Wallet')->where('uid=' . $userId)->field('money,pay_password')->find();

            //是否设置过支付密码
            if(empty($wallet['pay_password'])){
                $pay_password = 0;
            }else{
                $pay_password = 1;
            }

            $returnData['cid']          = strval($cid);
            $returnData['money']        = strval($wallet['money']);
            $returnData['is_paypasswd'] = strval($pay_password);
            $returnData['order_id']     = strval($unionOrderId);

            apiReturn('1024', AJAX_TRUE, $returnData);
        } else {
            apiReturn('1023', AJAX_FALSE);
        }
    }


    //创建付费提问订单
    protected function createPaymentQuestionOrder($data) {
        $orderId = M('article_reward_order')->add($data);

        return $orderId;
    }


    //新增订单关联数据
    protected function createPaymentQuestionUnionOrder($data) {
        $unionOrderId = M('order')->add($data);

        return $unionOrderId;
    }


    //采纳答案
    public function adopt_reply() {
        $AesMct        = new MCrypt();
        $hid           = $AesMct->decrypt(urldecode(I('post.hid')));  //求助id
        $rid           = $AesMct->decrypt(urldecode(I('post.rid')));  //回复id
        $userId        = session('my_id');
        $articleInfo
                       = M('Resource')->where(array('id' => $hid))->field('author,is_reward,reward_money')->find();     //帖子详情
        $authorId      = $articleInfo['author'];     //求助者id
        $commentUserId = M('Resource_comment')->where(array('id' => $rid))->getField('uid');    //回复者id

        //求助者不能采纳自己的答案
        if($userId == $commentUserId || $authorId == $commentUserId) {
            apiReturn('1023', AJAX_FALSE, '不能采纳自己的回答');
        }

        //是否已经被采纳过
        $tbdId    = M('Resource')->where('id=' . $hid)->getField('tbd_id');
        $comTbdId = M('Resource_comment')->where('id=' . $rid)->getField('tbd');
        if($tbdId || $comTbdId) {
            apiReturn('1023', AJAX_FALSE, '该回复已经被采纳过,不能重复采纳');
        }

        //执行采纳动作,修改是否采纳标识
        $setTbd    = M('Resource')->where('id=' . $hid)->setField('tbd_id', $rid);
        $setTbdCom = M('Resource_comment')->where('id=' . $rid)->setField('tbd', 1);
        if(!$setTbd || !$setTbdCom) {
            apiReturn('1025', AJAX_FALSE, '采纳失败');
        }

        //回复者手机号
        $commentUserMobile = strval(user_info($commentUserId, 'mobile'));
        $alias             = array('alias' => array($commentUserMobile));
        $n_content         = '你的回答已被采纳';

        //帖子为悬赏贴时，用户获得悬赏金额
        if($articleInfo['is_reward']) {
            $orderInfo
                         = M('article_reward_order')->where(['resource_id' => $hid])->field('record_sn,money,create_date')->find();
            $userBalance = M('wallet')->where(array('uid' => $commentUserId))->getField('money');//用户余额

            $n_content = '你的回答已被采纳，得到' . $orderInfo['money'] . '元悬赏';
            M('Resource_comment')->where(array('id' => $rid))->setField('author_reward', $orderInfo['money']);

            //打钱到用户钱包
            D('Wallet')->updateWalletMoney($commentUserId, $orderInfo['money'], 1);
            //插入钱包变动日志
            $time = $_SERVER["REQUEST_TIME"];
            D('WalletLog')->addWalletRecord($orderInfo['record_sn'], $commentUserId, $orderInfo['money'], $userBalance + $orderInfo['money'], '悬赏', $time, 1);
            //插入收入记录日志
            D('Harvest')->addHarvestLog($commentUserId, $orderInfo['money'], 1, 5, $hid, 1);
            //插入交易记录
            D('RecordLog')->addRecordLog($orderInfo['record_sn'], $commentUserId, $orderInfo['money'], 1, $hid, 4, '', 1, $time);
        }

        //推送通知
        $m_txt = array(
            'type' => 1,    //0系统消息 1回答我 2求助我 3评论我 4响应我
            'cid'  => $rid
        );
        $push  = A('PushMsg');
        $push->sendPush('all', $alias, $n_content, 'http', $m_txt);

        $push->addMessage($commentUserId, $n_content, $n_content, $commentUserMobile, 2, $_SERVER['REQUEST_TIME']);

        //采纳成功
        apiReturn('1024', AJAX_TRUE);
    }


    //回复帖子
    public function comment_posts() {
        $AesMct  = new MCrypt;
        $uid     = session('my_id');    //用户id
        $rid     = $AesMct->decrypt(urldecode(I('post.rid'))); //帖子id
        $pid     = $AesMct->decrypt(urldecode(I('post.pid'))); //帖子id
        $content = $AesMct->decrypt(urldecode(trim(I('post.content'))));  //回复内容
        $is_nym  = $AesMct->decrypt(urldecode(I('post.is_nym')));    //是否匿名 1是
        $type  = $AesMct->decrypt(urldecode(I('post.type')));    //文字0 视频1

        if($type == 0){
            $content = $AesMct->decrypt(urldecode(trim(I('post.content'))));  //回复内容
        }elseif($type == 1){
            $content = session("video_sid"); //获取上传视频后存储在session中的video_id
        }

        if(empty($rid) || empty($content)) {
            apiReturn('1030', AJAX_FALSE, '缺少必要参数');
        }
        if(empty($pid)) {
            $pid = 0;
        }

        $data = array(
            'uid'     => $uid,
            'rid'     => $rid,
            'pid'     => $pid,
            'content' => $content,
            'is_nym'  => $is_nym,
            'time'    => $_SERVER["REQUEST_TIME"]
        );

        if(M('Resource_comment')->add($data)) {
            $ctype = M('Resource')->where('id=' . $rid)->field('title,type,author')->find();

            //type 内容类别：1资讯 2求助
            //$m_txt = array('type' => 3, 'cid' => $rid);       //type 0系统消息 1回答我 2求助我 3评论我 4响应我
            switch($ctype['type']) {
                case 1;
                    $n_content = '你收到一条新的评论';
                    break;
                case 2;
                    $n_content = '收到一条新回答';
                    break;
            }

            //推送通知
            $tel   = (int)user_info($ctype['author'], 'mobile');
            $alias = array('alias' => array($tel));
            $m_txt = array(
                'type' => 1    //0系统消息 1回答我 2求助我 3评论我 4响应我
            );
            $push  = A('PushMsg');
            $push->sendPush('all', $alias, $n_content, 'http', $m_txt);

            apiReturn('1030', AJAX_TRUE);
        } else {
            apiReturn('1023', AJAX_FALSE);
        }
    }


    //活动列表
    public function activity_list() {
        $nowPage = I('post.nowpage');   //页码
        $num     = I('post.num');   //每页条数
        $where   = array(
            'status' => array('neq', 0)
        );
        $order   = array(
            'send_time' => 'desc',
            'views'     => 'desc',
        );
        //数据分页
        $config = array(
            'tablename' => 'Admin/ActivityRelation', // 表名
            'relation'  => true, // 关联条件
            'where'     => $where, //条件
            'order'     => $order, // 排序
            'page'      => $nowPage,  // 页码，默认为首页
            'num'       => $num,  // 每页条数
            'field'     => 'id,title,address,number,price,star_time,end_time,views,status,send_time,imgs,type,author,deadline'
        );
        $page   = new ApiPage($config);
        $data   = $page->get(); //获取分页数据
        if($data['now_page'] == 0) {
            apiReturn('1019', AJAX_FALSE);   //获取数据失败
        } else {
            $get_dat = array();
            foreach($data as $k => $v) {
                foreach($v as $m => $n) {
                    $get_dat[ $k ][ $m ]        = $n;
                    $get_dat[ $k ]['my_id']     = session('my_id');   //写入当前用户id
                    $get_dat[ $k ]['sums_page'] = $data['total_page'];    //总页数
                }
            }
            apiReturn('1020', AJAX_TRUE, $get_dat);   //获取数据成功
        }
    }


    //发布活动
    public function activity_send() {
        $AesMct = new MCrypt;
        $accid  = session('accid');  //用户accid
        $token  = session('token');  //用户云信token
        $uid    = M('Account')->where(array('mobile' => $accid, 'token' => $token))->field('id,type,status')->find();
        if($uid['type'] == 4 || $uid['status'] == 2) {
            apiReturn('1029', AJAX_FALSE);   //游客没有权限
        } else {
            $data['title']     = $AesMct->decrypt(urldecode(I('post.title')));  //活动标题
            $data['remark']    = $AesMct->decrypt(urldecode(I('post.content')));   //活动介绍
            $data['type']      = $AesMct->decrypt(urldecode(I('post.type')));   //活动方式1线下2线上
            $data['group']     = $AesMct->decrypt(urldecode(I('post.group')));   //群号id
            $data['address']   = $AesMct->decrypt(urldecode(I('post.address')));  //活动地址
            $data['number']    = $AesMct->decrypt(urldecode(I('post.peoples')));   //限制人数
            $data['price']     = $AesMct->decrypt(urldecode(I('post.price')));     //人均消费
            $data['star_time'] = $AesMct->decrypt(urldecode(I('post.star_time')));  //活动开始时间
            $data['end_time']  = $AesMct->decrypt(urldecode(I('post.end_time')));  //活动结束时间
            $data['deadline']  = $AesMct->decrypt(urldecode(I('post.deadline')));  //活动截止时间
            $data['sponsor']   = $AesMct->decrypt(urldecode(I('post.sponsor'))); //承办单位
            $data['author']    = $uid['id'];    //发布者
            $data['imgs']      = $AesMct->decrypt(urldecode(I('post.title_img')));   //标题图片
            $data['send_time'] = time();    //发布时间
            if(M('Activity')->add($data)) {
                apiReturn('1024', AJAX_TRUE);    //发布成功
            } else {
                apiReturn('1023', AJAX_FALSE);   //发布失败
            }
        }
    }


    //活动报名
    public function activity_join() {
        $mcrypt        = new MCrypt;
        $uid           = session('my_id');  //用户id
        $aid           = $mcrypt->decrypt(urldecode(I('post.aid')));  //活动id
        $activityPart = M('Activity_part');
        if(empty($aid)) {
            apiReturn('1022', AJAX_FALSE, '缺少必要参数');
        }

        $data = array(
            'aid'  => $aid,
            'uid'  => $uid,
            'time' => time()
        );
        $partCount = $activityPart->where('aid=' . $aid)->count();    //统计当前报名人数
        $activity = M('Activity')->where('id=' . $aid)
            ->field('author,star_time,end_time,number,deadline,status')
            ->find();   //活动限制条件
        $dead = $activity['deadline'];     //活动截止时间

        //活动已结束
        if($activity['end_time'] <= time() || $activity['status'] == 2) {
            apiReturn('1033', AJAX_FALSE, '活动已结束');
        }

        //报名已截止
        if($partCount >= $activity['number'] || $dead <= time() || $activity['status'] > 1) {
            apiReturn('1034', AJAX_FALSE, '报名已截止');
        }

        //用户已报名
        $map = array('aid' => $aid, 'uid' => $uid);
        $res = $activityPart->where($map)->count();
        if($res > 0) {
            apiReturn('1035', AJAX_FALSE, '用户已报名');
        }

        //付费活动
        if($data['is_money']) {
            if(empty($uid)) {
                apiReturn('205',AJAX_FALSE,'未获取到用户id,请重新登录');
            }

            //检测订单支付状态
            $where = ['user_id'  => $uid, 'activity_id' => $aid, 'status' => 1];
            if(empty(M('order_activity')->where($where)->find())) {
                apiReturn('504', AJAX_FALSE, '付款成功后才能参加活动');
            }
        }

        if($id = $activityPart->add($data)) {

            //区别后台管理员与用户
            if($activity['author'] > 0) {
                $tel = user_info($activity['author'], 'mobile');
                //0系统消息 1回答我 2求助我 3评论我 4响应我
                $m_txt     = array('type' => 4, 'cid' => $aid);
                $alias     = array('alias' => array($tel));
                $n_content = '你收到了一条报名信息';
                $Jpush     = new Jpush(C('JPUSH_APPKEY'), C('JPUSH_SECRET'));
                $res       = $Jpush->push('all', $alias, $n_content, $m_type = 'http', $m_txt);

                //消息推送失败或无响应
                if(!$res) {
                    apiReturn('1033', AJAX_FALSE);
                }

                $res_arr = json_decode($res, true);
                if(isset($res_arr['error'])) {
                    //如果返回了error则证明失败
                    $activityPart->where('id=' . $id)->delete();
                    apiReturn('1031', AJAX_FALSE, $res_arr);
                }
            }
            apiReturn('1030', AJAX_TRUE);

        } else {
            apiReturn('1036', AJAX_FALSE, '报名失败');   //报名失败
        }
    }


    //删除活动
    public function activity_del() {
        $AesMct = new MCrypt;
        $aid    = $AesMct->decrypt(urldecode(I('post.aid')));  //活动id
        $uid    = session('my_id');  //用户id
        $model  = M('Activity');
        $author = $model->where('id=' . $aid)->getField('author');
        if($author == $uid) {
            if($model->where('id=' . $aid)->setField('status', 0)) {
                apiReturn('1020', AJAX_TRUE);    //更改活动状态成功
            } else {
                apiReturn('1019', AJAX_FALSE);   //更改活动状态失败
            }
        } else {
            apiReturn('1029', AJAX_FALSE);   //当前用户没有权限
        }
    }


    //评论活动
    public function comment_activity() {
        $AesMct  = new MCrypt;
        $aid     = $AesMct->decrypt(urldecode(I('post.aid')));  //活动id
        $uid     = session('my_id');  //用户id
        $content = $AesMct->decrypt(urldecode(I('post.content')));  //回复内容
        $is_nym  = $AesMct->decrypt(urldecode(I('post.is_nym')));    //是否匿名
        $data    = array(
            'uid'     => $uid,
            'aid'     => $aid,
            'content' => $content,
            'time'    => time(),
            'is_nym'  => $is_nym
        );
        if(M('Activity_comment')->add($data)) {
            apiReturn('1024', AJAX_TRUE);    //发布成功
        } else {
            apiReturn('1023', AJAX_FALSE);   //发布失败
        }
    }


    //视频列表轮播图
    public function video_banner() {
        $where = array('mark' => 2, 'c_type' => 4);
        $field = 'cid,banner';    //查询参数
        $order = 'sort, push_time desc';
        $data  = M('Push')->where($where)->field($field)->order($order)->select();
        foreach($data as $k => $v) {
            $res[ $k ]['id']        = $v['cid'];
            $res[ $k ]['title_img'] = $v['banner'];
        }

        apiReturn('1020', AJAX_TRUE, $res);
    }


    //视频列表
    public function video_list() {
        $AesMct  = new MCrypt;
        $mid     = $AesMct->decrypt(urldecode(I('post.mid')));     //专业id
        $nowPage = I('post.nowpage');   //页码
        $num     = I('post.num');   //每页条数
        if(!empty($mid)) {
            $where['tags'] = $mid;
        }
        $where['status']     = 1;
        $where['is_private'] = 1;
        $order               = array(
            'create_at' => 'desc',
            'views'     => 'desc',
        );
        // 数据分页
        $config = array(
            'tablename' => 'Admin/TrainRelation', // 表名
            'relation'  => true, // 关联条件
            'where'     => $where,  //查询条件
            'order'     => $order, // 排序
            'page'      => $nowPage,  // 页码，默认为首页
            'num'       => $num,  // 每页条数
            //'field'     => 'id,title,title_img,views,type,roomid,rtmpPullUrl'
        );
        $page   = new ApiPage($config);
        $data   = $page->get(); //获取分页数据
        if($nowPage == 0) {
            apiReturn('1019', AJAX_FALSE);   //获取数据失败
        } else {
            $get_dat = array();
            foreach($data as $k => $v) {
                foreach($v as $m => $n) {
                    $get_dat[ $k ][ $m ]        = $n;
                    $get_dat[ $k ]['sums_page'] = $data['total_page'];    //总页数
                }
            }
            apiReturn('1020', AJAX_TRUE, $get_dat);   //获取数据成功
        }
    }


    //视频列表头部的标签导航数据
    public function head_tags() {
        $data = M('Train')->Distinct(true)->where('status=1')->field('tags')->select();    //获取视频中的所有标签id
        $arr  = array_column($data, 'tags');  //转换一维数组
        //专业表中查询存在的数据
        $where = array(
            'id' => array('in', $arr)
        );
        $tags  = M('Major')->where($where)->order('sort')->field('major_name,id')->select();
        apiReturn('1020', AJAX_TRUE, $tags);
    }


    //评论视频
    public function comment_video() {
        $AesMct  = new MCrypt;
        $vid     = $AesMct->decrypt(urldecode(I('post.vid')));  //视频id
        $content = $AesMct->decrypt(urldecode(I('post.content')));  //回复内容
        $is_nym  = $AesMct->decrypt(urldecode(I('post.is_nym')));    //是否匿名 1 是
        $uid     = session('my_id');
        $data    = array(
            'uid'     => $uid,
            'vid'     => $vid,
            'content' => $content,
            'ctime'   => time(),
            'is_nym'  => $is_nym
        );
        if(M('Train_comment')->add($data)) {
            apiReturn('1024', AJAX_TRUE);    //发布成功
        } else {
            apiReturn('1023', AJAX_FALSE);   //发布失败
        }
    }


    //收藏视频
    public function favorite_video() {
        $AesMct = new MCrypt;
        $vid    = $AesMct->decrypt(urldecode(I('post.vid')));  //视频id
        $uid    = session('my_id');  //用户id
        $status = $AesMct->decrypt(urldecode(I('post.status')));    //收藏状态 1收藏
        $data   = array(
            'uid' => $uid,
            'vid' => $vid,
        );
        $model  = M('Train_favorite');
        if(intval($status) == 1) {
            $data['time'] = time();
            if($model->add($data)) {
                apiReturn('1020', AJAX_TRUE);    //收藏成功
            } else {
                apiReturn('1019', AJAX_FALSE);   //收藏失败
            }
        } else {
            if($model->where($data)->delete()) {
                apiReturn('1020', AJAX_TRUE);    //取消收藏成功
            } else {
                apiReturn('1019', AJAX_FALSE);   //取消收藏失败
            }
        }
    }


    //常用网站列表
    public function web_list() {
        $nowPage = I('post.nowpage');   //页码
        $num     = I('post.num');   //每页条数
        $order   = array(
            'create_at' => 'desc'
        );
        //数据分页
        $config = array(
            'tablename' => 'Ad', // 表名
            'order'     => $order, // 排序
            'page'      => $nowPage,  // 页码，默认为首页
            'num'       => $num,  // 每页条数
            'field'     => 'id,title,url,create_at'
        );
        $page   = new ApiPage($config);
        $data   = $page->get(); //获取分页数据
        if($data['now_page'] == 0) {
            apiReturn('1019', AJAX_FALSE);   //获取数据失败
        } else {
            $get_dat = array();
            foreach($data as $k => $v) {
                foreach($v as $m => $n) {
                    $get_dat[ $k ][ $m ]        = $n;
                    $get_dat[ $k ]['sums_page'] = $data['total_page'];    //总页数
                }
            }
            apiReturn('1020', AJAX_TRUE, $get_dat);   //获取数据成功
        }
    }


    //帖子回复点赞
    public function likes() {
        $AesMct    = new MCrypt;
        $userId    = session('my_id');
        $commentId = $AesMct->decrypt(urldecode(I('post.cid')));//回复id
        $likes     = M('Comm_like');

        //检测重复点赞
        $data = array('comm_id' => $commentId, 'uid' => $userId);
        if($likes->where($data)->getField('id')) {
            apiReturn('1023', AJAX_TRUE, '你已经点过赞');
        }

        //点赞
        $resourceComment = M('Resource_comment');
        if(!$resourceComment->where('id=' . $commentId)->setInc('likes')) {
            apiReturn('1025', AJAX_FALSE);
        }
        //插入点赞日志记录
        $likes->add($data);

        $resources   = M('Resource');
        $commentInfo = $resourceComment->where('id=' . $commentId)->field('uid,likes,rid,content')->find();//回复详情
        $articleInfo
                     = $resources->where('id=' . $commentInfo['rid'])->field('is_money,red_status,redpack_id,author,title')->find();//求助详情

        //判断是不是红包贴
        if(!$articleInfo['is_money']) {
            //普通帖子点赞成功
            apiReturn('1020', AJAX_TRUE);
        }

        //判断红包是否已领
        if($articleInfo['red_status']) {
            apiReturn('1021', AJAX_TRUE);
        }

        $bonusConf    = M('Bonus_conf')->where('conf_id=' . $articleInfo['redpack_id'])->find();//红包配置详情
        $amount       = ($bonusConf['total_amount'] * trim($bonusConf['commission'], '%')) / 100;//回复者红包金额
        $authorAmount = $bonusConf['total_amount'] - $amount;//求助者红包金额
        $likesNum     = $bonusConf['like_nums'];//要求点赞数

        //点赞数不够分红包
        if($commentInfo['likes'] < $likesNum) {
            apiReturn('1204', AJAX_TRUE);   // 点赞成功,但未满点赞数要求
        }

        // 写入回复评论红包金额
        $resourceComment->where('id=' . $commentId)->setField('amount', $amount);
        // 标记帖子的红包金额,红包状态标为已领
        $resourceData = array('red_status' => 1, 'author_amount' => $authorAmount);
        $resources->where('id=' . $commentInfo['rid'])->save($resourceData);

        //插入收入日志记录A
        D('harvest')->addHarvestLog($articleInfo['author'], $authorAmount, 1, 3, $commentInfo['rid'], 1);
        D('harvest')->addHarvestLog($commentInfo['uid'], $amount, 1, 3, $commentInfo['rid'], 1);
        //增加用户余额
        D('wallet')->updateWalletMoney($articleInfo['author'], $authorAmount, 1);
        D('wallet')->updateWalletMoney($commentInfo['uid'], $amount, 1);
        //插入钱包变动日志
        $recordSn = A('order')->setRecordSn();
        D('walletLog')->addWalletRecord($recordSn, $articleInfo['author'], $authorAmount, '', '', $_SERVER['REQUEST_TIME'], 1);
        D('walletLog')->addWalletRecord($recordSn, $commentInfo['uid'], $amount, '', '', $_SERVER['REQUEST_TIME'], 1);
        //插入交易流水日志,供后台管理员查看
        D('recordLog')->addRecordLog($recordSn, $articleInfo['author'], $authorAmount, 1, $commentInfo['rid'], 3, 0, 1, $_SERVER['REQUEST_TIME']);
        D('recordLog')->addRecordLog($recordSn, $commentInfo['uid'], $amount, 1, $commentInfo['rid'], 3, 0, 1, $_SERVER['REQUEST_TIME']);

        //消息推送
        $telA       = user_info($articleInfo['author'], 'mobile');//求助者手机号
        $telB       = user_info($commentInfo['uid'], 'mobile');//回复者手机号
        $aliasA     = array('alias' => $telA);
        $aliasB     = array('alias' => $telB);
        $n_contentA = '恭喜您获得' . $authorAmount . '元求助活动红包奖励! 你的求助:' . substr($articleInfo['title'], 0, 30);
        $n_contentB = '恭喜您获得' . $amount . '元求助活动红包奖励! 你的回答:' . substr($commentInfo['content'], 0, 30);
        $m_txt      = array('type' => 0);  //0系统消息 1回答我 2求助我 3评论我 4响应我
        A('PushMsg')->sendPush($platform = 'all', $aliasA, $n_contentA, 'http', $m_txt);//求助者
        A('PushMsg')->sendPush($platform = 'all', $aliasB, $n_contentB, 'http', $m_txt);//回复者
        //推送成功
        $msgDataA = array(
            //求助者
            'title'     => '红包消息',
            'content'   => $n_contentA,
            'push_obj'  => $telA,
            'type'      => 2,
            'send_time' => $_SERVER['REQUEST_TIME']
        );
        $msgDataB = array(
            //回复者
            'title'     => '红包消息',
            'content'   => $n_contentB,
            'push_obj'  => $telB,
            'type'      => 2,
            'send_time' => $_SERVER['REQUEST_TIME']
        );
        //系统消息列表
        $messageA = M('Sys_messages')->add($msgDataA);
        $messageB = M('Sys_messages')->add($msgDataB);
        //插入未读列表
        $messageDataA = array(
            'msgid' => $messageA,
            'uid'   => $articleInfo['author']
        );
        $messageDataB = array(
            'msgid' => $messageB,
            'uid'   => $commentInfo['uid']
        );
        M('Message')->add($messageDataA);
        M('Message')->add($messageDataB);

        //红包分配成功
        apiReturn('1204', AJAX_TRUE);
    }


    //帖子回复点踩
    public function dislikes() {
        $AesMct = new MCrypt;
        $uid    = session('my_id');    //当前用户id
        $cid    = $AesMct->decrypt(urldecode(I('post.cid')));  //回复id
        $status = $AesMct->decrypt(urldecode(I('post.status')));    //点赞状态 1点赞
        $commt  = M('Resource_comment');
        $likes  = M('comm_dislike');
        $data   = array(
            'comm_id' => $cid,
            'uid'     => $uid
        );
        if(intval($status) == 1) {
            if($commt->where('id=' . $cid)->setInc('dislikes')) {
                $likes->add($data);
                apiReturn('1204', AJAX_TRUE);
            } else {
                apiReturn('1023', AJAX_FALSE);
            }
        } else {
            if($commt->where('id=' . $cid)->setDec('dislikes')) {
                $likes->where($data)->delete();
                apiReturn('1204', AJAX_TRUE);
            } else {
                apiReturn('1023', AJAX_FALSE);
            }
        }
    }


    //举报
    public function report() {
        $uid     = session('my_id');    //当前用户id
        $AesMct  = new MCrypt;
        $type    = $AesMct->decrypt(urldecode(I('post.tag')));     //举报对象类别    1帖子 2活动
        $because = $AesMct->decrypt(urldecode(I('post.reason')));     //举报原因
        $object  = $AesMct->decrypt(urldecode(I('post.obj_id')));    //举报对象id
        $data    = array(
            'title'  => $because,
            'uid'    => $uid,
            'object' => $object,
            'type'   => $type,
            'time'   => time()
        );
        if(M('Report')->add($data)) {
            apiReturn('1024', AJAX_TRUE);
        } else {
            apiReturn('1023', AJAX_FALSE);
        }
    }


    //收藏帖子
    public function favorite_topic() {
        $AesMct = new MCrypt;
        $rid    = $AesMct->decrypt(urldecode(I('post.rid')));  //帖子id
        $uid    = session('my_id');  //用户id
        $status = $AesMct->decrypt(urldecode(I('post.status')));    //收藏状态 1收藏
        $data   = array(
            'uid' => $uid,
            'rid' => $rid,
        );
        $model  = M('Resource_favorite');
        if(intval($status) == 1) {
            $data['time'] = time();
            if($model->add($data)) {
                apiReturn('1020', AJAX_TRUE);    //收藏成功
            } else {
                apiReturn('1019', AJAX_FALSE);   //收藏失败
            }
        } else {
            if($model->where($data)->delete()) {
                apiReturn('1020', AJAX_TRUE);    //取消收藏成功
            } else {
                apiReturn('1019', AJAX_FALSE);   //取消收藏失败
            }
        }
    }


    //收藏活动
    public function favorite_activity() {
        $AesMct = new MCrypt;
        $aid    = $AesMct->decrypt(urldecode(I('post.aid')));  //帖子id
        $uid    = session('my_id');  //用户id
        $status = $AesMct->decrypt(urldecode(I('post.status')));    //收藏状态 1收藏
        $data   = array(
            'uid' => $uid,
            'aid' => $aid,
        );
        $model  = M('Activity_favorite');
        if(intval($status) == 1) {
            $data['time'] = time();
            if($model->add($data)) {
                apiReturn('1020', AJAX_TRUE);    //收藏成功
            } else {
                apiReturn('1019', AJAX_FALSE);   //收藏失败
            }
        } else {
            if($model->where($data)->delete()) {
                apiReturn('1020', AJAX_TRUE);    //取消收藏成功
            } else {
                apiReturn('1019', AJAX_FALSE);   //取消收藏失败
            }
        }
    }


    //发布资讯
    public function news_send() {
        $AesMct  = new MCrypt;
        $user_id = session('my_id');  //用户id
        $usr     = M('Account')->where('id=' . $user_id)->field('id,type,status')->find();    //获取用户id和身份

        if(empty($usr)) {
            apiReturn('1025', AJAX_FALSE, '用户不存在');
        }
        if($usr['type'] == 4 || $usr['status'] == 2) {
            apiReturn('1029', AJAX_FALSE, '您的账号没有权限进行此操作，请申请认证');   //游客没有权限
        } else {
            $data['title']     = trim($AesMct->decrypt(urldecode(I('post.title'))));   //标题
            $data['content']   = trim($AesMct->decrypt(urldecode(I('post.content'))));   //内容
            $data['tag_major'] = $AesMct->decrypt(urldecode(I('post.majors')));  //专业领域
            $data['sort']      = $AesMct->decrypt(urldecode(I('post.sort')));   //文章分类
            $data['author']    = $usr['id'];   //发布者
            $data['type']      = 3;      //内容类别：1话题2求助3资讯
            $data['send_time'] = $_SERVER[ REQUEST_TIME ];    //发布时间

            if(empty($data['sort'])) {
                apiReturn('1023', AJAX_FALSE);
            }

            if(M('Resource')->add($data)) {
                apiReturn('1024', AJAX_TRUE);   //发布成功
            } else {
                apiReturn('1023', AJAX_FALSE);   //发布失败
            }
        }
    }


    //帖子点赞
    public function news_likes() {
        $AesMct    = new MCrypt;
        $uid       = session('my_id');    //当前用户id
        $rid       = $AesMct->decrypt(urldecode(I('post.rid')));      //帖子id
        $status    = $AesMct->decrypt(urldecode(I('post.status')));    //点赞状态  1点赞 2取消点赞
        $likes     = M('Likes');
        $resources = M('Resource');

        //检测是否已点过赞
        $data = array('rid' => $rid, 'uid' => $uid);
        if($likes->where($data)->count() > 0) {
            apiReturn('1023', AJAX_FALSE);
        }

        if($resources->where('id=' . $rid)->setInc('likes')) {

            $likes->add($data);
            $where       = array('id' => $rid, 'type' => 3);
            $news_info   = $resources->where($where)->field('id,title,author,type')->find();
            $username    = user_info($uid, 'uname');    //发布者名称
            $friends_arr = user_info($news_info['author'], 'mobile');    //发布者手机号
            $alias       = array('alias' => explode(',', $friends_arr));
            $n_content   = '用户' . $username . '赞了您';
            //0系统消息 1回答我 2求助我 3评论我 4响应我
            $m_txt = array('type' => 4,);
            $Jpush = new Jpush(C('JPUSH_APPKEY'), C('JPUSH_SECRET'));
            $res   = $Jpush->push('all', $alias, $n_content, $m_type = 'http', $m_txt);

            if(!$res) {
                apiReturn('1033', AJAX_FALSE);
            }   //推送提醒失败或无响应

            $msg_data = array( //回复者
                               'title'     => '点赞消息',
                               'content'   => $n_content,
                               'push_obj'  => $username . '(' . $friends_arr . ')',
                               'type'      => 4,
                               'send_time' => $_SERVER['REQUEST_TIME']
            );

            $sys_messages = M('Sys_messages');
            if($mid = $sys_messages->add($msg_data)) {

                $res_arr = json_decode($res, true);
                if(isset($res_arr['error'])) {
                    $sys_messages->where('id=' . $mid)->delete();
                } else {
                    $message_data = array('msgid' => $mid, 'uid' => $news_info['author']);
                    M('Message')->add($message_data);
                }

            }

            apiReturn('1024', AJAX_TRUE);    //点赞成功
        } else {
            apiReturn('1025', AJAX_FALSE);  //点赞失败
        }
    }


    //帖子点踩
    public function news_dislikes() {
        $AesMct    = new MCrypt;
        $uid       = session('my_id');    //当前用户id
        $rid       = $AesMct->decrypt(urldecode(I('post.rid')));  //回复id
        $status    = $AesMct->decrypt(urldecode(I('post.status')));    //点踩状态 1点踩
        $dislikes  = M('Dislikes');
        $resources = M('Resource');
        $data      = array(
            'rid' => $rid,
            'uid' => $uid
        );
        if(intval($status) == 1) {
            if($resources->where('id=' . $rid)->setInc('dislikes')) {
                $dislikes->add($data);
                apiReturn('1204', AJAX_TRUE);
            } else {
                apiReturn('1023', AJAX_FALSE);
            }
        } else {
            if($resources->where('id=' . $rid)->setDec('dislikes')) {
                $dislikes->where($data)->delete();
                apiReturn('1204', AJAX_TRUE);
            } else {
                apiReturn('1023', AJAX_FALSE);
            }
        }
    }


    //打赏下订单
    public function answerReward() {
        $crypt      = new MCrypt;
        $resourceId = $crypt->decrypt(urldecode(I('post.rid')));
        $commentId  = $crypt->decrypt(urldecode(I('post.cid')));
        $fromUid    = $crypt->decrypt(urldecode(I('post.from_uid')));
        $toUid = $crypt->decrypt(urldecode(I('post.to_id')));
        $createTime = $_SERVER['REQUEST_TIME'];

        if(empty($resourceId) || empty($commentId) || empty($fromUid) || empty($toUid)) {
            apiReturn('1021', AJAX_FALSE, 'Lack of necessary parameters');
        }

        //防止用户恶意请求
        $where      = array('comment_id' => $commentId, 'from_uid' => $fromUid);
        $answerInfo = M('answer_reward_order')->where($where)->getField('id,create_date');

        if(!empty($answerInfo)) {

            $timeDiff = $createTime - $answerInfo['create_date'];
            $timeDiff = $timeDiff % (86400 * 3600 * 60);
            if($timeDiff < 30) {
                apiReturn('1020', AJAX_FALSE, 'reward action too many');
            }
        }

        //新增悬赏业务订单
        $order      = A('Order');
        $orderSn  = $order->uniqueOrderSn($fromUid);
        $createData = [
            'record_sn'   => $orderSn,
            'comment_id'  => $commentId,
            'from_uid'    => $fromUid,
            'to_uid'      => $toUid,
            'create_date' => $createTime,
            'status'      => 0
        ];
        $orderId    = $order->insertOrder('answer_reward_order', $createData);

        if(!$orderId || empty($orderId)) {

            apiReturn('1021', AJAX_FALSE);
        }

        //新增订单关联数据
        $updateUnionData = [
            'record_sn'   => $orderSn,
            'order_type'  => 'answer_reward_order',
            'order_id'    => $orderId,
            'create_date' => $createTime,
            'status'      => 0
        ];
        $unionOrderId    = $order->insertOrder('order', $updateUnionData);
        $unionOrderId    = strval($unionOrderId);

        if(!$unionOrderId || empty($unionOrderId)) {

            apiReturn('1022', AJAX_FALSE);
        }

        //插入交易记录,支付成功后更改支付状态
        D('RecordLog')->addRecordLog($orderSn, $fromUid, '', 2, $resourceId, 5);

        $wallet = M('Wallet')->where('uid=' . $fromUid)->field('money,pay_password')->find();
        if(empty($wallet['pay_password'])) {
            $pay_password = '0';
        } else {
            $pay_password = '1';
        }
        $returnData = [
            'cid'          => strval($commentId),
            'money'        => strval($wallet['money']),
            'is_paypasswd' => strval($pay_password),
            'order_id'     => strval($unionOrderId)
        ];

        apiReturn('1024', AJAX_TRUE, $returnData);
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
//        apiReturn('1030', AJAX_TRUE,$video_data);
        if($video_data['code'] == 0) { //判断返回数据的状态码是否为成功，并插入数据库
            $id = M('Video')->add($video_data['data']);
            session('video_sid', $id); //保存数据id到session
            apiReturn('1025', AJAX_TRUE, "");
        }else{
            apiReturn('1026', AJAX_FALSE, "");
        }
    }
}
