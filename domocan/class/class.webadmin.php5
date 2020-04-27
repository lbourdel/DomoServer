<?php
// Includes
include_once('/var/www/domocan/www/conf/config.php');

include_once(PATHCLASS . 'class.envoiTrame.php5');
include_once(PATHCLASS . 'class.gradateur.php5');
include_once(PATHCLASS . 'class.communes.php5');

include_once PATH . 'lib/xajax/xajax_core/xajax.inc.php';
include_once PATH . 'lib/xtemplate/xtemplate.class.php';

/* XAJAX */
$xajax = new xajax();
$xajax->configure("javascript URI", URI . "/lib/xajax/");
$xajax->setFlag('debug', DEBUG_AJAX);

/* DECLARATION DES FONCTIONS EN AJAX */
$xajax->register(XAJAX_FUNCTION, 'scanSystem');

// AJAX PHP Functions:
// Scanbus
function scanSystem() {
  $scan = new webadmin;
  $scan->scanBus();
  return;
}
class webadmin extends communes {
  function scanBus() {
    $reponse = new XajaxResponse();
    //echo("System Can");
	
	// Connects to DB
    mysql_connect(MYSQL_HOST, MYSQL_LOGIN, MYSQL_PWD);
    mysql_select_db(MYSQL_DB);
	
	// Initiate Object
    $communes = new communes();

	// Check Available Subsystems in DB and starts Scan
	$sql = "SELECT * FROM `ha_subsystem_types` WHERE 1;";
	$query       = mysql_query($sql);
	while ($row = mysql_fetch_array($query)) {
	  $Card_Type = $row['Type'];
	  for ($c = 0; $c <= 254; $c++) {
	    //$Card_Type   = '0x60'; 
	    $Card_Number = '0x' . str_pad(dechex($c), 2, "0", STR_PAD_LEFT);
		//echo("<br><br><b>=>Scan c=".$c.", Card Number=".$Card_Number."<=</b><br>");

		// Send Card Detect Command (lirenom) 
	    $communes->lireNom(strval(hexdec($Card_Type)), strval(hexdec($Card_Number)));
		sleep(100);
		//echo("<br>=>Scan j=$j<=<b> Lire Nom (Type=$Card_Type, Number=$Card_Number) Launched</b><br>");


	  } // End For
	} // End While
    // Close DB connection
	mysql_close();
	$reponse->script("$('#traitement').css('display', 'none')");
	return $response;
  } // End Function ScanBus
  
} // End Class webadmin


echo("<img class=\"scanBus\" id=\"03_0x10\" src=\"./images/scan-button.jpg\" onclick=\"xajax_scanSystem();\" />" . CRLF);
?>