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

    /**
     * 初始化
     * @param type $config
     */

    public function __construct($config = [], $ambient = 0) {
        if (empty($config)) {
            die('配置参数为空');
        }
        self::$config = $config;
        self::$ambient = $ambient;
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
        if (self::$ambient == 1) {
            $url = $live_url;
        } else {
            $url = $test_url;
        }
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
        if (self::$ambient == 1) {
            $url = $live_url;
        } else {
            $url = $test_url;
        }
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
        if (self::$ambient == 1) {
            $url = $live_url;
        } else {
            $url = $test_url;
        }
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
        if (self::$ambient == 1) {
            $url = $live_url;
        } else {
            $url = $test_url;
        }
        $data = self::$config;
        $payData = [
            'cusid' => $data['cusid'] ?? '', //商户号
            'appid' => $data['appid'] ?? '', //应用ID
            'version' => $data['version'] ?? '', //版本号
            'reqip' => $data['reqip'] ?? '', //请求ip
            'reqtime' => $data['reqtime'] ?? '', //本次请求时间
            'randomstr' => $data['randomstr'] ?? self::createNonceStr(), //随机字符串
            'agreeid' => $data['agreeid'] ?? '', //协议编号
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
        if (self::$ambient == 1) {
            $url = $live_url;
        } else {
            $url = $test_url;
        }
        $data = self::$config;
        $payData = [
            'cusid' => $data['cusid'] ?? '', //商户号
            'appid' => $data['appid'] ?? '', //应用ID
            'version' => $data['version'] ?? '', //版本号
            'reqip' => $data['reqip'] ?? '', //请求ip
            'reqtime' => $data['reqtime'] ?? '', //本次请求时间
            'randomstr' => $data['randomstr'] ?? self::createNonceStr(), //随机字符串
            'meruserid' => $data['meruserid'] ?? '', //商户用户号
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
        if (self::$ambient == 1) {
            $url = $live_url;
        } else {
            $url = $test_url;
        }
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
        if (self::$ambient == 1) {
            $url = $live_url;
        } else {
            $url = $test_url;
        }
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
        if (self::$ambient == 1) {
            $url = $live_url;
        } else {
            $url = $test_url;
        }
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
        if (self::$ambient == 1) {
            $url = $live_url;
        } else {
            $url = $test_url;
        }
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
        if (self::$ambient == 1) {
            $url = $live_url;
        } else {
            $url = $test_url;
        }
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
        ];
        $payData['sign'] = self::SignArray($payData); //签名
        $res = self::curl_post_https($url, $payData);
        return $res;
    }

    /**
     * 交易查询（商户网站->H5收银台）
     * @return json
     */
    public function UnionOrderQuery() {
        //生产环境地址
        $live_url = "https://vsp.allinpay.com/apiweb/unitorder/query";
        //测试环境地址
        $test_url = "https://test.allinpaygd.com/apiweb/unitorder/query";
        if (self::$ambient == 1) {
            $url = $live_url;
        } else {
            $url = $test_url;
        }
        $data = self::$config;
        $payData = [
            'cusid' => $data['cusid'] ?? '', //商户号
            'appid' => $data['appid'] ?? '', //应用ID
            'version' => $data['version'] ?? '', //版本号
            'reqsn' => $data['reqsn'] ?? '', //商户订单号
            'trxid' => $data['trxid'] ?? '', //平台交易流水
            'randomstr' => $data['randomstr'] ?? self::createNonceStr(), //随机字符串
            'signtype' => $data['signtype'] ?? '', //签名方式
        ];
        $payData['sign'] = self::SignArray($payData); //签名
        $res = self::curl_post_https($url, $payData);
        return $res;
    }

    /**
     * 交易退款（商户网站->H5收银台）
     * @return json
     */
    public function UnionOrderRefund() {
        //生产环境地址
        $live_url = "https://vsp.allinpay.com/apiweb/unitorder/refund";
        //测试环境地址
        $test_url = "https://test.allinpaygd.com/apiweb/unitorder/refund";
        if (self::$ambient == 1) {
            $url = $live_url;
        } else {
            $url = $test_url;
        }
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
        ];
        $payData['sign'] = self::SignArray($payData); //签名
        $res = self::curl_post_https($url, $payData);
        return $res;
    }

    /**
     * 交易撤销（商户网站->H5收银台）
     * @return json
     */
    public function UnionOrderCancel() {
        //生产环境地址
        $live_url = "https://vsp.allinpay.com/apiweb/unitorder/cancel";
        //测试环境地址
        $test_url = "https://test.allinpaygd.com/apiweb/unitorder/cancel";
        if (self::$ambient == 1) {
            $url = $live_url;
        } else {
            $url = $test_url;
        }
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
        ];
        $payData['sign'] = self::SignArray($payData); //签名
        $res = self::curl_post_https($url, $payData);
        return $res;
    }

    /**
     * 订单提交接口（商户网站->H5收银台）
     * @return webPage
     */
    public function UnionOrder() {
        //生产环境地址
        $live_url = "https://syb.allinpay.com/apiweb/h5unionpay/unionorder";
        //测试环境地址
        $test_url = "https://test.allinpaygd.com/apiweb/h5unionpay/unionorder";
        if (self::$ambient == 1) {
            $url = $live_url;
        } else {
            $url = $test_url;
        }
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
        ];
        $payData['sign'] = self::SignArray($payData); //签名
        $res = self::curl_post_https($url, $payData);
        return $res;
    }

    /**
     * 将参数数组签名
     * @param array $array
     * @return string
     */
    public static function SignArray(array $array) {
        $array = array_filter($array);
        ksort($array);
        $blankStr = self::ToUrlParams($array);
        $sign = md5($blankStr);
        return $sign;
    }

    /**
     * 字典排序
     * @param array $array
     * @return string
     */
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
     * @return boolean
     */
    public static function ValidSign(array $array, $appkey) {
        $sign = $array['sign'];
        unset($array['sign']);
        $array['key'] = $appkey;
        $mySign = self::SignArray($array, $appkey);
        return strtolower($sign) == strtolower($mySign);
    }

    /**
     * 生成订单号 stirng
     * @return string
     */
    public static function GenerateOrderNumber() {
        return date('YmdHis') . rand(11111, 99999);
    }

    /**
     * 创建随机随机数
     * @param int $length 长度
     * @return String 随机数字符串
     */
    public static function createNonceStr($length = 16) {
        $chars = "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789";
        $str = "";
        for ($i = 0; $i < $length; $i++) {
            $str .= substr($chars, mt_rand(0, strlen($chars) - 1), 1);
        }
        return $str;
    }

    /**
     * 备用httpGet 提交方式
     * @param String $url 地址
     * @param bool $json 是否要返回json型数据
     * @return type 结果
     */
    public static function curl_get_https($url, $json = false) {
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
    public static function curl_post_https($url, $post_date) {
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
        return $result;
    }

}
