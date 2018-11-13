<?php
return array(

	/* 模块相关配置 */
	'DEFAULT_MODULE'        => 'Home',
	'MODULE_ALLOW_LIST'     => array('Home', 'Admin', 'App'),

	/* 加载扩展配置文件 */
	'LOAD_EXT_CONFIG'       => 'oss,db',

	/* 开启域名部署 */
	'APP_SUB_DOMAIN_DEPLOY' => 1, // 开启子域名配置
	'APP_SUB_DOMAIN_RULES'  => array(
		'admin' => 'Admin',  // admin子域名指向Admin模块
	),

	/* URL配置 */
	'URL_CASE_INSENSITIVE'  => true, //默认false 表示URL区分大小写 true则表示不区分大小写
	'URL_MODEL'             => 2, //URL模式
	'VAR_URL_PARAMS'        => '', // PATHINFO URL参数变量
	'URL_PATHINFO_DEPR'     => '/', //PATHINFO URL分割符
	'URL_HTML_SUFFIX'       => '',    //修改后台伪静态后缀名

	/* 路由定义 */
	//'URL_ROUTER_ON'   => true,
	//'URL_ROUTE_RULES'=>array(
	//	//'M/index'           => '/mobile'
	//),

	/* 系统数据加密设置*/
	'DATA_AUTH_KEY'         => 'MLG`S.V0TEpY,)CkI1%A$&x<Q;w=ljH6meW!sF]u', //默认数据加密KEY

	/* 字段缓存 */
	'DB_FIELDS_CACHE'       => false,

	/* SQL执行日志记录 */
	'DB_SQL_LOG'            => false,

	/* 本地上传文件驱动配置 */
	'UPLOAD_LOCAL_CONFIG'   => array(),
	'UPLOAD_BCS_CONFIG'     => array(
		'AccessKey' => '',
		'SecretKey' => '',
		'bucket'    => '',
		'rename'    => false,
	),

	/* Memcache缓存的配置 */
	//'DATA_CACHE_TYPE'       => 'Memcache',
	//'MEMCACHE_HOST'         => '127.0.0.1',
	//'MEMCACHE_PORT'         => '11211',
	//'DATA_CACHE_TIME'       => '3600',
	//'DATA_CACHE_KEY'        => 'lx',

	/* 极光推送 */
	'JPUSH_APPKEY'          => '92f6a467b8243bcdb82416b0',
	'JPUSH_SECRET'          => '5fe94e0c936e6d755b3afe0a',


	/* 支付宝公共参数 */
	'alipay_config' => array(
		'appId'	=>	'2016110202488751',		//绑定支付的APPID
		'rsaPrivateKey'	=> 'MIIEpAIBAAKCAQEA+EvNNrbnVmit9fkqLLw6f11lPHlPkaCAm8lX+pF8MMbfZ6gewz7n5n4xo8rTaUR+rjXyAl9TT791QTPxnm5kJbPN9VjzM7xvqFFR89NoLcoO/zkEo75B09U6L7UhZS8XTaAn97j92njXsPQfkon4jRtP+cu2EO+mDh4L1VuvWnqMzzyC91LGhV/O9NSV6+ySt0Oms6xrGp0G+kh6BSIUFdSAw1gBmYfYusrjNX4Fdk2Z2f5qoj3yUjQ9ojB5+Zli8JC5oj+nigPJMRCOO3oXlSglXSOgkT36uoBIvAaP5Huht4QUpknqmRcfFXw5xIT9sWEV88Bu4tpMTvhu+KYM9wIDAQABAoIBABw02EIv8wXQ7Ho17QGZ/Hc9j+OX0F81wYZxEAqJ/na4EOcApUX+3m9DxLXbs7qQqmd1/2EfnSJBGLvof7lExyG1VF9b1Hz7GiAf5NRzHrGc86kwvAMx+N5sqaiDxiBtghMyb3fnzwbDlZUnlnKaM98rzCEPcEZqQw31gdiiCsCXVB/kNPDAaRRfUIUa0RL3Dg0AecSPtCCJIlfnqOZQTSTJjKsRxTl5PkufnK53IkB1mC3Ofe4R0AL9jPUIdFk/EbXsZ5saTC+spVsPln4f2O0qH2fJ2/LmP8+ywb4udxCvaQPkZ7Hc4XLLzPwuGa3/imspCHCgtT6zUb65EvwbzuECgYEA/9OuICAaQJZJztmN0FBj2jOQ/BMVXrsDP3qovQFHJRKt6ZdDCPFETqodfAuz/HX8Xczm5ydD2GdZnxeqnNeWj5iYnPYDq6MTf9YrDYAfUA+B1gGlHjld3m/eVw1SprbPUZcRn9vWCCFtaBkIpT9WEjK1wOi2bZHRaTc5lgrmdJECgYEA+HbRGYhCd2V2Lisvc8/dRi5EKNIfkcfq1H6yCcOvWfuHGE2GH7X3sV9R9iKtgULklr8MUB+foVWRNURcwpAq9HUFXdmpLrDAuvmNmDFRzZLRToA1cvJlo6tFS1ym3snwVnpILR9L2WaZY6LoohlgjueqwuqbxQZUfEdXW2pVjQcCgYEAkDdztLaENh7C8vHfqSx4bdyeAgx8ApAob5OlJg9fXP3fvoxABP2FxVbPEkvCpjKZFwPYszyGWatBcZc03gAG52JdvjZNsWdYKbvBP3YXsNldFxiMTbKHQUsNK0PecQ8jZlpkXBrg2GqHs6RRJF7FFbMylNJbbP9D7y1rsDLq4ZECgYEApzjwikqW0VzMKU6PZValCjLhVMf2z1rJxbJviPrW7azHw6eJeZYy2oHY29uDAthOmNaEJvjhque4Dy2vcJMvFdAciImRwAbd7/k9Pw0SjVUe4cKQNojFt13yCUKWXKN0yf8KzLOfjPXsjo56G38Q6Z0p5H2Z0QTtn6c0OCZc5ckCgYBlYQS/N08jbdHJ1361Tir8OMZnp5wo//0vzXB84Z0vnYWRz8qgKB5zoVk+QQFs4IF2EdYKLI7OjRkp0sAdhUKu2gQcvE/n0xJJyoR9U/Y3SujfCP9JAC5I/ZvjnxpdCR0EE0q8WkFKomDWkvFaP8cf+1rTT1O8PmHTRfgrdhhoqA==',	//私钥
		'alipayrsaPublicKey' => 'MIIBIjANBgkqhkiG9w0BAQEFAAOCAQ8AMIIBCgKCAQEAj5AHQbBjnfxUQB2cC0Rppg7wEK/rdmmxrCv8Z0QU4NjemE0H3UFAeY/s4NTbe76HsKIzo88VArQlQRLvWxedGES57UEoEt7DT0tMQUwiKZ89Cb8EgkZEVwYCfN+RaAFOWBJVjH35pv0l/HMl0JNcp4sM07nAawdHJQBdYihuN5ufILu7Mxj2OfjAIXsKf8mOEeiFMHBdWC2siwvI1iICt9HO+3v999JfMkNW5SgJ1ZgP5L8OC83fCNeb2z6KUnpvjCVb+sb9T6nWoTbElu0mcXuKoCGipHjvR8q9KtDil5nz8nnIzUm15L7JxY6zRAp8LJrItlRJw7tczKbI0htL9wIDAQAB',	//应用公钥
	),


	/* 微信公共参数 */
	'wxpay_config' => array(
		'APPID' => 'wx765fd386d771f6c5',		//绑定支付的APPID
		'MCHID' => '1410179602',			//商户号
		'KEY' => '8934e7d15453e97507ef794cf7b0519d'		//商户支付密钥
	),
);