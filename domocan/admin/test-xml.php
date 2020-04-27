<?php
// Use URL: http://www.earthtools.org/sun/50.8503/4.4196/01/03/99/1
// http://www.earthtools.org/sun/50.8503/4.4196/Day/Month/99/DayLightSavingYorN


// Declarations
date_default_timezone_set('Europe/Brussels');
error_reporting('NONE');
//error_reporting(E_ALL);
//ini_set('display_errors', '1');


// Includes
include_once './Includes/xml.php';

// Variables
$tomorrow = mktime(0, 0, 0, date("m"), date("d")+1, date("y"));
//$tomorrow = mktime(0, 0, 0, 07, 14, 2012);

$DLS      = date("I",$tomorrow); // DayLight Savings
$Month    = date("m",$tomorrow);
$Day      = date("d",$tomorrow);
$URI      = "http://www.earthtools.org/sun/50.8503/4.4196/".$Day."/".$Month."/99/".$DLS; // 
//$URI      = "test.xml";

//$file     = fopen($URI,'rb');
//$xml      = fread($file,0); 
if ($xml = file_get_contents($URI)) {

  $array    = XML_unserialize($xml);

  // Output
  $sun_rise      = $array['sun']['morning']['sunrise'];
  $rise_twilight = $array['sun']['morning']['twilight']['civil']; // can be civil, nautical or astronomical
  $sun_set       = $array['sun']['evening']['sunset'];
  $set_twilight  = $array['sun']['evening']['twilight']['civil']; // can be civil, nautical or astronomical

  echo("Daylight Saving? $DLS<br>$URI<br>sun rise = $sun_rise/$rise_twilight<br>sun set  = $sun_set/$set_twilight");
} //End IF
echo("...");

//fclose($file);
?>