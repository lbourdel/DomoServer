<?php
/*
Info sources for Web Temp:
http://blog.turningdigital.com/2012/09/raspberry-pi-ds18b20-temperature-sensor-rrdtool/
http://weather.noaa.gov/pub/data/observations/metar/decoded/EBBR.TXT
*/
  /*
    SCRIPT DE RECUPERATION DES TEMPERATURES VIA 1WIRE, ET MISE EN BDD
  */

  /* DEPENDANCES */
  include_once('/var/www/domocan/www/conf/config.php');

  /* CONNEXION SQL */
  mysql_connect(MYSQL_HOST, MYSQL_LOGIN, MYSQL_PWD);
  mysql_select_db(MYSQL_DB);

  // chauffage_temp & lumieres_status Table already populated?
  $sql = "SELECT COUNT(*) AS county FROM `" . TABLE_CHAUFFAGE_TEMP . "`;";
  $retour = mysql_query($sql);
  $row = mysql_fetch_array($retour);
  if ($row['county']==0) {
    // Populate chauffage_temp (temperaure reading in RAM) ... no disk access every minute ;-)
    $sql = "SELECT * FROM  `" . TABLE_CHAUFFAGE_SONDE . "` ORDER BY `id`;";
    $retour = mysql_query($sql);
    while ( $row = mysql_fetch_array($retour) ) {
      $id   = $row['id'];
	  $mean = $row['moyenne'];
      $sql2 = "INSERT INTO `" . TABLE_CHAUFFAGE_TEMP . "` (`id`, `valeur`, `moyenne`, `update`) VALUES ('" . $id . "', '', '" . $mean . "', '0000-00-00 00:00:00');";
      $retour2 = mysql_query($sql2);
    } // END WHILE
	
	// Populates lumieres_status (light status in RAM)
	$sql = "SELECT * FROM  `" . TABLE_LUMIERES . "` ORDER BY `id`;";
	$retour = mysql_query($sql);
	while ( $row = mysql_fetch_array($retour) ) {
      $id   = $row['id'];
	  $sql2 = "INSERT INTO `" . TABLE_LUMIERES_STATUS . "` (`id`, `valeur`, `timer_pid`) VALUES ('" . $id . "', '00', '0');";
      $retour2 = mysql_query($sql2);
    } // END WHILE
	
	// Populates chauffage_clef_TEMP (Heating system Status in RAM)
	$sql = "INSERT INTO `" . TABLE_CHAUFFAGE_CLEF_TEMP . "` (`id`, `clef`, `valeur`) VALUES (NULL, 'chaudiere', '1');";
	$retour = mysql_query($sql);
	$sql = "INSERT INTO `" . TABLE_CHAUFFAGE_CLEF_TEMP . "` (`id`, `clef`, `valeur`) VALUES (NULL, 'boiler', '0');";
	$retour = mysql_query($sql);
	$sql = "INSERT INTO `" . TABLE_CHAUFFAGE_CLEF_TEMP . "` (`id`, `clef`, `valeur`) VALUES (NULL, 'warm_water', '1');"; 
	$retour = mysql_query($sql);
        $sql = "INSERT INTO `" . TABLE_CHAUFFAGE_CLEF_TEMP . "` (`id`, `clef`, `valeur`) VALUES (NULL, 'PAC', '0');";
        $retour = mysql_query($sql);
        $sql = "INSERT INTO `" . TABLE_CHAUFFAGE_CLEF_TEMP . "` (`id`, `clef`, `valeur`) VALUES (NULL, 'counter', '0');";
        $retour = mysql_query($sql);
	
  } // END IF


  /* RECUPERATION DES VALEURS DE TEMPERATURE ET MISES A JOUR DES GRAPHIQUES */
  $sql = "SELECT * FROM `" . TABLE_CHAUFFAGE_SONDE . "` WHERE 1;";
  $retour_s = mysql_query($sql);
  while ( $row_s = mysql_fetch_array($retour_s) ) {
    $sensor    = $row_s["id_sonde"];
	$sensor_id = $row_s["id"];
	if (substr($sensor,0,2)=="28") {
      // 1 Wire
	  // 1Wire Mode = OWFS
	  if (ONEWIRE_MODE=="OWFS") {
        $a = exec('cat ' . PATHOWFS . '/' . $sensor . '/temperature');
        $b = round(str_replace(' ', '', $a), 2);
      } // END IF
	  // 1Wire Mode = RaspberryPi
	  if (ONEWIRE_MODE=="RPI") {
	    $OneWireDir = "/sys/bus/w1/devices/" . $sensor . "/w1_slave";
	    $data = array();
	    $data = file($OneWireDir);  
	    $data = explode('t=',$data[1]);  
	    $b = round($data[1]/1000, 2); 
	  } // END IF
	  $sql = "UPDATE `" . TABLE_CHAUFFAGE_TEMP . "` SET `valeur` = '" . $b . "' WHERE `id` = '" . $sensor_id . "';";
	  mysql_query($sql);
	} else {
	  // From http://weather.noaa.gov ?
	  $URL = WEB_TEMP_URL . $sensor . ".TXT";
	  if (($handle = fopen($URL, "r")) !== FALSE) {
        $data = fgetcsv($handle, 1000, " ");
        $date = str_replace("/","-",$data[0]) . " " . $data[1] . ":00";
        $data = fgetcsv($handle, 1000, " ");
		$j=0;
		while (isset($data[$j])) {
		  if (strpos($data[$j],"/")) { break;}
		  //echo("\n".$data[$j].",k=".$k."\n");
		  $j++;
		} // END WHILE
		
        if (substr($data[$j],0,1)=="M") { $b = "-" . substr($data[$j],1, strpos($data[$j],"/")-1); } else { $b = substr($data[$j],0, strpos($data[$j],"/"));}
        fclose($handle);
		
		// Update DB
		$sql = "UPDATE `" . TABLE_CHAUFFAGE_TEMP . "` SET `valeur` = '" . $b . "', `update` = '" . $date . "' WHERE `id` = '1';";
		//echo("\n".$sql."\n");
		$retour = mysql_query($sql);
      } // END IF
	  
	  
	  
	} // END IF
	
	// RRD Graph
	if (RRDPATH!="") {
      if ( !is_file(RRDPATH . '/' . $sensor . '.rrd') ) {
        exec(RRDPATH . '/creation.sh ' . RRDPATH . '/' . $row['id_sonde'] . '.rrd');	  
      } // END IF
      exec('rrdtool update ' . RRDPATH . '/' . $row['id_sonde'] . '.rrd N:' . $b);
      exec(RRDPATH . '/mettre_a_jour.sh ' . RRDPATH . '/' . $row['id_sonde']);
    } // END IF
	
	// PUSH Valeur Sonde
	//$sonde = substr($row['id_sonde'], 3);
    $sonde = $sensor;
	$ch = curl_init(URIPUSH);
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_POSTFIELDS, "sonde;" . $sonde . "," . round($b, 1));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    $ret = curl_exec($ch);
    curl_close($ch);
  
  
  /* MISE A JOUR DU GRAPHIQUE DE TEMPERATURE MOYENNE MAISON */
  $retour = mysql_query("SELECT AVG(`valeur`) FROM `" . TABLE_CHAUFFAGE_TEMP . "` WHERE `moyenne` = '1'");
  $row = mysql_fetch_array($retour);
  
  if (RRDPATH!="") {
    if ( !is_file(RRDPATH . '/temperaturemaison.rrd') ) {
      exec(RRDPATH . '/creation.sh ' . RRDPATH . '/temperaturemaison.rrd');
    }
    exec('rrdtool update ' . RRDPATH . '/temperaturemaison.rrd N:' . round($row[0], 1));
    exec(RRDPATH . '/mettre_a_jour.sh ' . RRDPATH . '/temperaturemaison');
  } // END IF
  // PUSH Valeur Moyenne
  $ch = curl_init(URIPUSH);
  curl_setopt($ch, CURLOPT_POST, 1);
  curl_setopt($ch, CURLOPT_POSTFIELDS, "sonde;temperaturemoyenne," . round($row[0], 1));
  curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
  $ret = curl_exec($ch);
  curl_close($ch);
  
  /* MISE A JOUR DU GRAPHIQUE DE TEMPERATURE EXTERIEURE */
  $retour = mysql_query("SELECT `valeur` FROM `" . TABLE_CHAUFFAGE_TEMP . "` WHERE `id` = '1'");
  $row = mysql_fetch_array($retour);
  
  if (RRDPATH!="") {  
    if ( !is_file(RRDPATH . '/temperatureexterieure.rrd') ) {
      exec(RRDPATH . '/creation.sh ' . RRDPATH . '/temperatureexterieure.rrd');
    }
    exec('rrdtool update ' . RRDPATH . '/temperatureexterieure.rrd N:' . round($row[0], 1));
    exec(RRDPATH . '/mettre_a_jour.sh ' . RRDPATH . '/temperatureexterieure');
  } // END IF
  // PUSH Valeur Temp Exterieure
  $ch = curl_init(URIPUSH);
  curl_setopt($ch, CURLOPT_POST, 1);
  curl_setopt($ch, CURLOPT_POSTFIELDS, "sonde;temperatureexterieure," . round($row[0], 1));
  curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
  $ret = curl_exec($ch);
  curl_close($ch);

} // END WHILE

mysql_close();

?>
