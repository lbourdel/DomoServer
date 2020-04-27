<?php

function html_post($in) {
$out = "";
if (isset($_POST[$in])) { $out = $_POST[$in]; }
return $out;
} // End Function html_post

function html_get($in) {
$out = "";
if (isset($_GET[$in])) { $out= $_GET[$in]; }
return $out;
} // End Function html_get

function html_postget($in) {
$out = "";
if (isset($_GET[$in])) {
  $out= $_GET[$in]; 
} else { if (isset($_POST[$in])) { $out= $_POST[$in]; }} // End If
return $out;
} // End Function html_postget

function log_this($Lgn_Msg) {
  if (ADMIN_DEBUG) { disp_error($Lgn_Msg); }

  // Determines which function called this Log
  $bt = debug_backtrace();
  // get class, function called by caller of caller of caller
  $class    = ""; if (isset($bt[2]['class'])) {    $class    = $bt[2]['class']; }
  $function = ""; if (isset($bt[2]['function'])) { $function = $bt[2]['function']; }
  // get file, line where call to caller of caller was made
  $file = $bt[1]['file'];
  $line = $bt[1]['line'];
  // build & return the message
  $Lgn_Msg = "$class::$function: [$Lgn_Msg] in $file at $line";

  // Opens Log File to Output the Error Message
  $log_file = fopen("../log/DomoCAN-WebAdmin.log", "a");
  fwrite($log_file,date("Y-m-d H:i:s")." - ". $Lgn_Msg." \n");
  fclose ($log_file);
} // End Function Log

function disp_error($msg) {
  echo("<script type=\"text/javascript\">" . CRLF . "onload=alert(\"ERREUR: ".$msg." !\");" . CRLF . "</script>".CRLF);
} // End Function disp_error

// Quality is a number between 0 (best compression) and 100 (best quality) 
function png2jpg($originalFile, $outputFile, $quality) { 
  $image = imagecreatefrompng($originalFile); 
  imagejpeg($image, $outputFile, $quality); 
  imagedestroy($image); 
} // End Function png2jpg

?>