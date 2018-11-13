<?php
/**
 * Created by PhpStorm.
 * User: Mrx
 * Date: 2016/3/11
 * Time: 14:30
 */

namespace Admin\Controller;
use Think\Controller;
use Org\Util\Rbac;

class LoginController extends Controller
{
    public function login ()
    {
        $this->meta_title = '后台登陆';
        $this->display();
    }

    // 退出登录
    public function lockscreen ()
    {
        $this->meta_title = '后台登入';
        $this->display();
    }

    // 提交登录
    public function sub_login ()
    {
        $db = M('User');
        $username = I('username');
        $user = $db->where(array('username' => $username))->find();

        $resp = array('accessGranted' => false, 'errors' => ''); // For ajax response

        if(isset($_POST['do_login']))
        {
            $given_username = I('username');
            $given_password = md5(I('passwd'));

            if($user['username'] == $given_username && $user['passwd'] == $given_password)
            {
                $data = array(
                    'id' => $user['id'],
                    'login_at' => time(),
                    'loginip' => get_client_ip(),
                );
                $db->save($data);
                // 写入session
                // session('uid', $user['id']);
                session(C('USER_AUTH_KEY'), $user['id']);
                session('username', $user['username'], 3600*12);
                if($user['username'] == C('RBAC_SUPERADMIN')){
                    session(C('ADMIN_AUTH_KEY'), true);
                }
                RBAC::saveAccessList();
                $resp['accessGranted'] = true;
                setcookie('failed-attempts', 0);
            } else {
                // 尝试失败
                $fa = isset($_COOKIE['failed-attempts']) ? $_COOKIE['failed-attempts'] : 0;
                $fa++;

                setcookie('failed-attempts', $fa);

                // 错误信息
                $resp['errors'] = '<strong>无效登陆!</strong><br />请输入有效的用户名和密码。<br />尝试失败: ' . $fa;
            }
        }

        echo json_encode($resp);
    }

    /* 退出登录 */
    public function logout ()
    {
        cookie('cook_id',session(C('USER_AUTH_KEY')));
        cookie('cook_name', session('username'));
        session_unset();
        session_destroy();
        if(!isset($_SESSION['uid'])){
            $this->redirect('Login/lockscreen');
        }else {
            $this->redirect('Login/login');
        }
    }

    /* 找回密码 */
    public function forgetPwd ()
    {
        $this->meta_title = '忘记密码';
        $this->display();
    }

    //验证邮箱是否已注册
    public function check_email()
    {
        $user = I('post.email');
        $sum = M('user')->where("email='$user'")->select();
        if($sum){
            $valid = true;
        }else {
            $valid = false;
        }
        echo json_encode(array('valid'=>$valid));
    }

    //发送验证邮件
    public function submit_email ()
    {
        $email_local = I('post.email');     //收件人
        $passtime = time();     //发送时间
        $model = M('User');
        $user = $model->where("email='$email_local'")->find();
        if(empty($user['token'])){
            $utoken = md5($user['id'].$user['username'].$user['passwd']);
            $model->where('id='.$user['id'])->setField('token',$utoken);
        }else {
            $utoken = $user['token'];
        }
        $u = lx_encrypt($user['id']);
        $code = lx_encrypt($utoken);
        $url = "http://lawwith.think/Admin/Login/find_password?u=$u&code=$code";
        $subject = '【律携】找回密码';
        $content = '<p style="padding-top:5px;padding-bottom:5px;margin:0;"><strong>Hey, '.$user["username"].'</strong></p>
<p style="padding-top:5px;padding-bottom:5px;margin:0;">您收到这封邮件，是由于这个邮箱地址在 律携 被登记为用户邮箱， 且该用户请求使用 Email 密码重置功能所致。</p>
<p style="padding-top:5px;padding-bottom:5px;margin:0;">密码重置说明：</p>
<p style="padding-top:5px;padding-bottom:5px;margin:0;">您只需在提交请求后的24小时内，通过点击下面的链接重置您的密码：</p>
<p style="padding-top:5px;padding-bottom:5px;margin:0;"><a href='.$url.'>'.$url.'</a></p>
<p style="padding-top:5px;padding-bottom:5px;margin:0;">(如果上面不是链接形式，请将该地址手工粘贴到浏览器地址栏再访问)</p>
<p style="padding-top:5px;padding-bottom:5px;margin:0;">在上面链接所打开的页面中输入新的密码后提交，您即可使用新的密码登录网站了。</p>';
        $res = sendMail($email_local,$subject,$content);
        if($res){
            $model->where('id='.$user['id'])->setField('exptime',$passtime);
            $this->assign('email_name',$email_local);
            $this->display('submit_email');
        }else {
            $this->error("发送失败");
        }
    }


    //重置密码
    public function find_password ()
    {
        $uid = trim(lx_decrypt(I('get.u')));
        $token = trim(lx_decrypt(I('get.code')));
        $udata = M('User')->where('id='.$uid)->find();
        if($udata['token'] == $token){
            if(time()-$udata['exptime']>24*60*60){
                $this->error('该链接已过期');
            }else {
                $this->assign('email_name',$udata['email']);
                $this->assign('uid',$udata['id']);
                $this->display();
            }
        }else {
            $this->error('无效的链接');
        }
    }

    //提交重置密码
    public function sub_reset ()
    {
        $uid = I('post.uid');
        $pwd = I('post.repassword');
        if(M('User')->where('id='.$uid)->setField('passwd',md5($pwd))){
            $this->success('密码重置成功',U('Login/login'));
        }else {
            $this->error('密码重置失败');
        }
    }
}