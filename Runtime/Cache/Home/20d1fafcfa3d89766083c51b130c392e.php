<?php if (!defined('THINK_PATH')) exit();?><!DOCTYPE HTML>
<html>
<head>
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1">
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
    <title>免费送，《2017君合业务研究报告》</title>
    <link href="/Public/Share/activity/css/style_css.css" rel="stylesheet" type="text/css">
    <input type="hidden" id="appid" value="<?php echo ($signPackage["appId"]); ?>"/>
    <input type="hidden" id="timestamp" value="<?php echo ($signPackage["timestamp"]); ?>"/>
    <input type="hidden" id="nonceStr" value="<?php echo ($signPackage["nonceStr"]); ?>"/>
    <input type="hidden" id="signature" value="<?php echo ($signPackage["signature"]); ?>"/>
    <input type="hidden" id="url" value="<?php echo ($signPackage["url"]); ?>"/>
    <input type="hidden" id="title" value="<?php echo ($title); ?>"/>
    <input type="hidden" id="content" value="只要您邀请5个朋友注册律携，就能免费拿到书"/>
    <script src="http://apps.bdimg.com/libs/jquery/2.1.4/jquery.min.js"></script>
    <!--<script src="http://res.wx.qq.com/open/js/jweixin-1.2.0.js"></script>-->
    <style>
        .layui-input {
            display: block;
            width: 90%;
            padding-left: 10px;
            height: 38px;
            margin: auto;
            line-height: 14px;
            border: 1px solid #e6e6e6;
            background-color: #efeeec;
            border-radius: 4px;
            font-size: 1em;
            font-weight: bolder;
        }

        .Top4_button {
            margin-top: 30px;
        }

        .invite-success {
            color: #fff;
            font-size: 18px;
            text-align: center;
        }

        .success-mobile {
            font-size: 14px;
        }

        .Top2 h3 {
            text-align: center;
            padding: 25px 0;
        }
        #tips{
            margin-top: 20px;
            margin-bottom: 20px;
            background-color: #ffffff;
            border-radius:5px;
            opacity:0.6;
        }
    </style>
</head>
<body>
<div class="ActivityBox">
    <div class="Top">
        <div class="Top1">
            <img src="http://img.lawyerscloud.cn/activity/detailtop.png">
        </div>
        <!--<div id="tips" class="Top2"><h3>活动已结束</h3></div>-->
        <div class="Top2">
            <img src="http://img.lawyerscloud.cn/activity/detail_rule.png">
        </div>
        <div class="Top3">
            当前已有<span class="user-count"><?php echo ($count); ?></span>人参与活动
        </div>
        <div class="Top3 success-count">
            您已成功邀请<span class="user-count" id="success-count"><?php if(empty($actived_count)): ?>0
            <?php else: echo ($actived_count); endif; ?></span>人参与
        </div>
        <div class="Top4">
            <input type="text" name="title" required lay-verify="required" placeholder="请输入手机号" autocomplete="off" class="layui-input">
            <!--validate code star-->
            <div class="Top4_password">
                <div class="Top4_passwordL fl">
                    <div class="Top4_passwordL1"><input type="text" class="validate-code" placeholder="验证码" style="color: #ffffff"></div>
                    <div class="Top4_passwordL2"><img src="/Public/Share/activity/images/Top5.png"></div>
                </div>
                <div class="Top4_passwordR fr"><input type="button" class="validate-button" value="获取验证码"></div>
            </div>
            <!--validate code end-->
            <div class="invite-success" style="display: none">
                <p>参与手机号</p>
                <p class="success-mobile"></p>
            </div>
            <!--submit-->
            <div class="Top4_button"><div class="submit-button"></div></div>
        </div>

        </div>
        <div class="Top5">
            <img src="/Public/Share/activity/images/Top7.png">
        </div>
        <div class="Catalog">
            <div class="Catalog_Bt">
                <div class="Catalog_BtTxt">附带本书封面与目录</div>
            </div>
            <div class="Catalog_Img"><img src="http://img.lawyerscloud.cn/activity/book.png"></div>
            <div class="Catalog_Nr">

                <div class="Catalog_NrPage">
                    <span>公司并购业务年度报告</span>
                    <div>一、2017 年度公司并购业务重要立法摘要</div>
                    <div>二、2017 年度公司并购组重要业绩汇总</div>
                    <div>三、市场热点问题研究</div>
                    <div>四、立法展望</div>
                </div>
                <div class="Catalog_NrPage">
                    <span>资本市场业务年度报告</span>
                    <div>一、2017 年中国证券与资本市场重要立法摘要</div>
                    <div>二、2017 年君合参与的主要资本市场项目</div>
                    <div>三、主要业务领域市场实践及主要法律问题简析</div>
                    <div>四、2018 年资本市场预测</div>
                </div>
                <div class="Catalog_NrPage">
                    <span>银行金融保险业务年度报告</span>
                    <div>一、新法规则要</div>
                    <div>二、2017银行金融重大项目</div>
                    <div>三、主要业务问题</div>
                    <div>四、重点法规解读</div>
                </div>
                <div class="Catalog_NrPage">
                    <span>基础设施和项目融资组业务年度报告</span>
                    <div>一、一带一路专章</div>
                    <div>二、能源行业及PPP领域年度重要立法及政策摘要</div>
                    <div>三、能源行业和PPP领域年度若干重大项目</div>
                    <div>四、主要业务领域市场实践及主要法律问题简析</div>
                    <div>五、2018年业务展望</div>
                </div>
                <div class="Catalog_NrPage">
                    <span>房地产业务年度报告</span>
                    <div>一、房地产领域年度重要新法律法规</div>
                    <div>二、房地产业务组重要业绩</div>
                    <div>三、市场热点法律问题研究</div>
                    <div>四、房地产业务展望</div>
                </div>
                <div class="Catalog_NrPage">
                    <span>国际贸易业务年度报告</span>
                    <div>一、海关法事务</div>
                    <div>二、跨国公司的海关定价与转移定价</div>
                    <div>三、出口管制</div>
                    <div>四、WTO与企业营商环境</div>
                </div>
                <div class="Catalog_NrPage">
                    <span>境外投资业务年度报告</span>
                    <div>一、2017 年度中国境外投资领域相关法规的新发展</div>
                    <div>二、2017年境外投资重要业绩汇总</div>
                    <div>三、市场实践及主要问题研究</div>
                    <div>四、境外投资的预测和展望</div>
                </div>
                <div class="Catalog_NrPage">
                    <span>反垄断业务年度报告</span>
                    <div>一、2017 年中国反垄断动态</div>
                    <div>二、2017 年反垄断案件汇总</div>
                    <div>三、君合反垄断领域部分业绩</div>
                    <div>四、2018年反垄断工作展望</div>
                </div>
                <div class="Catalog_NrPage">
                    <span>传媒娱乐业务年度报告</span>
                    <div>一、2017 年度传媒与娱乐重要立法摘要</div>
                    <div>二、2017 年度传媒娱乐大事件</div>
                    <div>三、2017 年度君合传媒与娱乐领域业绩</div>
                    <div>四、市场热点问题研究——如何撬起IP和内容的长尾</div>
                </div>
                <div class="Catalog_NrPage">
                    <span>劳动法业务年度报告</span>
                    <div>一、2017年劳动法重要立法摘要</div>
                    <div>二、2017年劳动法领域的重点问题及典型案件/事件</div>
                    <div>三、2017年君合劳动法业务重大项目及典型案例分析</div>
                    <div>四、2018年劳动法律市场展望</div>
                </div>
                <div class="Catalog_NrPage">
                    <span>商标业务年度报告</span>
                    <div>一、2017年商标法领域新动态</div>
                    <div>二、2017年商标保护情况和发展趋势</div>
                    <div>三、2017年度君合重要业绩汇总</div>
                    <div>四、2017年度商标热点案例</div>
                </div>
                <div class="Catalog_NrPage">
                    <span>专利法业务年度报告</span>
                    <div>一、2017年中国专利法领域新动态</div>
                    <div>二、2017年专利组重大项目</div>
                    <div>三、专利法领域实践及相关问题分析</div>
                    <div>四、2018年专利法律市场展望</div>
                </div>
                <div class="Catalog_NrPage">
                    <span>合规业务年度报告</span>
                    <div>一、安全生产与职业健康领域法律新发展</div>
                    <div>二、企业在中国的环保法律责任与污染强制保险</div>
                    <div>三、《反不正当竞争法》修订及新法下商业贿赂的认定与适用</div>
                    <div>四、医药代表备案制全国层面推行在即</div>
                    <div>五、药品采购两票制改革以及合规思考</div>
                    <div>六、网络安全与信息保护</div>
                </div>
                <div class="Catalog_NrPage">
                    <span>诉讼仲裁业务年度报告</span>
                    <div>一、上市公司涉证券虚假陈述民事赔偿诉讼裁判要点分析</div>
                    <div>二、违反“举牌”义务进行股票交易的民事责任探析</div>
                    <div>三、证券中介机构虚假陈述的民事责任</div>
                    <div>四、信息型操纵证券市场犯罪的司法认定</div>
                    <div>五、从三起案件看“老鼠仓”犯罪司法从严趋势</div>
                    <div>六、跨国网络诈骗案的域内追赃实践</div>
                    <div>七、互联网金融监管与刑事司法实践新动态——以“E租宝”案为例</div>
                    <div>八、从诉讼角度对融资性贸易的法律分析</div>
                    <div>九、占有与不动产物权变动模式之考察——江苏省高级人民法院（2013）苏民终字第0003号民事判决评释</div>
                    <div>十、《反不正当竞争法》在企业数据竞争纠纷中的适用</div>
                    <div>十一、法人名誉权侵权案件适格原告的确定</div>
                    <div>十二、从上海高院案例看债权人代位权诉讼的适用——如何把握原被告之间利益的平衡</div>
                    <div>十三、国际投资仲裁与国际商事仲裁的主要区别：以新仲《投资仲裁规则》为视角</div>
                    <div>十四、仲裁条款可能因《公司法》第一百五十一条被规避</div>
                    <div>十五、南京中院首次裁定承认执行新加坡法院判决</div>
                    <div>十六、我国承认和执行外国法院判决的新趋势和相关疑难问题</div>
                </div>
                <div class="Catalog_NrPage">
                    <span>破产重组业务年度报告</span>
                    <div>一、破产重组领域最新动态</div>
                    <div>二、投资重整企业法律问题研究</div>
                    <div>三、2017 君合破产重组业务组典型案例</div>
                </div>
                <div class="Catalog_NrPage">
                    <span>纽约分所业务年度报告</span>
                    <div>一、美国外国投资委员会公布最新国家安全审查年度报告对中国投资者的启示</div>
                    <div>二、美国国家安全审查制度2017年发展趋势及其对中国企业赴美投资的影响</div>
                    <div>三、君合纽约分所业绩</div>
                </div>
            </div><!--目录 End-->
        </div><!--封面与目录 End-->
        <div class="bottom">本活动最终解释权归北京君时天下互动科技有限公司所有</div>
    </div>
    <script src="/Public/Share/activity/js/share.js"></script>
</body>
</html>