<?php
error_reporting(E_ALL);
  /*
    SCRIPT DE RECEPTION DES TRAMES, TRAMES VENU DU BINAIRE (server_udp) QUI ECOUTE SUR LE PORT 1470
  */

  

  $head = substr($argv[1],0,2);
  if ($head=="70") {
    $date = date('d/m/y : H:i:s');
    echo "[" . $date . "] - Reception brute(recv.php) : " . $argv[1];
	
    /* DEPENDANCES */
    include_once('/var/www/domocan/www/conf/config.php');
    include_once(PATHCLASS . '/class.receptionTrame.php5');
    include_once(PATHCLASS . '/class.debug.php5');

    $debug = new debug();

    $receptionTrame = new receptionTrame();
    $receptionTrame->traiter(substr(strtolower($argv[1]), 0, 32));
    unset($receptionTrame);
  //} else {
    // echo("Frame not 70 type or malformed!" . CRLF);
  } // END IF

?>
