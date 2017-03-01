<?php

namespace PayumTW\Ips\Tests\Action;

use Mockery as m;
use Payum\Core\Request\Convert;
use PHPUnit\Framework\TestCase;
use PayumTW\Ips\Action\ConvertPaymentAction;

class ConvertPaymentActionTest extends TestCase
{
    protected function tearDown()
    {
        m::close();
    }

    public function testExecute()
    {
        $action = new ConvertPaymentAction();
        $request = new Convert(
            $payment = m::mock('Payum\Core\Model\PaymentInterface'),
            $to = 'array'
        );
        $payment->shouldReceive('getDetails')->once()->andReturn([]);
        $payment->shouldReceive('getNumber')->once()->andReturn($number = 'foo');
        $payment->shouldReceive('getTotalAmount')->once()->andReturn($totalAmount = 'foo');
        $action->execute($request);
        $this->assertSame([
            'MerBillNo' => $number,
            'Amount' => $totalAmount,
        ], $request->getResult());
    }
}
