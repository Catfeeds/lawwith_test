<?php
//+----------------------------------------------------------------------
// | Copyright (c) 2015-2017 http://lawyerscloud.cn All rights reserved.
//+----------------------------------------------------------------------
// | Author: 慢悠悠的丑小鸭 <1597305236@qq.com>
//+----------------------------------------------------------------------

namespace App\Controller;
use Think\Controller;

class SystemController extends Controller{

	//版本更新检测与提示
	public function versionRefresh() {
		$client = I('get.client');
		$result = file_get_contents('version_refresh.json');
		$result = json_decode($result,true);

		if($client == 'ios') {
			$data['is_refresh'] = $result['is_refresh'];
		}else{
			$data['description'] = urldecode($result['description']);
			$data['downloadUrl'] = urldecode($result['download_url']);
			$data['versionCode'] = urldecode($result['version_code']);
			$data['versionName'] = urldecode($result['version_name']);
		}

		apiReturn('200',AJAX_TRUE,$data);
	}
}

