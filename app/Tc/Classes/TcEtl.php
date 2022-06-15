<?php

namespace App\Tc\Classes;

use Wizaplace\Etl\Etl;
use Wizaplace\Etl\Extractors\Collection;
use Wizaplace\Etl\Transformers\Trim;
use Wizaplace\Etl\Loaders\Insert;
use Wizaplace\Etl\Database\Manager;
use Wizaplace\Etl\Database\ConnectionFactory;

class TcEtl
{

	private $_manager;
	private $_etl;
	private $_extractor;
	private $_transformer;
	private $_loader;

	public function __construct(){

	  	$config = [
            'driver'    => 'mysql',
            'host'      => 'localhost',
            'port'      => '3306',
            'database'  => 'teste_bi',
            'username'  => 'root',
            'password'  => 'root',
            'charset'   => 'utf8',
            'collation' => 'utf8_unicode_ci',
        ];

        $this->_manager = new Manager(new ConnectionFactory());
        $this->_manager->addConnection($config);
        $this->_etl = new Etl();
        $this->_extractor = new Collection();
     	$this->_transformer = new Trim();   
	}

	public function insert($table = '', $data = array()){

		$this->_loader = new Insert($this->_manager);

        $this->_etl->extract($this->_extractor, $data)
        ->transform(
            $this->_transformer
        )
        ->load($this->_loader, $table)
        ->run();
	}

}
