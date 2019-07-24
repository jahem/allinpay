<?php

namespace Allinpay;

/**
 * 通联收银宝
 * 快捷支付 + H5收银台
 * @author Jahem
 */
class Allinpay {

    static $ambient; //0 测试 1 正式
    static $config; //参数
    static $return; //返回格式 0 返回curl结果 1 返回curl结果+curl提交数据 返回webPage的无论什么状态都是返回curl结果

    /**
     * 初始化
     * @param type $config
     */

    public function __construct($config = [], $ambient = 0, $return = 0) {
        if (empty($config)) {
            die('配置参数为空');
        }
        self::$config = $config;
        self::$ambient = $ambient;
        self::$return = $return;
    }

    /**
     * 异步通知
     * @return json
     */
    public function UnifiedNotifyurl() {
        $data = self::$config;
        $APPKEY = $data['key'] ?? '';
        $res = [];
        $params = array();
        //动态遍历获取所有收到的参数,此步非常关键,因为收银宝以后可能会加字段,动态获取可以兼容由于收银宝加字段而引起的签名异常
        foreach ($_POST as $key => $val) {
            $params[$key] = $val;
        }
        //如果参数为空,则不进行处理
        if (count($params) < 1) {
            $res = ['sta' => 1, 'msg' => "如果参数为空,则不进行处理"];
            return self::Response(json_encode($res));
        }
        $verifyvalue = "";
        //验签成功
        if (allinpay::ValidSign($params, $APPKEY)) {
            //此处进行业务逻辑处理
            $verifyvalue = "报文验签成功";
        } else {
            $verifyvalue = "报文验签失败";
            $res = ['sta' => 2, 'msg' => "报文验签失败"];
            return self::Response(json_encode($res));
        }
        //状态码对应信息
        $trxstatusArr = [
            2008 => "交易处理中,请查询交易,如果是实时交易(例如刷卡支付,交易撤销,退货),建议每隔一段时间(10秒)查询交易",
            2000 => "交易处理中,请查询交易,如果是实时交易(例如刷卡支付,交易撤销,退货),建议每隔一段时间(10秒)查询交易",
            3 => "开头的错误码代表交易失败",
            3888 => "流水号重复",
            3099 => "渠道商户错误",
            3014 => "交易金额小于应收手续费",
            3031 => "校验实名信息失败",
            3088 => "交易未支付(在查询时间区间内未成功支付,如已影响资金24小时内会做差错退款处理)",
            3089 => "撤销异常,如已影响资金24小时内会做差错退款处",
        ];
        $trxstatus = $params['trxstatus'];
        if ($trxstatus == "0000") {
            //此处进行支付成功业务逻辑处理
            $res = ['sta' => 0, 'msg' => "通知成功"];
            return self::Response(json_encode($res), $params);
        } else {
            $msg = !!$_POST['errmsg'] ? $_POST['errmsg'] : $trxstatusArr[$trxstatus] ?? '未知';
            $res = ['sta' => 3, 'msg' => $msg];
            return self::Response(json_encode($res));
        }
    }

    /**
     * 交易查询
     * @return json
     */
    public function UnifiedQuery() {
        //生产环境地址
        $live_url = "https://vsp.allinpay.com/apiweb/unitorder/query";
        //测试环境地址
        $test_url = "https://test.allinpaygd.com/apiweb/unitorder/query";
        $url = self::$ambient == 1 ? $live_url : $test_url;
        $data = self::$config;
        $payData = [
            'cusid' => $data['cusid'] ?? '', //商户号
            'appid' => $data['appid'] ?? '', //应用ID
            'version' => $data['version'] ?? '', //版本号
            'reqsn' => $data['reqsn'] ?? '', //商户订单号
            'trxid' => $data['trxid'] ?? '', //平台交易流水
            'randomstr' => $data['randomstr'] ?? self::createNonceStr(), //随机字符串
            'key' => $data['key'] ?? '',
        ];
        $payData['sign'] = self::SignArray($payData); //签名
        $res = self::curl_post_https($url, $payData);
        return $res;
    }

    /**
     * 交易退款
     * @return json
     */
    public function UnifiedRefund() {
        //生产环境地址
        $live_url = "https://vsp.allinpay.com/apiweb/unitorder/refund";
        //测试环境地址
        $test_url = "https://test.allinpaygd.com/apiweb/unitorder/refund";
        $url = self::$ambient == 1 ? $live_url : $test_url;
        $data = self::$config;
        $payData = [
            'cusid' => $data['cusid'] ?? '', //商户号
            'appid' => $data['appid'] ?? '', //应用ID
            'version' => $data['version'] ?? '', //版本号
            'trxamt' => $data['trxamt'] ?? '', //退款金额
            'reqsn' => $data['reqsn'] ?? '', //商户退款订单号
            'oldreqsn' => $data['oldreqsn'] ?? '', //原交易订单号
            'oldtrxid' => $data['oldtrxid'] ?? '', //原交易流水
            'remark' => $data['remark'] ?? '', //备注
            'randomstr' => $data['randomstr'] ?? self::createNonceStr(), //随机字符串
            'key' => $data['key'] ?? '',
        ];
        $payData['sign'] = self::SignArray($payData); //签名
        $res = self::curl_post_https($url, $payData);
        return $res;
    }

    /**
     * 交易撤销
     * @return json
     */
    public function UnifiedCancel() {
        //生产环境地址
        $live_url = "https://vsp.allinpay.com/apiweb/unitorder/cancel";
        //测试环境地址
        $test_url = "https://test.allinpaygd.com/apiweb/unitorder/cancel";
        $url = self::$ambient == 1 ? $live_url : $test_url;
        $data = self::$config;
        $payData = [
            'cusid' => $data['cusid'] ?? '', //商户号
            'appid' => $data['appid'] ?? '', //应用ID
            'version' => $data['version'] ?? '', //版本号
            'reqsn' => $data['reqsn'] ?? '', //商户退款交易单号
            'trxamt' => $data['trxamt'] ?? '', //交易金额
            'oldreqsn' => $data['oldreqsn'] ?? '', //原交易单号
            'oldtrxid' => $data['oldtrxid'] ?? '', //原交易流水
            'randomstr' => $data['randomstr'] ?? self::createNonceStr(), //随机字符串
            'key' => $data['key'] ?? '',
        ];
        $payData['sign'] = self::SignArray($payData); //签名
        $res = self::curl_post_https($url, $payData);
        return $res;
    }

    /**
     * 微信APP支付接口
     * @return json
     */
    public function UnifiedAppPay() {
        //生产环境地址
        $live_url = "https://vsp.allinpay.com/apiweb/unitorder/h5pay";
        //测试环境地址
        $test_url = "https://test.allinpaygd.com/apiweb/unitorder/h5pay";
        $url = self::$ambient == 1 ? $live_url : $test_url;
        $data = self::$config;
        $payData = [
            'cusid' => $data['cusid'] ?? '', //商户号
            'appid' => $data['appid'] ?? '', //应用ID
            'version' => $data['version'] ?? '', //版本号
            'trxamt' => $data['trxamt'] ?? '', //交易金额
            'reqsn' => $data['reqsn'] ?? self::GenerateOrderNumber(), //商户交易单号
            'paytype' => $data['paytype'] ?? '', //交易方式
            'body' => $data['body'] ?? '', //订单标题
            'remark' => $data['remark'] ?? '', //备注
            'validtime' => $data['validtime'] ?? '', //有效时间
            'notify_url' => $data['notify_url'] ?? '', //交易结果通知地址
            'limit_pay' => $data['limit_pay'] ?? '', //支付限制
            'apptype' => $data['apptype'] ?? '', //商户app类型
            'appname' => $data['appname'] ?? '', //商户app名称
            'apppackage' => $data['apppackage'] ?? '', //商户app包名
            'cusip' => $data['cusip'] ?? '', //终端ip
            'idno' => $data['idno'] ?? '', //证件号
            'sub_appid' => $data['sub_appid'] ?? '', //微信子appid
            'truename' => $data['truename'] ?? '', //付款人真实姓名
            'randomstr' => $data['randomstr'] ?? self::createNonceStr(), //随机字符串
            'key' => $data['key'] ?? '',
        ];
        $payData['sign'] = self::SignArray($payData); //签名
        $res = self::curl_post_https($url, $payData);
        return $res;
    }

    /**
     * 统一扫码接口
     * @return json
     */
    public function UnifiedScanqrPay() {
        //生产环境地址
        $live_url = "https://vsp.allinpay.com/apiweb/unitorder/scanqrpay";
        //测试环境地址
        $test_url = "https://test.allinpaygd.com/apiweb/unitorder/scanqrpay";
        $url = self::$ambient == 1 ? $live_url : $test_url;
        $data = self::$config;
        $payData = [
            'cusid' => $data['cusid'] ?? '', //商户号
            'appid' => $data['appid'] ?? '', //应用ID
            'version' => $data['version'] ?? '', //版本号
            'randomstr' => $data['randomstr'] ?? self::createNonceStr(), //随机字符串
            'trxamt' => $data['trxamt'] ?? '', //付款金额
            'reqsn' => $data['reqsn'] ?? self::GenerateOrderNumber(), //商户唯一订单号
            'body' => $data['body'] ?? '', //订单标题
            'remark' => $data['remark'] ?? '', //备注
            'authcode' => $data['authcode'] ?? '', //支付授权码
            'limit_pay' => $data['limit_pay'] ?? '', //支付限制
            'goods_tag' => $data['goods_tag'] ?? '', //订单优惠标记
            'benefitdetail' => $data['benefitdetail'] ?? '', //优惠信息
            'subbranch' => $data['subbranch'] ?? '', //门店号	
            'idno' => $data['idno'] ?? '', //证件号
            'truename' => $data['truename'] ?? '', //付款人真实姓名
            'asinfo' => $data['asinfo'] ?? '', //分账信息
            'signtype' => $data['signtype'] ?? '', //签名方式
            'key' => $data['key'] ?? '',
        ];
        $payData['sign'] = self::SignArray($payData); //签名
        $res = self::curl_post_https($url, $payData);
        return $res;
    }

    /**
     * 统一支付接口
     * @return json
     */
    public function UnifiedApiWeb() {
        //生产环境地址
        $live_url = "https://vsp.allinpay.com/apiweb/unitorder/pay";
        //测试环境地址
        $test_url = "https://test.allinpaygd.com/apiweb/unitorder/pay";
        $url = self::$ambient == 1 ? $live_url : $test_url;
        $data = self::$config;
        $payData = [
            'cusid' => $data['cusid'] ?? '', //商户号
            'appid' => $data['appid'] ?? '', //应用ID
            'version' => $data['version'] ?? '', //版本号
            'trxamt' => $data['trxamt'] ?? '', //付款金额
            'reqsn' => $data['reqsn'] ?? self::GenerateOrderNumber(), //商户唯一订单号
            'paytype' => $data['paytype'] ?? '', //交易方式
            'randomstr' => $data['randomstr'] ?? self::createNonceStr(), //随机字符串
            'body' => $data['body'] ?? '', //订单标题
            'remark' => $data['remark'] ?? '', //备注
            'validtime' => $data['validtime'] ?? '', //有效时间
            'acct' => $data['acct'] ?? '', //支付平台用户标识
            'notify_url' => $data['notify_url'] ?? '', //交易结果通知地址
            'limit_pay' => $data['limit_pay'] ?? '', //支付限制
            'sub_appid' => $data['sub_appid'] ?? '', //微信子appid
            'goods_tag' => $data['goods_tag'] ?? '', //订单优惠标识
            'benefitdetail' => $data['benefitdetail'] ?? '', //优惠信息
            'subbranch' => $data['subbranch'] ?? '', //门店号
            'cusip' => $data['cusip'] ?? '', //终端ip
            'idno' => $data['idno'] ?? '', //证件号
            'truename' => $data['truename'] ?? '', //付款人真实姓名
            'asinfo' => $data['asinfo'] ?? '', //分账信息
            'signtype' => $data['signtype'] ?? '', //签名方式
            'key' => $data['key'] ?? '',
        ];
        $payData['sign'] = self::SignArray($payData); //签名
        $res = self::curl_post_https($url, $payData);
        return $res;
    }

    /**
     * 订单退款接口（商户网站->支付网关）
     * @return json
     */
    public function GatewayRefund() {
        //生产环境地址
        $live_url = "https://vsp.allinpay.com/apiweb/gateway/refund";
        //测试环境地址
        $test_url = "";
        $url = self::$ambient == 1 ? $live_url : $test_url;
        $data = self::$config;
        $payData = [
            'cusid' => $data['cusid'] ?? '', //商户号
            'appid' => $data['appid'] ?? '', //应用ID
            'reqsn' => $data['reqsn'] ?? '', //商户退款流水
            'trxamt' => $data['trxamt'] ?? '', //退款金额
            'orderid' => $data['orderid'] ?? '', //商户订单号
            'trxid' => $data['trxid'] ?? '', //平台交易流水
            'notifyurl' => $data['notifyurl'] ?? '', //服务器异步通知页面路径
            'randomstr' => $data['randomstr'] ?? self::createNonceStr(), //随机字符串 
            'key' => $data['key'] ?? '',
        ];
        $payData['sign'] = self::SignArray($payData); //签名
        $res = self::curl_post_https($url, $payData);
        return $res;
    }

    /**
     * 交易查询接口（商户网站->支付网关）
     * @return json
     */
    public function GatewayQuery() {
        //生产环境地址
        $live_url = "https://vsp.allinpay.com/apiweb/gateway/query";
        //测试环境地址
        $test_url = "";
        $url = self::$ambient == 1 ? $live_url : $test_url;
        $data = self::$config;
        $payData = [
            'cusid' => $data['cusid'] ?? '', //商户号
            'appid' => $data['appid'] ?? '', //应用ID
            'orderid' => $data['orderid'] ?? '', //商户订单号
            'trxid' => $data['trxid'] ?? '', //平台交易流水
            'randomstr' => $data['randomstr'] ?? self::createNonceStr(), //随机字符串 
            'key' => $data['key'] ?? '',
        ];
        $payData['sign'] = self::SignArray($payData); //签名
        $res = self::curl_post_https($url, $payData);
        return $res;
    }

    /**
     * 订单提交接口（商户网站->支付网关）
     * @return webPage
     */
    public function Gateway() {
        //生产环境地址
        $live_url = "https://vsp.allinpay.com/apiweb/gateway/pay";
        //测试环境地址
        $test_url = "";
        $url = self::$ambient == 1 ? $live_url : $test_url;
        $data = self::$config;
        $payData = [
            'cusid' => $data['cusid'] ?? '', //商户号
            'appid' => $data['appid'] ?? '', //应用ID
            'charset' => $data['charset'] ?? '', //参数字符编码集
            'returl' => $data['returl'] ?? '', //页面跳转同步通知页面路径
            'notifyurl' => $data['notifyurl'] ?? "", //服务器异步通知页面路径 
            'goodsid' => $data['goodsid'] ?? "", //商品号
            'goodsinf' => $data['goodsinf'] ?? '', //商品描述信息
            'trxamt' => $data['trxamt'] ?? '', //付款金额
            'orderid' => $data['orderid'] ?? '', //商户唯一订单号
            'randomstr' => $data['randomstr'] ?? self::createNonceStr(), //随机字符串
            'gateid' => $data['gateid'] ?? '', //支付银行
            'paytype' => $data['paytype'] ?? '', //交易类型
            'validtime' => $data['validtime'] ?? '', //有效时间
            'limitpay' => $data['limitpay'] ?? '', //支付限制
            'key' => $data['key'] ?? '',
        ];
        $payData['sign'] = self::SignArray($payData); //签名
        $res = self::curl_post_https($url, $payData);
        return $res;
    }

    /**
     * 交易查询（快捷支付）
     * @return json
     */
    public function PayApplyAgreeQuery() {
        //生产环境地址
        $live_url = "https://vsp.allinpay.com/apiweb/qpay/query";
        //测试环境地址
        $test_url = "http://test.allinpaygd.com/apiweb/qpay/query";
        $url = self::$ambient == 1 ? $live_url : $test_url;
        $data = self::$config;
        $payData = [
            'cusid' => $data['cusid'] ?? '', //商户号
            'appid' => $data['appid'] ?? '', //应用ID
            'version' => $data['version'] ?? '', //版本号
            'reqip' => $data['reqip'] ?? '', //请求ip
            'reqtime' => $data['reqtime'] ?? '', //本次请求时间
            'randomstr' => $data['randomstr'] ?? self::createNonceStr(), //随机字符串
            'orderid' => $data['orderid'] ?? '', //商户的交易订单号
            'trxid' => $data['trxid'] ?? '', //平台交易流水
            'key' => $data['key'] ?? '',
        ];
        $payData['sign'] = self::SignArray($payData); //签名
        $res = self::curl_post_https($url, $payData);
        return $res;
    }

    /**
     * 交易退款（快捷支付）
     * @return json
     */
    public function PayApplyAgreeRefund() {
        //生产环境地址
        $live_url = "https://vsp.allinpay.com/apiweb/qpay/refund";
        //测试环境地址
        $test_url = "http://test.allinpaygd.com/apiweb/qpay/refund";
        $url = self::$ambient == 1 ? $live_url : $test_url;
        $data = self::$config;
        $payData = [
            'cusid' => $data['cusid'] ?? '', //商户号
            'appid' => $data['appid'] ?? '', //应用ID
            'version' => $data['version'] ?? '', //版本号
            'reqip' => $data['reqip'] ?? '', //请求ip
            'reqtime' => $data['reqtime'] ?? '', //本次请求时间
            'randomstr' => $data['randomstr'] ?? self::createNonceStr(), //随机字符串
            'orderid' => $data['orderid'] ?? '', //商户退款交易单号
            'trxamt' => $data['trxamt'] ?? '', //交易金额
            'orderid' => $data['orderid'] ?? '', //商户退款订单号
            'oldorderid' => $data['oldorderid'] ?? '', //原交易订单号
            'oldtrxid' => $data['oldtrxid'] ?? '', //原交易流水
            'key' => $data['key'] ?? '',
        ];
        $payData['sign'] = self::SignArray($payData); //签名
        $res = self::curl_post_https($url, $payData);
        return $res;
    }

    /**
     * 交易撤销（快捷支付）
     * @return json
     */
    public function PayApplyAgreeCancel() {
        //生产环境地址
        $live_url = "https://vsp.allinpay.com/apiweb/qpay/cancel";
        //测试环境地址
        $test_url = "http://test.allinpaygd.com/apiweb/qpay/cancel";
        $url = self::$ambient == 1 ? $live_url : $test_url;
        $data = self::$config;
        $payData = [
            'cusid' => $data['cusid'] ?? '', //商户号
            'appid' => $data['appid'] ?? '', //应用ID
            'version' => $data['version'] ?? '', //版本号
            'reqip' => $data['reqip'] ?? '', //请求ip
            'reqtime' => $data['reqtime'] ?? '', //本次请求时间
            'randomstr' => $data['randomstr'] ?? self::createNonceStr(), //随机字符串
            'orderid' => $data['orderid'] ?? '', //商户退款交易单号
            'trxamt' => $data['trxamt'] ?? '', //交易金额
            'oldorderid' => $data['oldorderid'] ?? '', //原交易单号
            'oldtrxid' => $data['oldtrxid'] ?? '', //原交易流水
            'key' => $data['key'] ?? '',
        ];
        $payData['sign'] = self::SignArray($payData); //签名
        $res = self::curl_post_https($url, $payData);
        return $res;
    }

    /**
     * 银行卡解绑
     * @return json
     */
    public function UnBind() {
        //生产环境地址
        $live_url = "https://vsp.allinpay.com/apiweb/qpay/unbind";
        //测试环境地址
        $test_url = "http://test.allinpaygd.com/apiweb/qpay/unbind";
        $url = self::$ambient == 1 ? $live_url : $test_url;
        $data = self::$config;
        $payData = [
            'cusid' => $data['cusid'] ?? '', //商户号
            'appid' => $data['appid'] ?? '', //应用ID
            'version' => $data['version'] ?? '', //版本号
            'reqip' => $data['reqip'] ?? '', //请求ip
            'reqtime' => $data['reqtime'] ?? '', //本次请求时间
            'randomstr' => $data['randomstr'] ?? self::createNonceStr(), //随机字符串
            'agreeid' => $data['agreeid'] ?? '', //协议编号
            'key' => $data['key'] ?? '',
        ];
        $payData['sign'] = self::SignArray($payData); //签名
        $res = self::curl_post_https($url, $payData);
        return $res;
    }

    /**
     * 协议查询接口
     * @return json
     */
    public function AgreeQuery() {
        //生产环境地址
        $live_url = "https://vsp.allinpay.com/apiweb/qpay/agreequery";
        //测试环境地址
        $test_url = "http://test.allinpaygd.com/apiweb/qpay/agreequery";
        $url = self::$ambient == 1 ? $live_url : $test_url;
        $data = self::$config;
        $payData = [
            'cusid' => $data['cusid'] ?? '', //商户号
            'appid' => $data['appid'] ?? '', //应用ID
            'version' => $data['version'] ?? '', //版本号
            'reqip' => $data['reqip'] ?? '', //请求ip
            'reqtime' => $data['reqtime'] ?? '', //本次请求时间
            'randomstr' => $data['randomstr'] ?? self::createNonceStr(), //随机字符串
            'meruserid' => $data['meruserid'] ?? '', //商户用户号
            'key' => $data['key'] ?? '',
        ];
        $payData['sign'] = self::SignArray($payData); //签名
        $res = self::curl_post_https($url, $payData);
        return $res;
    }

    /**
     * 重新获取支付短信
     * @return json
     */
    public function PaySmsAgree() {
        //生产环境地址
        $live_url = "https://vsp.allinpay.com/apiweb/qpay/paysmsagree";
        //测试环境地址
        $test_url = "http://test.allinpaygd.com/apiweb/qpay/paysmsagree";
        $url = self::$ambient == 1 ? $live_url : $test_url;
        $data = self::$config;
        $payData = [
            'cusid' => $data['cusid'] ?? '', //商户号
            'appid' => $data['appid'] ?? '', //应用ID
            'version' => $data['version'] ?? '', //版本号
            'reqip' => $data['reqip'] ?? '', //请求ip
            'reqtime' => $data['reqtime'] ?? '', //本次请求时间
            'randomstr' => $data['randomstr'] ?? self::createNonceStr(), //随机字符串
            'orderid' => $data['orderid'] ?? '', //商户订单号
            'agreeid' => $data['agreeid'] ?? '', //协议编号
            'thpinfo' => $data['thpinfo'] ?? '', //交易透传信息
            'key' => $data['key'] ?? '',
        ];
        $payData['sign'] = self::SignArray($payData); //签名
        $res = self::curl_post_https($url, $payData);
        return $res;
    }

    /**
     * 商户支付申请
     * @return json
     */
    public function PayApplyAgree() {
        //生产环境地址
        $live_url = "https://vsp.allinpay.com/apiweb/qpay/payapplyagree";
        //测试环境地址
        $test_url = "http://test.allinpaygd.com/apiweb/qpay/payapplyagree";
        $url = self::$ambient == 1 ? $live_url : $test_url;
        $data = self::$config;
        $payData = [
            'cusid' => $data['cusid'] ?? '', //商户号
            'appid' => $data['appid'] ?? '', //应用ID
            'version' => $data['version'] ?? '', //版本号
            'reqip' => $data['reqip'] ?? '', //请求ip
            'reqtime' => $data['reqtime'] ?? '', //本次请求时间
            'randomstr' => $data['randomstr'] ?? self::createNonceStr(), //随机字符串
            'orderid' => $data['orderid'] ?? '', //商户订单号
            'agreeid' => $data['agreeid'] ?? '', //协议编号
            'amount' => $data['amount'] ?? '', //订单金额
            'currency' => $data['currency'] ?? '', //币种
            'subject' => $data['subject'] ?? '', //订单内容
            'validtime' => $data['validtime'] ?? '', //有效时间
            'trxreserve' => $data['trxreserve'] ?? '', //交易备注
            'notifyurl' => $data['notifyurl'] ?? '', //交易结果通知地址
            'asinfo' => $data['asinfo'] ?? '', //分账信息
            'key' => $data['key'] ?? '',
        ];
        $payData['sign'] = self::SignArray($payData); //签名
        $res = self::curl_post_https($url, $payData);
        return $res;
    }

    /**
     * 重发签约短信验证码
     * @return json
     */
    public function AgreeSms() {
        //生产环境地址
        $live_url = "https://vsp.allinpay.com/apiweb/qpay/agreesms";
        //测试环境地址
        $test_url = "http://test.allinpaygd.com/apiweb/qpay/agreesms";
        $url = self::$ambient == 1 ? $live_url : $test_url;
        $data = self::$config;
        $payData = [
            'cusid' => $data['cusid'] ?? '', //商户号
            'appid' => $data['appid'] ?? '', //应用ID
            'version' => $data['version'] ?? '', //版本号
            'reqip' => $data['reqip'] ?? '', //请求ip
            'reqtime' => $data['reqtime'] ?? '', //本次请求时间
            'randomstr' => $data['randomstr'] ?? self::createNonceStr(), //随机字符串
            'meruserid' => $data['meruserid'] ?? '', //商户用户号
            'accttype' => $data['accttype'] ?? '', //卡类型
            'acctno' => $data['acctno'] ?? '', //银行卡号
            'idno' => $data['idno'] ?? '', //证件号
            'acctname' => $data['acctname'] ?? '', //户名
            'mobile' => $data['mobile'] ?? '', //手机号码
            'validdate' => $data['validdate'] ?? '', //有效期
            'cvv2' => $data['cvv2'] ?? '', //Cvv2
            'thpinfo' => $data['thpinfo'] ?? '', //交易透传信息
            'key' => $data['key'] ?? '',
        ];
        $payData['sign'] = self::SignArray($payData); //签名
        $res = self::curl_post_https($url, $payData);
        return $res;
    }

    /**
     * 签约确认
     * @return json
     */
    public function AgreeConfirm() {
        //生产环境地址
        $live_url = "https://vsp.allinpay.com/apiweb/qpay/agreeconfirm";
        //测试环境地址
        $test_url = "http://test.allinpaygd.com/apiweb/qpay/agreeconfirm";
        $url = self::$ambient == 1 ? $live_url : $test_url;
        $data = self::$config;
        $payData = [
            'cusid' => $data['cusid'] ?? '', //商户号
            'appid' => $data['appid'] ?? '', //应用ID
            'version' => $data['version'] ?? '', //版本号
            'reqip' => $data['reqip'] ?? '', //请求ip
            'reqtime' => $data['reqtime'] ?? '', //本次请求时间
            'randomstr' => $data['randomstr'] ?? self::createNonceStr(), //随机字符串
            'meruserid' => $data['meruserid'] ?? '', //商户用户号
            'accttype' => $data['accttype'] ?? '', //卡类型
            'acctno' => $data['acctno'] ?? '', //银行卡号
            'idno' => $data['idno'] ?? '', //证件号
            'acctname' => $data['acctname'] ?? '', //户名
            'mobile' => $data['mobile'] ?? '', //手机号码
            'validdate' => $data['validdate'] ?? '', //有效期
            'cvv2' => $data['cvv2'] ?? '', //Cvv2
            'smscode' => $data['smscode'] ?? '', //短信验证码
            'thpinfo' => $data['thpinfo'] ?? '', //交易透传信息
            'key' => $data['key'] ?? '',
        ];
        $payData['sign'] = self::SignArray($payData); //签名
        $res = self::curl_post_https($url, $payData);
        return $res;
    }

    /**
     * 签约申请
     * @return json
     */
    public function AgreeApply() {
        //生产环境地址
        $live_url = "https://vsp.allinpay.com/apiweb/qpay/agreeapply";
        //测试环境地址
        $test_url = "http://test.allinpaygd.com/apiweb/qpay/agreeapply";
        $url = self::$ambient == 1 ? $live_url : $test_url;
        $data = self::$config;
        $payData = [
            'cusid' => $data['cusid'] ?? '', //商户号
            'appid' => $data['appid'] ?? '', //应用ID
            'version' => $data['version'] ?? '', //版本号
            'reqip' => $data['reqip'] ?? '', //请求ip
            'reqtime' => $data['reqtime'] ?? '', //本次请求时间
            'randomstr' => $data['randomstr'] ?? self::createNonceStr(), //随机字符串
            'meruserid' => $data['meruserid'] ?? '', //商户用户号
            'accttype' => $data['accttype'] ?? '', //卡类型
            'acctno' => $data['acctno'] ?? '', //银行卡号
            'idno' => $data['idno'] ?? '', //证件号
            'acctname' => $data['acctname'] ?? '', //户名
            'mobile' => $data['mobile'] ?? '', //手机号码
            'validdate' => $data['validdate'] ?? '', //有效期
            'cvv2' => $data['cvv2'] ?? '', //Cvv2
            'key' => $data['key'] ?? '',
        ];
        $payData['sign'] = self::SignArray($payData); //签名
        $res = self::curl_post_https($url, $payData);
        return $res;
    }

    /**
     * 交易查询（商户网站->H5收银台）
     * @return json
     */
    public function H5unionPayQuery() {
        //生产环境地址
        $live_url = "https://vsp.allinpay.com/apiweb/unitorder/query";
        //测试环境地址
        $test_url = "https://test.allinpaygd.com/apiweb/unitorder/query";
        $url = self::$ambient == 1 ? $live_url : $test_url;
        $data = self::$config;
        $payData = [
            'cusid' => $data['cusid'] ?? '', //商户号
            'appid' => $data['appid'] ?? '', //应用ID
            'version' => $data['version'] ?? '', //版本号
            'reqsn' => $data['reqsn'] ?? '', //商户订单号
            'trxid' => $data['trxid'] ?? '', //平台交易流水
            'randomstr' => $data['randomstr'] ?? self::createNonceStr(), //随机字符串
            'signtype' => $data['signtype'] ?? '', //签名方式
            'key' => $data['key'] ?? '',
        ];
        $payData['sign'] = self::SignArray($payData); //签名
        $res = self::curl_post_https($url, $payData);
        return $res;
    }

    /**
     * 交易退款（商户网站->H5收银台）
     * @return json
     */
    public function H5unionPayRefund() {
        //生产环境地址
        $live_url = "https://vsp.allinpay.com/apiweb/unitorder/refund";
        //测试环境地址
        $test_url = "https://test.allinpaygd.com/apiweb/unitorder/refund";
        $url = self::$ambient == 1 ? $live_url : $test_url;
        $data = self::$config;
        $payData = [
            'cusid' => $data['cusid'] ?? '', //商户号
            'appid' => $data['appid'] ?? '', //应用ID
            'version' => $data['version'] ?? '', //版本号
            'trxamt' => $data['reqsn'] ?? '', //退款金额
            'reqsn' => $data['trxamt'] ?? '', //商户退款订单号 
            'oldreqsn' => $data['oldreqsn'] ?? '', //原交易订单号 
            'oldtrxid' => $data['oldtrxid'] ?? '', //原交易流水
            'remark' => $data['remark'] ?? '', //备注
            'randomstr' => $data['randomstr'] ?? self::createNonceStr(), //随机字符串
            'signtype' => $data['signtype'] ?? '', //签名方式
            'key' => $data['key'] ?? '',
        ];
        $payData['sign'] = self::SignArray($payData); //签名
        $res = self::curl_post_https($url, $payData);
        return $res;
    }

    /**
     * 交易撤销（商户网站->H5收银台）
     * @return json
     */
    public function H5unionPayCancel() {
        //生产环境地址
        $live_url = "https://vsp.allinpay.com/apiweb/unitorder/cancel";
        //测试环境地址
        $test_url = "https://test.allinpaygd.com/apiweb/unitorder/cancel";
        $url = self::$ambient == 1 ? $live_url : $test_url;
        $data = self::$config;
        $payData = [
            'cusid' => $data['cusid'] ?? '', //商户号
            'appid' => $data['appid'] ?? '', //应用ID
            'version' => $data['version'] ?? '', //版本号
            'reqsn' => $data['reqsn'] ?? '', //商户退款交易单号
            'trxamt' => $data['trxamt'] ?? '', //交易金额
            'oldreqsn' => $data['oldreqsn'] ?? '', //原交易单号 
            'oldtrxid' => $data['oldtrxid'] ?? '', //原交易流水
            'randomstr' => $data['randomstr'] ?? self::createNonceStr(), //随机字符串
            'signtype' => $data['signtype'] ?? '', //签名方式
            'key' => $data['key'] ?? '',
        ];
        $payData['sign'] = self::SignArray($payData); //签名
        $res = self::curl_post_https($url, $payData);
        return $res;
    }

    /**
     * 订单提交接口（商户网站->H5收银台）
     * @return webPage
     */
    public function H5unionPay() {
        //生产环境地址
        $live_url = "https://syb.allinpay.com/apiweb/h5unionpay/unionorder";
        //测试环境地址
        $test_url = "https://test.allinpaygd.com/apiweb/h5unionpay/unionorder";
        $url = self::$ambient == 1 ? $live_url : $test_url;
        $data = self::$config;
        $payData = [
            'cusid' => $data['cusid'] ?? '', //商户号
            'appid' => $data['appid'] ?? '', //应用ID
            'version' => $data['version'] ?? '', //版本号
            'trxamt' => $data['trxamt'] ?? '', //付款金额
            'reqsn' => $data['reqsn'] ?? self::GenerateOrderNumber(), //商户唯一订单号
            'charset' => $data['charset'] ?? '', //参数字符编码集
            'returl' => $data['returl'] ?? '', //页面跳转同步通知页面路径
            'notify_url' => $data['notify_url'] ?? '', //服务器异步通知页面路径
            'body' => $data['body'] ?? '', //订单标题
            'remark' => $data['remark'] ?? '', //订单备注信息
            'randomstr' => $data['randomstr'] ?? self::createNonceStr(), //随机字符串
            'validtime' => $data['validtime'] ?? '', //有效时间
            'limit_pay' => $data['limit_pay'] ?? '', //支付限制
            'asinfo' => $data['asinfo'] ?? '', //分账信息
            'key' => $data['key'] ?? '',
        ];
        $payData['sign'] = self::SignArray($payData); //签名
        $res = self::curl_post_https($url, $payData);
        return $res;
    }

    /**
     * 将参数数组签名
     */
    public static function SignArray(array $array, $appkey = '') {
        if ($appkey != '') {
            $array['key'] = $appkey; // 将key放到数组中一起进行排序和组装
        }
        ksort($array);
        $blankStr = allinpay::ToUrlParams($array);
        $sign = md5($blankStr);
        return $sign;
    }

    public static function ToUrlParams(array $array) {
        $buff = "";
        foreach ($array as $k => $v) {
            if ($v != "" && !is_array($v)) {
                $buff .= $k . "=" . $v . "&";
            }
        }

        $buff = trim($buff, "&");
        return $buff;
    }

    /**
     * 校验签名
     * @param array 参数
     * @param unknown_type appkey
     */
    public static function ValidSign(array $array, $appkey) {
        $sign = $array['sign'];
        unset($array['sign']);
        $array['key'] = $appkey;
        $mySign = allinpay::SignArray($array, $appkey);
        return strtolower($sign) == strtolower($mySign);
    }

    /**
     * 生成订单号 stirng
     * @return string
     */
    protected static function GenerateOrderNumber() {
        return date('YmdHis') . '_' . rand(11111, 99999);
    }

    /**
     * 创建随机随机数
     * @param int $length 长度
     * @return String 随机数字符串
     */
    protected static function createNonceStr($length = 16) {
        $chars = "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789";
        $str = "";
        for ($i = 0; $i < $length; $i++) {
            $str .= substr($chars, mt_rand(0, strlen($chars) - 1), 1);
        }
        return $str;
    }

    /**
     * httpGet 提交方式
     * @param String $url 地址
     * @param bool $json 是否要返回json型数据
     * @return type 结果
     */
    protected static function curl_get_https($url, $json = false) {
        $curl = curl_init(); // 启动一个CURL会话
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_HEADER, 0);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false); // 跳过证书检查
        // curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, true);  // 从证书中检查SSL加密算法是否存在
        $tmpInfo = curl_exec($curl);     //返回api的json对象
        //关闭URL请求
        curl_close($curl);
        return $json === false ? $tmpInfo : json_decode($tmpInfo); //返回json对象
    }

    /**
     * httpPost 提交方式
     * @param String $url 地址
     * @param String $post_date 提交数据
     * @return type 结果
     */
    protected static function curl_post_https($url = '', $post_date) {
        if (!$url) {
            return 'Errno:Post Url Not Null'; //提交URL不能为空
        }
        $curl = curl_init(); // 启动一个CURL会话
        curl_setopt($curl, CURLOPT_URL, $url); // 要访问的地址
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, 0); // 对认证证书来源的检查
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 0); // 从证书中检查SSL加密算法是否存在
        curl_setopt($curl, CURLOPT_USERAGENT, $_SERVER['HTTP_USER_AGENT']); // 模拟用户使用的浏览器
        curl_setopt($curl, CURLOPT_FOLLOWLOCATION, 1); // 使用自动跳转
        curl_setopt($curl, CURLOPT_AUTOREFERER, 1); // 自动设置Referer
        curl_setopt($curl, CURLOPT_POST, 1); // 发送一个常规的Post请求
        curl_setopt($curl, CURLOPT_POSTFIELDS, http_build_query($post_date)); // Post提交的数据包
        curl_setopt($curl, CURLOPT_TIMEOUT, 30); // 设置超时限制防止死循环
        curl_setopt($curl, CURLOPT_HEADER, 0); // 显示返回的Header区域内容
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1); // 获取的信息以文件流的形式返回
        $headers = array('Content-Type: application/x-www-form-urlencoded');
        curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
        $result = curl_exec($curl); // 执行操作
        if (curl_errno($curl)) {
            return 'Errno' . curl_error($curl); //捕抓异常
        }
        curl_close($curl); // 关闭CURL会话
        return self::Response($result, $post_date);
    }

    /**
     * 判断是否json
     * @param type $string
     * @return bool
     */
    protected static function is_json($string) {
        json_decode($string);
        return (json_last_error() == JSON_ERROR_NONE);
    }

    /**
     * 返回方法
     * @param type $result curl返回数据
     * @param type $post_date curl提交数据
     * @return type
     */
    protected static function Response($result, $post_date = []) {
        if (self::$return == 0) {
            return $result;
        } else {
            if (is_array($result)) {
                return json_encode([
                    'result' => $result,
                    'post_date' => $post_date
                ]);
            }
            if (self::is_json($result)) {
                return json_encode([
                    'result' => json_decode($result, TRUE),
                    'post_date' => $post_date
                ]);
            } else {
                return $result;
            }
        }
    }

}
