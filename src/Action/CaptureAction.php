<?php

namespace PayumTW\Ips\Action;

use Payum\Core\Request\Capture;
use Payum\Core\GatewayAwareTrait;
use Payum\Core\GatewayAwareInterface;
use Payum\Core\Action\ActionInterface;
use Payum\Core\Bridge\Spl\ArrayObject;
use Payum\Core\Request\GetHttpRequest;
use PayumTW\Ips\Action\Api\BaseApiAwareAction;
use PayumTW\Ips\Request\Api\CreateTransaction;
use Payum\Core\Exception\RequestNotSupportedException;
use Payum\Core\Security\GenericTokenFactoryAwareTrait;
use Payum\Core\Security\GenericTokenFactoryAwareInterface;

class CaptureAction extends BaseApiAwareAction implements ActionInterface, GatewayAwareInterface, GenericTokenFactoryAwareInterface
{
    use GatewayAwareTrait;
    use GenericTokenFactoryAwareTrait;

    /**
     * {@inheritdoc}
     *
     * @param Capture $request
     */
    public function execute($request)
    {
        RequestNotSupportedException::assertSupports($this, $request);
        $details = ArrayObject::ensureArrayObject($request->getModel());

        $httpRequest = new GetHttpRequest();
        $this->gateway->execute($httpRequest);

        if (isset($httpRequest->request['paymentResult']) === true) {
            $response = $this->api->parseResponse($httpRequest->request);
            if ($this->api->verifyHash($response) === false) {
                $response['RspCode'] = '-1';
            }
            $details->replace($response);

            return;
        }

        $token = $request->getToken();
        $targetUrl = $token->getTargetUrl();

        if (empty($details['Merchanturl']) === true) {
            $details['Merchanturl'] = $targetUrl;
        }

        if (empty($details['FailUrl']) === true) {
            $details['FailUrl'] = $targetUrl;
        }

        if (empty($details['ServerUrl']) === true) {
            $notifyToken = $this->tokenFactory->createNotifyToken(
                $token->getGatewayName(),
                $token->getDetails()
            );

            $details['ServerUrl'] = $notifyToken->getTargetUrl();
        }

        $this->gateway->execute(new CreateTransaction($details));
    }

    /**
     * {@inheritdoc}
     */
    public function supports($request)
    {
        return
            $request instanceof Capture &&
            $request->getModel() instanceof \ArrayAccess;
    }
}
