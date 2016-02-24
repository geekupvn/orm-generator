<?php

namespace Classes;

use Classes\AdapterConfig\None;
use Classes\AdapterConfig\ZendFrameworkOne;
use Classes\AdaptersDriver\Dblib;
use Classes\AdaptersDriver\Mssql;
use Classes\AdaptersDriver\Mysql;
use Classes\AdaptersDriver\Pgsql;
use Classes\AdaptersDriver\Sqlsrv;

/**
 * @author Pedro Alarcao <phacl151@gmail.com>
 * @link   https://github.com/pedro151
 */
class Config
{

    /**
     * @var string
     */
    private $version = "1.0";

    /**
     * @var array
     */
    private $argv = array ();

    /**
     * @var \Classes\AdapterConfig\AbstractAdapter
     */
    private $adapterConfig;

    /**
     * @var \Classes\AdaptersDriver\AbsractAdapter
     */
    private $adapterDriver;

    public function __construct ( $argv )
    {
        if ( array_key_exists ( 'help' , $argv ) )
        {
            die ( $this->getUsage () );
        }

        $configDefaul = parse_ini_file ( dirname ( __FILE__ )
                                         . '/../configs/config.ini' , true );
        $this->argv = $argv + array_filter ( $configDefaul[ 'main' ] );

        if ( strtolower ( $this->argv[ 'framework' ] ) == 'none' )
        {
            $this->argv += $configDefaul[ 'none' ];
        }

        $this->factoryConfig ();
        $this->factoryDriver ();
        unset( $this->argv );
    }

    /**
     * @return string
     */
    public function getUsage ()
    {
        return <<<USAGE
parameters:
    --database            : database name
    --driver              : driver do banco de dados
    --framework           : framework
    --path                : specify where to create the files (default is current directory)
 *  --table               : table name (parameter can be used more then once)
    --all-tables          : create classes for all the scripts in the database

 example: php DAO-generator.php --framework=zend_framework --database=foo --table=foobar

Data Access Object DAO-generator By: Pedro Alarcao Version: $this->version
USAGE;
    }

    /**
     *
     */
    private function compileListParamTables ()
    {
        // TODO: implement here
    }

    /**
     * analisa a opção e cria a instancia do Atapter do determinado framework
     *
     */
    private function factoryConfig ()
    {
        switch ( strtolower ( $this->argv[ 'framework' ] ) )
        {
            case 'none':
                $this->adapterConfig = new None( $this->argv );
                break;
            case 'zend_framework':
                $this->adapterConfig = new ZendFrameworkOne( $this->argv );
                break;
        }

    }

    /**
     * Analisa a opção e instancia o determinado banco de dados
     *
     */
    private function factoryDriver ()
    {
        switch ( $this->argv[ 'driver' ] )
        {
            case 'pgsql':
            case 'pdo_pgsql':
                $this->adapterDriver = new Pgsql( $this->getAdapterConfig () );
                break;
            case 'mysql':
            case 'pdo_mysql':
                $this->adapterDriver = new Mysql( $this->getAdapterConfig () );
                break;
            case 'mssql':
                $this->adapterDriver = new Mssql( $this->getAdapterConfig () );
                break;
            case 'dblib':
                $this->adapterDriver = new Dblib( $this->getAdapterConfig () );
                break;
            case 'sqlsrv':
                $this->adapterDriver = new Sqlsrv( $this->getAdapterConfig () );
                break;
        }

    }

    /**
     * @return AdapterConfig\AbstractAdapter
     */
    public function getAdapterConfig ()
    {
        return $this->adapterConfig;
    }

    /**
     * @return AdaptersDriver\AbsractAdapter
     */
    public function getAdapterDriver ()
    {
        return $this->adapterDriver;
    }

}