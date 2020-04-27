<?php

class erreur {

  function __construct() {
    $this->debug = new debug();
  }

  /*
    RECEPTION D'UNE ERREUR
  */
  function reception($parametre) {

    switch ($parametre) {

      case '80' :
        $this->debug->envoyer(1, "ERREUR", "Nombre de data incorrect");
        break;

      case '81' :
        $this->debug->envoyer(1, "ERREUR", "Valeur de data incorrect");
        break;

      case '82' :
        $this->debug->envoyer(1, "ERREUR", "Erreur d'écriture en eeprom");
        break;

      case '83' :
        $this->debug->envoyer(1, "ERREUR", "Erreur du paramètre de l'ID");
        break;

      case '84' :
        $this->debug->envoyer(1, "ERREUR", "Erreur d'écriture en mémoire flash");
        break;

      case '85' :
        $this->debug->envoyer(1, "ERREUR", "Valeur du pointeur flash : non multiple de 8");
        break;

      case '86' :
        $this->debug->envoyer(1, "ERREUR", "Pointeur flash en zone protégé");
        break;

      case '87' :
        $this->debug->envoyer(1, "ERREUR", "Pointeur flash hors limite");
        break;

      case '88' :
        $this->debug->envoyer(1, "ERREUR", "Pointeur flash non valide");
        break;

      case '89' :
        $this->debug->envoyer(1, "ERREUR", "Tentative d'accès bootloader en zone eeprom protégée");
        break;

      case '8a' :
        $this->debug->envoyer(1, "ERREUR", "Erreur d'écriture en eeprom externe");
        break;

      case '8c' :
        $this->debug->envoyer(1, "ERREUR", "Commande interdite en mode bootloader");
        break;

      case '8d' :
        $this->debug->envoyer(1, "ERREUR", "Commande commune interdite en mode bootloader");
        break;

      case '8e' :
        $this->debug->envoyer(1, "ERREUR", "Commande commune interdite en mode normal");
        break;

      case '8f' :
        $this->debug->envoyer(1, "ERREUR", "Commande invalide pour la carte");
        break;


    }

  }

}

?>
