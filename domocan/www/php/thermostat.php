<?php
// SELECT * FROM `chauffage_sonde` JOIN `chauffage_temp` ON `chauffage_temp`.`id`=`chauffage_sonde`.`id`
  $titre = 'Gestion du chauffage';

  include '../class/class.envoiTrame.php5';

  /* DECLARATION DES FONCTIONS EN AJAX */
  $xajax->register(XAJAX_FUNCTION, 'descendreTemperature');
  $xajax->register(XAJAX_FUNCTION, 'monterTemperature');
  $xajax->register(XAJAX_FUNCTION, 'moyenne');
  $xajax->register(XAJAX_FUNCTION, 'updateConsigne');
  $xajax->register(XAJAX_FUNCTION, 'autoAway');
  $xajax->register(XAJAX_FUNCTION, 'autoBack');
  $xajax->register(XAJAX_FUNCTION, 'HeatNow');
 
  /* FONCTIONS PHP AJAX */
  function moyenne() {
    $objResponse = new xajaxResponse();
    mysql_connect(MYSQL_HOST, MYSQL_LOGIN, MYSQL_PWD);
    mysql_select_db(MYSQL_DB);
    $retour = mysql_query("SELECT AVG(`valeur`) FROM `" . TABLE_CHAUFFAGE_TEMP . "` WHERE `moyenne` = '1';");
    $row = mysql_fetch_array($retour);
    mysql_close();
    $objResponse->assign("moyenne","innerHTML", round($row[0],1));
    return $objResponse;    
  }

  function descendreTemperature() {
    $objResponse = new xajaxResponse();
    mysql_connect(MYSQL_HOST, MYSQL_LOGIN, MYSQL_PWD);
    mysql_select_db(MYSQL_DB);    
    $retour = mysql_query("SELECT `valeur` FROM `" . TABLE_CHAUFFAGE_CLEF . "` WHERE `clef` = 'temperature';");
    $row = mysql_fetch_array($retour);
    $nouvelle = $row[0] - 1;
    mysql_query("UPDATE `" . TABLE_CHAUFFAGE_CLEF . "` SET `valeur` = '" . $nouvelle . "' WHERE `clef` = 'temperature';");
    mysql_close();
    $objResponse->assign("temperature","innerHTML", $nouvelle);
    return $objResponse;    
  }

  function monterTemperature() {
    $objResponse = new xajaxResponse();
    mysql_connect(MYSQL_HOST, MYSQL_LOGIN, MYSQL_PWD);
    mysql_select_db(MYSQL_DB);
    $retour = mysql_query("SELECT `valeur` FROM `" . TABLE_CHAUFFAGE_CLEF . "` WHERE `clef` = 'temperature';");
    $row = mysql_fetch_array($retour);
    $nouvelle = $row[0] + 1;
    mysql_query("UPDATE `" . TABLE_CHAUFFAGE_CLEF . "` SET `valeur` = '" . $nouvelle . "' WHERE `clef` = 'temperature';");
    mysql_close();
    $objResponse->assign("temperature","innerHTML", $nouvelle);
    return $objResponse;    
  }

  // Update consigne depuis Nest
   function updateConsigne($newTemp) {
    $objResponse = new xajaxResponse();
    mysql_connect(MYSQL_HOST, MYSQL_LOGIN, MYSQL_PWD);
    mysql_select_db(MYSQL_DB);
    mysql_query("UPDATE `" . TABLE_CHAUFFAGE_CLEF . "` SET `valeur` = '" . $newTemp . "' WHERE `clef` = 'temperature';");
    mysql_close();
    $objResponse->assign("temperature","innerHTML", $newTemp);
	//$objResponse->script("$('#traitement').css('display', 'none')");
	// exec('php /var/www/domocan/bin/chauffage.php');
    return $objResponse;    
  }
 
   // auto AWAY depuis Nest
   function autoAway() {
    $objResponse = new xajaxResponse();
    mysql_connect(MYSQL_HOST, MYSQL_LOGIN, MYSQL_PWD);
    mysql_select_db(MYSQL_DB);
    mysql_query("UPDATE `" . TABLE_CHAUFFAGE_CLEF . "` SET `valeur` = '1' WHERE `clef` = 'absence';");
	// Delete any Heat Now
    $sql = "DELETE FROM `" . TABLE_HEATING_TIMSESLOTS . "` WHERE `days` = '00000001';";
    mysql_query($sql);
	mysql_close();
	exec('php /var/www/domocan/bin/chauffage.php');
    return $objResponse;    
  }  
 
   // auto BACK depuis Nest
   function autoBack() {
    $objResponse = new xajaxResponse();
    mysql_connect(MYSQL_HOST, MYSQL_LOGIN, MYSQL_PWD);
    mysql_select_db(MYSQL_DB);
    mysql_query("UPDATE `" . TABLE_CHAUFFAGE_CLEF . "` SET `valeur` = '0' WHERE `clef` = 'absence';");
    mysql_close();
	exec('php /var/www/domocan/bin/chauffage.php');
    return $objResponse;    
  }
 
   // HEAT Now depuis Nest
   function HeatNow($Laps) {
    $objResponse = new xajaxResponse();
    mysql_connect(MYSQL_HOST, MYSQL_LOGIN, MYSQL_PWD);
    mysql_select_db(MYSQL_DB);
    $Now    = date("H:i:00");
    $End    = date("H:i:00",mktime(date("H")+$Laps, date("i"), 0, date("m"), date("d"), date("y")));
    $sql    = "INSERT INTO `" . TABLE_HEATING_TIMSESLOTS . "` SET `days` = '00000001', `start`='" . $Now . "', `stop`='" . $End . "', `active`='Y';";
    mysql_query($sql);
	exec('php /var/www/domocan/bin/chauffage.php');
    return $objResponse;    
  }
  
  /* CONNEXION SQL */
  mysql_connect(MYSQL_HOST, MYSQL_LOGIN, MYSQL_PWD);
  mysql_select_db(MYSQL_DB);

  /* AFFICHAGE DES TEMPERATURES SUR LES PLANS */
  $retour0 = mysql_query("SELECT `lieu` FROM `localisation`");
  while( $row0 = mysql_fetch_array($retour0) ) {

    $retour = mysql_query("SELECT * FROM `" . TABLE_CHAUFFAGE_SONDE . "` JOIN `" . TABLE_CHAUFFAGE_TEMP . "` ON `" . TABLE_CHAUFFAGE_SONDE . "`.`id`=`" . TABLE_CHAUFFAGE_TEMP . "`.`id` WHERE `" . TABLE_CHAUFFAGE_SONDE . "`/`localisation` = '" . $row0['lieu'] . "';");
    while( $row = mysql_fetch_array($retour) ) {
      $_XTemplate->assign('ID_SONDE', substr($row['id_sonde'], 3));
      $_XTemplate->assign('IMGX', $row['img_x']);
      $_XTemplate->assign('IMGY', $row['img_y']);
      $_XTemplate->assign('TEMPERATURE', round($row['valeur'], 1));
      $_XTemplate->parse('main.PLAN.THERMOMETRE');
    }
    $_XTemplate->assign('LOCALISATION', $row0['lieu']);
    if ( $row0['lieu'] == DEFAUT_LOCALISATION ) {
      $_XTemplate->assign('CACHER', 'display: block;');
    }
    else {
      $_XTemplate->assign('CACHER', 'display: none;');
    }
    $_XTemplate->parse('main.PLAN');

  }

  /* AFFICHAGE DES NIVEAUX */
  $retour = mysql_query("SELECT * FROM `" . TABLE_LOCALISATION . "`;");
  while( $row = mysql_fetch_array($retour) ) {
    $_XTemplate->assign('LOCALISATION', $row['lieu']);
    $_XTemplate->parse('main.NIVEAU');
  }

  /* AFFICHAGE DE LA TEMPERATURE MOYENNE DE LA MAISON */
  $retour = mysql_query("SELECT AVG(`valeur`) FROM `" . TABLE_CHAUFFAGE_TEMP . "` WHERE `moyenne` = '1';");
  $row    = mysql_fetch_array($retour);
  $_XTemplate->assign('MOYENNEMAISON', round($row[0],1));

  /* AFFICHAGE DE LA TEMPERATURE VOULUE */
  $retour = mysql_query("SELECT `valeur` FROM `" . TABLE_CHAUFFAGE_CLEF . "` WHERE `clef` = 'temperature';");
  $row    = mysql_fetch_array($retour);
  $_XTemplate->assign('TEMPERATURE', $row[0]);

  /* AFFICHAGE DE L'ABSENCE [PRESENCE-1] */
  $retour = mysql_query("SELECT `valeur` FROM `" . TABLE_CHAUFFAGE_CLEF . "` WHERE `clef` = 'absence';");
  $row    = mysql_fetch_array($retour);
  $_XTemplate->assign('ABSENCE', $row[0]);
  
  /* PRERIODE DE CHAUFFE? */
  $Now    = date("H:i:00");
  $DayBit = date("N");
  $Today  = str_pad(str_pad("1",$DayBit,"_",STR_PAD_LEFT),8,"_");
  $sql    = "SELECT COUNT(*) FROM `" . TABLE_HEATING_TIMSESLOTS . "` WHERE `function`='HEATER'  AND ((`days` LIKE '" . $Today . "') OR (`days` LIKE '_______1')) AND ('" . $Now . "' BETWEEN `start` AND `stop`) AND `active`='Y';";
  $retour = mysql_query($sql);
  $row    = mysql_fetch_array($retour);
  $_XTemplate->assign('PERIODECHAUFFE', $row[0]);
  
  /* AFFICHAGE DE FIN DE LA PERIODE DE CHAUFFE EN COURS */
  $Now    = date("H:i:00");
  $DayBit = date("N");
  $Today  = str_pad(str_pad("1",$DayBit,"_",STR_PAD_LEFT),8,"_");
  $sql    = "SELECT stop FROM `" . TABLE_HEATING_TIMSESLOTS . "` WHERE `function`='HEATER'  AND ((`days` LIKE '" . $Today . "') OR (`days` LIKE '_______1')) AND ('" . $Now . "' BETWEEN `start` AND `stop`) AND `active`='Y' ORDER BY start DESC;";
  $retour = mysql_query($sql);
  if ($row=mysql_fetch_array($retour)) {
    $heure = substr($row[0],0,2) . substr($row[0],3,2);
    $_XTemplate->assign('FINCHAUFFE', $heure);
  } else {
    $_XTemplate->assign('FINCHAUFFE', "");
  }

  /* AFFICHAGE DE LA PROCHAINE PERIODE DE CHAUFFE */
  $Now    = date("H:i:00");
  $DayBit = date("N");
  $Today  = str_pad(str_pad("1",$DayBit,"_",STR_PAD_LEFT),8,"_");
  $sql    = "SELECT start FROM `" . TABLE_HEATING_TIMSESLOTS . "` WHERE `function`='HEATER'  AND (`days` LIKE '" . $Today . "') AND (`start`>'" . $Now . "') AND `active`='Y' ORDER BY `start`;";
  $retour = mysql_query($sql);
  if ($row=mysql_fetch_array($retour)) {
	if (substr($row[0],0,1)=="0") { $heure    = substr($row[0],1,1) . substr($row[0],3,2); } else {$heure    = substr($row[0],0,2) . substr($row[0],3,2);}
    $_XTemplate->assign('PROCHAINECHAUFFE', $heure);
  } else {
    $DayBit   = date("N",mktime(1, 1, 1, date("m"), date("d")+1, date("y")));
    $Tomorrow = str_pad(str_pad("1",$DayBit,"_",STR_PAD_LEFT),8,"_");
    $sql      = "SELECT start FROM `" . TABLE_HEATING_TIMSESLOTS . "` WHERE (`function`='HEATER'  AND (`days` LIKE '" . $Tomorrow . "') AND `active`='Y') ORDER BY `start`;";
    $retour   = mysql_query($sql);
	$row=mysql_fetch_array($retour);
	if (substr($row[0],0,1)=="0") { $heure    = substr($row[0],1,1) . substr($row[0],3,2); } else {$heure    = substr($row[0],0,2) . substr($row[0],3,2);}
    $_XTemplate->assign('PROCHAINECHAUFFE', $heure);
  }
  
  // Hour & Day
  $_XTemplate->assign('HOUR', date("H"));
  $_XTemplate->assign('DD'  , date("N"));
  
  /* AFFICHAGE DE L'ETAT DE LA CHAUDIERE */
  $retour = mysql_query("SELECT `valeur` FROM `" . TABLE_CHAUFFAGE_CLEF_TEMP . "` WHERE `clef` = 'chaudiere';");
  $row = mysql_fetch_array($retour);
  if ( $row[0] == '0' ) {
    $chaudiere = "A l'arrêt";
  }
  else if ( $row[0] == '1' ) {
    $chaudiere = "En marche";
  }
  $_XTemplate->assign('CHAUDIERE', $chaudiere);
  $_XTemplate->assign('ETATCHAUDIERE', $row[0]);

  /* AFFICHAGE TEMPERATURE EXTERIEURE */
  $retour = mysql_query("SELECT `valeur` FROM `" . TABLE_CHAUFFAGE_TEMP . "` WHERE `id` = '1';");
  $row = mysql_fetch_array($retour);
  $_XTemplate->assign('TEMPERATUREEXTERIEURE', round($row[0], 1));

  /* FERMETURE SQL */
  mysql_close();

?>
