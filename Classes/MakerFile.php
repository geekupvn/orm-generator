<?php

namespace Classes;

use Classes\AdapterMakerFile\AbstractAdapter;
use Classes\AdapterMakerFile\DbTable;
use Classes\AdapterMakerFile\Entity;
use Classes\AdapterMakerFile\Model;


/**
 * @author Pedro Alarcao <phacl151@gmail.com>
 * @link   https://github.com/pedro151
 */
class MakerFile
{
    const SEPARETOR = '_';

    /**
     * @type string[]
     */
    public $location = array ();

    /**
     * caminho de pastas Base
     *
     * @type string
     */
    private $baseLocation = '';

    /**
     * @type \Classes\AdapterConfig\AbstractAdapter
     */
    private $config;

    /**
     * @type \Classes\AdaptersDriver\AbsractAdapter
     */
    private $driver;

    public function __construct ( Config $config )
    {
        $this->config = $config->getAdapterConfig ();
        $this->driver = $config->getAdapterDriver ();
        $this->parseLocation ();
    }

    /**
     * Analisa os caminhos das pastas base
     */
    public function parseLocation ()
    {
        $arrBase = array (
            dirname ( __FILE__ ) ,
            '..' ,
            $this->config->path
        );

        # pasta com nome do driver do banco
        if ( $this->config->folder_database )
        {
            $classDriver = explode ( '\\' , get_class ( $this->driver ) );
            $arrBase[] = end ( $classDriver );
        }

        $this->baseLocation = implode ( DIRECTORY_SEPARATOR , filter_var_array ( $arrBase ) );

        if ( $this->config->hasSchemas () )
        {
            $schemas = $this->config->getSchemas ();
            foreach ( $schemas as $schema )
            {
                $this->location[ $schema ] = implode ( DIRECTORY_SEPARATOR , array (
                        $this->baseLocation , ucfirst ( $schema )
                    )
                );
            }

        } else
        {
            $this->location = array ( $this->baseLocation );
        }
    }

    /**
     * Executa o Make, criando arquivos e Diretorios
     */
    public function run ()
    {
        foreach ( $this->location as $schema => $location )
        {
            foreach ( $this->factoryMakerFile () as $objMakeFile )
            {
                $path = $location . DIRECTORY_SEPARATOR . $objMakeFile->getPastName ();
                $this->makeDir ( $path );

                if ( $objMakeFile->getParentFileTpl () != '' )
                {
                    $fileAbstract = $this->baseLocation
                                    . DIRECTORY_SEPARATOR
                                    . $objMakeFile->getParentClass () . '.php';

                    $tplAbstract = $this->getParsedTplContents ( $objMakeFile->getParentFileTpl () );
                    $this->makeSourcer ( $fileAbstract , $tplAbstract );
                    unset( $fileAbstract , $tplAbstract );
                }

                foreach (
                    $this->driver->getTables () as $key => $objTables
                )
                {

                    $file = $path
                            . DIRECTORY_SEPARATOR
                            . $this->getClassName ( $objTables->getName () )
                            . '.php';



                    $tpl = $this->getParsedTplContents ( $objMakeFile->getFileTpl () , $objTables , $objMakeFile , $this->config->factoryRelationTables ( $objMakeFile , $this , $objTables ) );
                    $this->makeSourcer ( $file , $tpl );
                }

            }
        }
    }

    /**
     * Instancia os Modulos de diretorios e tampletes
     *
     * @return AbstractAdapter[]
     */
    public function factoryMakerFile ()
    {
        return array (
            DbTable::getInstance () ,
            Entity::getInstance () ,
            Model::getInstance ()
        );
    }

    /**
     * verifica se ja existe e cria as pastas em cascata
     *
     * @param $dir
     */
    private function makeDir ( $dir )
    {
        if ( ! is_dir ( $dir ) )
        {
            if ( ! @mkdir ( $dir , 0755 , true ) )
            {
                die( "error: could not create directory $dir\n" );
            }
        }
    }

    private function makeSourcer ( $modelFile , $modelData )
    {
        if ( ! is_file ( $modelFile ) )
        {
            if ( ! file_put_contents ( $modelFile , $modelData ) )
            {
                die( "Error: could not write model file $modelFile." );
            }
        }

    }

    /**
     * @param string $str
     *
     * @return string
     */
    public function getClassName ( $str )
    {
        $temp = '';
        foreach ( explode ( self::SEPARETOR , $str ) as $part )
        {
            $temp .= ucfirst ( $part );
        }

        return $temp;
    }

    /**
     *
     * parse a tpl file and return the result
     *
     * @param String $tplFile
     *
     * @return String
     */
    public function getParsedTplContents ( $tplFile , \Classes\Db\DbTable $objTables = null , $objMakeFile = null , $vars = array () )
    {
        if ( empty( $vars ) )
        {
            $vars = array ();
        }

        $arrUrl = array (
            dirname ( __FILE__ ) ,
            'templates' ,
            $this->config->framework ,
            $tplFile
        );

        extract ( $vars );
        ob_start ();
        require implode ( DIRECTORY_SEPARATOR , filter_var_array ( $arrUrl ) );
        $data = ob_get_contents ();
        ob_end_clean ();

        return $data;
    }

}