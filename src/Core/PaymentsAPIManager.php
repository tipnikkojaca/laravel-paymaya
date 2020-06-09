<?php

namespace Aceraven777\PayMaya\Core;

use Aceraven777\PayMaya\PayMayaSDK;

class PaymentsAPIManager
{
    private $publicApiKey;
    private $secretApiKey;
    private $environment;
    private $baseUrl;
    private $httpHeaders;

    public function __construct()
    {
        $this->publicApiKey = PayMayaSDK::getInstance()->getCheckoutPublicApiKey();
        $this->secretApiKey = PayMayaSDK::getInstance()->getCheckoutSecretApiKey();
        $this->environment = PayMayaSDK::getInstance()->getCheckoutEnvironment();
        $this->baseUrl = $this->getBaseUrl();
        $this->httpHeaders = ['Content-Type' => 'application/json'];
    }

    /**
     * Base URL for Payments API
     *
     * @return string|null
     */
    private function getBaseUrl()
    {
        $baseUrl = null;
        switch ($this->environment) {
            case 'PRODUCTION':
                $baseUrl = Constants::PAYMENTS_PRODUCTION_URL;
                break;
            default:
                $baseUrl = Constants::PAYMENTS_SANDBOX_URL;
        }

        return $baseUrl;
    }

    /**
     * Set authorizations with corresponding
     * private/public key.
     *
     * @param $apiKey
     */
    private function useBasicAuthWithApiKey($apiKey)
    {
        $authorizationToken = base64_encode($apiKey.':');
        $this->httpHeaders['Authorization'] = 'Basic '.$authorizationToken;
    }

    /* Tokenized Payments */

    /**
     * Creates payment token with the supplied
     * buyer's card information.
     *
     * TODO: You should never send card information to the server!
     *
     * @param $cardInformation
     * @return false|string
     * @throws \Exception
     */
    public function createPaymentToken($cardInformation)
    {
        $this->useBasicAuthWithApiKey($this->publicApiKey);
        $httpConfig = new HTTPConfig($this->baseUrl.'/v1/payment-tokens',
            'POST',
            $this->httpHeaders
        );
        $httpConnection = new HTTPConnection($httpConfig);
        $payload = json_encode($cardInformation);
        $response = $httpConnection->execute($payload);

        return $response;
    }

    /**
     * Create payment with a supplied token.
     * The token represents the buyer's card and
     * will be used for charging.
     *
     * @param $paymentInformation
     * @return false|string
     * @throws \Exception
     */
    public function createPaymentWithToken($paymentInformation)
    {
        $this->useBasicAuthWithApiKey($this->secretApiKey);
        $httpConfig = new HTTPConfig($this->baseUrl.'/v1/payments',
            'POST',
            $this->httpHeaders
        );
        $httpConnection = new HTTPConnection($httpConfig);
        $payload = json_encode($paymentInformation);
        $response = $httpConnection->execute($payload);

        return $response;
    }

    /**
     * Retrieve the payment information according
     * to the supplied paymentId
     *
     * @param $paymentId
     * @return false|string
     * @throws \Exception
     */
    public function retrievePayment($paymentId)
    {
        $this->useBasicAuthWithApiKey($this->secretApiKey);
        $httpConfig = new HTTPConfig($this->baseUrl.'/v1/payments'.$paymentId,
            'GET',
            $this->httpHeaders
        );
        $httpConnection = new HTTPConnection($httpConfig);
        $response = $httpConnection->execute(null);

        return $response;
    }

    /**
     * Retrieve the status of a payment
     * of a given Request Reference Number.
     *
     * @param $requestReferenceNumber
     * @return false|string
     * @throws \Exception
     */
    public function checkPaymentStatus($requestReferenceNumber)
    {
        $this->useBasicAuthWithApiKey($this->secretApiKey);
        $httpConfig = new HTTPConfig($this->baseUrl.'/v1/payments-rrns/'.$requestReferenceNumber,
            'GET',
            $this->httpHeaders
        );
        $httpConnection = new HTTPConnection($httpConfig);
        $response = $httpConnection->execute(null);

        return $response;
    }

    /* Card Vault: Customers */

    /**
     * Used to register a customer in the system.
     *
     * @param $customerInformation
     * @return false|string
     * @throws \Exception
     */
    public function registerCustomer($customerInformation)
    {
        $this->useBasicAuthWithApiKey($this->secretApiKey);
        $httpConfig = new HTTPConfig($this->baseUrl.'/v1/customers',
            'POST',
            $this->httpHeaders
        );
        $httpConnection = new HTTPConnection($httpConfig);
        $payload = json_encode($customerInformation);
        $response = $httpConnection->execute($payload);

        return $response;
    }

    /**
     * Retrieve customer details.
     *
     * @param $customerId
     * @return false|string
     * @throws \Exception
     */
    public function retrieveCustomer($customerId)
    {
        $this->useBasicAuthWithApiKey($this->secretApiKey);
        $httpConfig = new HTTPConfig($this->baseUrl.'/v1/customers/'.$customerId,
            'GET',
            $this->httpHeaders
        );
        $httpConnection = new HTTPConnection($httpConfig);
        $response = $httpConnection->execute(null);

        return $response;
    }

    /**
     * Used to revise customer details via
     * the customer’s unique ID provided upon registration.
     *
     * @param $customerId
     * @param $customerInformation
     * @return false|string
     * @throws \Exception
     */
    public function updateCustomerDetails($customerId, $customerInformation)
    {
        $this->useBasicAuthWithApiKey($this->secretApiKey);
        $httpConfig = new HTTPConfig($this->baseUrl.'/v1/customers/'.$customerId,
            'PUT',
            $this->httpHeaders
        );
        $httpConnection = new HTTPConnection($httpConfig);
        $payload = json_encode($customerInformation);
        $response = $httpConnection->execute($payload);

        return $response;
    }

    /**
     * Used to remove the a customer’s set of details in the system.
     *
     * @param $customerId
     * @return false|string
     * @throws \Exception
     */
    public function unregisterCustomer($customerId)
    {
        $this->useBasicAuthWithApiKey($this->secretApiKey);
        $httpConfig = new HTTPConfig($this->baseUrl.'/v1/customers/'.$customerId,
            'DELETE',
            $this->httpHeaders
        );
        $httpConnection = new HTTPConnection($httpConfig);
        $response = $httpConnection->execute(null);

        return $response;
    }

    /* Card Vault: Cards */

    /**
     * Saves a customer’s card into the
     * card vault given a PaymentToken.
     *
     * @param $customerId
     * @param $paymentTokenInformation
     * @return false|string
     * @throws \Exception
     */
    public function vaultACard($customerId, $paymentTokenInformation)
    {
        $this->useBasicAuthWithApiKey($this->secretApiKey);
        $httpConfig = new HTTPConfig($this->baseUrl.'/v1/customers/'.$customerId.'/cards',
            'POST',
            $this->httpHeaders
        );
        $httpConnection = new HTTPConnection($httpConfig);
        $payload = json_encode($paymentTokenInformation);
        $response = $httpConnection->execute($payload);

        return $response;
    }

    /**
     * Used to retrieve all of the vaulted cards
     * of a customer.
     *
     * @param $customerId
     * @return false|string
     * @throws \Exception
     */
    public function retrieveCustomerVaultedCards($customerId)
    {
        $this->useBasicAuthWithApiKey($this->secretApiKey);
        $httpConfig = new HTTPConfig($this->baseUrl.'/v1/customers/'.$customerId.'/cards',
            'GET',
            $this->httpHeaders
        );
        $httpConnection = new HTTPConnection($httpConfig);
        $response = $httpConnection->execute(null);

        return $response;
    }

    /**
     * Used to retrieve limited card
     * information of the customer.
     *
     * @param $customerId
     * @param $cardToken
     * @return false|string
     * @throws \Exception
     */
    public function retrieveCustomerVaultedCard($customerId, $cardToken)
    {
        $this->useBasicAuthWithApiKey($this->secretApiKey);
        $httpConfig = new HTTPConfig($this->baseUrl.'/v1/customers/'.$customerId.'/cards/'.$cardToken,
            'GET',
            $this->httpHeaders
        );
        $httpConnection = new HTTPConnection($httpConfig);
        $response = $httpConnection->execute(null);

        return $response;
    }

    /**
     * Used to deactivate permanently the
     * specific vaulted card of a customer.
     *
     * @param $customerId
     * @param $cardToken
     * @return false|string
     * @throws \Exception
     */
    public function deleteCustomerVaultedCard($customerId, $cardToken)
    {
        $this->useBasicAuthWithApiKey($this->secretApiKey);
        $httpConfig = new HTTPConfig($this->baseUrl.'/v1/customers/'.$customerId.'/cards/'.$cardToken,
            'DELETE',
            $this->httpHeaders
        );
        $httpConnection = new HTTPConnection($httpConfig);
        $response = $httpConnection->execute(null);

        return $response;
    }

    /**
     * Used to make the card the default card of a customer.
     *
     * Note: this endpoint may seem to be used to update the card entirely
     * but at current implementation of PayMaya, the documentation
     * explicitly and specifically says it updates the default
     * card of a particular customer.
     *
     * @param $customerId
     * @param $cardToken
     * @param $isDefault
     * @return false|string
     * @throws \Exception
     */
    public function setDefaultCustomerVaultedCard($customerId, $cardToken, $isDefault)
    {
        $data = [
            'isDefault' => $isDefault
        ];

        $this->useBasicAuthWithApiKey($this->secretApiKey);
        $httpConfig = new HTTPConfig($this->baseUrl.'/v1/customers/'.$customerId.'/cards/'.$cardToken,
            'PUT',
            $this->httpHeaders
        );
        $httpConnection = new HTTPConnection($httpConfig);
        $payload = json_encode($data);
        $response = $httpConnection->execute($data);

        return $response;
    }

    /**
     * Used to initiate payment from a customer’s card.
     *
     * @param $customerId
     * @param $cardToken
     * @param $paymentInformation
     * @return false|string
     * @throws \Exception
     */
    public function executePayment($customerId, $cardToken, $paymentInformation)
    {
        $this->useBasicAuthWithApiKey($this->secretApiKey);
        $httpConfig = new HTTPConfig($this->baseUrl.'/v1/customers/'.$customerId.'/cards/'.$cardToken,
            'POST',
            $this->httpHeaders
        );
        $httpConnection = new HTTPConnection($httpConfig);
        $payload = json_encode($paymentInformation);
        $response = $httpConnection->execute($payload);

        return $response;
    }

    /* Subscriptions: Customer Subscription Endpoints */

    /**
     * Used to create a subscription plan for a customer.
     *
     * A subscription may have any one of the following intervals:
     * DAY, MONTH, and YEAR.
     *
     * @param $customerId
     * @param $cardToken
     * @param $subscriptionIntervals
     * @return false|string
     * @throws \Exception
     */
    public function createCustomerSubscription($customerId, $cardToken, $subscriptionIntervals)
    {
        $this->useBasicAuthWithApiKey($this->secretApiKey);
        $httpConfig = new HTTPConfig($this->baseUrl.'/v1/customers/'.$customerId.'/cards/'.$cardToken.'/subscriptions',
            'POST',
            $this->httpHeaders
        );
        $httpConnection = new HTTPConnection($httpConfig);
        $payload = json_encode($subscriptionIntervals);
        $response = $httpConnection->execute($payload);

        return $response;
    }

    /**
     * Used to retrieve the list of customer's subscriptions (given their card token).
     *
     * @param $customerId
     * @param $cardToken
     * @return false|string
     * @throws \Exception
     */
    public function retrieveCustomerSubscriptions($customerId, $cardToken)
    {
        $this->useBasicAuthWithApiKey($this->secretApiKey);
        $httpConfig = new HTTPConfig($this->baseUrl.'/v1/customers/'.$customerId.'/cards/'.$cardToken.'/subscriptions',
            'GET',
            $this->httpHeaders
        );
        $httpConnection = new HTTPConnection($httpConfig);
        $response = $httpConnection->execute(null);

        return $response;
    }

    /* Subscriptions: Subscription Endpoints */

    /**
     * Used to retrieve a particular subscription
     * given its unique ID.
     *
     * @param $subscriptionId
     * @return false|string
     * @throws \Exception
     */
    public function getSubscription($subscriptionId)
    {
        $this->useBasicAuthWithApiKey($this->secretApiKey);
        $httpConfig = new HTTPConfig($this->baseUrl.'/v1/subscriptions/'.$subscriptionId,
            'GET',
            $this->httpHeaders
        );
        $httpConnection = new HTTPConnection($httpConfig);
        $response = $httpConnection->execute(null);

        return $response;
    }

    /**
     * Used to cancel a particular subscription
     * given its unique ID.
     *
     * @param $subscriptionId
     * @return false|string
     * @throws \Exception
     */
    public function deleteSubscription($subscriptionId)
    {
        $this->useBasicAuthWithApiKey($this->secretApiKey);
        $httpConfig = new HTTPConfig($this->baseUrl.'/v1/subscriptions/'.$subscriptionId,
            'DELETE',
            $this->httpHeaders
        );
        $httpConnection = new HTTPConnection($httpConfig);
        $response = $httpConnection->execute(null);

        return $response;
    }

    /**
     * Used to retrieve a list of payments made under the specified subscription.
     *
     * @param $subscriptionId
     * @return false|string
     * @throws \Exception
     */
    public function retrieveSubscriptionPaymentsList($subscriptionId)
    {
        $this->useBasicAuthWithApiKey($this->secretApiKey);
        $httpConfig = new HTTPConfig($this->baseUrl.'/v1/subscriptions/'.$subscriptionId.'/payments',
            'GET',
            $this->httpHeaders
        );
        $httpConnection = new HTTPConnection($httpConfig);
        $response = $httpConnection->execute(null);

        return $response;
    }

    /* Webhooks */

    /**
     * Retrieve all of the registered webhooks.
     *
     * @return false|string
     * @throws \Exception
     */
    public function retrieveWebhooks()
    {
        $this->useBasicAuthWithApiKey($this->secretApiKey);
        $httpConfig = new HTTPConfig($this->baseUrl.'/v1/webhooks',
            'GET',
            $this->httpHeaders
        );
        $httpConnection = new HTTPConnection($httpConfig);
        $response = $httpConnection->execute(null);

        return $response;
    }

    /**
     * Retrieve the specific webhook specified by its unique ID.
     *
     * @param $webhookId
     * @return false|string
     * @throws \Exception
     */
    public function retrieveWebhook($webhookId)
    {
        $this->useBasicAuthWithApiKey($this->secretApiKey);
        $httpConfig = new HTTPConfig($this->baseUrl.'/v1/webhooks/'.$webhookId,
            'GET',
            $this->httpHeaders
        );
        $httpConnection = new HTTPConnection($httpConfig);
        $response = $httpConnection->execute(null);

        return $response;
    }

    /**
     * Register a webhook.
     *
     * @param $webhookInformation
     * @return false|string
     * @throws \Exception
     */
    public function registerWebhook($webhookInformation)
    {
        $this->useBasicAuthWithApiKey($this->secretApiKey);
        $httpConfig = new HTTPConfig($this->baseUrl.'/v1/webhooks',
            'POST',
            $this->httpHeaders
        );
        $httpConnection = new HTTPConnection($httpConfig);
        $payload = json_encode($webhookInformation);
        $response = $httpConnection->execute($payload);

        return $response;
    }

    /**
     * Update the registered webhook with new information.
     *
     * @param $webhookId
     * @param $webhookInformation
     * @return false|string
     * @throws \Exception
     */
    public function updateWebhook($webhookId, $webhookInformation)
    {
        $this->useBasicAuthWithApiKey($this->secretApiKey);
        $httpConfig = new HTTPConfig($this->baseUrl.'/v1/webhooks/'.$webhookId,
            'PUT',
            $this->httpHeaders
        );
        $httpConnection = new HTTPConnection($httpConfig);
        $payload = json_encode($webhookInformation);
        $response = $httpConnection->execute($payload);

        return $response;
    }

    /**
     * Remove a registered webhook.
     *
     * @param $webhookId
     * @return false|string
     * @throws \Exception
     */
    public function deleteWebhook($webhookId)
    {
        $this->useBasicAuthWithApiKey($this->secretApiKey);
        $httpConfig = new HTTPConfig($this->baseUrl.'/v1/webhooks/'.$webhookId,
            'DELETE',
            $this->httpHeaders
        );
        $httpConnection = new HTTPConnection($httpConfig);
        $response = $httpConnection->execute(null);

        return $response;
    }

    /**
     * Voids a successful payment transaction. This invalidates the payment authorized by the buyer.
     *
     * Note: $data parameter contains the "reason" field.
     *
     * @param $paymentId
     * @param $data
     * @return false|string
     * @throws \Exception
     */
    public function voidPayment($paymentId, $data)
    {
        $this->useBasicAuthWithApiKey($this->secretApiKey);
        $httpConfig = new HTTPConfig($this->baseUrl.'/v1/checkouts/'.$paymentId,
            'DELETE',
            $this->httpHeaders
        );
        $httpConnection = new HTTPConnection($httpConfig);
        $payload = json_encode($data);
        $response = $httpConnection->execute($payload);

        return $response;
    }

    /**
     * Used to retrieve all void attempts for a payment.
     *
     * @param $paymentId
     * @return false|string
     * @throws \Exception
     */
    public function retrieveVoidPaymentTransactions($paymentId)
    {
        $this->useBasicAuthWithApiKey($this->secretApiKey);
        $httpConfig = new HTTPConfig($this->baseUrl.'/v1/payments/'.$paymentId.'/voids',
            'GET',
            $this->httpHeaders
        );
        $httpConnection = new HTTPConnection($httpConfig);
        $response = $httpConnection->execute(null);

        return $response;
    }

    /**
     * Used to retrieve a void attempt/transaction.
     *
     * @param $paymentId
     * @return false|string
     * @throws \Exception
     */
    public function retrieveVoidPaymentTransaction($paymentId, $voidId)
    {
        $this->useBasicAuthWithApiKey($this->secretApiKey);
        $httpConfig = new HTTPConfig($this->baseUrl.'/v1/payments/'.$paymentId.'/voids/'.$voidId,
            'GET',
            $this->httpHeaders
        );
        $httpConnection = new HTTPConnection($httpConfig);
        $response = $httpConnection->execute(null);

        return $response;
    }

    /**
     * Used to refund the full/partial amount of a successful payment transaction.
     *
     * Note: $data variable holds the "reason" field and "totalAmount" object.
     *
     * @param $paymentId
     * @param $data
     * @return false|string
     * @throws \Exception
     */
    public function refundPayment($paymentId, $data)
    {
        $this->useBasicAuthWithApiKey($this->secretApiKey);
        $httpConfig = new HTTPConfig(
            $this->baseUrl.'/v1/payments/'.$paymentId.'/refunds',
            'POST',
            $this->httpHeaders
        );
        $httpConnection = new HTTPConnection($httpConfig);
        $payload = json_encode($data);
        $response = $httpConnection->execute($payload);

        return $response;
    }

    /**
     * Used to retrieve all refund attempts for a payment.
     *
     * @param $checkoutId
     * @return false|string
     * @throws \Exception
     */
    public function retrieveRefunds($checkoutId)
    {
        $this->useBasicAuthWithApiKey($this->secretApiKey);
        $httpConfig = new HTTPConfig(
            $this->baseUrl.'/v1/payments/'.$checkoutId.'/refunds',
            'GET',
            $this->httpHeaders
        );
        $httpConnection = new HTTPConnection($httpConfig);
        $response = $httpConnection->execute(null);

        return $response;
    }

    /**
     * Used to retrieve a refund attempt/transaction for a payment.
     *
     * @param $checkoutId
     * @param $refundId
     * @return false|string
     * @throws \Exception
     */
    public function retrieveRefundInfo($checkoutId, $refundId)
    {
        $this->useBasicAuthWithApiKey($this->secretApiKey);
        $httpConfig = new HTTPConfig(
            $this->baseUrl.'/v1/payments/'.$checkoutId.'/refunds/'.$refundId,
            'GET',
            $this->httpHeaders
        );
        $httpConnection = new HTTPConnection($httpConfig);
        $response = $httpConnection->execute(null);

        return $response;
    }

    /**
     * Used to compute the convenience fee imposed by the merchant.
     * This API can only be used if merchant is enabled
     * to compute for Merchant Discount Rate or MDR.
     *
     * For example, if the MDR is set at 0.037 (3.7%),
     * the convenience fee of 1000 PHP is 38.42 PHP.
     *
     * @param $data
     * @return false|string
     * @throws \Exception
     */
    public function computeConvenienceFee($data)
    {
        $this->useBasicAuthWithApiKey($this->secretApiKey);
        $httpConfig = new HTTPConfig(
            $this->baseUrl.'/v1/fees',
            'POST',
            $this->httpHeaders
        );
        $httpConnection = new HTTPConnection($httpConfig);
        $payload = json_encode($data);
        $response = $httpConnection->execute($payload);

        return $response;
    }

}
