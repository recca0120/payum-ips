<?php

namespace PayumTW\Ips\Tests\Action\Api;

use Mockery as m;
use Payum\Core\Request\Capture;
use PHPUnit\Framework\TestCase;
use Payum\Core\Bridge\Spl\ArrayObject;
use Payum\Core\Reply\HttpPostRedirect;
use PayumTW\Ips\Request\Api\CreateTransaction;
use PayumTW\Ips\Action\Api\CreateTransactionAction;

class CreateTransactionActionTest extends TestCase
{
    protected function tearDown()
    {
        m::close();
    }

    public function testExecute()
    {
        $action = new CreateTransactionAction();
        $request = new CreateTransaction(new ArrayObject([]));

        $action->setApi(
            $api = m::mock('PayumTW\Ips\Api')
        );
        $api->shouldReceive('isSandbox')->once()->andReturn(false);
        $api->shouldReceive('getApiEndpoint')->once()->with('capture')->andReturn($apiEndpoint = 'foo');
        $api->shouldReceive('createTransaction')->once()->with((array) $request->getModel())->andReturn($params = ['foo' => 'bar']);

        try {
            $action->execute($request);
        } catch (HttpPostRedirect $e) {
            $this->assertSame($apiEndpoint, $e->getUrl());
            $this->assertSame($params, $e->getFields());
        }
    }

    public function testExecuteSandbox()
    {
        $action = new CreateTransactionAction();
        $request = new CreateTransaction(new ArrayObject([]));

        $action->setApi(
            $api = m::mock('PayumTW\Ips\Api')
        );
        $api->shouldReceive('isSandbox')->once()->andReturn(true);
        $params = ['foo' => 'bar'];
        $api->shouldReceive('generateTestingResponse')->once()->once((array) $request->getModel())->andReturn($params);
        $api->shouldReceive('parseResponse')->once()->andReturn($params);

        $action->execute($request);
        $this->assertSame($params, (array) $request->getModel());
    }
}
