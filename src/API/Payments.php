<?php

namespace Aceraven777\PayMaya\API;

use Aceraven777\PayMaya\Core\PaymentsAPIManager;
use Aceraven777\PayMaya\Traits\ErrorHandler;

class Payments
{
    use ErrorHandler;

    public $id;
    public $verificationUrl;

    // Fields to be passed onto execute() function
    public $requestReferenceNumber;
    public $paymentTokenId;
    public $buyer;
    public $totalAmount;
    public $redirectUrl;

//    // Fields retrieved from retrieve() function
//    public $isPaid;
//    public $status;
//    public $amount;
//    public $currency;
//    public $canVoid;
//    public $canRefund;
//    public $createdAt;
//    public $updatedAt;
//    public $description;
//    public $receiptNumber;


    private $apiManager;

    public function __construct()
    {
        $this->apiManager = new PaymentsAPIManager();
    }

    /**
     * Creates a Payment object given a Payment Token ID. PayMaya will automatically try
     * to charge the card upon creation of the Payment object. By default,
     * the API call for Payment creation is blocking (synchronous).
     *
     * @return bool|mixed
     * @throws \Exception
     */
    public function execute()
    {
        $paymentInformation = json_decode(json_encode($this), true);
        $response = $this->apiManager->createPaymentWithToken($paymentInformation);
        $responseArr = json_decode($response, true);

        if (! self::isResponseValid($responseArr)) {
            return false;
        }

        $this->id = $responseArr['id'];
        $this->verificationUrl = $responseArr['verificationUrl'];

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
