<?php

namespace PayumTW\Ips\Tests\Action;

use Mockery as m;
use Payum\Core\Request\Sync;
use PHPUnit\Framework\TestCase;
use PayumTW\Ips\Action\SyncAction;
use Payum\Core\Bridge\Spl\ArrayObject;

class SyncActionTest extends TestCase
{
    protected function tearDown()
    {
        m::close();
    }

    public function testExecute()
    {
        $action = new SyncAction();
        $request = new Sync(new ArrayObject([]));

        $action->setGateway(
            $gateway = m::mock('Payum\Core\GatewayInterface')
        );

        $gateway->shouldReceive('execute')->once()->with(m::type('PayumTW\Ips\Request\Api\GetTransactionData'));

        $action->execute($request);
    }
}
