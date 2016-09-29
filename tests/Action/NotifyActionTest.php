<?php

use Mockery as m;
use Payum\Core\Bridge\Spl\ArrayObject;
use Payum\Core\Reply\HttpResponse;
use PayumTW\Ips\Action\NotifyAction;

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
        | Set
        |------------------------------------------------------------
        */

        $action = new NotifyAction();
        $gateway = m::mock('Payum\Core\GatewayInterface');
        $request = m::mock('Payum\Core\Request\Notify');
        $details = new ArrayObject([
            'RspCode' => '000000',
        ]);

        /*
        |------------------------------------------------------------
        | Expectation
        |------------------------------------------------------------
        */

        $gateway->shouldReceive('execute')->with(m::type('Payum\Core\Request\Sync'))->once();

        $request->shouldReceive('getModel')->twice()->andReturn($details);

        /*
        |------------------------------------------------------------
        | Assertion
        |------------------------------------------------------------
        */

        $action->setGateway($gateway);
        try {
            $action->execute($request);
        } catch (HttpResponse $response) {
            $this->assertSame('1', $response->getContent());
            $this->assertSame(200, $response->getStatusCode());
        }
    }

    public function test_notify_vaild_fail()
    {
        /*
        |------------------------------------------------------------
        | Set
        |------------------------------------------------------------
        */

        $action = new NotifyAction();
        $gateway = m::mock('Payum\Core\GatewayInterface');
        $request = m::mock('Payum\Core\Request\Notify');
        $details = new ArrayObject([
            'RspCode' => '-1',
        ]);

        /*
        |------------------------------------------------------------
        | Expectation
        |------------------------------------------------------------
        */

        $gateway->shouldReceive('execute')->with(m::type('Payum\Core\Request\Sync'))->once();

        $request->shouldReceive('getModel')->twice()->andReturn($details);

        /*
        |------------------------------------------------------------
        | Assertion
        |------------------------------------------------------------
        */

        $action->setGateway($gateway);
        try {
            $action->execute($request);
        } catch (HttpResponse $response) {
            $this->assertSame('Signature verify fail.', $response->getContent());
            $this->assertSame(400, $response->getStatusCode());
        }
    }
}
