<?php

  /*
    PASSAGE DE LA STRUCTURE EN MODE JOUR (POUR EVITER CERTAINS ALLUMAGE DE LAMPE AVEC CAPTEUR)
  */


  /* DEPENDANCES */
  include_once('/var/www/domocan/www/conf/config.php');

  /* ACTIVATION DU MODE JOUR */
  if ( $argv['1'] == 'on' ) {
    mysql_connect(MYSQL_HOST, MYSQL_LOGIN, MYSQL_PWD);
    mysql_select_db(MYSQL_DB);
    mysql_query("UPDATE `" . TABLE_ENTREE . "` SET `actif` = '0' WHERE `id` = '10'");
    mysql_close();
  }

  /* DESACTIVATION DU MODE JOUR */
  if ( $argv['1'] == 'off' ) {
    mysql_connect(MYSQL_HOST, MYSQL_LOGIN, MYSQL_PWD);
    mysql_select_db(MYSQL_DB);
    mysql_query("UPDATE `" . TABLE_ENTREE . "` SET `actif` = '1' WHERE `id` = '10'");
    mysql_close();
  }

?>
