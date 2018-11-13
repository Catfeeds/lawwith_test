//footer定位最底部
$('.footer').css("position","relative");

//专业领域下拉框
$("#s2example-2").select2({
	placeholder: '请选择专业领域',
	allowClear: true
}).on('select2-open', function(){
	// Adding Custom Scrollbar
	// $(this).data('select2').results.addClass('overflow-hidden').perfectScrollbar();
});

//选择实务分类才让选择专业领域
$("#s2example-1").change(function(){
	var select = $("#s2example-1").find("option:selected").text();
	if(select !== '实务') {
		//禁用下拉列表
		$("#s2example-2").prop("disabled",true);
		$(".tips").html('"实务"才有专业领域');
	} else {
		$("#s2example-2").prop("disabled",false);
		$(".tips").empty();
	}
});
$(function(){
	var select = $("#s2example-1").find("option:selected").text();
	if(select !== '实务') {
		//禁用下拉列表
		$("#s2example-2").prop("disabled",true);
		$(".tips").html('"实务"才有专业领域');
	} else {
		$("#s2example-2").prop("disabled",false);
		$(".tips").empty();
	}
});
submitContent();

function submitContent() {
	$(document).ready(function() {
		$("#tijiao").click(function(){
			//验证标题
			if($("#title").val() == ''){
				alert('请输入至少6个字的标题！');
				return false;
			}

			//验证正文
			ue.ready(function() {
				var html = ue.getContentTxt();
				if(html == "" || html.length < 20) {
					alert('请至少输入20字的正文！');
					return false;
				}
			});

			//验证帖子分类
			if($("#s2example-1").val() == ''){
				alert("请选择分类!");
				return false;
			}

			//文章分类下拉框
			$("#s2example-1").select2({
				placeholder: '请选择文章分类',
				allowClear: true
			}).on('select2-open', function(){
				$(this).data('select2').results.addClass('overflow-hidden').perfectScrollbar();
			});

			$(this).attr('type','submit');
			return true;
		});

	});
}
