<?php

namespace RestApiPay\Payments;

use RestApiPay\Interfaces\PaymentInterface;

abstract class AbstractPayment implements PaymentInterface
{
    /**
     * @var array
     */
    protected array $dataOutput;

    /**
     * @param $data
     * @return mixed
     */
    abstract public function pay(array $data):void;

    /**
     * @param $data
     * @return void
     */
    abstract public function checkInput($data):void;

    /**
     * @param array $data
     * @return void
     */
    abstract protected function setDataOutput(array $data): void;

    /**
     * @param $digit
     * @return int
     */
    protected function luhnAlgorithm($digit):int
    {
        $number = strrev(preg_replace('/[^\d]/', '', $digit));
        $sum = 0;
        for ($i = 0, $j = strlen($number); $i < $j; $i++) {
            if (($i % 2) == 0) {
                $val = $number[$i];
            } else {
                $val = $number[$i] * 2;
                if ($val > 9) {
                    $val -= 9;
                }
            }
            $sum += $val;
        }
        return (($sum % 10) === 0);
    }

    /**
     * @return string
     */
    protected function get_client_ip():string
    {
        $ip= '0.0.0.0';
        if (getenv('HTTP_CLIENT_IP') && strcasecmp(getenv('HTTP_CLIENT_IP'), 'unknown')) {
            $ip = getenv('HTTP_CLIENT_IP');
        } elseif (getenv('HTTP_X_FORWARDED_FOR') && strcasecmp(getenv('HTTP_X_FORWARDED_FOR'), 'unknown')) {
            $ip = getenv('HTTP_X_FORWARDED_FOR');
        } elseif (getenv('REMOTE_ADDR') && strcasecmp(getenv('REMOTE_ADDR'), 'unknown')) {
            $ip = getenv('REMOTE_ADDR');
        } elseif (isset($_SERVER['REMOTE_ADDR']) && $_SERVER['REMOTE_ADDR'] && strcasecmp($_SERVER['REMOTE_ADDR'], 'unknown')) {
            $ip = $_SERVER['REMOTE_ADDR'];
        }
        return preg_match("/[\d\.]{7,15}/", $ip, $matches) ? $matches[0] : 'unknown';
    }

}