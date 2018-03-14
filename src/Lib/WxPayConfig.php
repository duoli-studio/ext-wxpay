<?php namespace Poppy\Extension\Wxpay\Lib;

/*
|--------------------------------------------------------------------------
| 微信支付动态设置APPID和MCHID等参数
|--------------------------------------------------------------------------
*/

/**
 *    配置账号信息
 */
class WxPayConfig
{
	/**
	 * 绑定支付的APPID（必须配置，开户邮件中可查看）
	 * @var string
	 */
	private $appId;

	/**
	 * 商户号（必须配置，开户邮件中可查看）
	 * @var string
	 */
	private $mchId;

	/**
	 * 商户支付密钥，参考开户邮件设置（必须配置，登录商户平台自行设置）
	 * 设置地址：https://pay.weixin.qq.com/index.php/account/api_cert
	 * @var string
	 */
	private $key;

	/**
	 * 公众帐号secret（仅JSAPI支付的时候需要配置， 登录公众平台，进入开发者中心可设置）
	 * 获取地址：https://mp.weixin.qq.com/advanced/advanced?action=dev&t=advanced/dev&token=2005451881&lang=zh_CN
	 * @var string
	 */
	private $appSecret;

	/*
	|--------------------------------------------------------------------------
	| 代理设置
	|--------------------------------------------------------------------------
	| 这里设置代理机器，只有需要代理的时候才设置，不需要代理，请设置为0.0.0.0和0
	| 本例程通过curl使用HTTP POST方法，此处可修改代理服务器，
	| 默认 HOST = 0.0.0.0 和 PORT = 0，此时不开启代理（如有需要才设置）
	|
	*/
	/**
	 * @var string curl代理Host
	 */
	private $proxyHost = '0.0.0.0';

	/**
	 * @var int curl代理 端口
	 */
	private $proxyPort = 0;

	/*
	|--------------------------------------------------------------------------
	| 证书路径设置
	|--------------------------------------------------------------------------
	| 设置商户证书路径
	| 证书路径,注意应该填写绝对路径（仅退款、撤销订单时需要，可登录商户平台下载，
	| API证书下载地址：https://pay.weixin.qq.com/index.php/account/api_cert，下载之前需要安装商户操作证书）
	|
	*/

	/**
	 * @var string 证书pem格式
	 */
	private $sslCertPath = '';

	/**
	 * @var string 证书密钥pem格式
	 */
	private $sslKeyPath = '';

	/*
	|--------------------------------------------------------------------------
	| 上报信息配置
	|--------------------------------------------------------------------------
	| 接口调用上报等级，默认紧错误上报（注意：上报超时间为【1s】，上报无论成败【永不抛出异常】，
	| 不会影响接口调用流程），开启上报之后，方便微信监控请求调用的质量，建议至少
	| 开启错误上报。
	| 上报等级，0.关闭上报; 1.仅错误出错上报; 2.全量上报
	|
	*/
	const REPORT_LEVEL = 1;

	public function setAppId($app_id)
	{
		$this->appId = $app_id;
	}

	public function setMchId($mch_id)
	{
		$this->mchId = $mch_id;
	}

	public function setKey($key)
	{
		$this->key = $key;
	}

	public function setAppSecret($appSecret)
	{
		$this->appSecret = $appSecret;
	}

	public function getAppId()
	{
		return $this->appId;
	}

	public function getMchId()
	{
		return $this->mchId;
	}

	public function getKey()
	{
		return $this->key;
	}

	public function getAppSecret()
	{
		return $this->appSecret;
	}

	/**
	 * @return string
	 */
	public function getProxyHost(): string
	{
		return $this->proxyHost;
	}

	/**
	 * @param string $proxy_host
	 */
	public function setProxyHost(string $proxy_host)
	{
		$this->proxyHost = $proxy_host;
	}

	/**
	 * @return int
	 */
	public function getProxyPort(): int
	{
		return $this->proxyPort;
	}

	/**
	 * @param int $proxy_port
	 */
	public function setProxyPort(int $proxy_port)
	{
		$this->proxyPort = $proxy_port;
	}

	/**
	 * @return string
	 */
	public function getSslCertPath(): string
	{
		return $this->sslCertPath;
	}

	/**
	 * @param string $sslCertPath
	 */
	public function setSslCertPath(string $sslCertPath)
	{
		$this->sslCertPath = $sslCertPath;
	}

	/**
	 * @return string
	 */
	public function getSslKeyPath(): string
	{
		return $this->sslKeyPath;
	}

	/**
	 * @param string $sslKeyPath
	 */
	public function setSslKeyPath(string $sslKeyPath)
	{
		$this->sslKeyPath = $sslKeyPath;
	}
}
