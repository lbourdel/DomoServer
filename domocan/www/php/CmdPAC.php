<?php
include_once('/var/www/domocan/www/conf/config.php');

//  echo 'Hello ' . htmlspecialchars($_GET["state"]) . '!';
// http://192.168.0.20/domocan/www/php/CmdPAC.php?state=0
  $titre = 'Gestion Pompe a chaleur';

  include '/var/www/domocan/class/class.envoiTrame.php5';
  include '/var/www/domocan/class/class.gradateur.php5';

  echo "Bonjour !\n";


try {
  $conn = new PDO("mysql:host=localhost;dbname=DomoBourdel", 'user', 'user');
  $sql = 'SELECT * FROM Status_PAC';

  $result_query = $conn->query($sql);
  $row = $result_query->fetch(PDO::FETCH_ASSOC); // only first row (we have only one !!)
  print_r($row);
  $status_PAC = $row['bool_status_PAC'];
  $counter = $row['counter'];
  print 'Status PAC'.$status_PAC.' Counter: '.$counter;
 //        print_r($row0['0']['bool_status_PAC']);
//    foreach($conn->query($sql) as $row) {
//        print_r($row);
//        print_r($row['bool_status_PAC']);
//    }
}catch (PDOException $e) {
    print "Erreur !: " . $e->getMessage() . "<br/>";
    die();
}

  $StateToSet = $_GET["state"];
  print 'StateToSet : '.$StateToSet;

  $CmdPAC = new gradateur();

  $carte=0x0C;
  $sortie=0x0C;
  $consigne=0x32;
  $delai=0;

  if( $StateToSet xor $status_PAC)
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

    $query_string = "UPDATE Status_PAC SET bool_status_PAC=$StateToSet WHERE 1";
    $result_query = $conn->query($query_string);
//    print $query_string;

    $counter =  $counter + 1;

    $query_string = "UPDATE Status_PAC SET counter=$counter WHERE 1";
    $result_query = $conn->query($query_string);
//    print $query_string;
  }

// Close
    $sql = null;
    $conn = null;


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
//  mysql_close();

  print ' Nouvel etat chaudiere=' . $StateToSet;

?>
