<?php
include_once('/var/www/domocan/www/conf/config.php');

//  echo 'Hello ' . htmlspecialchars($_GET["state"]) . '!';
// http://192.168.0.20/domocan/www/php/CmdPAC.php?state=0
  $titre = 'Gestion Pompe a chaleur';

  $StateToSet = $_GET["state"];

  include '/var/www/domocan/class/class.envoiTrame.php5';
  include '/var/www/domocan/class/class.gradateur.php5';

  $CmdPAC = new gradateur();

  /* CONNEXION A LA BDD */
  mysql_connect(MYSQL_HOST, MYSQL_LOGIN, MYSQL_PWD);
  mysql_select_db(MYSQL_DB);

 /* AFFICHAGE DE L'ETAT DE LA CHAUDIERE */
  $retour = mysql_query("SELECT `valeur` FROM `" . TABLE_CHAUFFAGE_CLEF_TEMP . "` WHERE `clef` = 'PAC'");                                                                                                    
  $row = mysql_fetch_array($retour);

  $chaudiere=$row[0];
  print "Etat chaudiere= " . $chaudiere ;

  print " Etat à setter = " . $StateToSet;

  $retour = mysql_query("SELECT `valeur` FROM `" . TABLE_CHAUFFAGE_CLEF_TEMP . "` WHERE `clef` = 'counter'");
  $row = mysql_fetch_array($retour);

  $counter = $row[0];
  print " Compteur= " . $counter;


  $carte=0x0C;
  $sortie=0x0C;
  $consigne=0x32;
  $delai=0;


  if( $StateToSet xor $chaudiere)
  {
    if ( $StateToSet==0 ){
      $consigne=0;
      $chaudiere=0;
      print " !!!Coupure chauffage!!!";
    }
    elseif ( $StateToSet==1 )
    {
      $consigne=0x32;
      $chaudiere=1;
      print " !!!Allumage chauffage!!!";
    }
    $CmdPAC->allumer($carte, $sortie, hexdec($delai), $consigne );

    $query_string = "UPDATE `" . TABLE_CHAUFFAGE_CLEF_TEMP . "` SET `valeur` = $chaudiere  WHERE `clef` = 'PAC';";
    mysql_query($query_string);
//    print $query_string;

    $counter =  $counter + 1;
    $query_string = "UPDATE `" . TABLE_CHAUFFAGE_CLEF_TEMP . "` SET `valeur` = $counter  WHERE `clef` = 'counter';";
    mysql_query($query_string);
//    print $query_string;
  }



/*
  if ( $StateToSet==0 && $chaudiere==1){
    $consigne=0;
    $chaudiere=0;
    $CmdPAC->allumer($carte, $sortie, hexdec($delai), $consigne );
    print "Coupure chauffage";
    $query_string = "UPDATE `" . TABLE_CHAUFFAGE_CLEF_TEMP . "` SET `valeur` = $chaudiere  WHERE `clef` = 'PAC';";
    mysql_query($query_string);
  }
  elseif ( $StateToSet==1 && $chaudiere==0)
  {
    $consigne=0x32;
    $chaudiere=1;
    $CmdPAC->allumer($carte, $sortie, hexdec($delai), $consigne );
    print "Allumage chauffage";
    $query_string = "UPDATE `" . TABLE_CHAUFFAGE_CLEF_TEMP . "` SET `valeur` = $chaudiere  WHERE `clef` = 'PAC';";
    mysql_query($query_string);
  }
 */

 /* FERMETURE SQL */
  mysql_close();

  print ' Nouvel etat chaudiere=' . $chaudiere;

?>
