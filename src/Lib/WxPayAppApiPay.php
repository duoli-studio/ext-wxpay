<?php namespace Poppy\Extension\Wxpay\Lib;

//APP支付参数
class WxPayAppApiPay extends WxPayDataBase
{

	public function SetAppid($value)
	{
		$this->values['appid'] = $value;
	}

	public function SetTimeStamp($value)
	{
		$this->values['timestamp'] = $value;
	}

	public function SetNonceStr($value)
	{
		$this->values['noncestr'] = $value;
	}

	public function SetPackage($value)
	{
		$this->values['package'] = $value;
	}

	public function SetSign($key)
	{
		$sign                 = $this->MakeNewSign($key);
		$this->values['sign'] = $sign;
		return $sign;
	}

	public function GetValues()
	{
		return $this->values;
	}

	public function SetPartnerId($value)
	{
		$this->values['partnerid'] = $value;
	}

	public function SetPrepayId($value)
	{
		$this->values['prepayid'] = $value;
	}

	public function MakeNewSign($key)
	{
		//签名步骤一：按字典序排序参数
		ksort($this->values);
		$string = $this->ToUrlParams();
		//签名步骤二：在string后加入KEY
		$string = $string . "&key=" . $key;
		// dd($string);
		//签名步骤三：MD5加密
		$string = md5($string);
		//签名步骤四：所有字符转为大写
		$result = strtoupper($string);
		return $result;
	}
}