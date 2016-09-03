<?php

namespace PayumTW\Ips\Action;

use Payum\Core\Action\GatewayAwareAction;
use Payum\Core\ApiAwareInterface;
use Payum\Core\ApiAwareTrait;
use Payum\Core\Bridge\Spl\ArrayObject;
use Payum\Core\Exception\RequestNotSupportedException;
use Payum\Core\Reply\HttpResponse;
use Payum\Core\Request\GetHttpRequest;
use Payum\Core\Request\Notify;
use PayumTW\Ips\Api;

class NotifyAction extends GatewayAwareAction implements ApiAwareInterface
{
    use ApiAwareTrait;

    public function __construct()
    {
        $this->apiClass = Api::class;
    }

    /**
     * {@inheritdoc}
     *
     * @param Notify $request
     */
    public function execute($request)
    {
        RequestNotSupportedException::assertSupports($this, $request);
        $model = ArrayObject::ensureArrayObject($request->getModel());

        $httpRequest = new GetHttpRequest();
        $this->gateway->execute($httpRequest);

        $params = $this->api->parseResult($httpRequest->request);

        if ($this->api->verifyHash($params) === false) {
            throw new HttpResponse('Signature verify fail.', 400, ['Content-Type' => 'text/plain']);
        }

        if ($model['MerBillNo'] !== $params['MerBillNo']) {
            throw new HttpResponse('MerBillNo fail.', 400, ['Content-Type' => 'text/plain']);
        }

        $model->replace($params);

        throw new HttpResponse('1', 200, ['Content-Type' => 'text/plain']);
    }

    /**
     * {@inheritdoc}
     */
    public function supports($request)
    {
        return
            $request instanceof Notify &&
            $request->getModel() instanceof \ArrayAccess;
    }
}
