<?php

namespace RestApiPay;

use RestApiPay\Interfaces\PaymentInterface;
use RestApiPay\Payments\Eshop;

class App implements PaymentInterface
{

    private object $s_payment;


    public function __construct()
    {
        $this->s_payment = new Eshop();
    }

    public function pay($data):void
    {
        $this->s_payment->pay($data);
    }
    public function checkInput($data):void
    {
        $this->s_payment->checkInput($data);
    }
}