<?php
return [
	/*
	|--------------------------------------------------------------------------
	| 支付宝公共配置项目
	|--------------------------------------------------------------------------
	*/

	// 合作身份者id，以2088开头的16位纯数字。
	'partner_id'              => env('L5_ALIPAY_PARTNER_ID'),

	// 卖家支付宝帐户
	'seller_id'               => env('L5_ALIPAY_SELL_ID'),


	/*
	|--------------------------------------------------------------------------
	| 即时到账 配置项目
	|--------------------------------------------------------------------------
	*/

	// 安全检验码，以数字和字母组成的32位字符
	'web_direct_key'          => env('L5_ALIPAY_WEB_DIRECT_KEY'),

	// 签名方式
	'web_direct_sign_type'    => 'MD5',

	// 服务器异步通知页面路径
	'web_direct_notify_url'   => 'charge-notify',

	// 页面跳转同步通知页面路径
	'web_direct_return_url'   => 'charge-callback',

	/*
	|--------------------------------------------------------------------------
	| 移动支付配置项目
	|--------------------------------------------------------------------------
	*/

	// 签名方式
	'mobile_sign_type'        => 'RSA',

	// 私钥
	'mobile_private_key_path' => '',

	// 公约
	'mobile_public_key_path'  => '',

	// 异步通知地址
	'mobile_notify_url'       => 'mobile-notify',


	/*
	|--------------------------------------------------------------------------
	| 转账到支付宝 配置项目
	|--------------------------------------------------------------------------
	*/
	//是否是测试环境 sandbox:测试环境  production:正式环境
	'open_api_env'              => env('ALIPAY_OPEN_API_ENV', 'sandbox'),

	//支付宝分配给开发者的应用ID : 2014072300007148
	'open_api_app_id'           => env('ALIPAY_OPEN_API_APP_ID'),

	// 开发者私钥
	'open_api_private_key_path' => env('ALIPAY_OPEN_API_RSA_PRIVATE'),

	// 支付宝公钥
	'open_api_public_key_path'  => env('ALIPAY_OPEN_API_RSA_PUBLIC'),

];