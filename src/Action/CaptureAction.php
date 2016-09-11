<?php

namespace PayumTW\Ips\Action;

use Payum\Core\Action\ActionInterface;
use Payum\Core\ApiAwareInterface;
use Payum\Core\ApiAwareTrait;
use Payum\Core\Bridge\Spl\ArrayObject;
use Payum\Core\Exception\RequestNotSupportedException;
use Payum\Core\Exception\UnsupportedApiException;
use Payum\Core\GatewayAwareInterface;
use Payum\Core\GatewayAwareTrait;
use Payum\Core\Reply\HttpPostRedirect;
use Payum\Core\Request\Capture;
use Payum\Core\Request\GetHttpRequest;
use Payum\Core\Security\GenericTokenFactoryAwareInterface;
use Payum\Core\Security\GenericTokenFactoryAwareTrait;
use PayumTW\Ips\Api;

class CaptureAction implements ActionInterface, ApiAwareInterface, GatewayAwareInterface, GenericTokenFactoryAwareInterface
{
    use ApiAwareTrait;
    use GatewayAwareTrait;
    use GenericTokenFactoryAwareTrait;

    /**
     * {@inheritdoc}
     */
    public function setApi($api)
    {
        if (false == $api instanceof Api) {
            throw new UnsupportedApiException(sprintf('Not supported. Expected %s instance to be set as api.', Api::class));
        }

        $this->api = $api;
    }

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

        if ($this->api->isSandbox() === true) {
            $httpRequest->request = $this->api->generateTestingResponse($details->toUnsafeArray());
        }

        if (isset($httpRequest->request['paymentResult']) === true) {
            $details->replace($this->api->parseResult($httpRequest->request));

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

        throw new HttpPostRedirect(
            $this->api->getApiEndpoint(),
            $this->api->preparePayment($details->toUnsafeArray())
        );
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
