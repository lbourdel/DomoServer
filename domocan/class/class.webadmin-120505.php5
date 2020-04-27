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
	  for ($c = 255; $c <= 255; $c++) {
	    
	    // Starts Timestamp
	    $now = date("YmdHis") . substr(microtime(true),11,3);
	    //$Card_Type   = '0x60';
	    $Card_Number = '0x' . str_pad(dechex($c), 2, "0", STR_PAD_LEFT);
		echo("<br><br><b>=>Scan c=$c, Card Numer=$Card_Number<=</b><br>");

		//$communes->lireNom(0x60, 0x01);
		$sql = "INSERT INTO `ha_command` (id,command,timestamp,status) VALUES ('', 'ScanBus[lireNom(".$Card_Type.",".$Card_Number.")]', '".$now."' ,'INIT');";
	    mysql_query($sql);
		mysql_close();
		  
	    $j          = 1;
	    $cmd_status = "";
	    while (($j<=3) AND ($cmd_status!="RESP") AND ($cmd_status!="ACK")) {
	      $communes->lireNom(strval(hexdec($Card_Type)), strval(hexdec($Card_Number)));
		  echo("<br>=>Scan j=$j<=<b> Lire Nom (Type=$Card_Type, Number=$Card_Number) Launched</b><br>");
	
	      $i          = 1;
		  $cmd_status = "";
	      while (($i<=5) AND ($cmd_status!="RESP") AND ($cmd_status!="ACK")) {
	        // Small Pause to guaranty answer is back via server_udp and class.receptionTrame.php5
	        sleep(1);
			// Connects to DB
			mysql_connect(MYSQL_HOST, MYSQL_LOGIN, MYSQL_PWD);
			mysql_select_db(MYSQL_DB);
	        
			$sql         = "SELECT * FROM `ha_command` WHERE command='ScanBus[lireNom(".$Card_Type.",".$Card_Number.")]';";
	        $query2      = mysql_query($sql);
	        $row2        = mysql_fetch_array($query2);
		    $cmd_status  = $row2['status'];
	        $answer_time = $row2['answer_time'];
			echo("<br>=>Scan Type=$Card_Type, Number=$Card_Number,j=$j,i=$i<=<br>");
			
	        echo("<br>CMD Status i*j=$i*$j: <b>$cmd_status</b>, Time= $answer_time<br>");
		
	        $i++;
	      } // End While
	
	      // Removes left over/unanswered Scan Requests
	      $sql    = "REMOVE FROM `ha_command` WHERE command='ScanBus[lireNom(".$Card_Type.",".$Card_Number.")]' AND timestamp='".$now."' AND status='INIT';";
	      mysql_query($sql);
	      $j++;
	    } // End While
	  } // End For
	} // End While
    // Close DB connection
	mysql_close();
	
  } // End Function ScanBus

  
  

  
  
} // End Class webadmin


?>