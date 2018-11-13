<?php
/**
 * Created by PhpStorm.
 * User: Mrx
 * Date: 2016/3/18
 * Time: 17:25
 */

namespace Admin\Controller;
use Think\Controller;

class RbacController extends AdminController {

    public function index() {
        $this->user = D('UserRelation')->field('passwd', true)->relation(true)->select(); //大小写
        $this->meta_title = '用户列表';
        $this->display('userlist');
    }

    //角色列表
    public function role() {
        $this->role = M('role')->select();
        $this->meta_title = '角色列表';
        $this->display('rolelist');
    }

    //节点列表
    public function node() {
        $field = array('id','name','title','pid');
        $node = M('node')->field($field)->order('sort')->select();
        $this->node = node_merge($node);
        $this->meta_title = '节点列表';
        $this->display('nodelist');
    }

    //用户检查
    public function checkUser() {
        $user = I('post.username');
        $sum = M('user')->where("username='$user'")->select();
        if($sum){
            $valid = false;
        }else {
            $valid = true;
        }
        echo json_encode(array('valid'=>$valid));
    }

    //添加用户
    public function addUser() {
        $map['name'] = array('neq',C('ADMIN_AUTH_KEY'));
        $this->role = M('role')->where($map)->select();
        $this->meta_title = '添加用户';
        $this->display();
    }

    //修改用户信息
    public function editUser() {
        $uid = lx_decrypt(I('uid'));
        // $this->data = M('User')->where('id='.$uid)->find();
        $this->data = D('UserRelation')->where('id='.$uid)->field('passwd', true)->relation(true)->find();
        $map['name'] = array('neq',C('ADMIN_AUTH_KEY'));
        $this->role = M('role')->where($map)->select();
        $this->meta_title = '修改用户';
        $this->display();
    }
    //添加用户处理
    public function addUserHandle() {
        $user = array(
            'username' => $_POST['username'],
            'passwd' => md5($_POST['password']),
            'email' => $_POST['email'],
            'created_at' => time(),
            'loginip' => get_client_ip(),
        );
        if($uid = M('user')->add($user)){
            foreach($_POST['role_id'] as $v){
                $role[] = array(
                    'role_id' => $v,
                    'user_id' => $uid,
                );
            }
            M('role_user')->addAll($role);
            $this->redirect('index');
        } else {
            $this->error('添加失败');
        }
    }

    //删除用户
    public function delUser() {
        $uid = lx_decrypt(I('uid'));
        if(D('UserRelation')->where('id='.$uid)->delete()){
            $this->redirect('index');
        }else {
            $this->error('删除失败');
        }
    }

    //添加角色
    public function addRole() {
        $this->meta_title = '添加角色';
        $this->display();
    }

    //角色添加处理
    public function addRoleHandle() {
        $status = !empty($_POST['status'])?$_POST['status']:2;
        $data=array(
            'name' => I('post.roleName'),
            'remark' => I('post.roleRemark'),
            'status' => $status
        );
        if(M('role')->add($data)){
            $this->redirect('role');
        } else {
            $this->error('添加失败');
        }
    }

    //删除角色
    public function delRole() {
        $rid = lx_decrypt(I('rid'));
        if(M('Role')->where('id='.$rid)->delete()){
            $this->redirect('role');
        }else {
            $this->error('删除失败');
        }
    }

    //添加节点
    public function addNode() {
        $this->pid = I('pid', 0, 'intval');
        $this->level = I('level', 1, 'intval');
        switch($this->level){
            case 1:
                $this->type = "应用";
                break;
            case 2:
                $this->type = "控制器";
                break;
            case 3:
                $this->type = "方法";
                break;
        }

        $this->meta_title = '添加'.$this->type;
        $this->display();
    }

    //添加节点处理
    public function addNodeHandle() {
        $data = array(
            'name' => I('appName'),
            'title' => I('appRemark'),
            'status' => I('status'),
            'sort' => I('sort'),
            'pid' => I('pid'),
            'level' => I('level')
        );
        if(M('node')->add($data)){
            $this->redirect('Rbac/node');
        } else {
            $this->error('添加失败');
        }
    }


    //权限处理
    public function access() {
        $rid = I('rid', 0, 'intval');
        $field = array('id', 'name', 'title', 'pid');
        $node = M('node')->order('sort')->field($field)->select();

        //原有权限
        $access = M('access')->where(array('role_id' => $rid))->getField('node_id', true);
        $this->node = node_merge($node, $access);
        $this->rid = $rid;
        $this->meta_title = '配置权限';
        $this->display('addconfig');
    }

    public function setAccess() {
        $rid = I('rid', 0, 'intval');
        $db = M('access');

        //删除原来的权限
        $db->where(array('role_id' => $rid))->delete();

        $data = array();
        foreach ($_POST['access'] as $v) {
            $tmp = explode('_', $v);
            $data[] = array(
                'role_id' => $rid,
                'node_id' => $tmp[0],
                'level' => $tmp[1],
            );
        }

        if ($db->addAll($data)) {
            $this->redirect('role');
        } else {
            $this->error('添加失败');
        }
    }

    //节点删除
    public function delNode() {
        if(delList('node','id','pid',I("pid"))){
            $this->redirect('Rbac/node');
        }else{
            $this->error('删除失败');
        }
    }

}