<?php

  /*
    SCRIPT POUR ALLUMAGE OU EXTINCTION DE LA CHAUDIERE
    SELON TEMPERATURE DE LA MAISON
    DOIT ETRE LANCE EN CRON
  */

  /* DEPENDANCES */
  include_once('/var/www/domocan/www/conf/config.php');
  include_once(PATHCLASS . '/class.envoiTrame.php5');
  include_once(PATHCLASS . '/class.gradateur.php5');

  $gradateur   = new gradateur();
  $grad_boiler = new gradateur();
  
  /* CONNEXION A LA BDD */
  mysql_connect(MYSQL_HOST, MYSQL_LOGIN, MYSQL_PWD);
  mysql_select_db(MYSQL_DB);

  /* TEMPERATURE MOYENNE DE LA MAISON */
  $retour = mysql_query("SELECT AVG(`valeur`) FROM `" . TABLE_CHAUFFAGE_TEMP . "` WHERE `moyenne` = '1';");
  $row = mysql_fetch_array($retour);
  $moyenne_actuelle = round($row[0],1); 

  /* VALEUR VOULUE */
  $retour = mysql_query("SELECT `valeur` FROM `" . TABLE_CHAUFFAGE_CLEF . "` WHERE `clef` = 'temperature';");
  $row = mysql_fetch_array($retour); 
  $voulu = round($row[0],1);
  $voulu2 = $voulu - 0.2;
  $voulu3 = $voulu + 0.5;

  /* TEMP EXT */
  $retour      = mysql_query("SELECT `valeur` FROM `" . TABLE_CHAUFFAGE_TEMP . "` WHERE `id` = '1';");
  $row         = mysql_fetch_array($retour);
  $ext_temp    = $row[0];
  
  /* ETAT DE LA CHAUDIERE */
  $retour      = mysql_query("SELECT `valeur` FROM `" . TABLE_CHAUFFAGE_CLEF_TEMP . "` WHERE `clef` = 'chaudiere';");
  $row         = mysql_fetch_array($retour);
  $chaudiere   = $row[0];
  /* ETAT DU BOILER */
  $retour      = mysql_query("SELECT `valeur` FROM `" . TABLE_CHAUFFAGE_CLEF_TEMP . "` WHERE `clef` = 'boiler';");
  $row         = mysql_fetch_array($retour);
  $etat_boiler = $row[0];
  $retour      = mysql_query("SELECT `valeur` FROM `" . TABLE_CHAUFFAGE_CLEF_TEMP . "` WHERE `clef` = 'warm_water';");
  $row         = mysql_fetch_array($retour);
  $warm_water  = $row[0];
  
  /* TEST PRESENCE & HEATER TIMESLOT ACTIF */
  $retour          = mysql_query("SELECT `valeur` FROM `" . TABLE_CHAUFFAGE_CLEF . "` WHERE `clef` = 'absence';");
  $row             = mysql_fetch_array($retour);
  $absence         = $row[0];
  
  $Now             = date("H:i:00");
  $DayBit          = date("N");
  $Today           = str_pad(str_pad("1",$DayBit,"_",STR_PAD_LEFT),8,"_");
  $sql             = "SELECT COUNT(*) FROM `" . TABLE_HEATING_TIMSESLOTS . "` WHERE `function`='HEATER'  AND ((`days` LIKE '" . $Today . "') OR (`days` LIKE '_______1')) AND ('" . $Now . "' BETWEEN `start` AND `stop`) AND `active`='Y';";
  $retour          = mysql_query($sql);
  $row             = mysql_fetch_array($retour);
  $periode_chauffe = $row[0];

   // Boiler
  $sql             = "SELECT COUNT(*) FROM `" . TABLE_HEATING_TIMSESLOTS . "` WHERE `function`='BOILER'  AND ((`days` LIKE '" . $Today . "') OR (`days` LIKE '_______1')) AND ('" . $Now . "' BETWEEN `start` AND `stop`) AND `active`='Y';";
  $retour          = mysql_query($sql);
  $row             = mysql_fetch_array($retour);
  $periode_boiler  = $row[0];
  
  /* SI LA TEMPERATURE VOULUE EST SUPERIEUR A CELLE DE LA MAISON */
  if (( $voulu2 >= $moyenne_actuelle && $moyenne_actuelle != '0' ) && ($absence==0 && $periode_chauffe>=1) && ($periode_boiler==0)) {
    $gradateur->allumer(CARTE_CHAUFFAGE, SORTIE_CHAUFFAGE, 0, 0x32);
	echo("\nHEATER on\n");
	if ( $chaudiere == '0' ) {
      if (DEBUG_AJAX) { mysql_query("INSERT INTO `logs` (`id_gradateur`, `id_sortie`, `date`, `valeur`) VALUES ('" . CARTE_CHAUFFAGE . "','" . SORTIE_CHAUFFAGE . "', CURRENT_TIMESTAMP, '32');"); }
	  mysql_query("UPDATE `" . TABLE_CHAUFFAGE_CLEF_TEMP . "` SET `valeur` = '1' WHERE `clef` = 'chaudiere';");
	  if (DEBUG_AJAX) { mysql_query("INSERT INTO `" . TABLE_MEASURE . "` (`measure_type`, `start_time`, `start_value`, `extra_measure`) VALUES ('HEATER', CURRENT_TIMESTAMP,'" . $moyenne_actuelle . "','" . $ext_temp . "');"); }
    }
  }
  /* SI LA TEMPERATURE MOYENNE EST SUPERIEUR A LA VOULUE ou absence ou pas de periode chauffe ou ... priorité eau chaude*/
  if (( $moyenne_actuelle >= $voulu3 || $moyenne_actuelle == '0' ) || ($absence==1 || $periode_chauffe==0) || ($periode_boiler>=1)) {
    $gradateur->eteindre(CARTE_CHAUFFAGE, SORTIE_CHAUFFAGE, 0);
	/* Efface HEAT Now quand finis */
    $sql = "DELETE FROM `". TABLE_HEATING_TIMSESLOTS . "` WHERE `function`='HEATER' AND `days`='00000001' AND `stop`<='" . $Now . "';";
	mysql_query($sql);	
	if ( $chaudiere == '1' ) {
      if (DEBUG_AJAX) { mysql_query("INSERT INTO `logs` (`id_gradateur`, `id_sortie`, `date`, `valeur`) VALUES ('" . CARTE_CHAUFFAGE . "','" . SORTIE_CHAUFFAGE . "', CURRENT_TIMESTAMP, '00');"); }
	  mysql_query("UPDATE `" . TABLE_CHAUFFAGE_CLEF_TEMP . "` SET `valeur` = '0' WHERE `clef` = 'chaudiere';");
	  // Determine Stop Value & Update DB
	  if (DEBUG_AJAX) { 
	    $sql         = "SELECT * FROM `" . TABLE_MEASURE . "` WHERE `measure_type`='HEATER' ORDER BY EXTRACT(YEAR_MONTH FROM `start_time`) DESC , EXTRACT(DAY_MINUTE FROM `start_time`) DESC limit 1;";
	    $retour      = mysql_query($sql);
	    $row         = mysql_fetch_array($retour);
	    $measure_id   = $row["id"];
	    $stop_reason = "UNKNOWN";
	    if (($periode_boiler>=1)) { $stop_reason = "BOILER"; } else { if ($moyenne_actuelle >= $voulu3) { $stop_reason = "TEMP"; } else { if ($periode_chauffe==0) { $stop_reason = "TIME"; } else { if ($absence==1) { $stop_reason = "OUT"; }}}}
	    mysql_query("UPDATE `" . TABLE_MEASURE . "`SET `stop_time` = CURRENT_TIMESTAMP, `stop_value` = '" . $moyenne_actuelle . "', `stop_reason` = '" . $stop_reason . "', `extra_measure2` = '" . $ext_temp . "' WHERE `id` = '" . $measure_id . "';");
	  } // END IF
    } // END IF
  }  // END IF
  
  /* Si Periode BOILER & Presence */
  if ($absence==0 && $periode_boiler>=1) { // && $warm_water==0) {
    $grad_boiler->allumer(CARTE_BOILER, SORTIE_BOILER, 0, 0x32);
	if ($etat_boiler == '0') {
      if (DEBUG_AJAX) { mysql_query("INSERT INTO `logs` (`id_gradateur`, `id_sortie`, `date`, `valeur`) VALUES ('" . CARTE_BOILER . "','" . SORTIE_BOILER . "', CURRENT_TIMESTAMP, '32');"); }
	  mysql_query("UPDATE `" . TABLE_CHAUFFAGE_CLEF_TEMP . "` SET `valeur` = '1' WHERE `clef` = 'boiler';");
	  if (DEBUG_AJAX) { mysql_query("INSERT INTO `" . TABLE_MEASURE . "` (`measure_type`, `start_time`, `start_value`) VALUES ('BOILER',CURRENT_TIMESTAMP, '0');"); }
    }
  }
  /* Si FIN periode BOILER */
  if ($absence==1 || $periode_boiler==0) { // || $warm_water==1) {
    $grad_boiler->eteindre(CARTE_BOILER, SORTIE_BOILER, 0);
	if ($etat_boiler == '1') {
      if (DEBUG_AJAX) { mysql_query("INSERT INTO `logs` (`id_gradateur`, `id_sortie`, `date`, `valeur`) VALUES ('" . CARTE_BOILER . "','" . SORTIE_BOILER . "', CURRENT_TIMESTAMP, '00');"); }
	  mysql_query("UPDATE `" . TABLE_CHAUFFAGE_CLEF_TEMP . "` SET `valeur` = '0' WHERE `clef` = 'boiler';");
	  // Determine Stop Value + reason & Update DB
	  if (DEBUG_AJAX) { 
	    $sql        = "SELECT * FROM `" . TABLE_MEASURE . "` WHERE `measure_type`='BOILER' ORDER BY EXTRACT(YEAR_MONTH FROM `start_time`) DESC , EXTRACT(DAY_MINUTE FROM `start_time`) DESC limit 1;";
	    $retour     = mysql_query($sql);
	    $row        = mysql_fetch_array($retour);
	    $measure_id  = $row["id"];
	    if ($warm_water == "0") { $stop_reason = "TEMP"; } else { if ($periode_boiler==0) { $stop_reason = "TIME"; } else { if ($absence==1) { $stop_reason = "OUT"; }}}
	    mysql_query("UPDATE `" . TABLE_MEASURE . "` SET `stop_time` = CURRENT_TIMESTAMP, `stop_reason` = '" . $stop_reason . "' WHERE `id` = '" . $measure_id . "';");
	  } // END IF
	  
	  // Less than 1 Hour before Heater start => Early Start
	  $next_hour = date("H:i:00",mktime(date("H")+1, date("i"), 0, 1, 1, 1));
	  $sql              = "SELECT COUNT(*) FROM `" . TABLE_HEATING_TIMSESLOTS . "` WHERE `function`='HEATER'  AND ((`days` LIKE '" . $Today . "') OR (`days` LIKE '_______1')) AND ('" . $next_hour . "' BETWEEN `start` AND `stop`) AND `active`='Y';";
	  $retour           = mysql_query($sql);
	  $row              = mysql_fetch_array($retour);
	  $enchaine_chauffe = $row[0];
	  if ($enchaine_chauffe>=1) {
		$sql    = "INSERT INTO `" . TABLE_HEATING_TIMSESLOTS . "` SET `days` = '00000001', `start`='" . $Now . "', `stop`='" . $next_hour . "', `active`='Y';";
		mysql_query($sql);
	  }
    }
	/* Efface HEAT Now quand fini */
    $sql = "DELETE FROM ". TABLE_HEATING_TIMSESLOTS . " WHERE `function`='BOILER' AND days`='00000001' AND `stop`<='" . $Now . "';";
	mysql_query($sql);
  } 
  
  
  

  mysql_close();

?>
