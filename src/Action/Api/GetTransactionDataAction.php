<?php

namespace PayumTW\Ips\Action\Api;

use Payum\Core\Bridge\Spl\ArrayObject;
use Payum\Core\Exception\RequestNotSupportedException;
use PayumTW\Ips\Request\Api\GetTransactionData;

class GetTransactionDataAction extends BaseApiAwareAction
{
    /**
     * {@inheritdoc}
     *
     * @param $request GetTransactionData
     */
    public function execute($request)
    {
        RequestNotSupportedException::assertSupports($this, $request);

        $details = ArrayObject::ensureArrayObject($request->getModel());

        $result = $this->api->getTransactionData((array) $details);

        if (isset($result['RspCode']) === true && $result['RspCode'] === '-1') {
            return;
        }

        $details->replace($result);
    }

    /**
     * {@inheritdoc}
     */
    public function supports($request)
    {
        return
            $request instanceof GetTransactionData &&
            $request->getModel() instanceof \ArrayAccess;
    }
}
