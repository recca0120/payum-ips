<?php

use Mockery as m;
use Payum\Core\Reply\HttpResponse;
use Payum\Core\Bridge\Spl\ArrayObject;
use PayumTW\Ips\Action\Api\CreateTransactionAction;

class CreateTransactionActionTest extends PHPUnit_Framework_TestCase
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
        $request = m::spy('PayumTW\Ips\Request\Api\CreateTransaction');
        $details = new ArrayObject([]);
        $endpoint = 'foo';

        /*
        |------------------------------------------------------------
        | Act
        |------------------------------------------------------------
        */

        $request
            ->shouldReceive('getModel')->andReturn($details);

        $api
            ->shouldReceive('isSandbox')->andReturn(false)
            ->shouldReceive('getApiEndpoint')->andReturn($endpoint)
            ->shouldReceive('createTransaction')->andReturn($details->toUnsafeArray());

        $action = new CreateTransactionAction();
        $action->setApi($api);

        /*
        |------------------------------------------------------------
        | Assert
        |------------------------------------------------------------
        */

        try {
            $action->execute($request);
        } catch (HttpResponse $response) {
            $this->assertSame($endpoint, $response->getUrl());
            $this->assertSame($details->toUnsafeArray(), $response->getFields());
        }

        $request->shouldHaveReceived('getModel')->twice();
        $api->shouldHaveReceived('isSandbox')->once();
        $api->shouldHaveReceived('getApiEndpoint')->with('capture')->once();
        $api->shouldHaveReceived('createTransaction')->with($details->toUnsafeArray())->once();
    }

    public function test_sandbox()
    {
        /*
        |------------------------------------------------------------
        | Arrange
        |------------------------------------------------------------
        */

        $api = m::spy('PayumTW\Ips\Api');
        $request = m::spy('PayumTW\Ips\Request\Api\CreateTransaction');
        $details = new ArrayObject([]);
        $endpoint = 'foo';

        /*
        |------------------------------------------------------------
        | Act
        |------------------------------------------------------------
        */

        $request
            ->shouldReceive('getModel')->andReturn($details);

        $api
            ->shouldReceive('isSandbox')->andReturn(true)
            ->shouldReceive('generateTestingResponse')->andReturn($details->toUnsafeArray())
            ->shouldReceive('parsePaymentResult')->andReturn($details->toUnsafeArray());


        $action = new CreateTransactionAction();
        $action->setApi($api);

        /*
        |------------------------------------------------------------
        | Assert
        |------------------------------------------------------------
        */

        try {
            $action->execute($request);
        } catch (HttpResponse $response) {
            $this->assertSame($endpoint, $response->getUrl());
            $this->assertSame($details->toUnsafeArray(), $response->getFields());
        }

        $request->shouldHaveReceived('getModel')->twice();
        $api->shouldHaveReceived('isSandbox')->once();
        $api->shouldHaveReceived('generateTestingResponse')->with($details->toUnsafeArray())->once();
        $api->shouldHaveReceived('parsePaymentResult')->with($details->toUnsafeArray())->once();
    }
}
