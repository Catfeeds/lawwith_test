<?php
// +----------------------------------------------------------------------
// | 分页 [ Api接口开发专用 ]
// +----------------------------------------------------------------------
namespace Common\Api;

class ApiPage
{
    protected $config = array(
        'tablename' => 'Post', // 表名
        'where'     => '', // 查询条件
        'relation'  => '', // 关联条件
        'order' => 'id desc', // 排序
        'page' => 1,  // 页码，默认为首页
        'num'  => 5  // 每页条数
    );

    function __construct($config=array())
    {
        $config['tablename'] = ucfirst($config['tablename']);

        // 合并配置文件
        $this->config = array_merge($this->config, $config);
    }

    public function get()
    {
        // 实例化数据库
        $dbIns = D($this->config['tablename']);

        // 获取查询条件
        $map = array();
        if (!empty($this->config['where'])) {
            $map = $this->config['where'];
        }

        // 统计表中条数
        $count = $dbIns->where($map)->count();

        // 查询条件拼装
        $condition = $dbIns
            ->where($map)
            ->order($this->config['order'])
            ->field($this->config['field'])
            ->limit(($this->config['page']-1)*$this->config['num'], $this->config['num']);

        // 查询数据
        $relation = array();
        if (!empty($this->config['relation'])) {
            // 关联模型查询数据
            $relation = $this->config['relation'];
            $data = $condition->relation($relation)->select();
        } else {
            // 正常查询数据、视图模型查询数据
            $data = $condition->select();
        }


        // 返回当前页和总页数、总数据条数
        $data['now_page'] = intval($this->config['page']);
        $data['total_page'] = intval(ceil($count / $this->config['num']));
        $data['data_sums']  =   intval($count);
        // dump($data);die();
        return $data;
    }
}