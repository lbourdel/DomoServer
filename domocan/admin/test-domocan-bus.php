<?php
// PHP Error Reporting
//error_reporting(E_ALL);
//ini_set('display_errors', '1');

// NO execution timeout
set_time_limit(0);
ini_set('max_execution_time', 0);

// Includes and Classes
include_once('../www/conf/config.php');
include_once('./Includes/func.misc.php');

include_once('../class/class.envoiTrame.php5');
include_once('../class/class.gradateur.php5');
include_once('../class/class.communes.php5');


include_once('../class/class.webadmin.php5');

$oWAdm = new webadmin;
$sWAdm = $oWAdm->scanBus();

echo("Scan Bus:<br>");
//echo($sWAdm);



?>