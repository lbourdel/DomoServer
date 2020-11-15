<?php
	include_once('/var/www/domocan/www/conf/config.php');
	
	//  echo 'Hello ' . htmlspecialchars($_GET["state"]) . '!';
	// https://lbourdel.ddns.net/domocan/www/php/CmdVRnew.php?carte=10&sortie=8&consigne=50
	$titre = 'Gestion Pompe a chaleur';
	
	include '/var/www/domocan/class/class.envoiTrame.php5';
	include '/var/www/domocan/class/class.gradateur.php5';
	
	//echo "Bonjour !\n";
	
	$CmdPAC = new gradateur();
	
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
	
	$CmdPAC->allumer($carte, $sortie, hexdec($delai), $consigne );
	//print 'carte : '.$carte.'  sortie : '.$sortie.'  consigne : '.$consigne;
	
?>
