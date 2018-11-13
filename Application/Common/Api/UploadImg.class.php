<?php
/**
 * Created by PhpStorm.
 * User: Mrx
 * Date: 2016/4/12
 * Time: 12:37
 */

namespace Common\Api;
/**
 * 上传七牛
 *
 * @author huqitao <huqitaoit@gmail.com>
 */


class UploadImg {

    protected $config;
    protected $domain;
    protected $bucket;

    public function __construct($domain, $bucket) {
        $this->domain = $domain;
        $this->bucket = $bucket;
        $this->config = array(
            'maxSize' => 30 * 1024 * 1024, //文件大小
            'exts' =>  array('mp4'),
            'rootPath' => './',
            'saveName' => array('uniqid', 'lx'),
            'driver' => 'Qiniu',
            'driverConfig' => array(
                'secretKey' => 'dT1eWtVpr9rRBs4p3Ju1HEiE4xpaom14cbE8Kiqj',  //七牛空间配置参数
                'accessKey' => 'gPUmgWg25Z0dvR77psVUZUVJcciRfBYCm4nsnPOS',
                'domain' => $this->domain, //空间地址
                'bucket' => $this->bucket, //空间名称
            )
        );
    }



    /**
     * 上传一个文件
     * @param array $file 文件参数
     * @return array 返回 code 与文件路径或错误信息
     */
    public function uploadOne($file) {
        $upload = new \Think\Upload($this->config);
        $info = $upload->uploadOne($file);
        if (!$info) {
            return [550, $upload->getError()];
        }
        return [200, $info['url']];
    }
    /**
     * 上传多文件
     * @param type $files
     * @return array 返回 code 与文件路径数组 或错误信息
     */
    public function uploads($files) {
        $upload = new \Think\Upload($this->config);
        $info = $upload->upload($files);
        if (!$info) {
            return [550, $upload->getError()];
        }
        foreach ($info as $v) {
            $pArray[] = "http://".$this->domain."/".strtr($v['name'], '/', '_');
        }
        return [200, $pArray];
    }

    /**
     * 计算下载七牛凭证
     * @param $str
     * @return mixed
     */
    public function Qiniu_Sign($url,$time) {//$info里面的url
        $setting = $this->config;
        $duetime = NOW_TIME + $time;//下载凭证有效时间
        $DownloadUrl = $url . '?e=' . $duetime;
        $Sign = hash_hmac ( 'sha1', $DownloadUrl, $setting ["driverConfig"] ["secretKey"], true );
        $EncodedSign = Qiniu_Encode ( $Sign );
        $Token = $setting ["driverConfig"] ["accessKey"] . ':' . $EncodedSign;
        $RealDownloadUrl = $DownloadUrl . '&token=' . $Token;
        return $RealDownloadUrl;
    }

}