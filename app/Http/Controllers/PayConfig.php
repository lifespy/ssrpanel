<?php

namespace App\Http\Controllers;

class PayConfig
{

    public $pay_config;

    public function init()
    {
        $this->pay_config = [
            "secret" => "0FF6FB2B5F9D1EF410BE8ACE7C6F66DD",
            "accesskey" => "F031E9CBA3DA72AE3C6D3EE3B270E78D",
            "notify_url" => "/yft/notify",
            "return_url" => "/yft/notify",
            "type" => "aliPay"
        ];
    }
}