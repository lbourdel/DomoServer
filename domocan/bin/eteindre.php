<?php

  /*
    SCRIPT POUR ETEINDRE LES LUMIERES SOUS TIMER (EXECUTION A LA FIN DU TIMER)
  */

  /* DEPENDANCES */
  include_once('/var/www/domocan/www/conf/config.php');
  include_once(PATHCLASS . '/class.envoiTrame.php5');
  include_once(PATHCLASS . '/class.gradateur.php5');

  /* CONNEXION */
  mysql_connect(MYSQL_HOST, MYSQL_LOGIN, MYSQL_PWD);
  mysql_select_db(MYSQL_DB);

  /* LIBERER LE TIMER EN SQL ET ETEINDRE */
  mysql_query("UPDATE `lumieres` SET `timer_pid` = '0' WHERE `id` = '" . $argv[3] . "'");
  $gradateur = new gradateur();
  $gradateur->eteindre($argv[1], hexdec($argv[2]));
  mysql_close();

?>
