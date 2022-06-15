<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\DB;
use Laravel\Lumen\Routing\Controller as BaseController;
use Illuminate\Http\Request;
use App\Http\AccessControl;
use App\Models\Fabrica;

class Controller extends BaseController
{
    /**
    * @param string $appKey
    * @return string
    */
    public function getAppKeyData($appKey)
    {   
        $accessControl = new AccessControl();
        $retorno = $accessControl->getAppKeyData($appKey);

        if (isset($retorno["client"]["code"]) && strlen($retorno["client"]["code"]) > 0) {
            return $retorno["client"]["code"];
        }
        return null;
    }

    /**
    * @param string $appKey
    * @return string
    */
    public function getAppEnvironment($appKey)
    {

        $accessControl = new AccessControl();
        $retorno = $accessControl->getAppKeyData($appKey);

        if (isset($retorno["key_type"]["system_code"]) && strlen($retorno["key_type"]["system_code"]) > 0) {
            return $retorno["key_type"]["system_code"];
        }
        return null;
    }

    /**
    * @param array $request
    * @return array
    */
    public function snakeToCamelCase($request) 
    {
        $retorno = [];
        foreach ($request as $chave => $valor) {

            $novaChave = ltrim(strtolower(preg_replace('/(?<!^)[A-Z]/', '_$0', $chave )), '_');
            $retorno[$novaChave] = is_array($valor) ? $this->snakeToCamelCase($valor) : $valor;

        }

        return $retorno;
    }

    /**
    * @param string $appKeys
    * @return int
    */
    public function getIdentifier($appKeys)
    {
        $fabrica = Fabrica::where("fabrica", "=", $appKeys)->first();
        return $fabrica->fabrica;
    }
}
