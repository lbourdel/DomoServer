<?php

  /*
    SCRIPT DE RECUPERATION DES TEMPERATURES VIA 1WIRE, ET MISE EN BDD
  */

  /* DEPENDANCES */
  include_once('/var/www/domocan/www/conf/config.php');

  /* CONNEXION SQL */
  mysql_connect(MYSQL_HOST, MYSQL_LOGIN, MYSQL_PWD);
  mysql_select_db(MYSQL_DB);

  /* RECUPERATION DES VALEURS DE TEMPERATURE ET MISES A JOUR DES GRAPHIQUES */
  $retour = mysql_query("SELECT `id_sonde` FROM `" . TABLE_CHAUFFAGE_SONDE . "`");
  while ( $row = mysql_fetch_array($retour) ) {
    
    $a = exec('cat ' . PATHOWFS . '/' . $row['id_sonde'] . '/temperature');
    $b = round(str_replace(' ', '', $a), 2);
  
	mysql_query("UPDATE `" . TABLE_CHAUFFAGE_SONDE . "` SET `valeur` = '" . $b . "' WHERE `id_sonde` = '" . $row['id_sonde'] . "'");
	if ( !is_file(RRDPATH . '/' . $row['id_sonde'] . '.rrd') ) {
      exec(RRDPATH . '/creation.sh ' . RRDPATH . '/' . $row['id_sonde'] . '.rrd');
    } // END IF
    exec('rrdtool update ' . RRDPATH . '/' . $row['id_sonde'] . '.rrd N:' . $b);
    exec(RRDPATH . '/mettre_a_jour.sh ' . RRDPATH . '/' . $row['id_sonde']);
    $sonde = substr($row['id_sonde'], 3);

    $ch = curl_init(URIPUSH);
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_POSTFIELDS, "sonde;" . $sonde . "," . round($b, 1));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    $ret = curl_exec($ch);
    curl_close($ch);
  }
  
  /* MISE A JOUR DU GRAPHIQUE DE TEMPERATURE MOYENNE MAISON */
  $retour = mysql_query("SELECT AVG(`valeur`) FROM `" . TABLE_CHAUFFAGE_SONDE . "` WHERE `moyenne` = '1'");
  $row = mysql_fetch_array($retour);
  if ( !is_file(RRDPATH . '/temperaturemaison.rrd') ) {
    exec(RRDPATH . '/creation.sh ' . RRDPATH . '/temperaturemaison.rrd');
  }
  exec('rrdtool update ' . RRDPATH . '/temperaturemaison.rrd N:' . round($row[0], 1));
  exec(RRDPATH . '/mettre_a_jour.sh ' . RRDPATH . '/temperaturemaison');

  /* MISE A JOUR DU GRAPHIQUE DE TEMPERATURE EXTERIEURE */
  $retour = mysql_query("SELECT `valeur` FROM `" . TABLE_CHAUFFAGE_SONDE . "` WHERE `id` = '1'");
  $row = mysql_fetch_array($retour);
  if ( !is_file(RRDPATH . '/temperatureexterieure.rrd') ) {
    exec(RRDPATH . '/creation.sh ' . RRDPATH . '/temperatureexterieure.rrd');
  }
  exec('rrdtool update ' . RRDPATH . '/temperatureexterieure.rrd N:' . round($row[0], 1));
  exec(RRDPATH . '/mettre_a_jour.sh ' . RRDPATH . '/temperatureexterieure');

  $ch = curl_init(URIPUSH);
  curl_setopt($ch, CURLOPT_POST, 1);
  curl_setopt($ch, CURLOPT_POSTFIELDS, "sonde;temperatureexterieure," . round($row[0], 1));
  curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
  $ret = curl_exec($ch);
  curl_close($ch);


  mysql_close();

?>
