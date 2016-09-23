<?php

use Mockery as m;
use Payum\Core\Bridge\Spl\ArrayObject;
use Payum\Core\Reply\HttpResponse;
use PayumTW\Ips\Action\Api\CreateTransactionAction;
use PayumTW\Ips\Api;
use PayumTW\Ips\Request\Api\CreateTransaction;

class CreateTransactionActionTest extends PHPUnit_Framework_TestCase
{
    public function tearDown()
    {
        m::close();
    }

    public function test_get_transaction_data()
    {
        /*
        |------------------------------------------------------------
        | Set
        |------------------------------------------------------------
        */

        $api = m::mock(Api::class);
        $request = m::mock(CreateTransaction::class);
        $details = new ArrayObject();

        /*
        |------------------------------------------------------------
        | Expectation
        |------------------------------------------------------------
        */

        $request->shouldReceive('getModel')->twice()->andReturn($details);

        $api
            ->shouldReceive('isSandbox')->once()->andReturn(false)
            ->shouldReceive('getApiEndpoint')->once()->andReturn('fooApiEndpoint')
            ->shouldReceive('createTransaction')->once()->andReturn([
                'foo' => 'bar',
            ]);

        /*
        |------------------------------------------------------------
        | Assertion
        |------------------------------------------------------------
        */

        $action = new CreateTransactionAction();
        $action->setApi($api);
        try {
            $action->execute($request);
        } catch (HttpResponse $response) {
            $this->assertSame('fooApiEndpoint', $response->getUrl());
            $this->assertSame([
                'foo' => 'bar',
            ], $response->getFields());
        }
    }

    public function test_sandbox()
    {
        /*
        |------------------------------------------------------------
        | Set
        |------------------------------------------------------------
        */

        $api = m::mock(Api::class);
        $request = m::mock(CreateTransaction::class);
        $details = new ArrayObject();

        /*
        |------------------------------------------------------------
        | Expectation
        |------------------------------------------------------------
        */

        $request->shouldReceive('getModel')->twice()->andReturn($details);

        $details['Merchanturl'] = 'fooApiEndpoint';

        $api
            ->shouldReceive('isSandbox')->once()->andReturn(true)
            ->shouldReceive('generateTestingResponse')->once()->andReturn([
                'foo' => 'bar',
            ]);

        /*
        |------------------------------------------------------------
        | Assertion
        |------------------------------------------------------------
        */

        $action = new CreateTransactionAction();
        $action->setApi($api);
        try {
            $action->execute($request);
        } catch (HttpResponse $response) {
            $this->assertSame('fooApiEndpoint', $response->getUrl());
            $this->assertSame([
                'foo' => 'bar',
            ], $response->getFields());
        }
    }
}
