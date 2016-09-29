<?php

use Mockery as m;
use Payum\Core\Bridge\Spl\ArrayObject;
use PayumTW\Ips\Action\Api\GetTransactionDataAction;

class GetTransactionDataActionTest extends PHPUnit_Framework_TestCase
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

        $api = m::mock('PayumTW\Ips\Api');
        $request = m::mock('PayumTW\Ips\Request\Api\GetTransactionData');
        $details = new ArrayObject();

        /*
        |------------------------------------------------------------
        | Expectation
        |------------------------------------------------------------
        */

        $request->shouldReceive('getModel')->twice()->andReturn($details);

        $api->shouldReceive('getTransactionData')->once()->andReturn($details);

        /*
        |------------------------------------------------------------
        | Assertion
        |------------------------------------------------------------
        */

        $action = new GetTransactionDataAction();
        $action->setApi($api);
        $action->execute($request);
    }
}
