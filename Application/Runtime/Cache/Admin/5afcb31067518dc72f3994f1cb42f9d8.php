<?php if (!defined('THINK_PATH')) exit();?><!DOCTYPE html>

</div>
<link rel="stylesheet" href="/Public/Admin/css/bootstrap.min.css"/>
<style>
    .app{
        width:94%;
        height:auto;
        overflow:hidden;
        margin:20px auto;
        padding:10px 20px;
    }
    .app dl{
        margin:10px 0;
        height:auto;
        overflow:hidden;
    }
    .app dl dt{
        display:blick;
        height:36px;
        line-height:36px;
        background:#e6e6e6;
        text-index:10px;
        padding:0 10px;
    }
    .app dl dd{
        float:left;
        margin:0 10px;
        padding:10px 15px;
    }
</style>
<div class="page-title">
    <div class="title-env">
        <h1 class="title"><?php echo ($meta_title); ?></h1>
        <p class="description"><?php echo ($meta_describe); ?></p>
    </div>
    <div class="breadcrumb-env">

        <ol class="breadcrumb bc-1">
            <li>
                <a href="<?php echo U('Index/index');?>"><i class="fa-home"></i>首页</a>
            </li>
            <li>
                <a>系统设置</a>
            </li>
            <li>
                <a>管理权限</a>
            </li>
            <li class="active">
                <strong><?php echo ($meta_title); ?></strong>
            </li>
        </ol>

    </div>

</div>
<!-- Responsive Table -->
<div class="row">
    <div class="col-md-12">

        <div class="panel panel-default">
            <div class="panel-body">
                <button class="btn btn-white btn-icon fa-plus" onclick="addNode()" >
                    <span>创建节点</span>
                </button>
                <div class="table-responsive" data-pattern="priority-columns" data-focus-btn-icon="fa-asterisk" data-sticky-table-header="true" data-add-display-all-btn="true" data-add-focus-btn="true">

                    <?php if(is_array($node)): foreach($node as $key=>$app): ?><div class="app">
                            <p><?php echo ($app["title"]); ?> <a href="<?php echo U('Rbac/addNode', array('pid' => $app['id'], 'level' => 2));?>" class="btn btn-info btn-xs">添加控制器</a></p>

                            <?php if(is_array($app["child"])): foreach($app["child"] as $key=>$action): ?><dl>
                                    <dt style="color:#f00;">
                                        <?php echo ($action["title"]); ?>
                                        <a href="<?php echo U('Rbac/addNode', array('pid' => $action['id'], 'level' => 3));?>" class="btn btn-info btn-xs">添加方法</a>
                                        <a href="<?php echo U('Rbac/delNode?pid='.$action['id']);?>" onclick="return del_confirm()" class="btn btn-danger btn-xs">删除</a>
                                    </dt>
                                    <?php if(is_array($action["child"])): foreach($action["child"] as $key=>$method): ?><dd>
                                            <span><?php echo ($method["title"]); ?></span>
                                            <a href="<?php echo U('Rbac/delNode?pid='.$method['id']);?>" onclick="return del_confirm()" class="btn btn-danger btn-xs">删除</a>
                                        </dd><?php endforeach; endif; ?>
                                </dl><?php endforeach; endif; ?>
                        </div><?php endforeach; endif; ?>

                </div>

                <script type="text/javascript">
                    function addNode(){
                        window.location.href = "<?php echo U('Rbac/addNode');?>";
                    }
                </script>
            </div>

        </div>
    </div>
</div>

<footer class="main-footer sticky footer-type-1">