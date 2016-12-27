<?php
//error_reporting(E_ERROR | E_WARNING | E_PARSE);
define( 'CONTROLLERS','app/controllers/' );
define( 'VIEWS','app/views/' );
define( 'MODELS','app/models/' );
define( 'HELPERS','system/helpers/' );

$localhost = (strpos($_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'],"localhost/sysfitness") > -1 ? $localhost = 'http://localhost/sysfitness/' : $localhost = 'http://localhost/daniel/sysfitness/');

$path = 'http://localhost/teste_mvc/';

define( 'PATH',$path );

define( 'DB_HOST','localhost' );
define( 'DB_USER','root' );
define( 'DB_DBNAME','test' );
define( 'DB_PASS','1234' );

/*DEFINIÇÕES MENU TOPO*/
$menu_topo = array(
		array('link'=>PATH.'usuario/home','titulo'=>'Home'),
		array('link'=>PATH.'usuario/cadastrar','titulo'=>utf8_encode('Usuários'))
);

define( 'MENU_TOPO',serialize($menu_topo) );
