<?php

include_once('/var/www/domocan/www/conf/config.php');

class envoiTrame {
  /* PREPARATION DU CHECKSUM */
  function checksum() {
    //LBR
    $check = 0;
    for ($i = 0; $i <= 14; $i++) {
      $check = $this->trame[$i] + $check;
    }

    $this->trame[15] = $check % 256;
  }

  /* CONVERSION DE LA TRAME AVEC PACK()  */
  function conversion() {
    for ($i = 0; $i <= 15; $i++) {
      $this->trame_ok .= pack("c", $this->trame[$i]);
      $trame .= $this->trame[$i];
    }
  }

  /* ENVOI DE LA TRAME SUR L'INTERFACE */
  function envoiTrame() {
    if (isset($this->trame_ok)) {
      $socket = socket_create(AF_INET, SOCK_DGRAM, 0);
      $longueur = strlen($this->trame_ok);
     socket_sendto($socket, $this->trame_ok, $longueur, 0, ADRESSE_INTERFACE, 1470);
      socket_close($socket);
	}

  }

  /* PREPARE UNE TRAME CAN */
  function CAN($entete, $IDCAN = array(), $donnees = array()) {

    $this->trame[0] = $entete; // ENVOI D'UNE TRAME CAN
    $this->trame[1] = dechex(substr($_SERVER['SERVER_ADDR'],strrpos($_SERVER['SERVER_ADDR'],".")+1)); // ID DU PC QUI ENVOI
    $this->trame[2] = dechex(count($IDCAN) + count($donnees)); // NOMBRE D'OCTETS DE DATA
//    print "trame2".$this->trame[2];

    if ( isset($IDCAN['DEST']) ) {
      $this->trame[3] = $IDCAN['DEST']; // TYPE DE CARTE (CAN)
    }
    else {
      $this->trame[3] = 0x00;
    }
 
   if ( isset($IDCAN['COMM']) ) {
      $this->trame[4] = $IDCAN['COMM']; // COMMANDE (CAN)
    }
    else { 
      $this->trame[4] = 0x00; 
    }

    if ( isset($IDCAN['CIBL']) ) {
      $this->trame[5] = $IDCAN['CIBL']; // CIBLE (CAN)
    }
    else { 
      $this->trame[5] = 0x00; 
    }

    if ( isset($IDCAN['PARAM']) ) {
      $this->trame[6] = $IDCAN['PARAM']; // PARAMETRE (CAN)
    }
    else { 
      $this->trame[6] = 0x00; 
    }

    $i = '7';
    foreach ($donnees as $valeur) {
      $this->trame[$i] = $valeur;
      $i++;
    }

    while ( $i <= 14 ) {
      $this->trame[$i] = 0x00;
      $i++;
    }

  }

  /* ARCHIVE VERS SQL */
  function logs($id_gradateur, $id_sortie, $valeur) {
    if (DEBUG_AJAX) {
      if (!mysql_connect(MYSQL_HOST, MYSQL_LOGIN, MYSQL_PWD)) { $this->debug->envoyer(1, "Reception Nom Carte", "!!! ERREUR Connection DB!!!"); }
	  if (!mysql_select_db(MYSQL_DB)) { $this->debug->envoyer(1, "Reception Nom Carte", "!!! ERREUR Selection DB!!!"); }
	  $sql = "INSERT INTO `logs` (id_gradateur,id_sortie,valeur) VALUES ('$id_gradateur', '$id_sortie', '$valeur');";
	  echo(CRLF."class.envoiTrame.php5/Log, SQL=$sql".CRLF);
      mysql_query($sql);
      mysql_close();
	}

  }

  /* ACCUSE VERS SQL */
  function accuse($id_gradateur, $id_sortie, $valeur) {

    if (!mysql_connect(MYSQL_HOST, MYSQL_LOGIN, MYSQL_PWD)) { $this->debug->envoyer(1, "Reception Nom Carte", "!!! ERREUR Connection DB!!!"); }
	if (!mysql_select_db(MYSQL_DB)) { $this->debug->envoyer(1, "Reception Nom Carte", "!!! ERREUR Selection DB!!!"); }

    /* POUR RETOUR CHAUFFAGE */
    if ( $id_gradateur == '0x' . CARTE_CHAUFFAGE && $id_sortie == SORTIE_CHAUFFAGE ) {
      if ( $valeur == '32' ) {
	    $sql = "UPDATE `" . TABLE_CHAUFFAGE_CLEF_TEMP . "` SET `valeur` = '1' WHERE `clef` = 'chaudiere';";
	    echo(CRLF."class.envoiTrame.php5/AccuseChaudiere ON, SQL=$sql".CRLF);
        mysql_query($sql);
        $ch = curl_init(URIPUSH);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, "chaudiere;En marche");
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $ret = curl_exec($ch);
        curl_close($ch);
      } else {
	    $sql = "UPDATE `" . TABLE_CHAUFFAGE_CLEF_TEMP . "` SET `valeur` = '0' WHERE `clef` = 'chaudiere';";
	    echo(CRLF."class.envoiTrame.php5/AccuseChaudiere OFF, SQL=$sql".CRLF);
        mysql_query($sql);
        $ch = curl_init(URIPUSH);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, "chaudiere;A l'arrêt");
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $ret = curl_exec($ch);
        curl_close($ch);
      }
    } else {
	  if ( $id_gradateur == '0x' . CARTE_BOILER && $id_sortie == SORTIE_BOILER ) {
        if ( $valeur == '32' ) {
	      $sql = "UPDATE `" . TABLE_CHAUFFAGE_CLEF_TEMP . "` SET `valeur` = '1' WHERE `clef` = 'boiler';";
	      echo(CRLF."class.envoiTrame.php5/AccuseBoiler ON, SQL=$sql".CRLF);
          mysql_query($sql);
          $ch = curl_init(URIPUSH);
          curl_setopt($ch, CURLOPT_POST, 1);
          curl_setopt($ch, CURLOPT_POSTFIELDS, "boiler;En marche");
          curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
          $ret = curl_exec($ch);
          curl_close($ch);
        } else {
	      $sql = "UPDATE `" . TABLE_CHAUFFAGE_CLEF_TEMP . "` SET `valeur` = '0' WHERE `clef` = 'boiler';";
	      echo(CRLF."class.envoiTrame.php5/AccuseBoiler OFF, SQL=$sql".CRLF);
          mysql_query($sql);
          $ch = curl_init(URIPUSH);
          curl_setopt($ch, CURLOPT_POST, 1);
          curl_setopt($ch, CURLOPT_POSTFIELDS, "boiler;A l'arrêt");
          curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
          $ret = curl_exec($ch);
          curl_close($ch);
        } // END IF
	  } else {
	    // Light Status in DB
	    $sql    = "SELECT * FROM `lumieres` WHERE `carte` = '" . $id_gradateur . "' AND `sortie` = '0x" . $id_sortie . "';";
	    echo(CRLF."class.envoiTrame.php5/AccuseLightStatus, SQL=$sql".CRLF);
	    $retour = mysql_query($sql);
	    $row    = mysql_fetch_array($retour);
	    $id     = $row['id'];
		if ($id!="") {
	      $sql = "UPDATE `" . TABLE_LUMIERES_STATUS . "` SET `valeur` = '" . $valeur . "' WHERE `id`='" . $id . "';";
	      echo(CRLF."class.envoiTrame.php5/AccuseLightStatus, SQL=$sql".CRLF);
          mysql_query($sql);
		} // END IF
	  } // END IF
    } // END IF

    mysql_close();

  }

}

?>
