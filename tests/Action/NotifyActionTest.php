<?php

use Mockery as m;
use Payum\Core\Reply\ReplyInterface;
use PayumTW\Ips\Action\NotifyAction;
use Payum\Core\Bridge\Spl\ArrayObject;

class NotifyActionTest extends PHPUnit_Framework_TestCase
{
    public function tearDown()
    {
        m::close();
    }

    public function test_notify_success()
    {
        /*
        |------------------------------------------------------------
        | Arrange
        |------------------------------------------------------------
        */

        $request = m::spy('Payum\Core\Request\Notify');
        $gateway = m::spy('Payum\Core\GatewayInterface');
        $api = m::spy('PayumTW\Ips\Api');

        $response = [
            'paymentResult' => '<Ips><GateWayRsp><head><ReferenceID></ReferenceID><RspCode>000000</RspCode><RspMsg><![CDATA[交易成功！]]></RspMsg><ReqDate>20160903022511</ReqDate><RspDate>20160903022558</RspDate><Signature>598633a6fcae5562ef63355f12a71ee1</Signature></head><body><MerBillNo>57c9aca80fdb4</MerBillNo><CurrencyType>156</CurrencyType><Amount>0.01</Amount><Date>20160903</Date><Status>Y</Status><Msg><![CDATA[支付成功！]]></Msg><IpsBillNo>BO20160903020025003799</IpsBillNo><IpsTradeNo>2016090302091180230</IpsTradeNo><RetEncodeType>17</RetEncodeType><BankBillNo>710002875951</BankBillNo><ResultType>0</ResultType><IpsBillTime>20160903022542</IpsBillTime></body></GateWayRsp></Ips>',
        ];

        $details = new ArrayObject($response);

        /*
        |------------------------------------------------------------
        | Act
        |------------------------------------------------------------
        */

        $request
            ->shouldReceive('getModel')->andReturn($details);

        $gateway
            ->shouldReceive('execute')->with(m::type('Payum\Core\Request\GetHttpRequest'))->andReturnUsing(function ($getHttpRequest) use ($response) {
                $getHttpRequest->request = $response;

                return $getHttpRequest;
            });

        $api
            ->shouldReceive('parsePaymentResult')->with($response['paymentResult'])->andReturn([])
            ->shouldReceive('verifyHash')->with($response)->andReturn(true);

        $action = new NotifyAction();
        $action->setGateway($gateway);
        $action->setApi($api);

        /*
        |------------------------------------------------------------
        | Assert
        |------------------------------------------------------------
        */

        try {
            $action->execute($request);
        } catch (ReplyInterface $e) {
            $this->assertSame(200, $e->getStatusCode());
            $this->assertSame('1', $e->getContent());
        }

        $request->shouldHaveReceived('getModel')->twice();
        $gateway->shouldHaveReceived('execute')->with(m::type('Payum\Core\Request\GetHttpRequest'))->once();
        $api->shouldHaveReceived('parsePaymentResult')->with($response['paymentResult'])->once();
        $api->shouldHaveReceived('verifyHash')->with($response)->once();
    }

    public function test_notify_when_checksum_fail()
    {
        /*
        |------------------------------------------------------------
        | Arrange
        |------------------------------------------------------------
        */

        $request = m::spy('Payum\Core\Request\Notify');
        $gateway = m::spy('Payum\Core\GatewayInterface');
        $api = m::spy('PayumTW\Ips\Api');

        $response = [
            'paymentResult' => '<Ips><GateWayRsp><head><ReferenceID></ReferenceID><RspCode>000000</RspCode><RspMsg><![CDATA[交易成功！]]></RspMsg><ReqDate>20160903022511</ReqDate><RspDate>20160903022558</RspDate><Signature>598633a6fcae5562ef63355f12a71ee1</Signature></head><body><MerBillNo>57c9aca80fdb4</MerBillNo><CurrencyType>156</CurrencyType><Amount>0.01</Amount><Date>20160903</Date><Status>Y</Status><Msg><![CDATA[支付成功！]]></Msg><IpsBillNo>BO20160903020025003799</IpsBillNo><IpsTradeNo>2016090302091180230</IpsTradeNo><RetEncodeType>17</RetEncodeType><BankBillNo>710002875951</BankBillNo><ResultType>0</ResultType><IpsBillTime>20160903022542</IpsBillTime></body></GateWayRsp></Ips>',
        ];

        $details = new ArrayObject($response);

        /*
        |------------------------------------------------------------
        | Act
        |------------------------------------------------------------
        */

        $request
            ->shouldReceive('getModel')->andReturn($details);

        $gateway
            ->shouldReceive('execute')->with(m::type('Payum\Core\Request\GetHttpRequest'))->andReturnUsing(function ($getHttpRequest) use ($response) {
                $getHttpRequest->request = $response;

                return $getHttpRequest;
            });

        $api
            ->shouldReceive('parsePaymentResult')->with($response['paymentResult'])->andReturn([])
            ->shouldReceive('verifyHash')->with($response)->andReturn(false);

        $action = new NotifyAction();
        $action->setGateway($gateway);
        $action->setApi($api);

        /*
        |------------------------------------------------------------
        | Assert
        |------------------------------------------------------------
        */

        try {
            $action->execute($request);
        } catch (ReplyInterface $e) {
            $this->assertSame(400, $e->getStatusCode());
            $this->assertSame('Signature verify fail.', $e->getContent());
        }

        $request->shouldHaveReceived('getModel')->twice();
        $gateway->shouldHaveReceived('execute')->with(m::type('Payum\Core\Request\GetHttpRequest'))->once();
        $api->shouldHaveReceived('parsePaymentResult')->with($response['paymentResult'])->once();
        $api->shouldHaveReceived('verifyHash')->with($response)->once();
    }
}
