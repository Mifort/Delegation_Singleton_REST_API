<?php

namespace RestApiPay\Interfaces;

interface PaymentInterface
{
    public function pay(array $data):void;

    public function checkInput(array $data):void;


}