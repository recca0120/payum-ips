<?php

use Mockery as m;
use PayumTW\Ips\Action\StatusAction;
use Payum\Core\Bridge\Spl\ArrayObject;

class StatusActionTest extends PHPUnit_Framework_TestCase
{
    public function tearDown()
    {
        m::close();
    }

    public function test_request_mark_new()
    {
        $this->validate([
            'Version' => 'v1.0.0',
            'MerCode' => 'c5374addd1a3d024c3a026199cb8feaf',
            'MerKey' => 'c71530d016b3475579da8af971f7ca6c',
            'MerName' => null,
            'Account' => 'b02e072eee68d65bff916e08b4f11df2',
            'ReqDate' => '20160903021801',
            'MerBillNo' => '57c9aca80fdb4',
            'GatewayType' => '01',
            'Date' => '20160903',
            'CurrencyType' => 156,
            'Amount' => 0.01,
            'Lang' => 'GB',
            'Merchanturl' => 'http://localhost/baotao/public/payment/capture/PHEzq0mjdqXM8EH7TQ3jDL6ONJMnBkt85BVrzK2TbJU',
            'FailUrl' => 'http://localhost/baotao/public/payment/capture/PHEzq0mjdqXM8EH7TQ3jDL6ONJMnBkt85BVrzK2TbJU',
            'OrderEncodeType' => 5,
            'RetEncodeType' => 17,
            'RetType' => 1,
            'ServerUrl' => 'http://localhost/baotao/public/payment/notify/Fz2WIYvQs1KPc3tRUGNKtOnkHFZuF4segpRnZdphze8',
            'GoodsName' => '商品名稱',
        ], 'markNew');
    }

    public function test_request_mark_captured()
    {
        $this->validate([
            'Version' => 'v1.0.0',
            'MerCode' => 'c5374addd1a3d024c3a026199cb8feaf',
            'MerKey' => 'c71530d016b3475579da8af971f7ca6c',
            'MerName' => null,
            'Account' => 'b02e072eee68d65bff916e08b4f11df2',
            'ReqDate' => '20160903021801',
            'MerBillNo' => '57c9aca80fdb4',
            'GatewayType' => '01',
            'Date' => '20160903',
            'CurrencyType' => 156,
            'Amount' => 0.01,
            'Lang' => 'GB',
            'Merchanturl' => 'http://localhost/baotao/public/payment/capture/PHEzq0mjdqXM8EH7TQ3jDL6ONJMnBkt85BVrzK2TbJU',
            'FailUrl' => 'http://localhost/baotao/public/payment/capture/PHEzq0mjdqXM8EH7TQ3jDL6ONJMnBkt85BVrzK2TbJU',
            'OrderEncodeType' => 5,
            'RetEncodeType' => 17,
            'RetType' => 1,
            'ServerUrl' => 'http://localhost/baotao/public/payment/notify/Fz2WIYvQs1KPc3tRUGNKtOnkHFZuF4segpRnZdphze8',
            'GoodsName' => '商品名稱',

            'paymentResult' => '<Ips><GateWayRsp><head><ReferenceID></ReferenceID><RspCode>000000</RspCode><RspMsg><![CDATA[交易成功！]]></RspMsg><ReqDate>20160903022511</ReqDate><RspDate>20160903022558</RspDate><Signature>598633a6fcae5562ef63355f12a71ee1</Signature></head><body><MerBillNo>57c9aca80fdb4</MerBillNo><CurrencyType>156</CurrencyType><Amount>0.01</Amount><Date>20160903</Date><Status>Y</Status><Msg><![CDATA[支付成功！]]></Msg><IpsBillNo>BO20160903020025003799</IpsBillNo><IpsTradeNo>2016090302091180230</IpsTradeNo><RetEncodeType>17</RetEncodeType><BankBillNo>710002875951</BankBillNo><ResultType>0</ResultType><IpsBillTime>20160903022542</IpsBillTime></body></GateWayRsp></Ips>',
            'ReferenceID' => '',
            'RspCode' => '000000',
            'RspMsg' => '交易成功！',
            'ReqDate' => '20160903022511',
            'RspDate' => '20160903022558',
            'Signature' => '598633a6fcae5562ef63355f12a71ee1',
            'MerBillNo' => '57c9aca80fdb4',
            'CurrencyType' => '156',
            'Amount' => '0.01',
            'Date' => '20160903',
            'Status' => 'Y',
            'Msg' => '支付成功！',
            'IpsBillNo' => 'BO20160903020025003799',
            'IpsTradeNo' => '2016090302091180230',
            'RetEncodeType' => '17',
            'BankBillNo' => '710002875951',
            'ResultType' => '0',
            'IpsBillTime' => '20160903022542',
            'Signature' => '598633a6fcae5562ef63355f12a71ee1',
        ], 'markCaptured');
    }

    public function test_request_mark_failed()
    {
        $this->validate([
            'Version' => 'v1.0.0',
            'MerCode' => 'c5374addd1a3d024c3a026199cb8feaf',
            'MerKey' => 'c71530d016b3475579da8af971f7ca6c',
            'MerName' => null,
            'Account' => 'b02e072eee68d65bff916e08b4f11df2',
            'ReqDate' => '20160903021801',
            'MerBillNo' => '57c9aca80fdb4',
            'GatewayType' => '01',
            'Date' => '20160903',
            'CurrencyType' => 156,
            'Amount' => 0.01,
            'Lang' => 'GB',
            'Merchanturl' => 'http://localhost/baotao/public/payment/capture/PHEzq0mjdqXM8EH7TQ3jDL6ONJMnBkt85BVrzK2TbJU',
            'FailUrl' => 'http://localhost/baotao/public/payment/capture/PHEzq0mjdqXM8EH7TQ3jDL6ONJMnBkt85BVrzK2TbJU',
            'OrderEncodeType' => 5,
            'RetEncodeType' => 17,
            'RetType' => 1,
            'ServerUrl' => 'http://localhost/baotao/public/payment/notify/Fz2WIYvQs1KPc3tRUGNKtOnkHFZuF4segpRnZdphze8',
            'GoodsName' => '商品名稱',

            'paymentResult' => '<Ips><GateWayRsp><head><ReferenceID></ReferenceID><RspCode>000000</RspCode><RspMsg><![CDATA[交易成功！]]></RspMsg><ReqDate>20160903022511</ReqDate><RspDate>20160903022558</RspDate><Signature>598633a6fcae5562ef63355f12a71ee1</Signature></head><body><MerBillNo>57c9aca80fdb4</MerBillNo><CurrencyType>156</CurrencyType><Amount>0.01</Amount><Date>20160903</Date><Status>Y</Status><Msg><![CDATA[支付成功！]]></Msg><IpsBillNo>BO20160903020025003799</IpsBillNo><IpsTradeNo>2016090302091180230</IpsTradeNo><RetEncodeType>17</RetEncodeType><BankBillNo>710002875951</BankBillNo><ResultType>0</ResultType><IpsBillTime>20160903022542</IpsBillTime></body></GateWayRsp></Ips>',
            'ReferenceID' => '',
            'RspCode' => '-1',
            'RspMsg' => '交易成功！',
            'ReqDate' => '20160903022511',
            'RspDate' => '20160903022558',
            'Signature' => '598633a6fcae5562ef63355f12a71ee1',
            'MerBillNo' => '57c9aca80fdb4',
            'CurrencyType' => '156',
            'Amount' => '0.01',
            'Date' => '20160903',
            'Status' => 'Y',
            'Msg' => '支付成功！',
            'IpsBillNo' => 'BO20160903020025003799',
            'IpsTradeNo' => '2016090302091180230',
            'RetEncodeType' => '17',
            'BankBillNo' => '710002875951',
            'ResultType' => '0',
            'IpsBillTime' => '20160903022542',
            'Signature' => '598633a6fcae5562ef63355f12a71ee1',
        ], 'markFailed');
    }

    protected function validate($input, $type)
    {
        /*
        |------------------------------------------------------------
        | Arrange
        |------------------------------------------------------------
        */

        $request = m::spy('Payum\Core\Request\GetStatusInterface');
        $details = new ArrayObject($input);

        /*
        |------------------------------------------------------------
        | Act
        |------------------------------------------------------------
        */

        $request->shouldReceive('getModel')->andReturn($details);

        $action = new StatusAction();
        $action->execute($request);

        /*
        |------------------------------------------------------------
        | Assert
        |------------------------------------------------------------
        */

        $request->shouldHaveReceived('getModel')->twice();
        $request->shouldHaveReceived($type)->once();
    }
}
