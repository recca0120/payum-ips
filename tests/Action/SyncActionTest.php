<?php

use Mockery as m;
use PayumTW\Ips\Action\SyncAction;
use Payum\Core\Bridge\Spl\ArrayObject;

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
        | Arrange
        |------------------------------------------------------------
        */

        $gateway = m::spy('Payum\Core\GatewayInterface');
        $request = m::spy('Payum\Core\Request\Sync');
        $details = new ArrayObject();

        /*
        |------------------------------------------------------------
        | Act
        |------------------------------------------------------------
        */

        $request->shouldReceive('getModel')->andReturn($details)->twice();

        $gateway
            ->shouldReceive('execute')->with(m::type('PayumTW\Ips\Request\Api\GetTransactionData'))->once();

        $action = new SyncAction();

        /*
        |------------------------------------------------------------
        | Assert
        |------------------------------------------------------------
        */

        $action->setGateway($gateway);
        $action->execute($request);
    }
}
