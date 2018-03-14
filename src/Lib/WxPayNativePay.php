<?php namespace Poppy\Extension\Wxpay\Lib;

/**
 * 支付实现类
 * @package App\Lemon\Repositories\Vendor\WxPay
 */
class WxPayNativePay
{
	/**
	 * @var WxPayConfig;
	 */
	private $config;

	/*
	 * 生成扫描支付URL,模式一
	 * @param $bizUrlInfo
	 */
	public function GetPrePayUrl($productId)
	{
		$biz = new WxPayBizPayUrl();
		$biz->SetProduct_id($productId);

		$request = new WxPayRequest($this->config);

		try {
			$values = $request->bizpayurl($biz);
		} catch (\Exception $e) {
			return '';
		}

		$url = 'weixin://wxpay/bizpayurl?' . $this->ToUrlParams($values);

		return $url;
	}

	/**
	 * 生成直接支付url，支付url有效期为2小时,模式二
	 *
	 * @param WxPayUnifiedOrder $input
	 *
	 * @return array|null
	 */
	public function GetPayUrl($input)
	{
		if ($input->GetTrade_type() == 'NATIVE' || $input->GetTrade_type() == 'APP') {
			$request = new WxPayRequest();
			$request->setConfig($this->config);
			try {
				$result = $request->unifiedOrder($input, 30);
			} catch (\Exception $e) {
				return null;
			}

			return $result;
		}

		return null;
	}

	public function setConfig($config)
	{
		$this->config = $config;
	}

	/**
	 * 参数数组转换为url参数
	 *
	 * @param array $urlObj
	 *
	 * @return string
	 */
	private function ToUrlParams($urlObj)
	{
		$buff = '';
		foreach ($urlObj as $k => $v) {
			$buff .= $k . '=' . $v . '&';
		}

		$buff = trim($buff, '&');

		return $buff;
	}
}