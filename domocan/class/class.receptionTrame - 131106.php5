<?php

include_once('class.debug.php5');
include_once('class.envoiTrame.php5');	
include_once('class.gradateur.php5');	
include_once('class.in16.php5');	
include_once('class.erreur.php5');	
//include('../www/conf/config.php');	

class receptionTrame {

  function __construct() {
    $this->debug = new debug();
    $this->gradateur = new gradateur();
    $this->erreur = new erreur();
    $this->in16 = new in16();
    $this->envoiTrame = new envoiTrame();
  }

  /*
    RECEPTION D'UNE TRAME DONC TRAITEMENT
  */
  function traiter($trame) {

    /* CONVERTIR EN TABLEAU */
    $this->trame_txt = substr($trame, 0, 32);
    $this->trame = str_split(substr($trame, 0, 32), 2);
    unset($trame);

    /* VERIFICATION CHECKSUM */
    $check = 0;
    for($i=0;$i<=14;$i++) {
      $check = hexdec($this->trame[$i]) + $check;
    }
    
    $check = dechex($check % 256);

    if ($this->trame[15] == $check) {	

      /* DEBUG */
      $this->debug->envoyer(2, "Class RECEPTION TRAME", $this->trame_txt);

      /* REPARTITION SELON ENTETE */
      switch ($this->entete()) {

        case '50' :
          /* ACCUSE DE RECEPTION D'ENVOI DE TRAME BRUTE */
          $PCID   = $this->trame[1];
          $STATUT = $this->trame[3];
          $this->debug->envoyer(1, "ACCUSE DE RECEPTION","DE " . $PCID . " | ERREUR : " . $STATUT);
          break;

        case '70' :
          /* RECEPTION DE TRAME CAN DEPUIS UNE CARTE FILLE */
          $DESTINATAIRE = $this->trame[3];
          $COMMANDE = $this->trame[4];
          $CIBLE = $this->trame[5];
          $PARAMETRE = $this->trame[6];
          $D0 = $this->trame[7];
          $D1 = $this->trame[8];
          $D2 = $this->trame[9];
          $D3 = $this->trame[10];
          $D4 = $this->trame[11];
          $D5 = $this->trame[12];
          $D6 = $this->trame[13];
          $D7 = $this->trame[14];
          $this->debug->envoyer(1, "Class RECEPTION D'UNE TRAME","DESTINATAIRE " . $DESTINATAIRE . " | COMMANDE : " . $COMMANDE . " | CIBLE : " . $CIBLE . " | PARAMETRE : " . $PARAMETRE . " | D0 : "
          . $D0 . " | D1 : " . $D1 . " | D2 : " . $D2 . " | D3 : " . $D3 . " | D4 : " . $D4 . " | D5 : " . $D5 . " | D6 : " . $D6 . " | D7 : " . $D7);

		  // Tests Henry 
		  // Voir Tableau p.42, Présentation_r7.pdf
		  
		  if ($PARAMETRE=="69") { 
		  
			// "Card Name Response" received
			// Connects to DB
			if (!mysql_connect(MYSQL_HOST, MYSQL_LOGIN, MYSQL_PWD)) { $this->debug->envoyer(1, "Reception Nom Carte", "!!! ERREUR Connection DB!!!"); }
			if (!mysql_select_db(MYSQL_DB)) { $this->debug->envoyer(1, "Reception Nom Carte", "!!! ERREUR Selection DB!!!"); }
			
			$Card_Name = chr(hexdec($D0)).chr(hexdec($D1)).chr(hexdec($D2)).chr(hexdec($D3)).chr(hexdec($D4)).chr(hexdec($D5)).chr(hexdec($D6)).chr(hexdec($D7));
		    $this->debug->envoyer(1, "Reception Nom Carte","Type Carte=" . $DESTINATAIRE . ", Numero=" . $CIBLE. ", Nom=".$Card_Name);
		

			$this->debug->envoyer(1, "Reception Nom Carte",$sql);
			$row = mysql_fetch_array($query);
			$Now          = date("YmdHis") . substr(microtime(true),11,3);
			$Answer_Time  = $Now - $row['timestamp'];
			
			// Communicates back to Admin via PUSH
            $ch = curl_init(URIPUSH);
            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_POSTFIELDS, "Admin-CardName;" . "0x" . $DESTINATAIRE . ",0x" .$CIBLE . "," . $Card_Name);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            $ret = curl_exec($ch);
            curl_close($ch);
			
			// Add or Modify Card in DB
		    $count = mysql_num_rows(mysql_query("SELECT * FROM `ha_subsystem` WHERE Manufacturer='DomoCAN' AND Type='0x".$DESTINATAIRE."' AND Reference='0x".$CIBLE."';"));
		    if ($count == 1) {
			  mysql_query("UPDATE `ha_subsystem` SET Name='".$Card_Name."' WHERE Manufacturer='DomoCAN' AND Type='0x".$DESTINATAIRE."' AND Reference='0x".$CIBLE."';");
		    } else {
			  mysql_query("INSERT INTO `ha_subsystem` (id,Manufacturer,Type,Reference,Name) VALUES ('','DomoCAN','0x".$DESTINATAIRE."','0x".$CIBLE."','".$Card_Name."');");
		    } // End IF		  

			
			mysql_close();
		  } //End IF
		  
          switch ($DESTINATAIRE) {

            /* RECEPTION DEPUIS UNE CARTE DE TYPE IN16 */
            case '60' :
              $this->in16->reception($COMMANDE, $CIBLE, $PARAMETRE, $D0, $D1, $D2, $D3, $D4, $D5, $D6, $D7);
			  // Boiler?
			  //mysql_query("UPDATE `" . TABLE_CHAUFFAGE_CLEF ."` SET `valeur` = '" . $D0 . ";". $D1 . ";". $D2 . ";". $D3 . ";". $D4 . ";". $D5 . ";". $D6 . ";". $D7 . "' WHERE `clef` = 'warm_water';"); // $COMMANDE . ",". $CIBLE . ",". $PARAMETRE . ";". 
			  //mysql_query("UPDATE `" . TABLE_CHAUFFAGE_CLEF ."` SET `valeur` = '" . "Com=" . $COMMANDE . ", C:" . $CIBLE . "=" . str_pad(CARTE_SONDE_BOILER, 2, "0", STR_PAD_LEFT) . ", P:" . $PARAMETRE . "=" . str_pad(ENTREE_SONDE_BOILER, 2, "0", STR_PAD_LEFT) . ", D0:" . $D0 . "' WHERE `clef` = 'warm_water';");
			  if (($COMMANDE=="18") AND ($CIBLE==str_pad(CARTE_SONDE_BOILER, 2, "0", STR_PAD_LEFT)) AND ($PARAMETRE==str_pad(ENTREE_SONDE_BOILER, 2, "0", STR_PAD_LEFT))) {
			    $new_key = "unknown";
				if ($D0=="8a") { $new_key = "1"; }
				if ($D0=="52") { $new_key = "0"; }
			    mysql_query("UPDATE `" . TABLE_CHAUFFAGE_CLEF ."` SET `valeur` = '" . $new_key  . "' WHERE `clef` = 'warm_water';");
			  } // ENDIF

              break;

            /* RECEPTION D'UN ACCUSE DEPUIS CARTE GRADATEUR (RETOUR ALLUMAGE) */
            case '50' :
              /* ENVOI VERS SQL POUR ACCUSE */
              $this->envoiTrame->accuse($CIBLE, $PARAMETRE, $D0);

              $ch = curl_init(URIPUSH);
              curl_setopt($ch, CURLOPT_POST, 1);
              curl_setopt($ch, CURLOPT_POSTFIELDS, "lumiere;" . $CIBLE . ",0x" . $PARAMETRE . "," . $D0);
              curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
              $ret = curl_exec($ch);
              curl_close($ch);

              /* LANCEMENT DU PROCESSUS DE FERMETURE AUTOMATIQUE SUR TIMER PROGRAMMABLE PAR LAMPE */
              mysql_connect(MYSQL_HOST, MYSQL_LOGIN, MYSQL_PWD);
              mysql_select_db(MYSQL_DB);
              $row = mysql_query("SELECT `id`,`timer`,`timer_pid` FROM `lumieres` WHERE `carte` = '" . $CIBLE . "' AND `sortie` = '0x" . $PARAMETRE . "'");
              $retour = mysql_fetch_array($row);

              if ( $D0 != '00' && $retour['id'] != '' ) {

                if ( $retour['timer'] != '0' ) {

                  if ( $retour['timer_pid'] != '0' ) {
                    $a = "kill -9 " . $retour['timer_pid'];
                    exec($a);
                  }
		  $cmd = "sleep " . $retour['timer'] . " && php /var/www/domocan/bin/eteindre.php " . $CIBLE . " " . $PARAMETRE . " " .  $retour['id'] . "&";
		  $ds = array(array('pipe', 'r'));
		  $cat = proc_open($cmd,$ds,$pipes);
		  $tmp = proc_get_status($cat);
		  $pid = $tmp['pid'] + 2;
                  mysql_query("UPDATE `lumieres` SET `timer_pid` = '" . $pid . "' WHERE `id` = '" . $retour['id'] . "'");

                }
              }
              mysql_close();
              break;


            case 'fd' :
              $this->erreur->reception($PARAMETRE);
              break;

          }

          break;

        case '51' :
          /* RECEPTION DES STATUTS DE COMMUNICATION CAN */
          $PCID = $this->trame[1];
          $TXB0CON = $this->trame[3];
          $COMSTAT = $this->trame[4];
          $STATUT = $this->trame[5];
          $this->debug->envoyer(1, "RECEPTION STATUS COMM. CAN","DE " . $PCID . " | TXB0CON : " . $TXB0CON . " | COMSTAT : " . $COMSTAT . " | ERREUR : " . $STATUT);
          break;

        case '52' :
          /* RECEPTION DU MASQUE ET DU FILTRE */
          $PCID = $this->trame[1];
          $FILTRE_DEST = $this->trame[3];
          $FILTRE_COMM = $this->trame[4];
          $FILTRE_CIBL = $this->trame[5];
          $FILTRE_PARA = $this->trame[6];
          $MASQUE_DEST = $this->trame[7];
          $MASQUE_COMM = $this->trame[8];
          $MASQUE_CIBLE = $this->trame[9];
          $MASQUE_PARA = $this->trame[10];
          $STATUT = $this->trame[11];
          $this->debug->envoyer(1, "RECEPTION MASQUE / FILTRE","DE " . $PCID . " | FILTRE_DEST : " . $FILTRE_DEST . " | FILTRE_COMM : " . $FILTRE_COMM . " | FILTRE_CIBL : " . $FILTRE_CIBL . " | FILTRE_PARA : "
          . $FILTRE_PARA . " | MASQUE_DEST : " . $MASQUE_DEST . " | MASQUE_COMM : " . $MASQUE_COMM . " | MASQUE_CIBLE : " . $MASQUE_CIBLE . " | MASQUE_PARA : " . $MASQUE_PARA . " | ERREUR : " . $STATUT);
          break;

        case '54' :
          /* RECEPTION DES PARAMETRES CAN ACTUELS AVEC EEEPROM CONCERNE */
          $PCID = $this->trame[1];
          $TQ = $this->trame[3];
          $TP = $this->trame[4];
          $PS1 = $this->trame[5];
          $PS2 = $this->trame[6];
          $SJW = $this->trame[7];
          $SAMPLE = $this->trame[8];
          $STATUT = $this->trame[9];
          $this->debug->envoyer(1, "RECEPTION PARAM. CAN","DE " . $PCID . " | TQ : " . $TQ . " | TP : " . $TP . " | PS1 : " . $PS1 . " | PS2 : "
          . $PS2 . " | SJW : " . $SJW . " | SAMPLE : " . $SAMPLE . " | ERREUR : " . $STATUT);
          break;

	  }

    }

  }

  /* RECUPERATION DE L'ENTETE */
  function entete() {
    return $this->trame[0];
  }

}

?>

