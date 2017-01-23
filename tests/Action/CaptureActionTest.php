<?php

use Mockery as m;
use PayumTW\Ips\Action\CaptureAction;
use Payum\Core\Bridge\Spl\ArrayObject;

class CaptureActionTest extends PHPUnit_Framework_TestCase
{
    public function tearDown()
    {
        m::close();
    }

    public function test_redirect_to_ips()
    {
        /*
        |------------------------------------------------------------
        | Arrange
        |------------------------------------------------------------
        */

        $api = m::spy('PayumTW\Ips\Api');
        $gateway = m::spy('Payum\Core\GatewayInterface');
        $request = m::spy('Payum\Core\Request\Capture');
        $tokenFactory = m::spy('Payum\Core\Security\GenericTokenFactoryInterface');
        $token = m::spy('Payum\Core\Security\TokenInterface');
        $notifyToken = m::spy('Payum\Core\Security\TokenInterface');
        $details = new ArrayObject([]);
        $targetUrl = 'foo.target_url';
        $gatewayName = 'foo.gateway_name';
        $hash = 'foo.hash';

        /*
        |------------------------------------------------------------
        | Act
        |------------------------------------------------------------
        */

        $request
            ->shouldReceive('getModel')->andReturn($details)
            ->shouldReceive('getToken')->andReturn($token);

        $token
            ->shouldReceive('getTargetUrl')->andReturn($targetUrl)
            ->shouldReceive('getGatewayName')->andReturn($gatewayName)
            ->shouldReceive('getDetails')->andReturn($details);

        $tokenFactory
            ->shouldReceive('createNotifyToken')->with($gatewayName, $details)->andReturn($notifyToken);

        $notifyToken
            ->shouldReceive('getTargetUrl')->andReturn($targetUrl);

        $action = new CaptureAction();
        $action->setApi($api);
        $action->setGateway($gateway);
        $action->setGenericTokenFactory($tokenFactory);
        $action->execute($request);

        /*
        |------------------------------------------------------------
        | Assert
        |------------------------------------------------------------
        */

        $request->shouldHaveReceived('getModel')->twice();
        $gateway->shouldHaveReceived('execute')->with(m::type('Payum\Core\Request\GetHttpRequest'))->once();
        $request->shouldHaveReceived('getToken')->once();
        $token->shouldHaveReceived('getTargetUrl')->once();
        $token->shouldHaveReceived('getGatewayName')->once();
        $token->shouldHaveReceived('getDetails')->once();
        $tokenFactory->shouldHaveReceived('createNotifyToken')->once();
        $notifyToken->shouldHaveReceived('getTargetUrl')->once();
        $gateway->shouldHaveReceived('execute')->with(m::type('PayumTW\Ips\Request\Api\CreateTransaction'))->once();
    }

    public function test_captured_success()
    {
        /*
        |------------------------------------------------------------
        | Arrange
        |------------------------------------------------------------
        */

        $api = m::spy('PayumTW\Ips\Api');
        $gateway = m::spy('Payum\Core\GatewayInterface');
        $request = m::spy('Payum\Core\Request\Capture');
        $tokenFactory = m::spy('Payum\Core\Security\GenericTokenFactoryInterface');
        $details = new ArrayObject([]);

        $response = [
            'paymentResult' => '<Ips><GateWayRsp><head><ReferenceID></ReferenceID><RspCode>000000</RspCode><RspMsg><![CDATA[交易成功！]]></RspMsg><ReqDate>20160903022511</ReqDate><RspDate>20160903022558</RspDate><Signature>598633a6fcae5562ef63355f12a71ee1</Signature></head><body><MerBillNo>57c9aca80fdb4</MerBillNo><CurrencyType>156</CurrencyType><Amount>0.01</Amount><Date>20160903</Date><Status>Y</Status><Msg><![CDATA[支付成功！]]></Msg><IpsBillNo>BO20160903020025003799</IpsBillNo><IpsTradeNo>2016090302091180230</IpsTradeNo><RetEncodeType>17</RetEncodeType><BankBillNo>710002875951</BankBillNo><ResultType>0</ResultType><IpsBillTime>20160903022542</IpsBillTime></body></GateWayRsp></Ips>',
        ];

        /*
        |------------------------------------------------------------
        | Act
        |------------------------------------------------------------
        */

        $request
            ->shouldReceive('getModel')->andReturn($details);

        $gateway
            ->shouldReceive('execute')->with(m::type('Payum\Core\Request\GetHttpRequest'))->andReturnUsing(function ($httpRequest) use ($response) {
                $httpRequest->request = $response;

                return $httpRequest;
            });

        $api
            ->shouldReceive('parsePaymentResult')->with($response['paymentResult'])->andReturn([])
            ->shouldReceive('verifyHash')->with($response)->andReturn(true);

        $action = new CaptureAction();
        $action->setApi($api);
        $action->setGateway($gateway);
        $action->setGenericTokenFactory($tokenFactory);
        $action->execute($request);

        /*
        |------------------------------------------------------------
        | Assert
        |------------------------------------------------------------
        */

        $request->shouldHaveReceived('getModel')->twice();
        $gateway->shouldHaveReceived('execute')->with(m::type('Payum\Core\Request\GetHttpRequest'))->once();
        $api->shouldHaveReceived('parsePaymentResult')->with($response['paymentResult'])->once();
        $api->shouldHaveReceived('verifyHash')->with($response)->once();
    }

    public function test_captured_fail()
    {
        /*
        |------------------------------------------------------------
        | Arrange
        |------------------------------------------------------------
        */

        $api = m::spy('PayumTW\Ips\Api');
        $gateway = m::spy('Payum\Core\GatewayInterface');
        $request = m::spy('Payum\Core\Request\Capture');
        $tokenFactory = m::spy('Payum\Core\Security\GenericTokenFactoryInterface');
        $details = new ArrayObject([]);

        $response = [
            'paymentResult' => '<Ips><GateWayRsp><head><ReferenceID></ReferenceID><RspCode>000000</RspCode><RspMsg><![CDATA[交易成功！]]></RspMsg><ReqDate>20160903022511</ReqDate><RspDate>20160903022558</RspDate><Signature>598633a6fcae5562ef63355f12a71ee1</Signature></head><body><MerBillNo>57c9aca80fdb4</MerBillNo><CurrencyType>156</CurrencyType><Amount>0.01</Amount><Date>20160903</Date><Status>Y</Status><Msg><![CDATA[支付成功！]]></Msg><IpsBillNo>BO20160903020025003799</IpsBillNo><IpsTradeNo>2016090302091180230</IpsTradeNo><RetEncodeType>17</RetEncodeType><BankBillNo>710002875951</BankBillNo><ResultType>0</ResultType><IpsBillTime>20160903022542</IpsBillTime></body></GateWayRsp></Ips>',
        ];

        /*
        |------------------------------------------------------------
        | Act
        |------------------------------------------------------------
        */

        $request
            ->shouldReceive('getModel')->andReturn($details);

        $gateway
            ->shouldReceive('execute')->with(m::type('Payum\Core\Request\GetHttpRequest'))->andReturnUsing(function ($httpRequest) use ($response) {
                $httpRequest->request = $response;

                return $httpRequest;
            });

        $api
            ->shouldReceive('parsePaymentResult')->with($response['paymentResult'])->andReturn([])
            ->shouldReceive('verifyHash')->with($response)->andReturn(false);

        $action = new CaptureAction();
        $action->setApi($api);
        $action->setGateway($gateway);
        $action->setGenericTokenFactory($tokenFactory);
        $action->execute($request);

        /*
        |------------------------------------------------------------
        | Assert
        |------------------------------------------------------------
        */

        $request->shouldHaveReceived('getModel')->twice();
        $gateway->shouldHaveReceived('execute')->with(m::type('Payum\Core\Request\GetHttpRequest'))->once();
        $api->shouldHaveReceived('parsePaymentResult')->with($response['paymentResult'])->once();
        $api->shouldHaveReceived('verifyHash')->with($response)->once();
    }
}
