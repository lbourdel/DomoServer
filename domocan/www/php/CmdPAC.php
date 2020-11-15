<?php
	include_once('/var/www/domocan/www/conf/config.php');
	
	//  echo 'Hello ' . htmlspecialchars($_GET["state"]) . '!';
	// http://192.168.0.20/domocan/www/php/CmdPAC.php?state=0
	$titre = 'Gestion Pompe a chaleur';
	
	include '/var/www/domocan/class/class.envoiTrame.php5';
	include '/var/www/domocan/class/class.gradateur.php5';
	
	echo "Bonjour !\n";
	if(isset($_GET['state']))
	{
		$StateToSet = $_GET["state"];
	}	
	if(0) // we don't use sql to avoid write access on SD
	{
		try {
			
			$conn = new PDO("mysql:host=localhost;dbname=".MYSQL_DB, MYSQL_LOGIN, MYSQL_PWD);
			$sql = 'SELECT * FROM Status_PAC';
			
			$result_query = $conn->query($sql);
			$row = $result_query->fetch(PDO::FETCH_ASSOC); // only first row (we have only one !!)
			//  print_r($row);
			$status_PAC = $row['bool_status_PAC'];
			$counter = $row['counter'];
			//  print 'Status PAC'.$status_PAC.' Counter: '.$counter;
			}catch (PDOException $e) {
			print "Erreur !: " . $e->getMessage() . "<br/>";
			die();
		}
	}
	else
	{
		print 'TOTO';
		$status_PAC = !$StateToSet;
		$counter = 0;
		
	}
	
	print 'StateToSet : '.$StateToSet;
	print 'StatusPAC : '.$status_PAC;
	
	$CmdPAC = new gradateur();
	
	$carte=0x0C;
	$sortie=0x0C;
	$consigne=0x32;
	$delai=0;
	if(isset($_GET['carte']))
	{
		$carte = $_GET["carte"];
	}
	if(isset($_GET['sortie']))
	{
		$sortie = $_GET["sortie"];
	}
	if(isset($_GET['consigne']))
	{
		$consigne = $_GET["consigne"];
	}
	
	
	if( $StateToSet xor $status_PAC)
	{
		if ( $StateToSet==0 ){
			$consigne=0;
			print " !!!Coupure chauffage!!!";
		}
		elseif ( $StateToSet==1 )
		{
			$consigne=0x32;
			print " !!!Allumage chauffage!!!";
		}
		$CmdPAC->allumer($carte, $sortie, hexdec($delai), $consigne );
		
		if(0) // we don't use sql to avoid write access on SD
		{
			$query_string = "UPDATE Status_PAC SET bool_status_PAC=$StateToSet WHERE 1";
			$result_query = $conn->query($query_string);
			//    print $query_string;
			
			$counter =  $counter + 1;
			
			$query_string = "UPDATE Status_PAC SET counter=$counter WHERE 1";
			$result_query = $conn->query($query_string);
			//    print $query_string;
		}
	}
	else
	{
		$CmdPAC->allumer($carte, $sortie, hexdec($delai), $consigne );
		print 'carte : '.$carte.'  sortie : '.$sortie.'  consigne : '.$consigne;
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
