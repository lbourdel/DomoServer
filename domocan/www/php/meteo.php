<?php

  $titre = 'Meteo';

  /* CONNEXION BDD */
  mysql_connect(MYSQL_HOST, MYSQL_LOGIN, MYSQL_PWD);
  mysql_select_db(MYSQL_DB);

  /* SELECTION ET AFFICHAGE DE L'ANNIVERSAIRE ET DE LA FETE DU JOUR */
  $retour = mysql_query("SELECT `Fete` FROM `" . TABLE_METEO_FETE . "` WHERE `JourMois` = '" . date('d/m') . "'");
  $row = mysql_fetch_array($retour);
  $_XTemplate->assign('FETE', utf8_encode($row['Fete']));
  $retour = mysql_query("SELECT prenom,DATE_FORMAT(date, '%d/%m'), mod( DATE_FORMAT( `date` , '%m%d' ) - DATE_FORMAT( CURDATE( ) , '%m%d' ) , 1231 ) + IF( mod( DATE_FORMAT( `date` , '%m%d' ) - DATE_FORMAT( CURDATE( ) , '%m%d' ) , 1231 ) >0, -1, 2000 ) AS poids FROM `meteo_anniversaire` WHERE YEAR( `date` ) <> '0000' ORDER BY poids ASC LIMIT 1");
  $row = mysql_fetch_array($retour);
  $_XTemplate->assign('PRENOM', utf8_encode($row['prenom']));
  $_XTemplate->assign('DATE', $row[1] . '/' . date(Y));

  /* FERMETURE BDD */
  mysql_close();

?>
