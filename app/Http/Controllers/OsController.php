<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Tc\Classes\TcEtl;
use App\Models\Os;


class OsController extends Controller
{   


    private $request;
    private $identifier;

    /**
    * @param Request $request, int $id
    * @return array
    * @throws Exception
    */
    public function __construct(Request $request)
    {
        $this->request = $request;

        $appKeys = $this->getAppKeyData($request->headers->get('access-application-key'));
        $data = $this->snakeToCamelCase($this->request->all());

        if (strlen($appKeys) == 0) {
            throw new \Exception("Fabrica inativada ou inexistente.", 404);
        }

        $this->identifier = $this->getIdentifier($appKeys);
    }

    public function getOsAberta(){


        $builder = Os::select(
            "tbl_os.os", 
            "tbl_os.data_abertura",
            "tbl_fabrica.fabrica_uuid",
            "tbl_fabrica.bi_password",
            "tbl_os.posto",
            "tbl_posto.pais",
            "tbl_posto_fabrica.contato_estado",
            "tbl_posto_fabrica.contato_cidade",
            "tbl_posto_fabrica.contato_cep",
            "tbl_os.consumidor_estado",
            "tbl_os.consumidor_cidade",
            "tbl_os.consumidor_cep",
            "tbl_os.data_digitacao",
            "tbl_os.data_abertura",
            "tbl_os.produto",
            "tbl_os.defeito_reclamado",
            "tbl_os.defeito_reclamado_descricao",

            DB::raw("
                (
                    SELECT row_to_json(d)
                    FROM (
                        select cnpj,
                        nome ,
                        tbl_posto_fabrica.nome_fantasia,
                        contato_endereco,
                        contato_numero,
                        contato_complemento,
                        contato_bairro,
                        pais,
                        contato_cidade,
                        contato_estado,
                        contato_cep ,
                        array_to_json(array[contato_fone_residencial, contato_fone_comercial] || contato_telefones) as telefone
                        from tbl_posto a
                        join tbl_posto_fabrica using(posto)
                        where tbl_posto.posto = a.posto 
                        and tbl_posto_fabrica.fabrica = tbl_os.fabrica
                    ) d
                ) as dados_posto"
            ),

            DB::raw(
                "(
                    SELECT row_to_json(d)
                    FROM (
                        select 
                        linha                    ,
                        descricao                ,
                        voltagem                 ,
                        referencia               ,
                        garantia                 ,
                        mao_de_obra              ,
                        origem                   ,
                        nome_comercial           ,
                        numero_serie_obrigatorio ,
                        referencia_pesquisa      ,
                        familia                  ,
                        referencia_fabrica       ,
                        mao_de_obra_admin        ,
                        troca_obrigatoria        ,
                        produto_critico       
                        from tbl_produto p 
                        where p.produto = tbl_os.produto   
                    ) d
                 ) as dados_produto"
            ),

            DB::raw(
                "(
                    SELECT row_to_json(d)
                    FROM (
                        select consumidor_cidade,
                        consumidor_estado
                        from tbl_os o
                        where o.os = tbl_os.os 
                    ) d
                ) as dados_consumidor"
            )
        );

        $builder->join("tbl_posto_fabrica",function($join){
            $join->on("tbl_os.posto","=","tbl_posto_fabrica.posto");
            $join->on("tbl_os.fabrica", "=", "tbl_posto_fabrica.fabrica");
        });

        $builder->join("tbl_posto",function($join){
            $join->on("tbl_posto_fabrica.posto","=","tbl_posto.posto");
        });

        $builder->join("tbl_fabrica",function($join){
            $join->on("tbl_os.fabrica","=","tbl_fabrica.fabrica");
        });

        $builder->orderBy('tbl_os.data_abertura');
        $builder->where("tbl_os.fabrica", "=" , $this->identifier);
        $builder->limit(1);
        $result = $builder->get()->toArray();  

        $etl = new TcEtl();
        $etl->insert('bi_os_aberta', $result);
    }   

}
