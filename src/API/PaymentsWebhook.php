<?php

namespace Aceraven777\PayMaya\API;

use Aceraven777\PayMaya\Core\PaymentsAPIManager;
use Aceraven777\PayMaya\Traits\ErrorHandler;

class PaymentsWebhook
{
    use ErrorHandler;

    const CHECKOUT_SUCCESS = 'CHECKOUT_SUCCESS';
    const CHECKOUT_FAILURE = 'CHECKOUT_FAILURE';
    const CHECKOUT_DROPOUT = 'CHECKOUT_DROPOUT';

    public $id;
    public $name;
    public $callbackUrl;

    private $apiManager;

    public function __construct()
    {
        $this->apiManager = new PaymentsAPIManager();
    }

    /**
     * Retrieve the all the webhooks.
     *
     * @return array
     * @throws \Exception
     */
    public static function retrieve()
    {
        $apiManager = new PaymentsAPIManager();
        $response = $apiManager->retrieveWebhooks();
        $responseArr = json_decode($response, true);

        if (! self::isResponseValid($responseArr)) {
            return [];
        }

        if (isset($responseArr['code']) || isset($responseArr['message'])) {
            return [];
        }

        $webhooks = [];
        foreach ($responseArr as $webhookInfo) {
            $webhook = new self();
            $webhook->id = $webhookInfo['id'];
            $webhook->name = $webhookInfo['name'];
            $webhook->callbackUrl = $webhookInfo['callbackUrl'];
            $webhooks[] = $webhook;
        }

        return $webhooks;
    }

    /**
     * Register a webhook URL.
     *
     * @return bool|mixed
     * @throws \Exception
     */
    public function register()
    {
        $webhookInformation = json_decode(json_encode($this), true);
        $response = $this->apiManager->registerWebhook($webhookInformation);
        $responseArr = json_decode($response, true);

        if (! self::isResponseValid($responseArr)) {
            return false;
        }

        $this->id = $responseArr['id'];

        return $responseArr;
    }

    /**
     * Update a registered webhook given its unique webhook ID.
     *
     * @return bool|mixed
     * @throws \Exception
     */
    public function update()
    {
        $webhookInformation = json_decode(json_encode($this), true);
        $response = $this->apiManager->updateWebhook($this->id, $webhookInformation);
        $responseArr = json_decode($response, true);

        if (! self::isResponseValid($responseArr)) {
            return false;
        }

        $this->id = $responseArr['id'];
        $this->name = $responseArr['name'];
        $this->callbackUrl = $responseArr['callbackUrl'];

        return $responseArr;
    }

    /**
     * Unregister a webhook.
     *
     * @return bool|mixed
     * @throws \Exception
     */
    public function delete()
    {
        $response = $this->apiManager->deleteWebhook($this->id);
        $responseArr = json_decode($response, true);

        if (! self::isResponseValid($responseArr)) {
            return false;
        }

        $this->id = null;
        $this->name = null;
        $this->callbackUrl = null;

        return $responseArr;
    }
}
