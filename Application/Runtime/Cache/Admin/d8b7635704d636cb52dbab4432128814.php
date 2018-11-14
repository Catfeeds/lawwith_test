<?php if (!defined('THINK_PATH')) exit();?><!DOCTYPE html>
<link href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.4/css/bootstrap.min.css" rel="stylesheet">
</div>
<link rel="stylesheet" type="text/css" href="/Public/Jquery_Upload/css/default.css">
<link href="/Public/Jquery_Upload/css/fileinput.css" media="all" rel="stylesheet" type="text/css" />
<script type="text/javascript" src="/Public/Admin/js/bootstrapValidator.js"></script>
<script type="text/javascript" src="/Public/Jquery_Upload/jquery.form.js"></script>
<div class="row">
    <div class="panel panel-default">
        <div class="panel-body form-horizontal">
            <form id="For_video" action="<?php echo U('Video/submitUpdate');?>" method="post">
                <div class="form-group">
                    <label class="col-lg-3 control-label">封面图</label>
                    <div id="file_path" class="col-lg-5">
                        <!--<textarea name="path" class="form-control autogrow" style="min-height: 50px; border:none;outline:none;" readonly><?php echo ($data["path"]); ?></textarea>-->
                        <img src="<?php echo ($data["title_img"]); ?>" alt="" style="width:250px;height:180px;">
                    </div>
                </div>
                <div class="form-group">
                    <label class="col-lg-3 control-label">标&nbsp;&nbsp;&nbsp;&nbsp;题</label>
                    <div class="col-lg-5">
                        <input class="form-control" type="text" name="title" value="<?php echo ($data['title']); ?>">
                    </div>
                </div>
                <div class="form-group">
                    <script type="text/javascript">
                        jQuery(document).ready(function($)
                        {
                            $("#s2example-2").select2({
                                placeholder: '请选择专业领域',
                                allowClear: true
                            }).on('select2-open', function()
                            {
                                // Adding Custom Scrollbar
                                $(this).data('select2').results.addClass('overflow-hidden').perfectScrollbar();
                            });

                        });
                    </script>
                    <label class="col-lg-3 control-label">专业领域 </label>
                    <div class="col-lg-5">
                        <select class="form-control" id="s2example-2" name="major_id[]" multiple>
                            <optgroup label="请选择专业领域">
                                <?php $_result=explode(',', $data['tags']);if(is_array($_result)): $i = 0; $__LIST__ = $_result;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$tag): $mod = ($i % 2 );++$i; if(is_array($major_tree)): $i = 0; $__LIST__ = $major_tree;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$major_vo): $mod = ($i % 2 );++$i; if($major_vo['id'] == $tag): ?><option value="<?php echo ($major_vo['id']); ?>" selected><?php echo ($major_vo["delimiter"]); echo ($major_vo["major_name"]); ?></option>
                                            <?php else: ?>
                                            <option value="<?php echo ($major_vo['id']); ?>"><?php echo ($major_vo["delimiter"]); echo ($major_vo["major_name"]); ?></option><?php endif; endforeach; endif; else: echo "" ;endif; endforeach; endif; else: echo "" ;endif; ?>
                            </optgroup>
                        </select>

                    </div>
                </div>
                <div class="form-group">
                    <label class="col-lg-3 control-label">描述</label>
                    <div class="col-lg-5"><textarea class="form-control autogrow" name="describe" placeholder="描述律所" style="min-height: 100px;"><?php echo ($data["remark"]); ?></textarea></div>
                </div>
                <div class="form-group">
                    <div class="col-lg-9 col-lg-offset-3">
                        <input name="vid" type="hidden" value="<?php echo ($data["id"]); ?>">
                        <button type="submit" class="btn btn-success">确认保存</button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>
<script src="/Public/Jquery_Upload/js/fileinput.js" type="text/javascript"></script>
<script src="/Public/Jquery_Upload/js/fileinput_locale_zh.js" type="text/javascript"></script>

<script type="text/javascript">
    $(document).ready(function(){
        $("#sub_up").click(function(){
            var formData = new FormData();
            formData.append('file', $('#file-0a')[0].files[0]);
            $.ajax({
                url: "<?php echo U('Train/uploadResource');?>",
                type: 'POST',
                cache: false,
                data: formData,
                processData: false,
                contentType: false,
                beforeSend: function()
                {
                    $(".progress").show();
                    //clear everything
                    $(".bar").width('0%');
                    $(".percent").html("0%");
                },
                uploadProgress: function(event, position, total, percentComplete)
                {
                    $(".bar").width(percentComplete+'%');
                    $(".percent").html(percentComplete+'%');
                },
                success: function()
                {
                    $(".bar").width('100%');
                    $(".percent").html('100%');
                }

            }).done(function(res) {
                $('#file_path').html("<textarea class='form-control autogrow' name='path' style='min-height: 50px; border:none;outline:none;' readonly>"+res+"</textarea>");
            }).fail(function(res) {
                $('#file_path').html('<span style="color: crimson">上传文件失败</span>');
            });
        });
    });
</script>
<script type="text/javascript">
    $(document).ready(function() {
        $('#For_video')
                .bootstrapValidator({
                    message: '这个值无效',
                    feedbackIcons: {
                        valid: 'glyphicon glyphicon-ok',
                        invalid: 'glyphicon glyphicon-remove',
                        validating: 'glyphicon glyphicon-refresh'
                    },
                    fields: {
//                        file: {
//                            message: '视频文件无效！',
//                            validators: {
//                                notEmpty: {
//                                    message: '视频文件不能为空！'
//                                }
//                            }
//                        },
                        path: {
                            validators: {
                                notEmpty: {
                                    message: '文件路径不能为空！'
                                },
                                regexp: {
                                    regexp: /^(http|https):\/\//,
                                    message: '文件路径不合法！'
                                }
                            }
                        },
                        title: {
                            validators: {
                                notEmpty: {
                                    message: '标题不能为空！'
                                }
                            }
                        },

                        describe: {
                            validators: {
                                notEmpty: {
                                    message: '描述不能为空！'
                                }
                            }
                        }
                    }
                })
    });
</script>
<script src="/Public/Admin/js/select2/select2.min.js"></script>
<link rel="stylesheet" href="/Public/Admin/js/select2/select2.css">
<link rel="stylesheet" href="/Public/Admin/js/select2/select2-bootstrap.css">
<footer class="main-footer sticky footer-type-1">