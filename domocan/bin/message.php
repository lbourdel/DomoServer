<?php

  /*
    SCRIPT D'ENVOI DE MESSAGE AU PORTAIL WEB (SOIT L'HEURE => SI AUCUN ARGUMENT, SOIT LE MESSAGE PASSE EN ARGUMENT)
  */

  /* DEPENDANCES */
  include_once('/var/www/domocan/www/conf/config.php');

  /* SI AUCUN MESSAGE, ENVOI DE L'HEURE ACTUELLE */
  if ( $argv[1] == "" ) {

    /* CONNEXION */
    mysql_connect(MYSQL_HOST, MYSQL_LOGIN, MYSQL_PWD);
    mysql_select_db(MYSQL_DB);

    $retour = mysql_query("SELECT `prenom`,DATE_FORMAT(date, '%d/%m') FROM `meteo_anniversaire` WHERE DATE_FORMAT(date, '%m%D') = DATE_FORMAT(NOW( ), '%m%D') LIMIT 1");
    $row = mysql_fetch_array($retour);
    if ( $row[0] != "" ) {
      $argv[1] = date('H:i') . " (Anniversaire de : " . $row[0] . ")";
    }
    else {
      $argv[1] = date('H:i');
    }
  }

  /* PROCESSUS D'ENVOI */
  $ch = curl_init(URIPUSH);
  curl_setopt($ch, CURLOPT_POST, 1);
  curl_setopt($ch, CURLOPT_POSTFIELDS, "message;" . $argv[1]);
  curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
  $ret = curl_exec($ch);
  curl_close($ch);


?>
