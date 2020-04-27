<?php

  /* POUR ENVOI DE TRAME A LA CARTE CAN/ETH */
  define('ADRESSE_INTERFACE', 'localhost');
  define('PORT_INTERFACE', '1470');

  /*
    DEBUG POUR TRAME CAN
      0 : Aucun
      1 : Bas (information)
      2 : Haut (affichage des trames)
  */
  define('DEBUG', 0 );

  /* INTERFACE WEB */
  define('URI', '/domocan/www/');
  define('DEBUG_AJAX', FALSE);
  define('PATH', '/var/www/domocan/www/');
  define('PATHCLASS', '/var/www/domocan/class/');
  define('PATHVAR', '/var/www/domocan/var/');
  define('PATHBIN', '/var/www/domocan/bin/');
  define('RRDPATH', '/var/www/domocan/rrdtool/');
  define('DEFAUT_LOCALISATION', 'RDC');
  define('URIPUSH', 'http://localhost/domocan/envoi');
  define('URIRECV', 'http://localhost/domocan/reception');

  /* SQL */
  define('MYSQL_HOST', 'localhost');
  define('MYSQL_LOGIN', 'root');
  define('MYSQL_PWD', 'DomoCAN'); 
  define('MYSQL_DB', 'domotique');
  define('TABLE_ENTREE', 'entree');
  define('TABLE_LUMIERES', 'lumieres');
  define('TABLE_LUMIERES_CLEF', 'lumieres_clef');
  define('TABLE_CHAUFFAGE_SONDE', 'chauffage_sonde');
  define('TABLE_CHAUFFAGE_CLEF', 'chauffage_clef');
  define('TABLE_LOCALISATION', 'localisation');
  define('TABLE_METEO_FETE', 'meteo_fete');
  define('TABLE_HEATING_TIMSESLOTS', 'ha_thermostat_timeslots');
  define('TABLE_MEASURE', 'ha_measures');
  

  /* Admin Interface */
  define('ADMIN_DEBUG', '1'); // 0 = NO Debug, 1 = Outputs debug and error messages on Screen
  define('CRLF', chr(10).chr(13));
  define('PATHWEBADMIN', '/var/www/domocan/admin/');
  define('ONEWIRE_OWSERVER_PORT', '4304');
  define('ADMIN_INTERFACE_NAME', 'DomoCAN EM Admin');
  define('ADMIN_LIGHT_PAGE_NAME', 'Position des points lumineux');
  define('ADMIN_LIGHT_SIDE_TITLE', 'Points Lumineux :');
  define('ADMIN_TEMP_PAGE_NAME', 'Position des Sondes de Temp&eacute;rature');
  define('ADMIN_TEMP_SIDE_TITLE', 'Sondes :');

  /* MODULE CHAUFFAGE AU GAZ (POUR L'ELECTROVANNE) */
  define('CARTE_CHAUFFAGE', 0x03);
  define('SORTIE_CHAUFFAGE', 0x01);
  define('CARTE_BOILER', 0x03);
  define('SORTIE_BOILER', 0x03);
  define('CARTE_SONDE_BOILER', 03);  // Format: xx ex.: 03 carte 03
  define('ENTREE_SONDE_BOILER', 01); // Format: xx ex.: 01 in 01
  
  /* MODULE RECYCLAGE D'AIR - SPECIFIQUE */
  define('CARTE_RECYCLAGE', 0x03);
  define('SORTIE_RECYCLAGE', 0x0D);

  /* 1WIRE */
  define('SONDE_EXTERIEURE', '28.3845BB030000');
  define('PATHOWFS', '/mnt/1wire');
  

?>
