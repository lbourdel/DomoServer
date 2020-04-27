<?php

class communes extends envoiTrame {

  /*
    SORTIR DU MODE BOOTLOADER
  */
  function sortirBootloader($destinataire = '', $cible = '') {
    $IDCAN[DEST] = $destinataire;
    $IDCAN[COMM] = 0x00;
    $IDCAN[CIBL] = $cible;
    $IDCAN[PARA] = 0x10;
    $donnees[0]  = 0x55;
    $donnees[1]  = 0xA5;
    $this->CAN(0x60, $IDCAN, $donnees);
    $this->checksum();
    $this->conversion();
    $this->envoiTrame();
  }

  /*
    DEFINIR POSITION POINTEUR EN MEMOIRE FLASH
  */
  function defPointeur($destinataire = '', $cible = '', $msb = '', $lsb = '') {
    // LSB DOIT ETRE VERIFIE => MULTIPLE DE 8 SINON DEHORS !
    $IDCAN[DEST] = $destinataire;
    $IDCAN[COMM] = 0x00;
    $IDCAN[CIBL] = $cible;
    $IDCAN[PARA] = 0x11;
    $donnees[0]  = $msb;
    $donnees[1]  = $lsb;
    $this->CAN(0x60, $IDCAN, $donnees);
    $this->checksum();
    $this->conversion();
    $this->envoiTrame();
  }

  /*
    DEMANDE VALEUR DU POINTEUR EN MEMOIRE FLASH
  */
  function verifPointeur($destinataire = '', $cible = '') {
    $IDCAN[DEST] = $destinataire;
    $IDCAN[COMM] = 0x00;
    $IDCAN[CIBL] = $cible;
    $IDCAN[PARA] = 0x12;
    $donnees     = array();
    $this->CAN(0x60, $IDCAN, $donnees);
    $this->checksum();
    $this->conversion();
    $this->envoiTrame();
  }

  /*
    ECRIRE EN MEMOIRE FLASH (8 octets)
      Utilisation : $octet doit être un tableau de 8 entrées (1 entrée = 1 octet)
  */
  function ecrireFlash($destinataire = '', $cible = '', $octets = array()) {
    $IDCAN[DEST] = $destinataire;
    $IDCAN[COMM] = 0x00;
    $IDCAN[CIBL] = $cible;
    $IDCAN[PARA] = 0x13;
    $donnees     = $octets;
    $this->CAN(0x60, $IDCAN, $donnees);
    $this->checksum();
    $this->conversion();
    $this->envoiTrame();
  }

  /*
    LECTURE EN MEMOIRE FLASH (par 8 octets)
  */
  function lectureFlash($destinataire = '', $cible = '', $msb = '', $lsb = '') {
    $IDCAN[DEST] = $destinataire;
    $IDCAN[COMM] = 0x00;
    $IDCAN[CIBL] = $cible;
    $IDCAN[PARA] = 0x14;
    $donnees[0]  = $msb;
    $donnees[1]  = $lsb;
    $this->CAN(0x60, $IDCAN, $donnees);
    $this->checksum();
    $this->conversion();
    $this->envoiTrame();
  }

  /*
    ECRITURE D'UN OCTET EN MEMOIRE EEPROM
  */
  function ecrireEeprom($destinataire = '', $cible = '', $adresse = '', $octet = '') {
    $IDCAN[DEST] = $destinataire;
    $IDCAN[COMM] = 0x00;
    $IDCAN[CIBL] = $cible;
    $IDCAN[PARA] = 0x15;
    $donnees[0]  = $adresse;
    $donnees[1]  = $octet;
    $this->CAN(0x60, $IDCAN, $donnees);
    $this->checksum();
    $this->conversion();
    $this->envoiTrame();
  }

  /*
    LECTURE D'UN OCTET EN EEPROM    
  */
  function lectureEeprom($destinataire = '', $cible = '', $adresse = '') {
    $IDCAN[DEST] = $destinataire;
    $IDCAN[COMM] = 0x00;
    $IDCAN[CIBL] = $cible;
    $IDCAN[PARA] = 0x16;
    $donnees[0]  = $adresse;
    $this->CAN(0x60, $IDCAN, $donnees);
    $this->checksum();
    $this->conversion();
    $this->envoiTrame();
  }

  /*
    DEMANDE LES INFORMATIONS DE LA CARTE
  */
  function informations($destinataire = '', $cible = '') {
    $IDCAN[DEST] = $destinataire;
    $IDCAN[COMM] = 0x00;
    $IDCAN[CIBL] = $cible;
    $IDCAN[PARA] = 0x20;
    $donnees     = array();
    $this->CAN(0x60, $IDCAN, $donnees);
    $this->checksum();
    $this->conversion();
    $this->envoiTrame();
  }

  /*
    ECRIRE 8 OCTETS DANS LA ZONE UTILISATEUR
      Utilisation : Remplir $octets avec un tableau de 8 entrées (1 entrée = 1 octet)
  */
  function ecrireZoneUtilisateur($destinataire = '', $cible = '', $octets = array()) {
    $IDCAN[DEST] = $destinataire;
    $IDCAN[COMM] = 0x00;
    $IDCAN[CIBL] = $cible;
    $IDCAN[PARA] = 0x21;
    $donnees     = $octets;
    $this->CAN(0x60, $IDCAN, $donnees);
    $this->checksum();
    $this->conversion();
    $this->envoiTrame();
  }

  /*
    LIRE LE CONTENU DE LA ZONE UTILISATEUR
  */
  function lireZoneUtilisateur($destinataire = '', $cible = '') {
    $IDCAN[DEST] = $destinataire;
    $IDCAN[COMM] = 0x00;
    $IDCAN[CIBL] = $cible;
    $IDCAN[PARA] = 0x22;
    $donnees     = array();
    $this->CAN(0x60, $IDCAN, $donnees);
    $this->checksum();
    $this->conversion();
    $this->envoiTrame();
  }

  /*
    MODIFIER LE NOM D'UNE CARTE
      Utilisation : $octets = 8 entrées = (1 entrée = 1 caractère)
  */
  function modifierNom($destinataire = '', $cible = '', $octets = array()) {
    $IDCAN[DEST] = $destinataire;
    $IDCAN[COMM] = 0x00;
    $IDCAN[CIBL] = $cible;
    $IDCAN[PARA] = 0x23;
    $donnees     = $octets;
    $this->CAN(0x60, $IDCAN, $donnees);
    $this->checksum();
    $this->conversion();
    $this->envoiTrame();
  }

  /*
    LIRE LE NOM D'UNE CARTE
  */
  function lireNom($destinataire = '', $cible = '') {
  
    echo("<br><br>Lire Nom $destinataire , $cible <br><br>");
    $IDCAN[DEST] = $destinataire;
    $IDCAN[COMM] = 0x00;
    $IDCAN[CIBL] = $cible;
    $IDCAN[PARA] = 0x24;
    $donnees     = array();
    $this->CAN(0x60, $IDCAN, $donnees);
    $this->checksum();
    $this->conversion();
    $this->envoiTrame();
  }

  /*
    MODIFIER LE NUMERO DE CIBLE D'UNE CARTE
  */
  function modifierCible($destinataire = '', $cible = '', $nouvelleCible = '') {
    $IDCAN[DEST] = $destinataire;
    $IDCAN[COMM] = 0x00;
    $IDCAN[CIBL] = $cible;
    $IDCAN[PARA] = 0x25;
    $donnees[0]  = $nouvelleCible;
    $this->CAN(0x60, $IDCAN, $donnees);
    $this->checksum();
    $this->conversion();
    $this->envoiTrame();
  }

  /*
    PASSER LA CARTE EN MODE BOOTLOADER
  */
  function entrerBootloader($destinataire = '', $cible = '') {
    $IDCAN[DEST] = $destinataire;
    $IDCAN[COMM] = 0x00;
    $IDCAN[CIBL] = $cible;
    $IDCAN[PARA] = 0x26;
    $donnees[0]  = 0x55;
    $donnees[1]  = 0xa5;
    $this->CAN(0x60, $IDCAN, $donnees);
    $this->checksum();
    $this->conversion();
    $this->envoiTrame();
  }

  /*
    MODIFIER LE MODE DE FONCTIONNEMENT
      Utilisation : $octets[0] = bla bla bla (à expliquer et traiter par la suite...)
  */
  function modifierFonctionnement($destinataire = '', $cible = '', $octets = array()) {
    $IDCAN[DEST] = $destinataire;
    $IDCAN[COMM] = 0x00;
    $IDCAN[CIBL] = $cible;
    $IDCAN[PARA] = 0x27;
    $donnees[0]  = $nouvelleCible;
    $this->CAN(0x60, $IDCAN, $donnees);
    $this->checksum();
    $this->conversion();
    $this->envoiTrame();
  }

  /*
    RESETER LA CARTE
  */
  function reset($destinataire = '', $cible = '') {
    $IDCAN[DEST] = $destinataire;
    $IDCAN[COMM] = 0x00;
    $IDCAN[CIBL] = $cible;
    $IDCAN[PARA] = 0x28;
    $donnees[0]  = 0xa5;
    $donnees[1]  = 0x55;
    $this->CAN(0x60, $IDCAN, $donnees);
    $this->checksum();
    $this->conversion();
    $this->envoiTrame();
  }

}
?>

