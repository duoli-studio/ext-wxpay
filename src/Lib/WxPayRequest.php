<?php namespace Poppy\Extension\Wxpay\Lib;

use Curl\Curl;
use Poppy\Framework\Helper\EnvHelper;

/**
 * 接口访问类，包含所有微信支付API列表的封装，
 * 每个接口有默认超时时间（除提交被扫支付为10s，上报超时时间为1s外，其他均为6s）
 * @author widyhu
 */
class WxPayRequest
{
	/** @var WxPayConfig */
	private $config;

	/**
	 * WxPayRequest constructor.
	 * @param WxPayConfig $config
	 */
	public function __construct($config)
	{
		$this->config = $config;
	}

	/**
	 * @param WxPayConfig $config
	 */
	public function setConfig($config)
	{
		$this->config = $config;
	}

	/**
	 * @return WxPayConfig
	 */
	public function getConfig()
	{
		return $this->config;
	}

	/**
	 * 统一下单，WxPayUnifiedOrder中out_trade_no、body、total_fee、trade_type必填
	 * appid、mchid、spbill_create_ip、nonce_str不需要填入
	 * @param WxPayUnifiedOrder $inputObj
	 * @param int               $timeOut
	 * @return array   成功时返回
	 * @throws WxPayException 其他抛异常
	 */
	public function unifiedOrder($inputObj, $timeOut = 6)
	{
		if (!$this->config) {
			throw new WxPayException('请设置请求的配置信息');
		}
		$url = 'https://api.mch.weixin.qq.com/pay/unifiedorder';
		//检测必填参数
		if (!$inputObj->IsOut_trade_noSet()) {
			throw new WxPayException('缺少统一支付接口必填参数out_trade_no！');
		}
		elseif (!$inputObj->IsBodySet()) {
			throw new WxPayException('缺少统一支付接口必填参数body！');
		}
		elseif (!$inputObj->IsTotal_feeSet()) {
			throw new WxPayException('缺少统一支付接口必填参数total_fee！');
		}
		elseif (!$inputObj->IsTrade_typeSet()) {
			throw new WxPayException('缺少统一支付接口必填参数trade_type！');
		}

		//关联参数
		if ($inputObj->GetTrade_type() == 'JSAPI' && !$inputObj->IsOpenidSet()) {
			throw new WxPayException('统一支付接口中，缺少必填参数openid！trade_type为JSAPI时，openid为必填参数！');
		}
		if ($inputObj->GetTrade_type() == 'NATIVE' && !$inputObj->IsProduct_idSet()) {
			throw new WxPayException('统一支付接口中，缺少必填参数product_id！trade_type为JSAPI时，product_id为必填参数！');
		}

		//异步通知url未设置，则使用配置文件中的url
		if (!$inputObj->IsNotify_urlSet()) {
			throw new WxPayException('统一支付接口中，缺少必填参数 notify url！');
		}

		$inputObj->SetAppid($this->config->getAppId());

		// 商户号
		$inputObj->SetMch_id($this->config->getMchId());

		// 终端ip
		$inputObj->SetSpbill_create_ip(EnvHelper::ip());

		// 随机字符串
		$inputObj->SetNonce_str(self::getNonceStr());

		//签名
		$inputObj->SetSign($this->config->getKey());

		$xml = $inputObj->ToXml();

		$startTimeStamp = self::getMillisecond();//请求开始时间
		$response       = self::postXmlCurl($xml, $url, false, $timeOut);
		$result         = WxPayResults::Init($response, $this->config);

		/* 上报请求花费时间
		 -------------------------------------------- */
		self::reportCostTime($url, $startTimeStamp, $result);

		return $result;
	}

	/**
	 * 查询订单，WxPayOrderQuery中out_trade_no、transaction_id至少填一个
	 * appid、mchid、spbill_create_ip、nonce_str不需要填入
	 * @param WxPayOrderQuery $inputObj
	 * @param int             $timeOut
	 * @throws WxPayException
	 * @return array 成功时返回，其他抛异常
	 */
	public function orderQuery($inputObj, $timeOut = 6)
	{
		$url = 'https://api.mch.weixin.qq.com/pay/orderquery';
		//检测必填参数
		if (!$inputObj->IsOut_trade_noSet() && !$inputObj->IsTransaction_idSet()) {
			throw new WxPayException('订单查询接口中，out_trade_no、transaction_id至少填一个！');
		}
		$inputObj->SetAppid($this->config->getAppId());//公众账号ID
		$inputObj->SetMch_id($this->config->getMchId());//商户号
		$inputObj->SetNonce_str(self::getNonceStr());//随机字符串

		$inputObj->SetSign($this->config->getKey());//签名
		$xml = $inputObj->ToXml();

		$startTimeStamp = self::getMillisecond();//请求开始时间
		$response       = self::postXmlCurl($xml, $url, false, $timeOut);
		$result         = WxPayResults::Init($response, $this->config);
		self::reportCostTime($url, $startTimeStamp, $result);//上报请求花费时间

		return $result;
	}

	/**
	 * 关闭订单，WxPayCloseOrder中out_trade_no必填
	 * appid、mchid、spbill_create_ip、nonce_str不需要填入
	 * @param WxPayCloseOrder $inputObj
	 * @param int             $timeOut
	 * @throws WxPayException
	 * @return array 成功时返回，其他抛异常
	 */
	public function closeOrder($inputObj, $timeOut = 6)
	{
		$url = 'https://api.mch.weixin.qq.com/pay/closeorder';
		//检测必填参数
		if (!$inputObj->IsOut_trade_noSet()) {
			throw new WxPayException('订单查询接口中，out_trade_no必填！');
		}
		$inputObj->SetAppid($this->config->getAppId());//公众账号ID
		$inputObj->SetMch_id($this->config->getMchId());//商户号
		$inputObj->SetNonce_str(self::getNonceStr());//随机字符串

		$inputObj->SetSign($this->config->getKey());//签名
		$xml = $inputObj->ToXml();

		$startTimeStamp = self::getMillisecond();//请求开始时间
		$response       = self::postXmlCurl($xml, $url, false, $timeOut);
		$result         = WxPayResults::Init($response, $this->config);
		self::reportCostTime($url, $startTimeStamp, $result);//上报请求花费时间

		return $result;
	}

	/**
	 * 申请退款，WxPayRefund中out_trade_no、transaction_id至少填一个且
	 * out_refund_no、total_fee、refund_fee、op_user_id为必填参数
	 * appid、mchid、spbill_create_ip、nonce_str不需要填入
	 * @param WxPayRefund $inputObj
	 * @param int         $timeOut
	 * @throws WxPayException
	 * @return array 成功时返回，其他抛异常
	 */
	public function refund($inputObj, $timeOut = 6)
	{
		$url = 'https://api.mch.weixin.qq.com/secapi/pay/refund';
		//检测必填参数
		if (!$inputObj->IsOut_trade_noSet() && !$inputObj->IsTransaction_idSet()) {
			throw new WxPayException('退款申请接口中，out_trade_no、transaction_id至少填一个！');
		}
		elseif (!$inputObj->IsOut_refund_noSet()) {
			throw new WxPayException('退款申请接口中，缺少必填参数out_refund_no！');
		}
		elseif (!$inputObj->IsTotal_feeSet()) {
			throw new WxPayException('退款申请接口中，缺少必填参数total_fee！');
		}
		elseif (!$inputObj->IsRefund_feeSet()) {
			throw new WxPayException('退款申请接口中，缺少必填参数refund_fee！');
		}
		elseif (!$inputObj->IsOp_user_idSet()) {
			throw new WxPayException('退款申请接口中，缺少必填参数op_user_id！');
		}

		if (!$this->config->getSslKeyPath()) {
			throw new WxPayException('退款申请接口中，缺少SSL密钥配置！');
		}
		if (!$this->config->getSslCertPath()) {
			throw new WxPayException('退款申请接口中，缺少SSL证书配置！');
		}

		if (!file_exists($this->config->getSslKeyPath())) {
			throw new WxPayException('SSL密钥文件不存在！');
		}
		if (!file_exists($this->config->getSslCertPath())) {
			throw new WxPayException('SSL 证书文件不存在！');
		}

		$inputObj->SetAppid($this->config->getAppId());//公众账号ID
		$inputObj->SetMch_id($this->config->getMchId());//商户号
		$inputObj->SetNonce_str(self::getNonceStr());//随机字符串

		$inputObj->SetSign($this->config->getKey());//签名
		$xml            = $inputObj->ToXml();
		$startTimeStamp = self::getMillisecond();//请求开始时间
		$response       = self::postXmlCurl($xml, $url, true, $timeOut);
		$result         = WxPayResults::Init($response, $this->config);
		self::reportCostTime($url, $startTimeStamp, $result);//上报请求花费时间

		return $result;
	}

	/**
	 * 查询退款
	 * 提交退款申请后，通过调用该接口查询退款状态。退款有一定延时，
	 * 用零钱支付的退款20分钟内到账，银行卡支付的退款3个工作日后重新查询退款状态。
	 * WxPayRefundQuery中out_refund_no、out_trade_no、transaction_id、refund_id四个参数必填一个
	 * appid、mchid、spbill_create_ip、nonce_str不需要填入
	 * @param WxPayRefundQuery $inputObj
	 * @param int              $timeOut
	 * @throws WxPayException
	 * @return array 成功时返回，其他抛异常
	 */
	public function refundQuery($inputObj, $timeOut = 6)
	{
		$url = 'https://api.mch.weixin.qq.com/pay/refundquery';
		//检测必填参数
		if (!$inputObj->IsOut_refund_noSet() &&
			!$inputObj->IsOut_trade_noSet() &&
			!$inputObj->IsTransaction_idSet() &&
			!$inputObj->IsRefund_idSet()) {
			throw new WxPayException('退款查询接口中，out_refund_no、out_trade_no、transaction_id、refund_id四个参数必填一个！');
		}
		$inputObj->SetAppid($this->config->getAppId());//公众账号ID
		$inputObj->SetMch_id($this->config->getMchId());//商户号
		$inputObj->SetNonce_str(self::getNonceStr());//随机字符串

		$inputObj->SetSign($this->config->getKey());//签名
		$xml = $inputObj->ToXml();

		$startTimeStamp = self::getMillisecond();//请求开始时间
		$response       = self::postXmlCurl($xml, $url, false, $timeOut);
		$result         = WxPayResults::Init($response, $this->config);
		self::reportCostTime($url, $startTimeStamp, $result);//上报请求花费时间

		return $result;
	}

	/**
	 * 下载对账单，WxPayDownloadBill中bill_date为必填参数
	 * appid、mchid、spbill_create_ip、nonce_str不需要填入
	 * @param WxPayDownloadBill $inputObj
	 * @param int               $timeOut
	 * @throws WxPayException
	 * @return array|string 成功时返回，其他抛异常
	 */
	public function downloadBill($inputObj, $timeOut = 6)
	{
		$url = 'https://api.mch.weixin.qq.com/pay/downloadbill';
		//检测必填参数
		if (!$inputObj->IsBill_dateSet()) {
			throw new WxPayException('对账单接口中，缺少必填参数bill_date！');
		}
		$inputObj->SetAppid($this->config->getAppId());//公众账号ID
		$inputObj->SetMch_id($this->config->getMchId());//商户号
		$inputObj->SetNonce_str(self::getNonceStr());//随机字符串

		$inputObj->SetSign($this->config->getKey());//签名
		$xml = $inputObj->ToXml();

		$response = self::postXmlCurl($xml, $url, false, $timeOut);
		if (substr($response, 0, 5) == '<xml>') {
			return '';
		}

		return $response;
	}

	/**
	 * 提交被扫支付API
	 * 收银员使用扫码设备读取微信用户刷卡授权码以后，二维码或条码信息传送至商户收银台，
	 * 由商户收银台或者商户后台调用该接口发起支付。
	 * WxPayWxPayMicroPay中body、out_trade_no、total_fee、auth_code参数必填
	 * appid、mchid、spbill_create_ip、nonce_str不需要填入
	 * @param WxPayMicroPay $inputObj
	 * @param int           $timeOut
	 * @return array
	 * @throws WxPayException
	 */
	public function micropay($inputObj, $timeOut = 10)
	{
		$url = 'https://api.mch.weixin.qq.com/pay/micropay';
		//检测必填参数
		if (!$inputObj->IsBodySet()) {
			throw new WxPayException('提交被扫支付API接口中，缺少必填参数body！');
		}
		elseif (!$inputObj->IsOut_trade_noSet()) {
			throw new WxPayException('提交被扫支付API接口中，缺少必填参数out_trade_no！');
		}
		elseif (!$inputObj->IsTotal_feeSet()) {
			throw new WxPayException('提交被扫支付API接口中，缺少必填参数total_fee！');
		}
		elseif (!$inputObj->IsAuth_codeSet()) {
			throw new WxPayException('提交被扫支付API接口中，缺少必填参数auth_code！');
		}

		$inputObj->SetSpbill_create_ip($_SERVER['REMOTE_ADDR']);//终端ip
		$inputObj->SetAppid($this->config->getAppId());//公众账号ID
		$inputObj->SetMch_id($this->config->getMchId());//商户号
		$inputObj->SetNonce_str(self::getNonceStr());//随机字符串

		$inputObj->SetSign($this->config->getKey());//签名
		$xml = $inputObj->ToXml();

		$startTimeStamp = self::getMillisecond();//请求开始时间
		$response       = self::postXmlCurl($xml, $url, false, $timeOut);
		$result         = WxPayResults::Init($response, $this->config);
		self::reportCostTime($url, $startTimeStamp, $result);//上报请求花费时间

		return $result;
	}

	/**
	 * 撤销订单API接口，WxPayReverse中参数out_trade_no和transaction_id必须填写一个
	 * appid、mchid、spbill_create_ip、nonce_str不需要填入
	 * @param WxPayReverse $inputObj
	 * @param int          $timeOut
	 * @return array
	 * @throws WxPayException
	 */
	public function reverse($inputObj, $timeOut = 6)
	{
		$url = 'https://api.mch.weixin.qq.com/secapi/pay/reverse';
		//检测必填参数
		if (!$inputObj->IsOut_trade_noSet() && !$inputObj->IsTransaction_idSet()) {
			throw new WxPayException('撤销订单API接口中，参数out_trade_no和transaction_id必须填写一个！');
		}

		$inputObj->SetAppid($this->config->getAppId());//公众账号ID
		$inputObj->SetMch_id($this->config->getMchId());//商户号
		$inputObj->SetNonce_str(self::getNonceStr());//随机字符串

		$inputObj->SetSign($this->config->getKey());//签名
		$xml = $inputObj->ToXml();

		$startTimeStamp = self::getMillisecond();//请求开始时间
		$response       = self::postXmlCurl($xml, $url, true, $timeOut);
		$result         = WxPayResults::Init($response, $this->config);
		self::reportCostTime($url, $startTimeStamp, $result);//上报请求花费时间

		return $result;
	}

	/**
	 * 测速上报，该方法内部封装在report中，使用时请注意异常流程
	 * WxPayReport中interface_url、return_code、result_code、user_ip、execute_time_必填
	 * appid、mchid、spbill_create_ip、nonce_str不需要填入
	 * @param WxPayReport $inputObj
	 * @param int         $timeOut
	 * @throws WxPayException
	 * @return array 成功时返回，其他抛异常
	 */
	public function report($inputObj, $timeOut = 1)
	{
		$url = 'https://api.mch.weixin.qq.com/payitil/report';
		//检测必填参数
		if (!$inputObj->IsInterface_urlSet()) {
			throw new WxPayException('接口URL，缺少必填参数interface_url！');
		}
		if (!$inputObj->IsReturn_codeSet()) {
			throw new WxPayException('返回状态码，缺少必填参数return_code！');
		}
		if (!$inputObj->IsResult_codeSet()) {
			throw new WxPayException('业务结果，缺少必填参数result_code！');
		}
		if (!$inputObj->IsUser_ipSet()) {
			throw new WxPayException('访问接口IP，缺少必填参数user_ip！');
		}
		if (!$inputObj->IsExecute_time_Set()) {
			throw new WxPayException('接口耗时，缺少必填参数execute_time_！');
		}
		$inputObj->SetAppid($this->config->getAppId());//公众账号ID
		$inputObj->SetMch_id($this->config->getMchId());//商户号
		$inputObj->SetUser_ip($_SERVER['REMOTE_ADDR']);//终端ip
		$inputObj->SetTime(date('YmdHis'));//商户上报时间
		$inputObj->SetNonce_str(self::getNonceStr());//随机字符串

		$inputObj->SetSign($this->config->getKey());//签名
		$xml = $inputObj->ToXml();

		$response = self::postXmlCurl($xml, $url, false, $timeOut);

		return $response;
	}

	/**
	 * 生成二维码规则,模式一生成支付二维码
	 * appid、mchid、spbill_create_ip、nonce_str不需要填入
	 * @param WxPayBizPayUrl $inputObj
	 * @throws WxPayException
	 * @return array 成功时返回，其他抛异常
	 */
	public function bizpayurl($inputObj)
	{
		if (!$inputObj->IsProduct_idSet()) {
			throw new WxPayException('生成二维码，缺少必填参数product_id！');
		}

		$inputObj->SetAppid($this->config->getAppId());//公众账号ID
		$inputObj->SetMch_id($this->config->getMchId());//商户号
		$inputObj->SetTime_stamp(time());//时间戳
		$inputObj->SetNonce_str(self::getNonceStr());//随机字符串

		$inputObj->SetSign($this->config->getKey());//签名

		return $inputObj->GetValues();
	}

	/**
	 * 转换短链接
	 * 该接口主要用于扫码原生支付模式一中的二维码链接转成短链接(weixin://wxpay/s/XXXXXX)，
	 * 减小二维码数据量，提升扫描速度和精确度。
	 * appid、mchid、spbill_create_ip、nonce_str不需要填入
	 * @param WxPayShortUrl $inputObj
	 * @param int           $timeOut
	 * @throws WxPayException
	 * @return array 成功时返回，其他抛异常
	 */
	public function shorturl($inputObj, $timeOut = 6)
	{
		$url = 'https://api.mch.weixin.qq.com/tools/shorturl';
		//检测必填参数
		if (!$inputObj->IsLong_urlSet()) {
			throw new WxPayException('需要转换的URL，签名用原串，传输需URL encode！');
		}
		$inputObj->SetAppid($this->config->getAppId());//公众账号ID
		$inputObj->SetMch_id($this->config->getMchId());//商户号
		$inputObj->SetNonce_str(self::getNonceStr());//随机字符串

		$inputObj->SetSign($this->config->getKey());//签名
		$xml = $inputObj->ToXml();

		$startTimeStamp = self::getMillisecond();//请求开始时间
		$response       = self::postXmlCurl($xml, $url, false, $timeOut);
		$result         = WxPayResults::Init($response, $this->config);
		self::reportCostTime($url, $startTimeStamp, $result);//上报请求花费时间

		return $result;
	}

	/**
	 * 支付结果通用通知
	 * @param callable $callback
	 * 直接回调函数使用方法: notify(you_function);
	 * 回调类成员函数方法:notify(array($this, you_function));
	 * $callback  原型为：function function_name($data){}
	 * @param          $msg
	 * @return bool|mixed
	 */
	public function notify($callback, &$msg)
	{
		//获取通知的数据
		$xml = \Input::getContent();
		//如果返回成功则验证签名
		try {
			$result = WxPayResults::Init($xml, $this->config);
		} catch (WxPayException $e) {
			$msg = $e->errorMessage();

			return false;
		}

		return call_user_func($callback, $result);
	}

	/**
	 * 产生随机字符串，不长于32位
	 * @param int $length
	 * @return string 产生的随机字符串
	 */
	public function getNonceStr($length = 32)
	{
		$chars = 'abcdefghijklmnopqrstuvwxyz0123456789';
		$str   = '';
		for ($i = 0; $i < $length; $i++) {
			$str .= substr($chars, mt_rand(0, strlen($chars) - 1), 1);
		}

		return $str;
	}

	/**
	 * 直接输出xml
	 * @param string $xml
	 */
	public static function replyNotify($xml)
	{
		echo $xml;
	}

	/**
	 * code 换取 session_key
	 * ​这是一个 HTTPS 接口，开发者服务器使用登录凭证 code 获取 session_key 和 openid。
	 * session_key 是对用户数据进行加密签名的密钥。为了自身应用安全，session_key 不应该在网络上传输
	 * https://mp.weixin.qq.com/debug/wxadoc/dev/api/api-login.html#code-换取-sessionkey
	 * @param string $code
	 * @return array|mixed
	 * @throws WxPayException
	 * @throws \ErrorException
	 */
	public function code2Session($code)
	{
		$url  = 'https://api.weixin.qq.com/sns/jscode2session';
		$req  = [
			'appid'      => $this->config->getAppId(),
			'secret'     => $this->config->getAppSecret(),
			'js_code'    => $code,
			'grant_type' => 'authorization_code',
		];
		$curl = new Curl();
		$curl->setTimeout(3);
		$data = $curl->get($url, $req);
		if ($curl->error) {
			throw new WxPayException('curl出错，错误码:' . $curl->errorCode);
		}
		 
			return json_decode($data, true);
	}

	/**
	 * 获取毫秒级别的时间戳
	 */
	private function getMillisecond()
	{
		//获取毫秒的时间戳
		$time  = explode(' ', microtime());
		$time  = $time[1] . ($time[0] * 1000);
		$time2 = explode('.', $time);
		$time  = $time2[0];

		return $time;
	}

	/**
	 * 以post方式提交xml到对应的接口url
	 * @param string $xml     需要post的xml数据
	 * @param string $url     url
	 * @param bool   $useCert 是否需要证书，默认不需要
	 * @param int    $second  url执行超时时间，默认30s
	 * @return mixed
	 * @throws WxPayException
	 */
	private function postXmlCurl($xml, $url, $useCert = false, $second = 30)
	{
		$ch = curl_init();
		//设置超时
		curl_setopt($ch, CURLOPT_TIMEOUT, $second);

		//如果有配置代理这里就设置代理
		if (
			$this->config->getProxyHost() != '0.0.0.0'
			&&
			$this->config->getProxyPort() != 0
		) {
			curl_setopt($ch, CURLOPT_PROXY, $this->config->getProxyHost());
			curl_setopt($ch, CURLOPT_PROXYPORT, $this->config->getProxyPort());
		}
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
		curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);//严格校验
		//设置header
		curl_setopt($ch, CURLOPT_HEADER, false);
		//要求结果为字符串且输出到屏幕上
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

		if ($useCert == true) {
			//设置证书
			//使用证书：cert 与 key 分别属于两个.pem文件
			curl_setopt($ch, CURLOPT_SSLCERTTYPE, 'PEM');
			curl_setopt($ch, CURLOPT_SSLCERT, $this->config->getSslCertPath());
			curl_setopt($ch, CURLOPT_SSLKEYTYPE, 'PEM');
			curl_setopt($ch, CURLOPT_SSLKEY, $this->config->getSslKeyPath());
		}
		//post提交方式
		curl_setopt($ch, CURLOPT_POST, true);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $xml);
		//运行curl
		$data = curl_exec($ch);
		//返回结果
		if ($data) {
			curl_close($ch);

			return $data;
		}
		 
			$error = curl_errno($ch);
			curl_close($ch);
			throw new WxPayException("curl出错，错误码:$error");
	}

	/**
	 * 上报数据， 上报的时候将屏蔽所有异常流程
	 * @param string $url
	 * @param int    $startTimeStamp
	 * @param array  $data
	 */
	private function reportCostTime($url, $startTimeStamp, $data)
	{
		//如果不需要上报数据
		if (WxPayConfig::REPORT_LEVEL == 0) {
			return;
		}
		//如果仅失败上报
		if (WxPayConfig::REPORT_LEVEL == 1 &&
			array_key_exists('return_code', $data) &&
			$data['return_code'] == 'SUCCESS' &&
			array_key_exists('result_code', $data) &&
			$data['result_code'] == 'SUCCESS') {
			return;
		}

		//上报逻辑
		$endTimeStamp = self::getMillisecond();
		$objInput     = new WxPayReport();
		$objInput->SetInterface_url($url);
		$objInput->SetExecute_time_($endTimeStamp - $startTimeStamp);
		//返回状态码
		if (array_key_exists('return_code', $data)) {
			$objInput->SetReturn_code($data['return_code']);
		}
		//返回信息
		if (array_key_exists('return_msg', $data)) {
			$objInput->SetReturn_msg($data['return_msg']);
		}
		//业务结果
		if (array_key_exists('result_code', $data)) {
			$objInput->SetResult_code($data['result_code']);
		}
		//错误代码
		if (array_key_exists('err_code', $data)) {
			$objInput->SetErr_code($data['err_code']);
		}
		//错误代码描述
		if (array_key_exists('err_code_des', $data)) {
			$objInput->SetErr_code_des($data['err_code_des']);
		}
		//商户订单号
		if (array_key_exists('out_trade_no', $data)) {
			$objInput->SetOut_trade_no($data['out_trade_no']);
		}
		//设备号
		if (array_key_exists('device_info', $data)) {
			$objInput->SetDevice_info($data['device_info']);
		}

		try {
			self::report($objInput);
		} catch (WxPayException $e) {
			//不做任何处理
		}
	}
}

