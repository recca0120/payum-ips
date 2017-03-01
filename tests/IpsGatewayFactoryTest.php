<?php

namespace PayumTW\Ips\Tests;

use Mockery as m;
use PHPUnit\Framework\TestCase;
use PayumTW\Ips\IpsGatewayFactory;
use Payum\Core\Bridge\Spl\ArrayObject;

class IpsGatewayFactoryTest extends TestCase
{
    protected function tearDown()
    {
        m::close();
    }

    public function testCreateConfig()
    {
        $gateway = new IpsGatewayFactory();
        $config = $gateway->createConfig([
            'payum.api' => false,
            'payum.required_options' => [],
            'payum.http_client' => $httpClient = m::mock('Payum\Core\HttpClientInterface'),
            'httplug.message_factory' => $messageFactory = m::mock('Http\Message\MessageFactory'),
            'MerCode' => 'foo',
            'MerKey' => 'foo',
            'Account' => 'foo',
            'sandbox' => true,
        ]);

        $this->assertInstanceOf(
            'PayumTW\Ips\Api',
            $config['payum.api'](ArrayObject::ensureArrayObject($config))
        );
    }
}
