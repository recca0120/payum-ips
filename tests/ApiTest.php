<?php

use Http\Message\MessageFactory;
use Mockery as m;
use Payum\Core\HttpClientInterface;
use PayumTW\Ips\Api;

class ApiTest extends PHPUnit_Framework_TestCase
{
    public function tearDown()
    {
        m::close();
    }

    public function test_prepare_payment()
    {
        /*
        |------------------------------------------------------------
        | Set
        |------------------------------------------------------------
        */

        $options = [
            'Version' => 'v1.0.0',
            'MerCode' => 'c5374addd1a3d024c3a026199cb8feaf',
            'MerKey'  => 'c71530d016b3475579da8af971f7ca6c',
            'MerName' => null,
            'Account' => 'b02e072eee68d65bff916e08b4f11df2',
            'sandbox' => false,
        ];

        $params = [
            'ReqDate'         => '20160903021801',
            'MerBillNo'       => '57c9aca80fdb4',
            'GatewayType'     => '01',
            'Date'            => '20160903',
            'CurrencyType'    => 156,
            'Amount'          => 0.01,
            'Lang'            => 'GB',
            'Merchanturl'     => 'http://localhost/baotao/public/payment/capture/PHEzq0mjdqXM8EH7TQ3jDL6ONJMnBkt85BVrzK2TbJU',
            'FailUrl'         => 'http://localhost/baotao/public/payment/capture/PHEzq0mjdqXM8EH7TQ3jDL6ONJMnBkt85BVrzK2TbJU',
            'OrderEncodeType' => 5,
            'RetEncodeType'   => 17,
            'RetType'         => 1,
            'ServerUrl'       => 'http://localhost/baotao/public/payment/notify/Fz2WIYvQs1KPc3tRUGNKtOnkHFZuF4segpRnZdphze8',
            'GoodsName'       => '商品名稱',
        ];

        $httpClient = m::mock(HttpClientInterface::class);
        $message = m::mock(MessageFactory::class);

        /*
        |------------------------------------------------------------
        | Expectation
        |------------------------------------------------------------
        */

        $api = new Api($options, $httpClient, $message);

        /*
        |------------------------------------------------------------
        | Assertion
        |------------------------------------------------------------
        */

        $expected = [
            'pGateWayReq' => '<Ips><GateWayReq><head><Version>v1.0.0</Version><MerCode>c5374addd1a3d024c3a026199cb8feaf</MerCode><Account>b02e072eee68d65bff916e08b4f11df2</Account><ReqDate>20160903021801</ReqDate><Signature>527c01c2b04d4f8b198180ba72b0f66e</Signature></head><body><MerBillNo>57c9aca80fdb4</MerBillNo><GatewayType>01</GatewayType><Date>20160903</Date><CurrencyType>156</CurrencyType><Amount>0.01</Amount><Lang>GB</Lang><Merchanturl><![CDATA[http://localhost/baotao/public/payment/capture/PHEzq0mjdqXM8EH7TQ3jDL6ONJMnBkt85BVrzK2TbJU]]></Merchanturl><FailUrl><![CDATA[http://localhost/baotao/public/payment/capture/PHEzq0mjdqXM8EH7TQ3jDL6ONJMnBkt85BVrzK2TbJU]]></FailUrl><OrderEncodeType>5</OrderEncodeType><RetEncodeType>17</RetEncodeType><RetType>1</RetType><ServerUrl><![CDATA[http://localhost/baotao/public/payment/notify/Fz2WIYvQs1KPc3tRUGNKtOnkHFZuF4segpRnZdphze8]]></ServerUrl><GoodsName><![CDATA[商品名稱]]></GoodsName></body></GateWayReq></Ips>',
        ];
        $this->assertSame($expected, $api->preparePayment($params));
        $this->assertSame('https://newpay.ips.com.cn/psfp-entry/gateway/payment.do', $api->getApiEndpoint());
    }

    public function test_parse_result()
    {
        /*
        |------------------------------------------------------------
        | Set
        |------------------------------------------------------------
        */

        $options = [
            'Version' => 'v1.0.0',
            'MerCode' => 'c5374addd1a3d024c3a026199cb8feaf',
            'MerKey'  => 'c71530d016b3475579da8af971f7ca6c',
            'MerName' => null,
            'Account' => 'b02e072eee68d65bff916e08b4f11df2',
            'sandbox' => false,
        ];

        $params = [
            'paymentResult' => '<Ips><GateWayRsp><head><ReferenceID></ReferenceID><RspCode>000000</RspCode><RspMsg><![CDATA[交易成功！]]></RspMsg><ReqDate>20160903022511</ReqDate><RspDate>20160903022558</RspDate><Signature>598633a6fcae5562ef63355f12a71ee1</Signature></head><body><MerBillNo>57c9aca80fdb4</MerBillNo><CurrencyType>156</CurrencyType><Amount>0.01</Amount><Date>20160903</Date><Status>Y</Status><Msg><![CDATA[支付成功！]]></Msg><IpsBillNo>BO20160903020025003799</IpsBillNo><IpsTradeNo>2016090302091180230</IpsTradeNo><RetEncodeType>17</RetEncodeType><BankBillNo>710002875951</BankBillNo><ResultType>0</ResultType><IpsBillTime>20160903022542</IpsBillTime></body></GateWayRsp></Ips>',
        ];

        $httpClient = m::mock(HttpClientInterface::class);
        $message = m::mock(MessageFactory::class);

        /*
        |------------------------------------------------------------
        | Expectation
        |------------------------------------------------------------
        */

        $api = new Api($options, $httpClient, $message);

        /*
        |------------------------------------------------------------
        | Assertion
        |------------------------------------------------------------
        */

        $params = $api->parseResult($params);

        $expected = [
            'ReferenceID'   => '',
            'RspCode'       => '000000',
            'RspMsg'        => '交易成功！',
            'ReqDate'       => '20160903022511',
            'RspDate'       => '20160903022558',
            'Signature'     => '598633a6fcae5562ef63355f12a71ee1',
            'MerBillNo'     => '57c9aca80fdb4',
            'CurrencyType'  => '156',
            'Amount'        => '0.01',
            'Date'          => '20160903',
            'Status'        => 'Y',
            'Msg'           => '支付成功！',
            'IpsBillNo'     => 'BO20160903020025003799',
            'IpsTradeNo'    => '2016090302091180230',
            'RetEncodeType' => '17',
            'BankBillNo'    => '710002875951',
            'ResultType'    => '0',
            'IpsBillTime'   => '20160903022542',
            'Signature'     => '598633a6fcae5562ef63355f12a71ee1',
        ];

        foreach ($expected as $key => $value) {
            $this->assertSame($value, $params[$key]);
        }
    }

    public function test_parse_result_fail()
    {
        /*
        |------------------------------------------------------------
        | Set
        |------------------------------------------------------------
        */

        $options = [
            'Version' => 'v1.0.0',
            'MerCode' => 'c5374addd1a3d024c3a026199cb8feaf',
            'MerKey'  => 'c71530d016b3475579da8af971f7ca6c',
            'MerName' => null,
            'Account' => 'b02e072eee68d65bff916e08b4f11df2',
            'sandbox' => false,
        ];

        $params = [
            'paymentResult' => '<Ips><GateWayRsp><head><ReferenceID></ReferenceID><RspCode>000000</RspCode><RspMsg><![CDATA[交易成功！]]></RspMsg><ReqDate>20160903022511</ReqDate><RspDate>20160903022558</RspDate><Signature>598633a6fcae5562ef63355f12a71ee2</Signature></head><body><MerBillNo>57c9aca80fdb4</MerBillNo><CurrencyType>156</CurrencyType><Amount>0.01</Amount><Date>20160903</Date><Status>Y</Status><Msg><![CDATA[支付成功！]]></Msg><IpsBillNo>BO20160903020025003799</IpsBillNo><IpsTradeNo>2016090302091180230</IpsTradeNo><RetEncodeType>17</RetEncodeType><BankBillNo>710002875951</BankBillNo><ResultType>0</ResultType><IpsBillTime>20160903022542</IpsBillTime></body></GateWayRsp></Ips>',
        ];

        $httpClient = m::mock(HttpClientInterface::class);
        $message = m::mock(MessageFactory::class);

        /*
        |------------------------------------------------------------
        | Expectation
        |------------------------------------------------------------
        */

        $api = new Api($options, $httpClient, $message);

        /*
        |------------------------------------------------------------
        | Assertion
        |------------------------------------------------------------
        */

        $params = $api->parseResult($params);
        $this->assertSame('-1', $params['RspCode']);
    }

    public function test_generate_testing_data()
    {
        /*
        |------------------------------------------------------------
        | Set
        |------------------------------------------------------------
        */

        $options = [
            'Version' => 'v1.0.0',
            'MerCode' => 'c5374addd1a3d024c3a026199cb8feaf',
            'MerKey'  => 'c71530d016b3475579da8af971f7ca6c',
            'MerName' => null,
            'Account' => 'b02e072eee68d65bff916e08b4f11df2',
            'sandbox' => true,
        ];

        $httpClient = m::mock(HttpClientInterface::class);
        $message = m::mock(MessageFactory::class);

        /*
        |------------------------------------------------------------
        | Expectation
        |------------------------------------------------------------
        */

        $api = new Api($options, $httpClient, $message);

        /*
        |------------------------------------------------------------
        | Assertion
        |------------------------------------------------------------
        */

        $this->assertTrue($api->isTesting());
        $params = $api->parseResult($api->generateTestingResponse());
        $this->assertSame('000000', $params['RspCode']);
    }
}
