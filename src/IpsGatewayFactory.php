<?php

namespace PayumTW\Ips;

use Payum\Core\Bridge\Spl\ArrayObject;
use Payum\Core\GatewayFactory;
use PayumTW\Ips\Action\CaptureAction;
use PayumTW\Ips\Action\ConvertPaymentAction;
use PayumTW\Ips\Action\NotifyAction;
use PayumTW\Ips\Action\StatusAction;
use PayumTW\Ips\Action\SyncAction;
use PayumTW\Ips\Action\Api\CreateTransactionAction;
use PayumTW\Ips\Action\Api\GetTransactionDataAction;

class IpsGatewayFactory extends GatewayFactory
{
    /**
     * {@inheritdoc}
     */
    protected function populateConfig(ArrayObject $config)
    {
        $config->defaults([
            'payum.factory_name' => 'ips',
            'payum.factory_title' => 'Ips',
            'payum.action.capture' => new CaptureAction(),
            'payum.action.nofity' => new NotifyAction(),
            'payum.action.sync' => new SyncAction(),
            'payum.action.status' => new StatusAction(),
            'payum.action.convert_payment' => new ConvertPaymentAction(),

            'payum.action.api.create_transaction' => new CreateTransactionAction(),
            'payum.action.api.get_transaction_data' => new GetTransactionDataAction(),
        ]);

        if (false == $config['payum.api']) {
            $config['payum.default_options'] = [
                'Version' => 'v1.0.0',
                'MerCode' => null,
                'MerKey' => null,
                'MerName' => null,
                'Account' => null,
                'sandbox' => true,
            ];

            $config->defaults($config['payum.default_options']);
            $config['payum.required_options'] = ['MerCode', 'MerKey', 'Account'];

            $config['payum.api'] = function (ArrayObject $config) {
                $config->validateNotEmpty($config['payum.required_options']);

                return new Api((array) $config, $config['payum.http_client'], $config['httplug.message_factory']);
            };
        }
    }
}
