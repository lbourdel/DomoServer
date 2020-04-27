<?php

// PHP Error Reporting
//error_reporting(E_ALL);
//ini_set('display_errors', '1');

set_time_limit(0);
ini_set('max_execution_time', 0);

include_once('/var/www/domocan/www/conf/config.php');

include_once(PATHCLASS . 'class.envoiTrame.php5');
include_once(PATHCLASS . 'class.gradateur.php5');
include_once(PATHCLASS . 'class.communes.php5');

class webadmin extends communes {

  function scanBus() {
	// Connects to DB
    mysql_connect(MYSQL_HOST, MYSQL_LOGIN, MYSQL_PWD);
    mysql_select_db(MYSQL_DB);
    
	// Empty ha_command Table
	$sql   = "TRUNCATETABLE`ha_command` ;";
	$query = mysql_query($sql);
	
	// Initiate Object
    $communes = new communes();

	// Check Available Subsystems in DB and starts Scan
	$sql = "SELECT * FROM `ha_subsystem_types`;";
	$query       = mysql_query($sql);
	while ($row = mysql_fetch_array($query)) {
	  $Card_Type = $row['Type'];
	  for ($c = 1; $c <= 1; $c++) {
	    
	    // Starts Timestamp
	    $now = date("YmdHis") . substr(microtime(true),11,3);
	    //$Card_Type   = '0x60';
	    $Card_Number = '0x' . str_pad(dechex($c), 2, "0", STR_PAD_LEFT);
		echo("<br><br><b>=>Scan c=$c, Card Number=$Card_Number<=</b><br>");

		  
	    $j          = 1;
	    $cmd_status = "";
	    while (($j<=1) AND ($cmd_status!="RESP")) {
	      $communes->lireNom(strval(hexdec($Card_Type)), strval(hexdec($Card_Number)));
		  echo("<br>=>Scan j=$j<=<b> Lire Nom (Type=$Card_Type, Number=$Card_Number) Launched</b><br>");

		  
	      $i          = 1;
		  $cmd_status = "";

		  //$handle = fopen("http://localhost/domocan/reception", "r");
		  
		  //$handle = stream_socket_client("tcp://localhost:80/domocan/reception/", $errno, $errstr, 30, STREAM_CLIENT_CONNECT | STREAM_CLIENT_PERSISTENT);
		  $handle = stream_socket_client("tcp://localhost:80/domocan/reception/", $errno, $errstr, 30, STREAM_CLIENT_CONNECT | STREAM_CLIENT_PERSISTENT);
		  if (!$handle) {
			echo "ERROR: $errstr ($errno)<br />\n";
		  } else {
			fwrite($handle, "GET / HTTP/1.0\r\nHost: localhost\r\nAccept: */*\r\n\r\n");
		    while ((!feof($handle)) AND ($i<=500) AND ($cmd_status!="RESP")) {
			  $content = fgets($handle, 4096);
			  echo("<br>Frame Type = " . substr($content,0,14));
			  $ok =  "no"; 
			  if (substr($content,0,14)=="Admin-CardName") {
				$CT = substr($content,15,4);
				if ($CT==$Card_Type) {
				  $cmd_status="RESP";
				  $ok="YES!";
				} // End IF
			  } // End IF
			  echo(", LongPoll= $content -$ok-$CT<br>");
			  $i++;
		    }
		    fclose($handle);
		  }

		  
	      $j++;
	    } // End While
	  } // End For
	} // End While
    // Close DB connection
	mysql_close();
	
  } // End Function ScanBus

  
  

  
  
} // End Class webadmin


?>