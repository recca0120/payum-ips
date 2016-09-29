<?php

use Mockery as m;
use PayumTW\Ips\Action\ConvertPaymentAction;

class ConvertPaymentActionTest extends PHPUnit_Framework_TestCase
{
    public function tearDown()
    {
        m::close();
    }

    public function test_convert()
    {
        /*
        |------------------------------------------------------------
        | Set
        |------------------------------------------------------------
        */

        $action = new ConvertPaymentAction();
        $request = m::mock('Payum\Core\Request\Convert');
        $payment = m::mock('Payum\Core\Model\PaymentInterface');

        /*
        |------------------------------------------------------------
        | Expectation
        |------------------------------------------------------------
        */

        $request
            ->shouldReceive('getSource')->twice()->andReturn($payment)
            ->shouldReceive('getTo')->once()->andReturn('array');

        $payment
            ->shouldReceive('getDetails')->andReturn([])
            ->shouldReceive('getNumber')->andReturn('fooMerBillNo')
            ->shouldReceive('getTotalAmount')->andReturn('fooAmount');

        /*
        |------------------------------------------------------------
        | Assertion
        |------------------------------------------------------------
        */

        $request->shouldReceive('setResult')->once()->andReturnUsing(function ($data) {
            $this->assertSame([
                'MerBillNo' => 'fooMerBillNo',
                'Amount' => 'fooAmount',
            ], $data);
        });

        $action->execute($request);
    }
}
