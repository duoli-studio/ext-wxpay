# Alipay 在 Laravel 5 的封装包

修改自 [Latrell/Alipay](https://github.com/Latrell/Alipay), 目的是在 Laravel5框架下更便捷使用支付宝付款功能。

## 安装

```
composer require imvkmark/l5-alipay dev-master
```

更新你的依赖包 ```composer update``` 或者全新安装 ```composer install```。

## 发布配置

```
php artisan vendor:publish
```

## 使用

要使用支付宝SDK服务提供者，你必须自己注册服务提供者到Laravel服务提供者列表中。
基本上有两种方法可以做到这一点。

找到 `config/app.php` 配置文件中，key为 `providers` 的数组，在数组中添加服务提供者。

```php
'providers' => [
    // ...
    Poppy\Extension\Alipay\L5AlipayServiceProvider::class,
]
```

运行 `php artisan vendor:publish` 命令，发布配置文件到你的项目中。

配置文件 `config/polly-alipay.php` 为公共配置信息文件， `web_direct_` 为Web版支付宝SDK配置前缀。
配置 回调地址的时候使用 url 函数， 填写的是完整的回调地址， 不是部分地址， 例如填写

```
// 服务器异步通知页面路径
'web_direct_notify_url' => env('URL_SITE') . '/callback/alipay-charge-notify',

// 页面跳转同步通知页面路径
// 这里不支持url， config 函数调用
'web_direct_return_url' => env('URL_SITE') . '/finance/alipay-charge-callback',
```
## 例子

### 支付申请

#### Web 版即时到账 (web_direct)

```php
// 创建支付单。
$alipay = app('l5.alipay.web-direct');
$alipay->setOutTradeNo('order_id');
$alipay->setTotalFee('order_price');
$alipay->setSubject('goods_name');
$alipay->setBody('goods_description');
//该设置为可选，添加该参数设置，支持二维码支付。
$alipay->setQrPayMode(2);

// 跳转到支付页面。
return redirect()->to($alipay->getPayLink());
```

### 结果通知

#### 网页

```php
/**
 * 异步通知
 */
public function webNotify()
{
    // 验证请求。
    if (! app('l5.alipay.web-direct')->verify()) {
        Log::notice('Alipay notify post data verification fail.', [
        'data' => Request::instance()->getContent()
    ]);
    return 'fail';
}

    // 判断通知类型。
    switch (Input::get('trade_status')) {
        case 'TRADE_SUCCESS':
        case 'TRADE_FINISHED':
            // TODO: 支付成功，取得订单号进行其它相关操作。
            Log::debug('Alipay notify post data verification success.', [
                'out_trade_no' => Input::get('out_trade_no'),
                'trade_no' => Input::get('trade_no')
            ]);
        break;
    }
    return 'success';
}

/**
 * 同步通知
 */
public function webReturn()
{
    // 验证请求。
    if (! app('l5.alipay.web-direct')->verify()) {
        Log::notice('Alipay return query data verification fail.', [
            'data' => Request::getQueryString()
        ]);
        return view('alipay.fail');
    }

    // 判断通知类型。
    switch (Input::get('trade_status')) {
        case 'TRADE_SUCCESS':
        case 'TRADE_FINISHED':
            // TODO: 支付成功，取得订单号进行其它相关操作。
            Log::debug('Alipay notify get data verification success.', [
                'out_trade_no' => Input::get('out_trade_no'),
                'trade_no' => Input::get('trade_no')
            ]);
            break;
    }

	return view('alipay.success');
}
```
