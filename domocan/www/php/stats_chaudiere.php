<?php

  include '../class/class.envoiTrame.php5';
  include '../class/class.gradateur.php5';

  $titre = "Statistiques - Chaudière";

  /* RECUPERATION DES VALEURS DE TEMPERATURE ET MISES A JOUR DES GRAPHIQUES */
  mysql_connect(MYSQL_HOST, MYSQL_LOGIN, MYSQL_PWD);
  mysql_select_db(MYSQL_DB);
  $retour = mysql_query("SELECT UNIX_TIMESTAMP(`date`),`valeur` FROM `logs` WHERE `id_gradateur` = '" . CARTE_CHAUFFAGE . "' AND '" . SORTIE_CHAUFFAGE . "' = '0'");
  $a = 0;
  $b = 0;
  $c = 0;
  $d = 0;
  $tmp = 0;
  while ( $row = mysql_fetch_array($retour) ) {
    if ( $tmp == 0 ) {
      $premiere_date = $row[0];
      $tmp = 1;
    }
    if ($a == '') {
      $a = $row[0];
    }
    else if ($b == '') {
      $b = $row[0];
    }
    if ( $a != '' && $b != '' ) {
      $c = $b - $a;
      //echo " : " . $c . " secondes<br>";

      //echo $b . " - " . $a . " = " . $c . "\n";
      $a = '';
      $b = '';
      $d = $d + $c;
    }
  }

  $retour = mysql_query("SELECT UNIX_TIMESTAMP(`date`),`valeur` FROM `logs` WHERE `id_gradateur` = '" . CARTE_CHAUFFAGE . "' AND '" . SORTIE_CHAUFFAGE . "' = '0' ORDER BY `date` DESC LIMIT 0,2");
  $rowa = mysql_fetch_array($retour);
  $rowb = mysql_fetch_array($retour);

  if ( $rowa[1] == '32' ) {
    $dernier_marche = date("d/m/Y H:i:s", $rowa[0]);
    $dernier_arret = "En cours";
  }
  else if ( $rowa[1] == '00' ) {
    $dernier_marche = date("d/m/Y H:i:s",$rowb[0]);
    $dernier_arret = date("d/m/Y H:i:s",$rowa[0]);
  }

  $e = $d / 60;
  $f = $e / 60;
  mysql_close();

  $_XTemplate->assign('DATE', date("d/m/Y H:i:s", $premiere_date));
  $_XTemplate->assign('DUREE', round($f,1));
  $_XTemplate->assign('DERNIER_MARCHE', $dernier_marche);
  $_XTemplate->assign('DERNIER_ARRET', $dernier_arret);

  $totalm3 = round($f,1) * 3.81 * 10.73;
  $euros = $totalm3 * 0.041;
  $_XTemplate->assign('COUT', round($euros,1));

?>
