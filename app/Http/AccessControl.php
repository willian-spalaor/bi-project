<?php

namespace App\Http;

use Illuminate\Http\Request;
use GuzzleHttp\Client;

class AccessControl
{

    public function getAppKeyData($appKey)
    {

        $client = new Client([
            'http_errors' => false
        ]);

        $response = $client->get("https://api2.telecontrol.com.br/access-control/applicationKey/applicationKey/" . $appKey);

        return json_decode($response->getBody()->getContents(), 1);
    }

}
