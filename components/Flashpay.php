<?php

namespace app\components;

use yii\base\Component;
use flashpay\PaymentPage;

class FlashPay extends Component
{
    public $merchantId;
    public $secretKey;

    public function init()
    {
        parent::init();

        // Include the SDK manually
        require_once __DIR__ . '/flashpay/PaymentPage.php';

        if (!$this->merchantId || !$this->secretKey) {
            throw new \Exception('FlashPay configuration is incomplete.');
        }
    }

    /**
     * Generate payment URL using FlashPay SDK
     */
    public function createPaymentUrl(array $params)
    {
        $pp = new PaymentPage($this->merchantId, $this->secretKey);

        $pp->setOrderId($params['order_id']);
        $pp->setAmount($params['amount']);
        $pp->setDescription($params['description']);
        $pp->setReturnUrl($params['return_url']);
        $pp->setFailUrl($params['fail_url']);

        // Optional: Language or other fields
        if (!empty($params['language'])) {
            $pp->setLanguage($params['language']);
        }

        return $pp->getPaymentUrl();
    }

    /**
     * Validate IPN (callback) data from FlashPay
     */
    public function validateSignature(array $postData)
    {
        return PaymentPage::isValidIPN($postData, $this->secretKey);
    }
}
