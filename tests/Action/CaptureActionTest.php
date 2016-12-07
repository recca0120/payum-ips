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
        | Set
        |------------------------------------------------------------
        */

        $action = new CaptureAction();
        $gateway = m::mock('Payum\Core\GatewayInterface');
        $request = m::mock('Payum\Core\Request\Capture');
        $tokenFactory = m::mock('Payum\Core\Security\GenericTokenFactoryInterface');
        $token = m::mock('stdClass');
        $notifyToken = m::mock('stdClass');
        $details = new ArrayObject([]);

        /*
        |------------------------------------------------------------
        | Expectation
        |------------------------------------------------------------
        */

        $gateway
            ->shouldReceive('execute')->with(m::type('Payum\Core\Request\GetHttpRequest'))->once()
            ->shouldReceive('execute')->with(m::type('PayumTW\Ips\Request\Api\CreateTransaction'))->once();

        $request
            ->shouldReceive('getModel')->twice()->andReturn($details)
            ->shouldReceive('getToken')->once()->andReturn($token);

        $token
            ->shouldReceive('getTargetUrl')->andReturn('fooMerchanturl')
            ->shouldReceive('getGatewayName')->andReturn('fooGatewayName')
            ->shouldReceive('getDetails')->andReturn([
                'foo' => 'bar',
            ]);

        $notifyToken->shouldReceive('getTargetUrl')->andReturn('fooServerUrl');

        $tokenFactory
            ->shouldReceive('createNotifyToken')->once()->andReturn($notifyToken);

        /*
        |------------------------------------------------------------
        | Assertion
        |------------------------------------------------------------
        */

        $action->setGateway($gateway);
        $action->setGenericTokenFactory($tokenFactory);
        $action->execute($request);
        $this->assertSame([
            'Merchanturl' => 'fooMerchanturl',
            'FailUrl' => 'fooMerchanturl',
            'ServerUrl' => 'fooServerUrl',
        ], (array) $details);
    }

    public function test_ips_response()
    {
        /*
        |------------------------------------------------------------
        | Set
        |------------------------------------------------------------
        */

        $action = new CaptureAction();
        $gateway = m::mock('Payum\Core\GatewayInterface');
        $request = m::mock('Payum\Core\Request\Capture');
        $tokenFactory = m::mock('Payum\Core\Security\GenericTokenFactoryInterface');
        $token = m::mock('stdClass');
        $notifyToken = m::mock('stdClass');
        $api = m::mock('PayumTW\Ips\Api');
        $details = new ArrayObject([]);

        /*
        |------------------------------------------------------------
        | Expectation
        |------------------------------------------------------------
        */

        $expected = [
            'paymentResult' => ['foo' => 'bar'],
        ];

        $gateway->shouldReceive('execute')->with(m::type('Payum\Core\Request\GetHttpRequest'))->once()->andReturnUsing(function ($httpRequest) use ($api, $expected) {
            $httpRequest->request = $expected;
        })->shouldReceive('execute')->with(m::type('Payum\Core\Request\Sync'));

        $request->shouldReceive('getModel')->twice()->andReturn($details);

        /*
        |------------------------------------------------------------
        | Assertion
        |------------------------------------------------------------
        */

        $action->setGateway($gateway);
        $action->setGenericTokenFactory($tokenFactory);
        $action->execute($request);
    }
}
