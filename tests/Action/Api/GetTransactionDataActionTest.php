<?php

namespace PayumTW\Ips\Tests\Action\Api;

use Mockery as m;
use PHPUnit\Framework\TestCase;
use Payum\Core\Bridge\Spl\ArrayObject;
use PayumTW\Ips\Request\Api\GetTransactionData;
use PayumTW\Ips\Action\Api\GetTransactionDataAction;

class GetTransactionDataActionTest extends TestCase
{
    protected function tearDown()
    {
        m::close();
    }

    public function testGetTransactionData()
    {
        $action = new GetTransactionDataAction();
        $request = new GetTransactionData(new ArrayObject([]));

        $action->setApi(
            $api = m::mock('PayumTW\Ips\Api')
        );

        $api->shouldReceive('getTransactionData')->once()->with((array) $request->getModel())->andReturn($params = ['RepCode' => '1']);

        $action->execute($request);

        $this->assertSame($params, (array) $request->getModel());
    }
}
