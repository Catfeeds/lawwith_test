<?php
//+----------------------------------------------------------------------
// | Copyright (c) 2015-2017 http://lawyerscloud.cn All rights reserved.
//+----------------------------------------------------------------------
// | Author: 慢悠悠的丑小鸭 <1597305236@qq.com>
//+----------------------------------------------------------------------

namespace App\Controller;

use Think\Controller;
use Common\Api\ChatApi;
use Common\Api\MCrypt;

class LoginController extends Controller
{
    //获取验证码
    public function sendCode() {
        $AesMcrypt = new MCrypt;
        $accid     = $AesMcrypt->decrypt(urldecode(I('post.tel')));
        $cache_tel = S('send_tel');
        $yunxin    = new ChatApi;

        if(empty($cache_tel)) {
            S('send_tel', $accid, 60);
            //发送短息验证码，并获取返回数据
            $back_info = $yunxin->sendSmsCode($accid);
            if($back_info['code'] == 200) {
                apiReturn('1006', AJAX_TRUE);
            } else {
                apiReturn('10019', AJAX_FALSE, '验证码获取失败');
            }
        } else {
            if($cache_tel !== $accid && !empty($accid)) {
                S('send_tel', $accid, 60);
                $back_info = $yunxin->sendSmsCode($accid);
                if($back_info['code'] == 200) {
                    apiReturn('1006', AJAX_TRUE);
                } else {
                    apiReturn('10029', AJAX_FALSE, '验证码获取失败');
                }
            } else {
                apiReturn('1013', AJAX_FALSE, '手机号不能为空');
            }
        }
    }


    //获取验证码v2
    public function sendCodeV2() {
        $AesMcrypt  = new MCrypt;
        $tel        = I('post.tel');
        $encryptTel = $AesMcrypt->decrypt(urldecode(I('post.encrypt_tel')));

        if($tel !== $encryptTel) {
            apiReturn('1013', AJAX_FALSE, '参数传入错误');
        }
        if(empty($tel)) {
            apiReturn('1013', AJAX_FALSE, '手机号不能为空');
        }

        $yunxin    = new ChatApi;
        $back_info = $yunxin->sendSmsCode($tel);

        if($back_info['code'] == 200) {
            apiReturn('1006', AJAX_TRUE);
        } else {
            apiReturn('10029', AJAX_FALSE, '验证码获取失败');
        }
    }


    //校验短信验证码
    public function checkSMS() {
        $AesMcrypt = new MCrypt; //调用AES加密接口
        $yunxin    = new ChatApi;  //调用云信接口
        $p_accid   = urldecode(I('post.tel')); //过滤手机号加密字符
        $p_code    = urldecode(I('post.code')); //过滤验证码加密字符
        $accid     = $AesMcrypt->decrypt($p_accid);
        $code      = $AesMcrypt->decrypt($p_code);
        $res       = $yunxin->verifycode($accid, $code); //实例化校验短信验证码
        if($res['code'] == 200) {
            apiReturn('1002', AJAX_TRUE); //校验验证码成功
        } else {
            apiReturn('1003', AJAX_FALSE, '验证码验证失败'); //校验失败
        }

    }


    //获取验证码,参数未加密版本
    public function sendValidateCode() {
        $tel = I('post.tel');
        if(empty($tel)) {
            apiReturn('1013', AJAX_FALSE, '手机号不能为空');
        }

        $yunxin    = new ChatApi;
        $back_info = $yunxin->sendSmsCode($tel);

        if($back_info['code'] == 200) {
            apiReturn('1006', AJAX_TRUE);
        } else {
            apiReturn('10029', AJAX_FALSE, '验证码获取失败');
        }
    }


    //校验短信验证码,参数未加密版本
    public function verifyValidateCode($mobile, $code) {
        $yunxin = new ChatApi;  //调用云信接口
        $res    = $yunxin->verifycode($mobile, $code); //实例化校验短信验证码
        if($res['code'] == 200) {
            return true; //校验验证码成功
        } else {
            return false; //校验失败
        }

    }


    /**
     * 用户注册
     */
    public function register() {
        $yunxin    = new ChatApi;
        $AesMcrypt = new MCrypt;
        $p_accid   = urldecode(I('post.tel'));
        $p_passwd  = urldecode(I('post.password'));
        $p_code    = urldecode(I('post.code'));
        $accid     = $AesMcrypt->decrypt($p_accid);
        $passwd    = $AesMcrypt->decrypt($p_passwd);
        $code      = $AesMcrypt->decrypt($p_code);
        //手机参数为空
        if(empty($accid)) {
            apiReturn('1005', AJAX_FALSE, '手机号不能为空');
        }
        //手机号已存在
        $isUser = M('Account')->where('mobile=' . $accid)->count();
        if($isUser) {
            apiReturn('1001', AJAX_FALSE, '您已注册过，请直接登录');
        }
        //短信验证码校验
        $res = $yunxin->verifycode($accid, $code);
        if($res['code'] !== 200) {
            apiReturn('1003', AJAX_FALSE, '验证码验证失败');
        }
        //判断用户是否已经存在云信
        $token = $yunxin->createUserId($accid);
        if($token['code'] == 200) {
            $data = [
                'mobile'    => $token['info']['accid'],
                'token'     => $token['info']['token'],
                'passwd'    => trim($passwd),
                'create_at' => time(),
            ];
        } else {
            //如果用户已经存在云信，更新用户token并更新本地数据
            $toke = $yunxin->updateUserToken($accid);
            if($toke['code'] !== 200) {
                apiReturn('1011', AJAX_FALSE);
            }
            $data = [
                'mobile'    => $toke['info']['accid'],
                'token'     => $toke['info']['token'],
                'passwd'    => trim($passwd),
                'create_at' => time(),
            ];
        }
        if($uid = M('Account')->add($data)) {
            D('Wallet')->addUserWallet(array('uid' => $uid));

            //标记邀请账号已激活
            if($refer_id = M('Invite')->where('mobile=' . $accid)->getField('refer_id')) {
                M('Invite')->where('mobile=' . $accid)->save(
                    [
                        'is_actived'   => '1',
                        'actived_time' => $_SERVER["REQUEST_TIME"]
                    ]);

                //给邀请者发推送通知
                $invite_mobile = user_info($refer_id,'mobile');
                $count = M('Invite')->where(['refer_id'=>$refer_id,'is_actived'=>'1'])->count();
                $alias['alias'] = explode(',', $invite_mobile);
                send_message_push('all',$alias,'您已成功邀请'.$count.'人');
            }

            apiReturn('1004', AJAX_TRUE, $data);  //注册成功
        } else {
            apiReturn('1011', AJAX_FALSE, '注册失败,请重试');   //插入数据库失败
        }

    }


    /**
     * 用户登陆
     */
    public function login() {
        $mcrypt   = new MCrypt;
        $acid     = $mcrypt->decrypt(urldecode(I('post.tel'))); //手机号码
        $password = $mcrypt->decrypt(urldecode(I('post.password'))); //登陆密码

        if(empty($acid)) {
            apiReturn('1005', AJAX_FALSE, '手机号不能为空');
        }

        $where = array('mobile' => $acid);
        $model = D('AccountRelation');
        $data
               = $model->relation(true)->where($where)->field('id,mobile,passwd,token,type,province,tag_citys,majors,law,is_hide')->find();
        if(empty($data['mobile'])) {
            apiReturn('1006', AJAX_FALSE, '您还没有未注册，请注册后登录');
        }
        if($password == $data['passwd']) {
            $user = array(
                'token'    => $data['token'], //用户唯一标识
                'role'     => $data['type'], //用户身份标识
                'uid'      => $data['id'], //用户id
                'province' => $data['province'], //所在省（直辖市）
                'citys'    => $data['tag_citys'],  //城市标签
                'majors'   => $data['majors'], //专业标签
                'is_hide'  => $data['is_hide'], //是否隐藏
                'law'      => $data['law'],    //律所id
                'law_info' => $data['law_info'] //律所信息
            );
            apiReturn('1010', AJAX_TRUE, $user);
        } else {
            apiReturn('1011', AJAX_FALSE, '密码不正确，请重试或找回密码');
        }
    }


    /**
     * 新用户邀请注册
     */
    public function register_by_invite() {
        $mobile     = trim(I('post.mobile'));
        $code       = trim(I('post.code'));   //短信验证码
        $refer_id   = I('post.refer_id');     //分享者用户id
        $refer_code = I('post.refer_code');     //用户邀请标识

        if(empty($refer_id) || !is_numeric($refer_id)) {
            apiResponse(500, AJAX_FALSE, '此活动链接已失效,请重新获取');
        }

        if(empty($mobile) || empty($code)) {
            apiResponse(500, AJAX_FALSE, '请输入手机号');
        }

        if(!is_numeric($mobile)) {
            apiResponse(500, AJAX_FALSE, '手机号码有误，请重填');
        }

        if(!preg_match('/^1[34578]\d{9}$/', $mobile)) {
            apiResponse(500, AJAX_FALSE, '手机号码有误，请重填');
        }

        if(!$this->verifyValidateCode($mobile, $code)) {
            apiResponse(500, AJAX_FALSE, '验证码验证失败');
        }

        if((M('Invite')->where('mobile=' . $mobile)->count()) > 0) {
            apiResponse(500, AJAX_FALSE, '手机号已经注册过，请直接下载APP登录');
        }

        cookie('refer_code', $refer_code);

        if(M('Invite')->add([
                                'mobile'      => $mobile,
                                'refer_id'    => $refer_id,
                                'refer_code'  => $refer_code,
                                'create_time' => $_SERVER["REQUEST_TIME"]
                            ])
        ) {

            apiResponse(200, AJAX_TRUE, '参加活动成功');
        } else {
            apiResponse(500, AJAX_FALSE, '注册失败,请重试');
        }
    }


    //忘记密码
    public function resetPwd() {
        $AesMcrypt = new MCrypt; //调用AES加密接口
        $yunxin    = new ChatApi;  //调用网易云信接口
        $accid     = $AesMcrypt->decrypt(urldecode(I('post.tel'))); //获取手机号码
        $pwd       = $AesMcrypt->decrypt(urldecode(I('post.password'))); //获取新密码
        $code      = $AesMcrypt->decrypt(urldecode(I('post.code')));    //短信验证码
        if($accid == '13699440971') {
            apiReturn('1015', AJAX_FALSE, '小秘书账号不允许修改密码，请联系技术支持人员');
        }
        $res = $yunxin->verifycode($accid, $code); //实例化校验短信验证码
        if($res['code'] == 200) {
            //如果用户已经存在云信，更新用户token并更新本地数据
            $toke = $yunxin->updateUserToken($accid);
            if($toke['code'] == 200) {
                $data = array(
                    'mobile' => $toke['info']['accid'],
                    'token'  => $toke['info']['token'],
                    'passwd' => $pwd,
                );
                if(M('Account')->where('mobile=' . $accid)->save($data)) {
                    apiReturn('1016', AJAX_TRUE);
                } else {
                    apiReturn('1015', AJAX_FALSE, '修改密码失败,请联系客服');
                }
            } else {
                apiReturn('1011', AJAX_FALSE);   //更新token失败
            }
        } else {
            apiReturn('1003', AJAX_FALSE); //校验失败
        }
    }

}