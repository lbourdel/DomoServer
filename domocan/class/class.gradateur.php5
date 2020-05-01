<?php

class gradateur extends envoiTrame {


  /*

    ALLUMER UNE SORTIE

    $cible => NUMERO DE CARTE GRADATEUR
    $sortie => NUMERO DE LA SORTIE DE CETTE CARTE
    $progression => TEMPS ENTRE L'ETAT FERME ET L'ETAT OUVERT (0 - 2550 ms)

  */
  function allumer($cible = 0xfe, $sortie = 0x01, $progression = 0, $valeur = 0x32) {
    $IDCAN['DEST'] = 0x50;
    $IDCAN['COMM'] = 0x01;
    $IDCAN['CIBL'] = $cible;
    $IDCAN['PARAM'] = $sortie;
    $donnees[0] = $valeur;
    $donnees[1] = $progression;
    $this->CAN(0x60,$IDCAN,$donnees);
    $this->checksum();
    $this->conversion();
    $this->envoiTrame();

    print 'Allumage Carte ' . $cible . ' sortie ' . $sortie . ' valeur ' . $valeur . ' progression ' . $progression;

  }


  /*
    ETEINDRE UNE SORTIE

    $cible => NUMERO DE CARTE GRADATEUR
    $sortie => NUMERO DE LA SORTIE DE CETTE CARTE
    $progression => TEMPS ENTRE L'ETAT OUVERT ET L'ETAT FERME (0 - 2550 ms)

  */
  function eteindre($cible = 0xfe, $sortie = 0x01, $progression = 0) {
    $IDCAN['DEST'] = 0x50;
    $IDCAN['COMM'] = 0x01;
    $IDCAN['CIBL'] = $cible;
    $IDCAN['PARAM'] = $sortie;
    $donnees[0] = 0x00;
    $donnees[1] = $progression;
    $this->CAN(0x60,$IDCAN,$donnees);
    $this->checksum();
    $this->conversion();
    $this->envoiTrame();
  }


  /*

    INVERSER UNE SORTIE

    $cible => NUMERO DE CARTE GRADATEUR
    $sortie => NUMERO DE LA SORTIE DE CETTE CARTE

  */
  function inverser($cible = 0xfe, $sortie = 0x01, $intensite = 0x32, $progression = 0x0) {
    $IDCAN['DEST'] = 0x50;
    $IDCAN['COMM'] = 0x04;
    $IDCAN['CIBL'] = $cible;
    $IDCAN['PARAM'] = $sortie;
    $donnees[0] = 0x00;
    $donnees[1] = $intensite;
    $donnees[2] = $progression;
    $this->CAN(0x60,$IDCAN,$donnees);
    $this->checksum();
    $this->conversion();
    $this->envoiTrame();

  }

  /*

    INVERSER UNE SORTIE VERSION NUIT

    $cible => NUMERO DE CARTE GRADATEUR
    $sortie => NUMERO DE LA SORTIE DE CETTE CARTE

  */
  function inverserNuit($cible = 0xfe, $sortie = 0x01, $progression = 0) {
    $IDCAN['DEST'] = 0x50;
    $IDCAN['COMM'] = 0x04;
    $IDCAN['CIBL'] = $cible;
    $IDCAN['PARAM'] = $sortie;
    $donnees[0] = $progression;
    $tmp = `/bin/cat /tmp/lum_meza_dodo`;
    if ( $tmp == 0 ) {
      $donnees[1] = 0x32;
    }
    if ( $tmp == 1 ) {
      $donnees[1] = 0xb;
    }
    $donnees[2] = 0x00; // Delai
    $this->CAN(0x60,$IDCAN,$donnees);
    $this->checksum();
    $this->conversion();
    $this->envoiTrame();

  }


  /*

    INVERSER UNE PROGRESSION
    $cible => NUMERO DE CARTE GRADATEUR
    $sortie => NUMERO DE LA SORTIE DE CETTE CARTE

  */
  function togalnum($cible = 0xfe, $sortie = 0x01, $progression = 0) {
    $IDCAN['DEST'] = 0x50;
    $IDCAN['COMM'] = 0x07;
    $IDCAN['CIBL'] = $cible;
    $IDCAN['PARAM'] = $sortie;
    $donnees[0]  = $progression;
    $this->CAN(0x60,$IDCAN,$donnees);
    $this->checksum();
    $this->conversion();
    $this->envoiTrame();

  }


  /*

    STOPER UNE PROGRESSION EN COURS

    $cible => NUMERO DE CARTE GRADATEUR
    $sortie => NUMERO DE LA SORTIE DE CETTE CARTE

  */
  function stopalnum($cible = 0xfe, $sortie = 0x01) {
    $IDCAN['DEST'] = 0x50;
    $IDCAN['COMM'] = 0x08;
    $IDCAN['CIBL'] = $cible;
    $IDCAN['PARAM'] = $sortie;
    $donnees = array();
    $this->CAN(0x60,$IDCAN,$donnees);
    $this->checksum();
    $this->conversion();
    $this->envoiTrame();

  }


  /*

    ETAT D'UNE SORTIE

    $cible => NUMERO DE CARTE GRADATEUR
    $sortie => NUMERO DE LA SORTIE DE CETTE CARTE

  */
  function etat($cible = 0xfe, $sortie = 0x01) {
    $IDCAN['DEST'] = 0x50;
    $IDCAN['COMM'] = 0x02;
    $IDCAN['CIBL'] = $cible;
    $IDCAN['PARAM'] = $sortie;
    $donnees = array();
    $this->CAN(0x60,$IDCAN,$donnees);
    $this->checksum();
    $this->conversion();
    $this->envoiTrame();

  }


  /*

    INCREMENTE OU DECREMENTE UNE SORTIE

    $cible => NUMERO DE CARTE GRADATEUR
    $sortie => NUMERO DE LA SORTIE DE CETTE CARTE
    $sens => 0 : incrément & 2 : décrément
    $valeur => de 0x00 à 0x32
    $progression => TEMPS ENTRE L'ETAT FERME ET L'ETAT OUVERT (0 - 2550 ms)

  */
  function varier($cible = 0xfe, $sortie = 0x01, $sens, $valeur = 0x00, $progression = 0) {
    $IDCAN['DEST'] = 0x50;
    $IDCAN['COMM'] = 0x03;
    $IDCAN['CIBL'] = $cible;
    $IDCAN['PARAM'] = $sortie;
    $u = sprintf("%b", $sens) . sprintf("%b", 0) . sprintf("%06b", $valeur);
    $donnees[0] = bindec($u);
    $donnees[1] = $progression;
    $this->CAN(0x60,$IDCAN,$donnees);
    $this->checksum();
    $this->conversion();
    $this->envoiTrame();

  }



  /*

    Restaurer les éclairages d’une mémoire (CMD 16, 0X10)
	
    $cible => NUMERO DE CARTE GRADATEUR
    $memoire => NUMERO DE LA MEMOIRE SUR CETTE CARTE (0x00 à 0x0E)
    $delai => TEMPS DE MISE EN PLACE (0 - 2550 ms)

  */
  function restaurerMemoire($cible = 0xfe, $memoire = 0x00, $delai = 0) {
    $IDCAN['DEST'] = 0x50;
    $IDCAN['COMM'] = 0x10;
    $IDCAN['CIBL'] = $cible;
    $IDCAN['PARAM'] = $memoire;
    $donnees[0] = $delai;
    $this->CAN(0x60,$IDCAN,$donnees);
    $this->checksum();
    $this->conversion();
    $this->envoiTrame();

  }
  
}
?>
