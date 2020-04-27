<?php
// SELECT * FROM `chauffage_sonde` JOIN `chauffage_temp` ON `chauffage_temp`.`id`=`chauffage_sonde`.`id`
  $titre = 'Gestion du chauffage2';

  include '../class/class.envoiTrame.php5';

  /* DECLARATION DES FONCTIONS EN AJAX */
  $xajax->register(XAJAX_FUNCTION, 'descendreTemperature');
  $xajax->register(XAJAX_FUNCTION, 'monterTemperature');
  $xajax->register(XAJAX_FUNCTION, 'moyenne');

  /* FONCTIONS PHP AJAX */
  function moyenne() {
    $objResponse = new xajaxResponse();
    mysql_connect(MYSQL_HOST, MYSQL_LOGIN, MYSQL_PWD);
    mysql_select_db(MYSQL_DB);
    $retour = mysql_query("SELECT AVG(`valeur`) FROM `" . TABLE_CHAUFFAGE_TEMP . "` WHERE `moyenne` = '1'");
    $row = mysql_fetch_array($retour);
    mysql_close();
    $objResponse->assign("moyenne","innerHTML", round($row[0],1));
    return $objResponse;    
  }

  function descendreTemperature() {
    $objResponse = new xajaxResponse();
    mysql_connect(MYSQL_HOST, MYSQL_LOGIN, MYSQL_PWD);
    mysql_select_db(MYSQL_DB);    
    $retour = mysql_query("SELECT `valeur` FROM `" . TABLE_CHAUFFAGE_CLEF . "` WHERE `clef` = 'temperature'");
    $row = mysql_fetch_array($retour);
    $nouvelle = $row[0] - 1;
    mysql_query("UPDATE `" . TABLE_CHAUFFAGE_CLEF . "` SET `valeur` = '" . $nouvelle . "' WHERE `clef` = 'temperature'");
    mysql_close();
    $objResponse->assign("temperature","innerHTML", $nouvelle);
    return $objResponse;    
  }

  function monterTemperature() {
    $objResponse = new xajaxResponse();
    mysql_connect(MYSQL_HOST, MYSQL_LOGIN, MYSQL_PWD);
    mysql_select_db(MYSQL_DB);
    $retour = mysql_query("SELECT `valeur` FROM `" . TABLE_CHAUFFAGE_CLEF . "` WHERE `clef` = 'temperature'");
    $row = mysql_fetch_array($retour);
    $nouvelle = $row[0] + 1;
    mysql_query("UPDATE `" . TABLE_CHAUFFAGE_CLEF . "` SET `valeur` = '" . $nouvelle . "' WHERE `clef` = 'temperature'");
    mysql_close();
    $objResponse->assign("temperature","innerHTML", $nouvelle);
    return $objResponse;    
  }

  /* CONNEXION SQL */
  mysql_connect(MYSQL_HOST, MYSQL_LOGIN, MYSQL_PWD);
  mysql_select_db(MYSQL_DB);

  /* AFFICHAGE DES TEMPERATURES SUR LES PLANS */
  $retour0 = mysql_query("SELECT `lieu` FROM `localisation`");
  while( $row0 = mysql_fetch_array($retour0) ) {
    $sql = "SELECT `" . TABLE_CHAUFFAGE_SONDE . "`.`id` , `" . TABLE_CHAUFFAGE_SONDE . "`.`id_sonde` , `" . TABLE_CHAUFFAGE_SONDE . "`.`moyenne` , `" . TABLE_CHAUFFAGE_SONDE . "`.`localisation` , `" . TABLE_CHAUFFAGE_SONDE . "`.`img_x` , `" . 
			TABLE_CHAUFFAGE_SONDE . "`.`img_y` , `" . TABLE_CHAUFFAGE_SONDE . "`.`description` , `" . TABLE_CHAUFFAGE_TEMP . "`.`valeur`, `" . TABLE_CHAUFFAGE_TEMP . "`.`update` " . 
	        " FROM `" . TABLE_CHAUFFAGE_SONDE . "` JOIN `" . TABLE_CHAUFFAGE_TEMP . "` ON `" . TABLE_CHAUFFAGE_SONDE . "`.`id`=`" . TABLE_CHAUFFAGE_TEMP . "`.`id` WHERE `" . TABLE_CHAUFFAGE_SONDE . "`.`localisation` = '" . $row0['lieu'] . "';";
	$retour = mysql_query($sql);
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
  $retour = mysql_query("SELECT * FROM `" . TABLE_LOCALISATION . "`");
  while( $row = mysql_fetch_array($retour) ) {
    $_XTemplate->assign('LOCALISATION', $row['lieu']);
    $_XTemplate->parse('main.NIVEAU');
  }

  /* AFFICHAGE DE LA TEMPERATURE MOYENNE DE LA MAISON */
  $retour = mysql_query("SELECT AVG(`valeur`) FROM `" . TABLE_CHAUFFAGE_TEMP . "` WHERE `moyenne` = '1'");
  $row = mysql_fetch_array($retour);
  $_XTemplate->assign('MOYENNEMAISON', round($row[0],1));

  /* AFFICHAGE DE LA TEMPERATURE VOULUE */
  $retour = mysql_query("SELECT `valeur` FROM `" . TABLE_CHAUFFAGE_CLEF . "` WHERE `clef` = 'temperature'");
  $row = mysql_fetch_array($retour);
  $_XTemplate->assign('TEMPERATURE', $row[0]);

  /* AFFICHAGE DE L'ETAT DE LA CHAUDIERE */
  $retour = mysql_query("SELECT `valeur` FROM `" . TABLE_CHAUFFAGE_CLEF_TEMP . "` WHERE `clef` = 'chaudiere'");
  $row = mysql_fetch_array($retour);
  if ( $row[0] == '0' ) {
    $chaudiere = "A l'arrêt";
  }
  else if ( $row[0] == '1' ) {
    $chaudiere = "En marche";
  }
  $_XTemplate->assign('CHAUDIERE', $chaudiere);

  /* AFFICHAGE TEMPERATURE EXTERIEURE */
  $retour = mysql_query("SELECT `valeur` FROM `" . TABLE_CHAUFFAGE_TEMP . "` WHERE `id` = '1';");
  $row = mysql_fetch_array($retour);
  $_XTemplate->assign('TEMPERATUREEXTERIEURE', round($row[0], 1));

  /* FERMETURE SQL */
  mysql_close();

?>
