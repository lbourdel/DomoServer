<?php


require "/usr/share/php/OWNet/ownet.php";
$ow=new OWNet("tcp://127.0.0.1:4304");
$content = $ow->get("/",OWNET_MSG_DIR,true);
$i=0;
while (isset($content[$i]["data"])) {
  $sensor = $content[$i]["data"];
  $sensor = substr($sensor,1);
  if (substr($sensor,0,3)=="28.") { echo($sensor."<br>"); } // End IF
  $i++;
} // End While
//echo($content[0]["data"]."<br>"); 


//var_dump($content);

/*
var_dump($ow->get("/10.67C6697351FF/temperature",OWNET_MSG_READ,true));
var_dump($ow->get("/10.67C6697351FF",OWNET_MSG_PRESENCE,true));
var_dump($ow->get("/WRONG VALUE",OWNET_MSG_PRESENCE,true));

var_dump($ow->get("/",OWNET_MSG_DIR,false));
var_dump($ow->get("/10.67C6697351FF/temperature",OWNET_MSG_READ,false));
var_dump($ow->get("/10.67C6697351FF",OWNET_MSG_PRESENCE,false));
var_dump($ow->get("/WRONG VALUE",OWNET_MSG_PRESENCE,false));
*/


?>