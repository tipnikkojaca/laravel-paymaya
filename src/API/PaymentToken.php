<?php

namespace Aceraven777\PayMaya\API;

use Aceraven777\PayMaya\Core\PaymentsAPIManager;
use Aceraven777\PayMaya\Traits\ErrorHandler;

class PaymentToken
{
    use ErrorHandler;

    public $id;

    // Fields to be passed onto execute() function
    public $card;

    // Fields retrieved from execute()
    public $state;
    public $createdAt;
    public $updatedAt;
    public $issuer;

    private $apiManager;

    public function __construct()
    {
        $this->apiManager = new PaymentsAPIManager();
    }

    /**
     * This endpoint creates a payment token that represents your customer’s
     * credit or debit card details which can be used for payments and customer card
     * addition. The payment token is valid for a specific amount of time. Before it expires,
     * it is valid for single use only in payment transactions.
     *
     * TODO: You should never send card information to the server!
     *
     * @return bool|mixed
     * @throws \Exception
     */
    public function execute()
    {
        $cardInformation = json_decode(json_encode($this), true);
        $response = $this->apiManager->createPaymentToken($cardInformation);
        $responseArr = json_decode($response, true);

        if (! self::isResponseValid($responseArr)) {
            return false;
        }

        $this->id = $responseArr['paymentTokenId'];
        $this->state = $responseArr['state'];
        $this->createdAt = $responseArr['createdAt'];
        $this->updatedAt = $responseArr['updatedAt'];
        $this->issuer = $responseArr['issuer'];

        return $responseArr;
    }

    /**
     * Retrieves the payment given its unique ID.
     *
     * @return bool|mixed
     * @throws \Exception
     */
    public function retrieve()
    {
        $response = $this->apiManager->retrievePayment($this->id);
        $responseArr = json_decode($response, true);

        if (! self::isResponseValid($responseArr)) {
            return false;
        }

        return $responseArr;
    }

}
