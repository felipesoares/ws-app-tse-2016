<?php

require 'vendor/autoload.php';
#require_once "adodb5/adodb.inc.php";

$CFG = array(
    "db_host" => "localhost",
    "db_user" => "root",
    "db_password" => "",
    "db_name" => "eleicoes"
);

$CFG = (object) $CFG;

$DB = NewADOConnection("mysqli");

#$DB->debug = true;

$DB->Connect($CFG->db_host, $CFG->db_user, $CFG->db_password, $CFG->db_name) or die("<h1>Falha na conex&atilde;o!</h1>");
$DB->EXECUTE("SET NAMES 'utf8'");

date_default_timezone_set ( "America/Sao_Paulo" );

ini_set ( "display_errors", 1 );
ini_set ( "display_startup_erros", 1 );
error_reporting ( E_ALL );
