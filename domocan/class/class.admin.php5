<?php

class admin extends envoiTrame {

  /*
    ABANDONNE L'ENVOI DE LA TRAME CAN EN COURS
  */
  function stop() {
    $this->CAN(0x41);
    $this->checksum();
    $this->conversion();
    $this->envoiTrame();
  }

  /*
    MODIFIE LE MASQUE / FILTRE 
  */
  function modifierMasqueFiltre() {
    $IDCAN[DEST] = $FILTRE_DEST;
    $IDCAN[COMM] = $FILTRE_COMM;
    $IDCAN[CIBL] = $FILTRE_CIBL;
    $IDCAN[PARA] = $FILTRE_PARA;
    $donnees[0]  = $MASQUE_DEST;
    $donnees[1]  = $MASQUE_COMM;
    $donnees[2]  = $MASQUE_CIBL;
    $donnees[3]  = $MASQUE_PARA;
    $this->CAN(0x42, $IDCAN, $donnees);
    $this->checksum();
    $this->conversion();
    $this->envoiTrame();
  }

  /*
    DEMANDE LE MASQUE ET LE FILTRE
  */
  function masqueFiltre() {
    $this->CAN(0x43);
    $this->checksum();
    $this->conversion();
    $this->envoiTrame();
  }

  /*
    MODIFIE LES PARAMETRES CAN
  */
  function modifierParametresCAN($TQ, $TP, $PS1, $PS2, $SJW, $SAMPLE) {
    $IDCAN[DEST] = $TQ;
    $IDCAN[COMM] = $TP;
    $IDCAN[CIBL] = $PS1;
    $IDCAN[PARA] = $PS2;
    $donnees[0]  = $SJW;
    $donnees[1]  = $SAMPLE;
    $this->CAN(0x44, $IDCAN, $donnees);
    $this->checksum();
    $this->conversion();
    $this->envoiTrame();
  }

  /*
    DEMANDE LES PARAMETRES CAN
  */
  function parametresCAN() {
    $this->CAN(0x45);
    $this->checksum();
    $this->conversion();
    $this->envoiTrame();
  }

}
?>

