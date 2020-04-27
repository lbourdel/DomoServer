<?php
include_once('/var/www/domocan/www/conf/config.php');

// echo 'Hello ' . htmlspecialchars($_GET["state"]) . '!';

  $titre = 'Gestion Pompe a chaleur';

  $carte = $_GET["carte"];
  $entree = $_GET["entree"];
  $data0 = $_GET["data0"];

include_once('/var/www/domocan/class/class.envoiTrame.php5');
include_once('/var/www/domocan/class/class.gradateur.php5');
include_once('/var/www/domocan/class/class.communes.php5');
include_once('/var/www/domocan/class/class.admin.php5');
include_once('/var/www/domocan/class/class.in16.php5');
include_once('/var/www/domocan/class/class.debug.php5');

  $in16 = new in16();

  $carte = hexdec(  $_GET["carte"] );
  $entree = hexdec(  $_GET["entree"] );
  $data0 = hexdec(  $_GET["data0"] );

  print " carte = 0x" . dechex($carte);
  print " entree = 0x" . dechex($entree);
  print " data0 = 0x" . dechex($data0);

  $in16->Cmd_StatF($carte, $entree, ~$data0, $data0, 0x0 );


// VR Salon
// [70,FF,05] [60,18,06,0F] [26] Fermeture http://192.168.0.20/domocan/www/php/CmdVR.php?carte=0x06&entree=0x0F&data0=0x26
// [70,FF,05] [60,18,06,0E] [26] Ouverture http://192.168.0.20/domocan/www/php/CmdVR.php?carte=0x06&entree=0x0E&data0=0x26
// Dest  : Carte d'entrée                                                                                                    
// Cible : 6
// Cmd   : Cmd_StatF
// Rôle  : Statut d'une fonction                                                                                                    

// VR haut cuisine
// [70,FF,05] [60,18,06,09] [52] Ouverture http://192.168.0.20/domocan/www/php/CmdVR.php?carte=0x06&entree=0x09&data0=0x52 APPUI LONG data0=0x52
// [70,FF,05] [60,18,06,08] [52] Fermeture http://192.168.0.20/domocan/www/php/CmdVR.php?carte=0x06&entree=0x08&data0=0x52

// VR Chambre 3
// [70,FF,05] [60,18,02,02] [26] : Ouverture http://192.168.0.20/domocan/www/php/CmdVR.php?carte=0x02&entree=0x02&data0=0x26 APPUI LONG data0=0x26
// [70,FF,05] [60,18,02,03] [26] : Fermeture http://192.168.0.20/domocan/www/php/CmdVR.php?carte=0x02&entree=0x03&data0=0x26

// VR Chambre 4
// [70,FF,05] [60,18,02,07] [26] : Ouverture http://192.168.0.20/domocan/www/php/CmdVR.php?carte=0x02&entree=0x07&data0=0x26
// [70,FF,05] [60,18,02,06] [26] : Fermeture http://192.168.0.20/domocan/www/php/CmdVR.php?carte=0x02&entree=0x06&data0=0x26

// VR Sdb 2 + Garage
// [70,FF,05] [60,18,02,09] [26] : Ouverture http://192.168.0.20/domocan/www/php/CmdVR.php?carte=0x02&entree=0x09&data0=0x26
// [70,FF,05] [60,18,02,09] [52] : Fermeture http://192.168.0.20/domocan/www/php/CmdVR.php?carte=0x02&entree=0x09&data0=0x52

// VR Sdb 1
// [70,FF,05] [60,18,0A,0B] [26] : Ouverture http://192.168.0.20/domocan/www/php/CmdVR.php?carte=0x0A&entree=0x0B&data0=0x26
// [70,FF,05] [60,18,0A,00] [26] : Fermeture http://192.168.0.20/domocan/www/php/CmdVR.php?carte=0x0A&entree=0x00&data0=0x26

// VR Fixe Chambre Nord
// [70,FF,05] [60,18,04,06] [26] : Ouverture http://192.168.0.20/domocan/www/php/CmdVR.php?carte=0x04&entree=0x06&data0=0x26
// [70,FF,05] [60,18,04,07] [26] : Fermeture http://192.168.0.20/domocan/www/php/CmdVR.php?carte=0x04&entree=0x07&data0=0x26

// VR Chambre Nord
// [70,FF,05] [60,18,04,01] [26] : Ouverture http://192.168.0.20/domocan/www/php/CmdVR.php?carte=0x04&entree=0x01&data0=0x26
// [70,FF,05] [60,18,04,00] [26] : Fermeture http://192.168.0.20/domocan/www/php/CmdVR.php?carte=0x04&entree=0x00&data0=0x26

// VR Fixe Chambre Terrasse
// [70,FF,05] [60,18,0A,0B] [26] : Ouverture http://192.168.0.20/domocan/www/php/CmdVR.php?carte=0x0A&entree=0x0B&data0=0x26
// [70,FF,05] [60,18,0A,03] [26] : Fermeture http://192.168.0.20/domocan/www/php/CmdVR.php?carte=0x0A&entree=0x03&data0=0x26

// VR Chambre Terrasse
// [70,FF,05] [60,18,0A,06] [26] : Ouverture http://192.168.0.20/domocan/www/php/CmdVR.php?carte=0x0A&entree=0x06&data0=0x26
// [70,FF,05] [60,18,0A,05] [26] : Fermeture http://192.168.0.20/domocan/www/php/CmdVR.php?carte=0x0A&entree=0x05&data0=0x26



?>