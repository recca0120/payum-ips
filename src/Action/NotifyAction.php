<?php

namespace PayumTW\Ips\Action;

use Payum\Core\Request\Notify;
use Payum\Core\GatewayAwareTrait;
use Payum\Core\Reply\HttpResponse;
use Payum\Core\GatewayAwareInterface;
use Payum\Core\Action\ActionInterface;
use Payum\Core\Bridge\Spl\ArrayObject;
use Payum\Core\Request\GetHttpRequest;
use PayumTW\Ips\Action\Api\BaseApiAwareAction;
use Payum\Core\Exception\RequestNotSupportedException;

class NotifyAction extends BaseApiAwareAction implements ActionInterface, GatewayAwareInterface
{
    use GatewayAwareTrait;

    /**
     * {@inheritdoc}
     *
     * @param Notify $request
     */
    public function execute($request)
    {
        RequestNotSupportedException::assertSupports($this, $request);
        $details = ArrayObject::ensureArrayObject($request->getModel());

        $httpRequest = new GetHttpRequest();
        $this->gateway->execute($httpRequest);

        $params = array_merge(
            $httpRequest->request,
            $this->api->parsePaymentResult($httpRequest->request['paymentResult'])
        );

        if ($this->api->verifyHash($params) === false) {
            throw new HttpResponse('Signature verify fail.', 400, ['Content-Type' => 'text/plain']);
        }

        $details->replace($params);

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
