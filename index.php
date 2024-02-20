<?php

$p = new \RestApiPay\App();
$p->checkInput($_POST);
$p->pay($_POST);
