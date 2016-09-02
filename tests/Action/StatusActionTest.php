<?php

use Mockery as m;
use Payum\Core\Request\GetStatusInterface;
use PayumTW\Ips\Action\StatusAction;

class StatusActionTest extends PHPUnit_Framework_TestCase
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

        $action = new StatusAction();
        $request = m::mock(GetStatusInterface::class);
        $model = m::mock(ArrayAccess::class.','.Traversable::class);

        /*
        |------------------------------------------------------------
        | Expectation
        |------------------------------------------------------------
        */

        // $request->shouldReceive('getModel')->once()->andReturn($model);

        /*
        |------------------------------------------------------------
        | Assertion
        |------------------------------------------------------------
        */

        // $action->execute($request);
    }
}
