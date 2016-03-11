<?php

if ( ! ini_get ( 'short_open_tag' ) )
{
    die( "please enable short_open_tag directive in php.ini\n" );
}

if ( ! ini_get ( 'register_argc_argv' ) )
{
    die( "please enable register_argc_argv directive in php.ini\n" );
}

function __autoload ( $class )
{
    $parts = \explode ( '\\' , $class );
    include __DIR__ . DIRECTORY_SEPARATOR
            . \implode ( DIRECTORY_SEPARATOR , $parts ) . '.php';
}

global $_path;
\Phar::interceptFileFuncs();

try
{
    $arrValid = array (
        'help' ,
        'database:' ,
        'schema:' ,
        'driver:' ,
        'framework:' ,
        'status:' ,
        'path:'
    );


    $_path = realpath ( dirname ( str_replace (
        'phar://'
        , '' , __DIR__
    ) ) );

    $configIni = $_path . '/configs/config.ini' ;

    if ( ! is_file ( $configIni ) )
    {
        throw new \Exception( "File does not exist: configs/config.ini \n" );
    }

    $maker = new \Classes\MakerFile( new \Classes\Config( getopt ( null , $arrValid ) , $configIni ) );
    $maker->run ();

} catch ( \Exception $e )
{
    die( $e->getMessage () );
}

__halt_compiler();