<?php


class Local_Payment_PayPal_AdaptivePayment_ResponsePayMock implements Local_Payment_PayPal_PaymentInterface
{

    public $transactionStatus;
    public $payKey;
    public $transactionAmount;
    public $transactionReceiver;

    /** @var array|null */
    protected $_rawResponse;

    public $successful = true;

    public $status = null;

    public $transactionId = null;

    /**
     * @param array|null $rawResponse
     */
    function __construct($rawResponse = null)
    {
        if (isset($rawResponse)) {
            $this->_rawResponse = $rawResponse;
        }
    }

    /**
     * @return bool
     */
    public function isSuccessful()
    {
        return $this->successful;
    }

    /**
     * @return mixed
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * @return mixed
     */
    public function getTransactionId()
    {
        return $this->transactionId;
    }

    public function getTransactionStatus()
    {
        return $this->transactionStatus;
    }

    /**
     * @return mixed
     */
    public function getPaymentId()
    {
        return $this->payKey;
    }

    /**
     * @param $name
     * @return mixed
     */
    public function getField($name)
    {
        return null;
    }

    /**
     * @return array|null
     */
    public function getRawMessage()
    {
        return null;
    }

    /**
     * @return string
     */
    public function getProviderName()
    {
        return 'paypal-MOCK';
    }

    public function getTransactionAmount()
    {
        return $this->transactionAmount;
    }

    public function getTransactionReceiver()
    {
        return $this->transactionReceiver;
    }

}