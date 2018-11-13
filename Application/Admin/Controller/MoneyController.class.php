<?php
//+----------------------------------------------------------------------
// | Copyright (c) 2015-2017 http://lawyerscloud.cn All rights reserved.
//+----------------------------------------------------------------------
// | Author: 慢悠悠的丑小鸭 <1597305236@qq.com>
//+----------------------------------------------------------------------

namespace Admin\Controller;

use Think\Controller;
use Common\Api\JPush;

class MoneyController extends AdminController
{

    //红包设置管理首页
    public function setting() {
        $db            = M('Bonus_conf');
        $map['status'] = array('ELT', 1);
        $res           = $db->where($map)->select();
        $this->data    = json_encode($res);
        $this->assign('res', $res);
        $this->meta_title = '红包配置项管理';
        $this->display();
    }


    //新建红包设置
    public function add_red() {
        if(IS_POST) {
            $data = array(
                'type'         => I('post.type'),
                'operator'     => I('post.operator'),
                'total_amount' => I('post.total_amount'),
                'like_nums'    => I('post.like_nums'),
                'start_time'   => strtotime(I('post.start_time')),
                'end_time'     => strtotime(I('post.end_time')),
                'commission'   => I('post.commission'),
                'remark'       => I('post.remark'),
                'ip'           => get_client_ip(),
                'create_time'  => $_SERVER['REQUEST_TIME'],
                'status'       => 1
            );

            if(M('Bonus_conf')->add($data)) {
                $this->redirect('setting');
            } else {
                $this->error('新增失败');
            }
        }
        $this->meta_title = '新建红包设置';
        $this->display();
    }


    //删除红包设置
    public function del_conf() {
        $ids = I('get.conf_id');
        $db  = M('Bonus_conf');
        if($db->where('conf_id=' . $ids)->setField('status', 2)) {
            $this->redirect('setting');
        }
    }


    // 取消该配置,暂停使用
    public function suspendred() {
        $ids    = I('get.conf_id');
        $db     = M('Bonus_conf');
        $status = $db->where('conf_id=' . $ids)->getField('status');
        // 开启和暂停改红包设置
        if($status['status'] == 0) {
            $db->where('conf_id=' . $ids)->setField('status', '1');
            $this->redirect('setting');
        } else {
            $db->where('conf_id=' . $ids)->setField('status', '0');
            $this->redirect('setting');
        }
    }


    //编辑红包配置项
    public function edit_red() {
        $this->meta_title = '红包配置项配置';
        $db               = M('Bonus_conf');
        $ids              = I('get.conf_id');
        $infos            = $db->where('conf_id=' . $ids)->find();
        $this->assign('infos', $infos);
        $this->display();
    }


    //提交配置项
    public function subeditRed() {
        $db   = M('Bonus_conf');
        $ids  = I('get.conf_id');
        $data = array(
            'type'         => I('post.type'),
            'operator'     => I('post.operator'),
            'total_amount' => I('post.total_amount'),
            'like_nums'    => I('post.like_nums'),
            'start_time'   => strtotime(I('post.start_time')),
            'end_time'     => strtotime(I('post.end_time')),
            'commission'   => I('post.commission'),
            'remark'       => I('post.remark'),
            'ip'           => get_client_ip(),
            'create_time'  => $_SERVER['REQUEST_TIME']
        );

        // 提交更新
        if($db->where('conf_id=' . $ids)->save($data)) {
            $this->redirect('setting');
        } else {
            $this->error('修改失败');
        }
    }


    // 给指定回复用户发红包
    public function postMoney() {
        $uid       = I('post.uid');
        $type      = I('post.type');    //红包类型,1资讯,2求助
        $amount    = I('post.amount');
        $commentId = I('post.com_id');
        $articleId = I('post.res_id');

        if(empty($uid) || empty($type) || empty($articleId) || empty($commentId)) {
            apiResponse('400', false, '参数错误');
        }
        if(empty($amount)) {
            apiResponse('400', false, '请正确填写金额');
        }

        $model = M('Resource_comment');
        $comment = $model->where('id=' . $commentId)->field('post_amount,content')->find();
        //该回复已经得到过红包
        if(!empty($comment['post_amount'])) {
            apiResponse('400', false, '该回答已经得到过红包,不能重复打赏');
        }
        //更新回答的打赏标记
        $model->where('id=' . $commentId)->setField('post_amount', $amount);
        $data = array(
            'res_id'       => $articleId,
            'amount'       => $amount,
            'payment_time' => time(),
            'pay_user'     => $_SESSION['username'],
            'get_user'     => $uid,
            'type'         => $type,
            'commsision'   => '',
            'remark'       => '官方指定回复者红包',
            'money_type'   => 1.2  //官方奖励
        );
        //增加交易记录
        M('bonus_log')->add($data);

        // 余额更新
        D('App/Wallet')->updateWalletMoney($uid, $amount, 1);

        //增加钱包变动日志
        $recordSn    = A('App/Order')->setOrderSn();
        $walletMoney = D('App/Wallet')->getUserWallet($uid);
        D('App/WalletLog')->addWalletRecord($recordSn, $uid, $amount, $walletMoney['money'], '', $_SERVER["REQUEST_TIME"], 1);
        //插入收入日志记录
        D('harvest')->addHarvestLog($uid, $amount, 1, 3, $articleId, 1);
        //插入交易流水记录
        D('RecordLog')->addRecordLog($recordSn, $uid, $amount, 1, $articleId, 5, 4, 1, $_SERVER["REQUEST_TIME"]);

        // 消息推送
        $user_data = M('Account')->where('id=' . $uid)->field('mobile,uname')->find();//用户昵称,手机号
        $n_content = '恭喜您获得' . $amount . '元官方奖励红包!红包来自回复:' . substr($comment['content'], 0, 30);
        $msg_data  = array(
            'title'     => '红包消息',
            'content'   => $n_content,
            'push_obj'  => $user_data['uname'] . '(' . $user_data['mobile'] . ')',
            'type'      => 5,
            'send_time' => time()
        );

        $sys_messages = M('Sys_messages');
        $alias        = array(
            'alias' => explode(',', $user_data['mobile'])
        );

        $m_txt = array('type' => 0,);   //0系统消息 1回答我 2求助我 3评论我 4响应我 5红包 6提现

        $Jpush = new Jpush(C('JPUSH_APPKEY'), C('JPUSH_SECRET'));
        $res   = $Jpush->push('all', $alias, $n_content, $m_type = 'http', $m_txt);

        $mid          = $sys_messages->add($msg_data);
        $message_data = array(
            'msgid' => $mid,
            'uid'   => $uid
        );

        if($res) {
            $res_arr = json_decode($res, true);
            if(isset($res_arr['error'])) {
                // 如果返回了error则删除该条消息
                $sys_messages->where('id=' . $mid)->delete();
            } else {
                // 插入未读已读
                M('Message')->add($message_data);
            }
        }

        apiResponse('200', true);
    }


    // 给指定求助者发红包
    public function postAuthorMoney() {
        $author    = I('post.uid');    //求助者用户id
        $type      = I('post.type');    //红包类型,1资讯,2求助
        $amount    = I('post.post_amount');   //求助者指定红包金额
        $articleId = I('post.id');     //帖子id

        if(empty($author) || empty($type) || empty($articleId)) {
            apiResponse('400', false, '参数错误');
        }
        if(empty($amount)) {
            apiResponse('400', false, '请正确填写金额');
        }

        //该回复是否已经得到过红包
        $article = M('Resource')->where('id=' . $articleId)->field('post_amount')->find();
        if(!empty($article['post_amount'])) {
            apiResponse('400', false, '该提问作者已经得到过红包,不能重复打赏');
        }

        //资讯类型红包需要在前端列表显示,需要给is_money参数标注,指定求助红包则不需要
        $type ? $isMoney = 1 : $isMoney = 0;
        $data['is_money']    = $isMoney; //帖子是否标注红包贴
        $data['post_amount'] = $amount; //红包金额

        //修改帖子红包标识
        $res = M('Resource')->where('id=' . $articleId)->save($data);

        if(!$res) {
            apiResponse('401', false, '打赏失败');
        }
        //用户余额更新
        $orderSn = createOrderSn();
        $res     = D('App/Wallet')->updateWalletMoney($author, $amount, 1);
        if(!$res) {
            apiResponse('402', false, '打赏失败');
        }
        //增加钱包变动日志
        $walletMoney = D('App/Wallet')->getUserWallet($author);
        $res
                     = D('App/WalletLog')->addWalletRecord($orderSn, $author, $amount, $walletMoney['money'], '', $_SERVER["REQUEST_TIME"], 1);
        if(!$res) {
            apiResponse('403', false, '打赏失败');
        }
        //插入收入日志记录
        $res = D('harvest')->addHarvestLog($author, $amount, 1, 3, $articleId, 1);
        if(!$res) {
            apiResponse('404', false, '打赏失败');
        }
        //插入交易流水记录
        $res
            = D('RecordLog')->addRecordLog($orderSn, $author, $amount, 1, $articleId, 5, 4, 1, $_SERVER["REQUEST_TIME"]);
        if(!$res) {
            apiResponse('405', false, '打赏失败');
        }
        //红包消息推送给用户
        $this->sendMessage($articleId, $author, $amount);

        apiResponse('200', true);
    }


    //增加红包记录
    public function redpacketLog($id, $post_amount, $author, $type) {
        $data = array(
            'res_id'       => $id,              //帖子id
            'amount'       => $post_amount,     //红包金额
            'get_user'     => $author,          //作者
            'type'         => $type,            //红包类型,1资讯,2求助
            'payment_time' => $_SERVER['REQUEST_TIME'],
            'remark'       => '官方指定红包',
            'money_type'   => 1.2,  //官方奖励
            'pay_user'     => $_SESSION['username'],
            'commsision'   => '',
        );

        D('Bonus_log')->add($data);
    }


    //红包消息推送给用户
    public function sendMessage($id, $author, $post_amount) {
        $user_data = M('Account')->where('id=' . $author)->field('mobile,uname')->find();
        $posts     = M('Resource')->where('id=' . $id)->field('post_amount,title')->find();

        $alias     = array('alias' => explode(',', $user_data['mobile']));
        $n_content = '恭喜您得到一个' . $post_amount . '元官方奖励红包,来自:' . substr($posts['title'], 0, 30);
        $m_txt     = array('type' => 5);   //0系统消息 1回答我 2求助我 3评论我 4响应我 5红包 6提现

        //系统消息列表
        $msg_data = array(
            'title'     => '红包消息',
            'content'   => $n_content,
            'push_obj'  => $user_data['uname'] . '(' . $user_data['mobile'] . ')',
            'type'      => 5,
            'send_time' => $_SERVER['REQUEST_TIME']
        );

        $sys_messages = M('Sys_messages');
        if($mid = $sys_messages->add($msg_data)) {
            $Jpush = new Jpush(C('JPUSH_APPKEY'), C('JPUSH_SECRET'));
            $res   = $Jpush->push('all', $alias, $n_content, $m_type = 'http', $m_txt);
        }

        if($res) {
            $res_arr = json_decode($res, true);
            if(isset($res_arr['error'])) {
                // 如果返回了error则删除该条消息
                $sys_messages->where('id=' . $mid)->delete();
            } else {
                //未读消息列表,插入未读已读
                $message_data = array('msgid' => $mid, 'uid' => $author);
                M('Message')->add($message_data);
            }
        }
    }


    //全部交易记录
    public function recordLog() {
        $model = D('RecordLogRelation');
        if(!empty(I('mobile'))) {
            $where['user_id'] = M('Account')->where('mobile=' . trim(I('mobile')))->getField('id');
        }
        $where['pay_status'] = 1;
        $count               = $model->where($where)->count(); //查询总记录数
        $Page                = new \Think\Page($count, 12);// 实例化分页类 传入总记录数和每页显示的记录数(25)
        $Page->setConfig('header', '共%TOTAL_ROW%条');
        $Page->setConfig('first', '首页');
        $Page->setConfig('last', '共%TOTAL_PAGE%页');
        $Page->setConfig('prev', '上一页');
        $Page->setConfig('next', '下一页');
        $Page->setConfig('link', 'indexpagenumb');//pagenumb 会替换成页码
        $Page->setConfig('theme', '%HEADER% %FIRST% %UP_PAGE% %LINK_PAGE% %DOWN_PAGE% %END%');
        $show  = $Page->show();
        $data  = $model
            ->relation(true)
            ->where($where)
            ->field('record_sn,user_id, money,trade_type,trade_rource,type,pay_type,pay_status,pay_time')
            ->order('pay_time desc')
            ->limit($Page->firstRow . ',' . $Page->listRows)
            ->select();
        $lists = array();
        foreach($data as $k => $v) {
            foreach($v as $m => $n) {
                $lists[ $k ]['record_sn']   = $v['record_sn'];
                $lists[ $k ]['user_id']     = $v['user_id'];
                $lists[ $k ]['user_name']   = $v['user']['uname'];
                $lists[ $k ]['money']       = $v['money'];
                $lists[ $k ]['trade_type']  = $v['trade_type'];
                $lists[ $k ]['type']        = $v['type'];
                $lists[ $k ]['pay_type']    = $v['pay_type'];
                $lists[ $k ]['pay_time']    = $v['pay_time'];
                $lists[ $k ]['trade_title'] = $v['resources']['title'];
            }
        }
        $this->assign('data', $lists);
        $this->assign('page', $show);
        $this->meta_title = '交易记录';
        $this->display();
    }


    //求助红包记录
    public function redpacket() {
        $model = D('RecordLogRelation');
        if(!empty(I('mobile'))) {
            $where['get_user'] = M('Account')->where('mobile=' . trim(I('mobile')))->getField('id');
        }

        $where['type']    = array('gt', 5);
        $where['user_id'] = array('neq', 0);
        //$where['trade_type'] = array('eq',1);
        $count = $model->where($where)->count(); //查询总记录数
        $Page  = new \Think\Page($count, 15);// 实例化分页类 传入总记录数和每页显示的记录数(25)
        $Page->setConfig('header', '共%TOTAL_ROW%条');
        $Page->setConfig('first', '首页');
        $Page->setConfig('last', '共%TOTAL_PAGE%页');
        $Page->setConfig('prev', '上一页');
        $Page->setConfig('next', '下一页');
        $Page->setConfig('link', 'indexpagenumb');//pagenumb 会替换成页码
        $Page->setConfig('theme', '%HEADER% %FIRST% %UP_PAGE% %LINK_PAGE% %DOWN_PAGE% %END%');
        $show  = $Page->show();
        $data  = $model
            ->relation(true)
            ->where($where)
            ->order('pay_time desc')
            ->limit($Page->firstRow . ',' . $Page->listRows)
            ->select();
        $lists = array();
        foreach($data as $k => $v) {
            foreach($v as $m => $n) {
                $lists[ $k ]['record_sn']   = $v['record_sn'];
                $lists[ $k ]['user_id']     = $v['user_id'];
                $lists[ $k ]['user_name']   = $v['user']['uname'];
                $lists[ $k ]['money']       = $v['money'];
                $lists[ $k ]['trade_type']  = $v['trade_type'];
                $lists[ $k ]['type']        = $v['type'];
                $lists[ $k ]['pay_type']    = $v['pay_type'];
                $lists[ $k ]['pay_time']    = $v['pay_time'];
                $lists[ $k ]['trade_title'] = $v['resources']['title'];
            }
        }

        $this->assign('data', $lists);
        $this->assign('page', $show);
        $this->meta_title = '求助红包记录';
        $this->display();
    }


    // 提现流水记录
    public function withdraw() {
        $model = M('withdrawal_log');
        $count = $model->count(); //查询总记录数
        $Page  = new \Think\Page($count, 12);// 实例化分页类 传入总记录数和每页显示的记录数(25)
        $Page->setConfig('header', '共%TOTAL_ROW%条');
        $Page->setConfig('first', '首页');
        $Page->setConfig('last', '共%TOTAL_PAGE%页');
        $Page->setConfig('prev', '上一页');
        $Page->setConfig('next', '下一页');
        $Page->setConfig('link', 'indexpagenumb');//pagenumb 会替换成页码
        $Page->setConfig('theme', '%HEADER% %FIRST% %UP_PAGE% %LINK_PAGE% %DOWN_PAGE% %END%');
        $show  = $Page->show();
        $lists = $model
            ->order('create_date desc')
            ->limit($Page->firstRow . ',' . $Page->listRows)
            ->select();
        $this->assign('data', $lists);
        $this->assign('page', $show);
        $this->meta_title = '提现管理';
        $this->display();
    }


    // 提现申请不通过
    public function provent() {
        $withdrawalId = I('get.withdrawal_id');
        $userId       = I('post.user_id');
        $remark       = trim(I('post.remark'));

        $model = M('withdrawal_log');
        $withdrawalLogInfo
               = $model->where(array('id' => $withdrawalId))->field('id,uid,user_name,withdraw_account,total_money,status')->find();
        if(empty(IS_POST)) {
            $this->assign('info', $withdrawalLogInfo);
            $this->meta_title = '提现审核意见';
            $this->display();
            exit;
        }

        //判断是否已经审核过
        if($withdrawalLogInfo['status']) {
            $this->error('已经审核过,不能重复审核!');
            $this->redirect('withdraw');
            exit;
        }

        //修改不通过标识
        $data['status'] = 2;
        $data['remark'] = $remark;
        $res            = $model->where(['id' => $withdrawalId])->save($data);
        if(!$res) {
            $this->error('审核失败!');
            exit;
        }

        //提现资金返回到用户钱包
        $walletMoney = D('Wallet')->getUserWallet($userId,'money'); //返回数组格式
        D('Wallet')->updateWalletMoney($userId, $withdrawalLogInfo['total_money'], 1);
        $recordSn    = createOrderSn();
        $changedTotalMoney = $walletMoney['money'] + $withdrawalLogInfo['total_money'];    //资金变动后的用户总金额
        D('WalletLog')->addWalletRecord($recordSn, $userId, $withdrawalLogInfo['total_money'], $changedTotalMoney, '', $_SERVER["REQUEST_TIME"], 1);
        //插入收入记录日志
        D('Harvest')->addHarvestLog($userId, $withdrawalLogInfo['total_money'], 1, 1, '', 1);
        //插入交易流水记录
        D('RecordLog')->addRecordLog($recordSn, $userId, $withdrawalLogInfo['total_money'], 1, 0, 2, 0, 1, $_SERVER["REQUEST_TIME"]);

        //消息推送
        $userData       = M('Account')->where('id=' . $userId)->field('mobile,uname')->find();
        $alias['alias'] = explode(',', $userData['mobile']);
        $n_content      = '你的提现申请不通过,提现金额退回到你的钱包余额。不通过原因:' . $remark;
        $m_txt['type']  = 6;   //0系统消息 1回答我 2求助我 3评论我 4响应我 5红包 6提现
        send_message_push('all', $alias, $n_content, 'http', $m_txt);

        //系统消息列表
        $msgData = array(
            'title'     => '提现申请消息',
            'content'   => $n_content,
            'push_obj'  => $userData['mobile'],
            'type'      => 6,
            'send_time' => $_SERVER['REQUEST_TIME']
        );
        if($msgId = M('Sys_messages')->add($msgData)) {

            //插入未读已读未读消息列表
            $messageData = array(
                'msgid' => $msgId,
                'uid'   => $userId
            );
            M('Message')->add($messageData);
        }

        $this->redirect('withdraw');
    }


    //确认提现
    public function transfer() {
        $withdrawalId = I('get.withdrawal_id');

        $model    = M('withdrawal_log');
        $withdrawalLogInfo
                  = $model->where(array('id' => $withdrawalId))->field('id,uid,user_name,withdraw_account,total_money,money,status')->find();
        $userData = M('Account')->where('id=' . $withdrawalLogInfo['uid'])->field('mobile,uname')->find();

        if($withdrawalLogInfo['status']) {
            $this->error('不要重复操作确认转款');
        }
        // 修改状态
        $status = $model->where(array('id' => $withdrawalId))->setField('status', 1);
        if($status) {
            $alias['alias'] = explode(',', $userData['mobile']);
            $n_content      = '你提现' . $withdrawalLogInfo['money'] . '元现金已打入你的支付宝账户,待支付宝处理。';
            $m_txt['type']  = 6;   //0系统消息 1回答我 2求助我 3评论我 4响应我 5红包 6提现

            //系统消息列表
            $msgData = array(
                'title'     => '提现申请消息',
                'content'   => $n_content,
                'push_obj'  => $userData['mobile'],
                'type'      => 6,
                'send_time' => $_SERVER['REQUEST_TIME']
            );

            $sysMessages = M('Sys_messages');
            if($mid = $sysMessages->add($msgData)) {
                send_message_push('all', $alias, $n_content, 'http', $m_txt);
                // 插入未读已读消息列表
                $messageData = array(
                    'msgid' => $mid,
                    'uid'   => $withdrawalLogInfo['uid']
                );
                M('Message')->add($messageData);

                $this->redirect('withdraw');
            }
        } else {
            $this->error('操作失败');
        }
    }


    //提现申请记录筛选搜索
    public function draw_search() {
        $this->meta_title = '提现申请筛选搜索';
        $this->display();
    }


    //导出报表提交方法
    public function subexport() {
        $data
            = M('withdrawal_log')->where('status=1')->field('id,record_sn,user_name,withdraw_account,money,process,create_date')->select();
        $this->excel_export($data);
    }


    //导出数据方法
    protected function excel_export($data_list = array()) {
        $data = array();
        foreach($data_list as $k => $data_info) {
            $data[ $k ]['record_sn']        = "'" . $data_info['record_sn'];
            $data[ $k ]['user_name']        = $data_info['user_name'];
            $data[ $k ]['withdraw_account'] = $data_info['withdraw_account'];
            $data[ $k ]['money']            = $data_info['money'];
            $data[ $k ]['process']          = $data_info['process'];
            $data[ $k ]['create_date']      = $data_info['create_date'];
        }

        foreach($data as $field => $v) {
            if($field == 'record_sn') {
                $headArr[] = '流水号';
            }

            if($field == 'withdraw_account') {
                $headArr[] = '提现账号';
            }

            if($field == 'user_name') {
                $headArr[] = '姓名';
            }

            if($field == 'money') {
                $headArr[] = '提现金额（元）';
            }
            if($field == 'process') {
                $headArr[] = '手续费（元）';
            }

            if($field == 'create_date') {
                $headArr[] = '提现时间';
            }
        }
        $filename = "提现流水记录";
        $this->getExcel($filename, $headArr, $data);
    }


    private function getExcel($fileName, $headArr, $data) {
        //导入PHPExcel类库，因为PHPExcel没有用命名空间，只能inport导入
        import("Org.Util.PHPExcel");
        import("Org.Util.PHPExcel.Writer.Excel5");
        import("Org.Util.PHPExcel.IOFactory.php");

        $date = date("Ymd", $_SERVER['REQUEST_TIME']);
        $fileName .= "_{$date}.xls";

        //创建PHPExcel对象，注意，不能少了\
        $objPHPExcel = new \PHPExcel();
        //设置表头
        $key = ord("A");
        foreach($headArr as $v) {
            $colum = chr($key);
            $objPHPExcel->setActiveSheetIndex(0)->setCellValue($colum . '1', $v);
            $objPHPExcel->setActiveSheetIndex(0)->setCellValue($colum . '1', $v);
            $key += 1;
        }

        $objActSheet = $objPHPExcel->getActiveSheet();
        $objPHPExcel->getActiveSheet()->insertNewRowBefore(4, 2);

        //将文字写到单元格中
        $objPHPExcel->getActiveSheet()->getCell('A1')->setValue('流水号');
        $objPHPExcel->getActiveSheet()->getCell('B1')->setValue('姓名');
        $objPHPExcel->getActiveSheet()->getCell('C1')->setValue('提现账号');
        $objPHPExcel->getActiveSheet()->getCell('D1')->setValue('提现金额（元）');
        $objPHPExcel->getActiveSheet()->getCell('E1')->setValue('手续费（元）');
        $objPHPExcel->getActiveSheet()->getCell('F1')->setValue('提现时间');

        //设置列的宽度
        $objActSheet->getColumnDimension('A')->setWidth(30);
        $objActSheet->getColumnDimension('B')->setWidth(15);
        $objActSheet->getColumnDimension('C')->setWidth(30);
        $objActSheet->getColumnDimension('D')->setWidth(15);
        $objActSheet->getColumnDimension('E')->setWidth(15);
        $objActSheet->getColumnDimension('F')->setWidth(30);

        //文字对齐方式  锚：bbb
        $objPHPExcel->getActiveSheet()->getStyle('C')->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_LEFT);    //水平方向上对齐
        $objPHPExcel->getActiveSheet()->getStyle('F')->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_RIGHT);    //水平方向上对齐

        $column = 2;        //从第几行开始
        foreach($data as $key => $rows) { //行写入
            $rows['create_date'] = date('Y-m-d H:i:s', $rows['create_date']);
            $span                = ord("A");
            foreach($rows as $keyName => $value) {   // 列写入
                $j = chr($span);
                $objActSheet->setCellValue($j . $column, $value);
                $span ++;
            }
            $column ++;
        }

        // 文件编码
        $fileName = iconv("utf-8", "gb2312", $fileName);
        //设置活动单指数到第一个表,所以Excel打开这是第一个表
        $objPHPExcel->setActiveSheetIndex(0);
        ob_end_clean();     //清除缓冲区,避免乱码
        header('Content-Type: application/vnd.ms-excel');
        header("Content-Disposition: attachment;filename=\"$fileName\"");
        header('Cache-Control: max-age=0');

        $objWriter = \PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
        $objWriter->save('php://output'); //文件通过浏览器下载
        exit();
    }


    //导出红包记录报表
    public function exportRedpack() {
        $data = D('RedpacketsRelation')->relation(true)->where('money_type < 2')
                                       ->field('rd_id,res_id,get_user,amount,payment_time,remark,money_type')->select();
        $this->excelExportRedpack($data);
    }


    //导出红包记录数据方法
    protected function excelExportRedpack($data_list = array()) {
        $data = array();
        foreach($data_list as $k => $data_info) {
            $data[ $k ]['rd_id']        = $data_info['rd_id'];
            $data[ $k ]['get_username'] = $data_info['userinfo']['uname'];
            $data[ $k ]['get_account']  = $data_info['userinfo']['mobile'];
            $data[ $k ]['amount']       = $data_info['amount'];
            $data[ $k ]['title']        = $data_info['resources']['title'];
            $data[ $k ]['payment_time'] = $data_info['payment_time'];
        }

        foreach($data as $field => $v) {
            if($field == 'rd_id') {
                $headArr[] = '流水号';
            }
            if($field == 'get_username') {
                $headArr[] = '姓名';
            }

            if($field == 'get_account') {
                $headArr[] = '手机号';
            }

            if($field == 'amount') {
                $headArr[] = '红包金额（元）';
            }
            if($field == 'title') {
                $headArr[] = '红包来源';
            }

            if($field == 'payment_time') {
                $headArr[] = '时间';
            }
        }
        $filename = "红包记录";
        $this->getExcelRedpack($filename, $headArr, $data);
    }


    //红包记录
    private function getExcelRedpack($fileName, $headArr, $data) {
        //导入PHPExcel类库，因为PHPExcel没有用命名空间，只能inport导入
        import("Org.Util.PHPExcel");
        import("Org.Util.PHPExcel.Writer.Excel5");
        import("Org.Util.PHPExcel.IOFactory.php");

        $date = date("Ymd", $_SERVER['REQUEST_TIME']);
        $fileName .= "_{$date}.xls";

        //创建PHPExcel对象，注意，不能少了\
        $objPHPExcel = new \PHPExcel();
        //设置表头
        $key = ord("A");
        foreach($headArr as $v) {
            $colum = chr($key);
            $objPHPExcel->setActiveSheetIndex(0)->setCellValue($colum . '1', $v);
            $objPHPExcel->setActiveSheetIndex(0)->setCellValue($colum . '1', $v);
            $key += 1;
        }

        $objActSheet = $objPHPExcel->getActiveSheet();
        $objPHPExcel->getActiveSheet()->insertNewRowBefore(4, 2);

        //将文字写到单元格中
        $objPHPExcel->getActiveSheet()->getCell('A1')->setValue('流水号');
        $objPHPExcel->getActiveSheet()->getCell('B1')->setValue('姓名');
        $objPHPExcel->getActiveSheet()->getCell('C1')->setValue('手机号');
        $objPHPExcel->getActiveSheet()->getCell('D1')->setValue('红包金额（元）');
        $objPHPExcel->getActiveSheet()->getCell('E1')->setValue('红包来源');
        $objPHPExcel->getActiveSheet()->getCell('F1')->setValue('时间');

        //设置列的宽度
        $objActSheet->getColumnDimension('B')->setWidth(15);
        $objActSheet->getColumnDimension('C')->setWidth(30);
        $objActSheet->getColumnDimension('D')->setWidth(15);
        $objActSheet->getColumnDimension('E')->setWidth(35);
        $objActSheet->getColumnDimension('F')->setWidth(30);

        //文字对齐方式  锚：bbb
        $objPHPExcel->getActiveSheet()->getStyle('C')->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_LEFT);    //水平方向上对齐
        $objPHPExcel->getActiveSheet()->getStyle('D')->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_LEFT);    //水平方向上对齐
        $objPHPExcel->getActiveSheet()->getStyle('E')->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_LEFT);    //水平方向上对齐

        $column = 2;        //从第几行开始
        foreach($data as $key => $rows) { //行写入
            $rows['payment_time'] = date('Y-m-d H:i:s', $rows['payment_time']);
            $span                 = ord("A");
            foreach($rows as $keyName => $value) {   // 列写入
                $j = chr($span);
                $objActSheet->setCellValue($j . $column, $value);
                $span ++;
            }
            $column ++;
        }

        // 文件编码
        $fileName = iconv("utf-8", "gb2312", $fileName);
        //设置活动单指数到第一个表,所以Excel打开这是第一个表
        $objPHPExcel->setActiveSheetIndex(0);
        ob_end_clean();     //清除缓冲区,避免乱码
        header('Content-Type: application/vnd.ms-excel');
        header("Content-Disposition: attachment;filename=\"$fileName\"");
        header('Cache-Control: max-age=0');

        $objWriter = \PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
        $objWriter->save('php://output'); //文件通过浏览器下载
        exit();
    }


    //最低提现额度
    public function minimum() {
        $minimum = I('post.minimum');

        if(!empty($minimum)) {

            $data['minimum'] = $minimum;
            file_put_contents('bonus_minimum.json', json_encode($data));

            $this->redirect('minimum');

        } else {
            $minimum = file_get_contents('bonus_minimum.json');
            $minimum = json_decode($minimum, true)['minimum'];
            $this->assign('minimum', $minimum);
            $this->meta_title = '最低提现额度';
            $this->display();
        }
    }

}