<?php

namespace PayumTW\Ips\Tests\Action;

use Mockery as m;
use Payum\Core\Request\Capture;
use PHPUnit\Framework\TestCase;
use PayumTW\Ips\Action\CaptureAction;
use Payum\Core\Bridge\Spl\ArrayObject;
use Payum\Core\Request\GetHttpRequest;

class CaptureActionTest extends TestCase
{
    protected function tearDown()
    {
        m::close();
    }

    public function testExecute()
    {
        $action = new CaptureAction();
        $request = m::mock(new Capture(new ArrayObject([])));

        $action->setGateway(
            $gateway = m::mock('Payum\Core\GatewayInterface')
        );
        $gateway->shouldReceive('execute')->once()->with(m::on(function ($httpRequest) {
            return $httpRequest instanceof GetHttpRequest;
        }))->andReturn([]);

        $request->shouldReceive('getToken')->once()->andReturn(
            $token = m::mock('Payum\Core\Security\TokenInterface')
        );

        $token->shouldReceive('getTargetUrl')->once()->andReturn(
            $targetUrl = 'targetUrl'
        );

        $action->setGenericTokenFactory(
            $tokenFactory = m::spy('Payum\Core\Security\GenericTokenFactoryInterface')
        );

        $token->shouldReceive('getGatewayName')->once()->andReturn(
            $gatewayName = 'foo.targetUrl'
        );
        $token->shouldReceive('getDetails')->once()->andReturn(
            $details = new ArrayObject()
        );

        $tokenFactory->shouldReceive('createNotifyToken')->once()->with(
            $gatewayName, $details
        )->andReturn(
            $notifyToken = m::mock('Payum\Core\Security\TokenInterface')
        );
        $notifyToken->shouldReceive('getTargetUrl')->once()->andReturn($notifyTargetUrl = 'foo.notifyTargetUrl');

        $gateway->shouldReceive('execute')->once()->with('PayumTW\Ips\Request\Api\CreateTransaction');

        $action->execute($request);

        $this->assertSame([
            'Merchanturl' => $targetUrl,
            'FailUrl' => $targetUrl,
            'ServerUrl' => $notifyTargetUrl,
        ], (array) $request->getModel());
    }

    public function testCaptured()
    {
        $action = new CaptureAction();
        $request = m::mock(new Capture(new ArrayObject([])));

        $action->setGateway(
            $gateway = m::mock('Payum\Core\GatewayInterface')
        );

        $response = [
            'paymentResult' => 'foo',
        ];

        $gateway->shouldReceive('execute')->once()->with(m::on(function ($httpRequest) use ($response) {
            $httpRequest->request = $response;

            return $httpRequest instanceof GetHttpRequest;
        }))->andReturn([]);

        $action->setApi(
            $api = m::mock('PayumTW\Ips\Api')
        );

        $api->shouldReceive('parseResponse')->once()->with($response)->andReturn($params = ['RspCode' => 1]);
        $api->shouldReceive('verifyHash')->once()->with($params)->andReturn(true);

        $action->execute($request);

        $this->assertSame($params, (array) $request->getModel());
    }
}
