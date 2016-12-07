<?php

use Mockery as m;
use PayumTW\Ips\Action\StatusAction;
use Payum\Core\Bridge\Spl\ArrayObject;

class StatusActionTest extends PHPUnit_Framework_TestCase
{
    public function tearDown()
    {
        m::close();
    }

    public function test_request_mark_new()
    {
        /*
        |------------------------------------------------------------
        | Set
        |------------------------------------------------------------
        */

        $action = new StatusAction();
        $request = m::mock('Payum\Core\Request\GetStatusInterface');
        $details = new ArrayObject();

        /*
        |------------------------------------------------------------
        | Expectation
        |------------------------------------------------------------
        */

        $request
            ->shouldReceive('getModel')->andReturn($details)->twice()
            ->shouldReceive('markNew')->once();

        /*
        |------------------------------------------------------------
        | Assertion
        |------------------------------------------------------------
        */

        $action->execute($request);
    }

    public function test_request_mark_captured()
    {
        /*
        |------------------------------------------------------------
        | Set
        |------------------------------------------------------------
        */

        $action = new StatusAction();
        $request = m::mock('Payum\Core\Request\GetStatusInterface');
        $details = new ArrayObject([
            'paymentResult' => 'foo',
            'RspCode' => '000000',
        ]);

        /*
        |------------------------------------------------------------
        | Expectation
        |------------------------------------------------------------
        */

        $request
            ->shouldReceive('getModel')->andReturn($details)->twice()
            ->shouldReceive('markCaptured')->once();

        /*
        |------------------------------------------------------------
        | Assertion
        |------------------------------------------------------------
        */

        $action->execute($request);
    }

    public function test_request_mark_failed()
    {
        /*
        |------------------------------------------------------------
        | Set
        |------------------------------------------------------------
        */

        $action = new StatusAction();
        $request = m::mock('Payum\Core\Request\GetStatusInterface');
        $details = new ArrayObject([
            'paymentResult' => 'foo',
            'RspCode' => '-1',
        ]);

        /*
        |------------------------------------------------------------
        | Expectation
        |------------------------------------------------------------
        */

        $request
            ->shouldReceive('getModel')->andReturn($details)->twice()
            ->shouldReceive('markFailed')->once();

        /*
        |------------------------------------------------------------
        | Assertion
        |------------------------------------------------------------
        */

        $action->execute($request);
    }
}
