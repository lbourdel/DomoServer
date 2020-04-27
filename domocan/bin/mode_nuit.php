<?php

  /*
    PASSAGE DE LA STRUCTURE EN MODE NUIT (POUR BAISSER CHAUFFAGE OU ALLUMAGE DES LUMIERES A 5%)
  */

  /* DEPENDANCES */
  include_once('/var/www/domocan/www/conf/config.php');

  /* ACTIVATION DU MODE NUIT */
  if ( $argv['1'] == 'on' ) {
    mysql_connect(MYSQL_HOST, MYSQL_LOGIN, MYSQL_PWD);
    mysql_select_db(MYSQL_DB);
    mysql_query("UPDATE `" . TABLE_LUMIERES . "` SET `valeur_souhaitee` = '0'");
    mysql_query("UPDATE `" . TABLE_LUMIERES_CLEF . "` SET `valeur` = '1' WHERE `clef` = 'nuit'");

    $retour = mysql_query("SELECT `valeur` FROM `" . TABLE_CHAUFFAGE_CLEF . "` WHERE `clef` = 'temperature'");
    $row = mysql_fetch_array($retour);
    /* - 2 degrés */
    $nouvelle = $row[0] - 2;
    mysql_query("UPDATE `" . TABLE_CHAUFFAGE_CLEF . "` SET `valeur` = '" . $nouvelle . "' WHERE `clef` = 'temperature'");

    mysql_close();
  }

  /* DESACTIVATION DU MODE NUIT */
  if ( $argv['1'] == 'off' ) {
    mysql_connect(MYSQL_HOST, MYSQL_LOGIN, MYSQL_PWD);
    mysql_select_db(MYSQL_DB);
    mysql_query("UPDATE `" . TABLE_LUMIERES . "` SET `valeur_souhaitee` = '0'");
    mysql_query("UPDATE `" . TABLE_LUMIERES_CLEF . "` SET `valeur` = '0' WHERE `clef` = 'nuit'");

    $retour = mysql_query("SELECT `valeur` FROM `" . TABLE_CHAUFFAGE_CLEF . "` WHERE `clef` = 'temperature'");
    $row = mysql_fetch_array($retour);
    /* + 2 degrés */
    $nouvelle = $row[0] + 2;
    mysql_query("UPDATE `" . TABLE_CHAUFFAGE_CLEF . "` SET `valeur` = '" . $nouvelle . "' WHERE `clef` = 'temperature'");

    mysql_close();
  }

  /* PROCESSUS D'ENVOI */
  $ch = curl_init(URIPUSH);
  curl_setopt($ch, CURLOPT_POST, 1);
  curl_setopt($ch, CURLOPT_POSTFIELDS, "modenuit;" . $argv[1]);
  curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
  $ret = curl_exec($ch);
  curl_close($ch);

  $ch = curl_init(URIPUSH);
  curl_setopt($ch, CURLOPT_POST, 1);
  curl_setopt($ch, CURLOPT_POSTFIELDS, "temperaturevoulue;" . $nouvelle);
  curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
  $ret = curl_exec($ch);
  curl_close($ch);

?>
