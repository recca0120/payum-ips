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

        parse_str('paymentResult=%3CIps%3E%3CGateWayRsp%3E%3Chead%3E%3CReferenceID%3E%3C%2FReferenceID%3E%3CRspCode%3E000000%3C%2FRspCode%3E%3CRspMsg%3E%3C%21%5BCDATA%5B%E4%BA%A4%E6%98%93%E6%88%90%E5%8A%9F%EF%BC%81%5D%5D%3E%3C%2FRspMsg%3E%3CReqDate%3E20160922115657%3C%2FReqDate%3E%3CRspDate%3E20160922115726%3C%2FRspDate%3E%3CSignature%3E6e521198674636da79907d92a527cafe%3C%2FSignature%3E%3C%2Fhead%3E%3Cbody%3E%3CMerBillNo%3E57e3560d0361d%3C%2FMerBillNo%3E%3CCurrencyType%3E156%3C%2FCurrencyType%3E%3CAmount%3E0.1%3C%2FAmount%3E%3CDate%3E20160922%3C%2FDate%3E%3CStatus%3EY%3C%2FStatus%3E%3CMsg%3E%3C%21%5BCDATA%5B%E6%94%AF%E4%BB%98%E6%88%90%E5%8A%9F%EF%BC%81%5D%5D%3E%3C%2FMsg%3E%3CIpsBillNo%3EBO20160922115447069368%3C%2FIpsBillNo%3E%3CIpsTradeNo%3E2016092211095717482%3C%2FIpsTradeNo%3E%3CRetEncodeType%3E17%3C%2FRetEncodeType%3E%3CBankBillNo%3E710002896036%3C%2FBankBillNo%3E%3CResultType%3E0%3C%2FResultType%3E%3CIpsBillTime%3E20160922115721%3C%2FIpsBillTime%3E%3C%2Fbody%3E%3C%2FGateWayRsp%3E%3C%2FIps%3E', $_REQUEST);
        $httpRequest = new GetHttpRequest();
        $this->gateway->execute($httpRequest);

        if ($this->api->isSandbox() === true) {
            $httpRequest->request = $this->api->generateTestingResponse($details->toUnsafeArray());
        }

        if (isset($httpRequest->request['paymentResult']) === true) {
            dump($this->api->parseResult($httpRequest->request), 123);
            exit;
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
