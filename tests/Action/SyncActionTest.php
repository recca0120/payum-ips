<?php

use Mockery as m;
use Payum\Core\Bridge\Spl\ArrayObject;
use PayumTW\Ips\Action\SyncAction;

class SyncActionTest extends PHPUnit_Framework_TestCase
{
    public function tearDown()
    {
        m::close();
    }

    public function test_execute()
    {
        /*
        |------------------------------------------------------------
        | Set
        |------------------------------------------------------------
        */

        $action = new SyncAction();
        $gateway = m::mock('Payum\Core\GatewayInterface');
        $request = m::mock('Payum\Core\Request\Sync');
        $details = new ArrayObject();

        /*
        |------------------------------------------------------------
        | Expectation
        |------------------------------------------------------------
        */

        $request->shouldReceive('getModel')->andReturn($details)->twice();

        $gateway
            ->shouldReceive('execute')->with(m::type('Payum\Core\Request\GetHttpRequest'))->once()
            ->shouldReceive('execute')->with(m::type('PayumTW\Ips\Request\Api\GetTransactionData'))->once();

        /*
        |------------------------------------------------------------
        | Assertion
        |------------------------------------------------------------
        */

        $action->setGateway($gateway);
        $action->execute($request);
    }
}
