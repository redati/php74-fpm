<?php

namespace App\Magento;

class Magento {

    public $api;

    public function __construct(RestApi  $service)
    {
        $this->api = $service;
        $this->api->setUsername(config('app.mg_un'));
        $this->api->setPassword(config('app.mg_pw'));
        $this->api->setUrl(config('app.mg_ur').'/index.php/rest/default/V1/');
        $this->api->setStoreCode(config('app.mg_st'));
        $this->api->init();
        return $this->api;
    }

}
