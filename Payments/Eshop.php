<?php

namespace RestApiPay\Payments;


use RestApiPay\Components\Logger;

class Eshop extends AbstractPayment
{

    protected string $userNo = '04282149';
    protected string $paySecret = '80r1gVj57n1eo5GCn57172M7hQ77N20N';
    protected string $payUrl = 'https://www.yourshop.com/card/doPay';
    protected string $urlYourSite = 'https://example.com';
    public function checkInput($data):void
    {
        $res = [];
        $sign_i = $data['sign'];
        array_pop($data);
        $str = $this->sorting($data) . '&paySecret=' . $this->paySecret;
        $sign = strtoupper(md5($str));
        // проверка рекомендованная платежной системой
        if ($sign_i != $sign) {
            $res['message'] = 'Sign Error';
            $res['code'] = '1111';
        }
        if (!$this->luhnAlgorithm($data['cardNum'])) {
            $res['message'] = 'CardNum Error';
            $res['code'] = '1111';
        }
        if(!empty($res)){
            header('Content-Type: application/json');
            echo json_encode($res);
            die();
        }
    }
    public function pay(array $data):void
    {
        $this->setDataOutput($data);
        $payResult = $this->curlPost();
        $log= Logger::run('log.txt');
        $log->process($payResult);
        if ($payResult) {
            header('Content-Type: application/json');
            echo $payResult;
            die();
        }
    }

    /**
     * @param $array
     * @return string
     */
    private function sorting($array):string
    {
        ksort($array);
        $buff = "";
        foreach ($array as $k => $v) {
            if ($v != "" && !is_array($v)) {
                $buff .= $k . "=" . $v . "&";
            }
        }
        return trim($buff, "&");
    }
    protected function setDataOutput($data):void
    {
        if(empty($data['ip'])){
            $data['ip'] = $this->get_client_ip();
        }

        $dataOutput = array(
            'address' => $data['address'],
            'cardNum' => $data['cardNum'],
            'city' => $data['city'],
            'country' => $data['country'],
            'currency' => $data['currency'],
            'cvv2' => $data['cvv2'],
            'email' => $data['email'],
            'firstName' => $data['firstName'],
            'ip' => $data['ip'],
            'language' => $data['language'],
            'lastName' => $data['lastName'],
            'merOrderNo' => 'OrderNo' . time(),
            'month' => $data['month'],
            'notifyUrl' => $this->urlYourSite,
            'orderPrice' => $data['amount'],
            'phone' => $data['phone'],
            'productInfo' => $data['productInfo'],
            'requestUrl' => $this->urlYourSite,
            'returnURL' => $this->urlYourSite,
            'state' => $data['state'],
            'userNo' => $this->userNo,
            'year' => $data['year'],
            'zipCode' => $data['zipCode'],
        );
        $dataOutput['orderPrice'] = floatval($dataOutput['orderPrice']);
        $str = $this->sorting($dataOutput) . '&paySecret=' . $this->paySecret;
        $dataOutput['sign'] = strtoupper(md5($str));
        $this->dataOutput = $dataOutput;
    }
    private function curlPost()
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $this->payUrl);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 0);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_TIMEOUT, 60);
        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $this->dataOutput);
        $result = curl_exec($ch);
        curl_close($ch);
        return json_decode($result, 1);
    }
}