<?php

namespace PayumTW\Ips;

use Http\Message\MessageFactory;
use Payum\Core\HttpClientInterface;

class Api
{
    /**
     * @var HttpClientInterface
     */
    protected $client;

    /**
     * @var MessageFactory
     */
    protected $messageFactory;

    /**
     * @var array
     */
    protected $options = [];

    /**
     * @var array
     */
    protected $heads = [
        'Version',
        'MerCode',
        'MerName',
        'Account',
        'MsgId',
        'ReqDate',
        'Signature',
        'ReferenceID',
        'RspCode',
        'RspMsg',
        'RspDate',
    ];

    /**
     * @var array
     */
    protected $cdata = [
        'Merchanturl',
        'FailUrl',
        'ServerUrl',
        'GoodsName',
        'MsgId',
        'RspMsg',
        'Msg',
    ];

    /**
     * @param array               $options
     * @param HttpClientInterface $client
     * @param MessageFactory      $messageFactory
     *
     * @throws \Payum\Core\Exception\InvalidArgumentException if an option is invalid
     */
    public function __construct(array $options, HttpClientInterface $client, MessageFactory $messageFactory)
    {
        $this->options = $options;
        $this->client = $client;
        $this->messageFactory = $messageFactory;
    }

    /**
     * getApiEndpoint.
     *
     * @return string
     */
    public function getApiEndpoint()
    {
        return 'https://newpay.ips.com.cn/psfp-entry/gateway/payment.do';
    }

    /**
     * preparePayment.
     *
     * @param array $params
     * @param mixed $request
     *
     * @return array
     */
    public function preparePayment(array $params)
    {
        $supportedParams = [
            'Version' => $this->options['Version'],
            'MerCode' => $this->options['MerCode'],
            'MerName' => $this->options['MerName'],
            'Account' => $this->options['Account'],
            'MsgId' => null,
            'ReqDate' => date('YmdHis'),

            'MerBillNo' => null,
            'GatewayType' => '01',
            'Date' => date('Ymd'),
            'CurrencyType' => 156,
            'Amount' => 0,
            'Lang' => 'GB',
            'Merchanturl' => null,
            'FailUrl' => null,
            'Attach' => null,
            /*
             * 说明：存放商户所选择订单支 付接口加密方式。
             * 5#订单支付采用 Md5 的摘要 讣证方式
             */
            'OrderEncodeType' => 5,
            /*
             *返回给商户其所选用的交易 返回签名方式
             *16#Md5WithRsa 数字签 名方式
             *17#Md5数字签名方式
             */
            'RetEncodeType' => 17,
            /*
             * Server to Server 返回。
             * 1#S2S返回
             */
            'RetType' => 1,
            'ServerUrl' => null,
            'BillEXP' => null,
            'GoodsName' => null,
            'IsCredit' => null,
            'BankCode' => null,
            'ProductType' => null,
        ];

        $params = array_filter(array_replace(
            $supportedParams,
            array_intersect_key($params, $supportedParams)
        ));

        return [
            'pGateWayReq' => $this->generatGetwayRequest($params),
        ];
    }

    /**
     * generatGetwayRequest.
     *
     * @method generatGetwayRequest
     *
     * @param array $params
     *
     * @return string
     */
    protected function generatGetwayRequest($params)
    {
        $params['Signature'] = $this->calculateHash($params);
        $params = $this->split($this->addCdata($params));

        return $this->convertToXML([
            'Ips' => [
                'GateWayReq' => $params,
            ],
        ]);
    }

    /**
     * convertToXML.
     *
     * @method convertToXML
     *
     * @param array $params
     *
     * @return string
     */
    protected function convertToXML($params)
    {
        $xml = '';
        foreach ($params as $key => $value) {
            if (is_array($value) === true) {
                $value = $this->convertToXML($value);
            }

            $xml .= '<'.$key.'>'.$value.'</'.$key.'>';
        }

        return $xml;
    }

    /**
     * split.
     *
     * @method split
     *
     * @param array $params
     *
     * @return array
     */
    protected function split($params)
    {
        $head = [];
        $body = [];

        foreach ($params as $key => $value) {
            if (in_array($key, $this->heads, true) === true) {
                $head[$key] = $value;
            } else {
                $body[$key] = $value;
            }
        }

        return compact('head', 'body');
    }

    /**
     * addCdata.
     *
     * @method addCdata
     *
     * @param array $params
     *
     * @return array
     */
    protected function addCdata($params)
    {
        foreach ($this->cdata as $cdata) {
            if (empty($params[$cdata]) === false) {
                $params[$cdata] = '<![CDATA['.$params[$cdata].']]>';
            }
        }

        return $params;
    }

    /**
     * @param array $params
     *
     * @return string
     */
    protected function calculateHash(array $params)
    {
        if (isset($params['paymentResult']) === true) {
            unset($params['paymentResult']);
        }

        $params = $this->split($params);
        $body = $this->addCdata($params['body']);

        return hash('md5', $this->convertToXML(['body' => $body]).$this->options['MerCode'].$this->options['MerKey']);
    }

    /**
     * Verify if the hash of the given parameter is correct.
     *
     * @param array $params
     *
     * @return bool
     */
    public function verifyHash(array $params)
    {
        return $params['Signature'] === $this->calculateHash($params);
    }

    /**
     * parseResult.
     *
     * @param mixed $params
     *
     * @return array
     */
    public function parseResult($params)
    {
        $paymentResult = str_replace(['<![CDATA[', ']]>'], '', $params['paymentResult']);
        $params = array_merge($params, $this->parsePaymentResult($paymentResult));

        if ($this->verifyHash($params) === false) {
            $params['RspCode'] = '-1';
            $params['RspMsg'] = '驗證失敗';
        }

        $params['statusReason'] = $params['RspMsg'];

        return $params;
    }

    /**
     * parseResultTags.
     *
     * @method parseResultTags
     *
     * @param string $paymentResult
     *
     * @return array
     */
    protected function parseResultTags($paymentResult)
    {
        $result = [];
        if (preg_match_all('/<([^\/]+?)>/', $paymentResult, $tags)) {
            $result = array_diff($tags[1], ['Ips', 'GateWayRsp', 'head', 'body']);
        }

        return $result;
    }

    /**
     * parsePaymentResult.
     *
     * @method parsePaymentResult
     *
     * @param string $paymentResult
     *
     * @return array
     */
    protected function parsePaymentResult($paymentResult)
    {
        $result = [];
        $tags = $this->parseResultTags($paymentResult);
        $regexp = '/<(?<key>'.implode('|', $tags).')>(?<value>[^<]*)<\/('.implode('|', $tags).')>/';
        if (preg_match_all($regexp, $paymentResult, $matches, PREG_SET_ORDER)) {
            foreach ($matches as $match) {
                $result[$match['key']] = $match['value'];
            }
        }

        return $result;
    }

    /**
     * isSandbox.
     *
     * @method isSandbox
     *
     * @return bool
     */
    public function isSandbox()
    {
        return $this->options['sandbox'];
    }

    /**
     * generateTestingResponse.
     *
     * @method generateTestingResponse
     *
     * @param array $params
     *
     * @return array
     */
    public function generateTestingResponse($params = [])
    {
        $supportedParams = [
            'ReferenceID' => '',
            'RspCode' => '000000',
            'RspMsg' => '交易成功！',
            'ReqDate' => date('YmdHis'),
            'RspDate' => date('YmdHis'),
            'CurrencyType' => 156,
            'Amount' => null,
            'Date' => date('Ymd'),
            'Status' => 'Y',
            'Msg' => '支付成功！',
            'IpsBillNo' => date('YmdHis'),
            'IpsTradeNo' => date('YmdHis'),
            'RetEncodeType' => 17,
            'BankBillNo' => '710002875951',
            'ResultType' => 0,
            'IpsBillTime' => date('YmdHis'),
        ];

        $params = array_filter(array_replace(
            $supportedParams,
            array_intersect_key($params, $supportedParams)
        ));

        return [
            'paymentResult' => $this->generatGetwayRequest($params),
        ];
    }
}
