<?PHP
// Main Function Lamps
function Lamps() {

  // Variables Passed Globally
  global $Access_Level;

  // Variables passed within the <Form> or URL
  $selected_level = html_postget("selected_level");
  if ($selected_level=="") { 
    $sql = mysql_real_escape_string("SELECT * FROM `localisation` LIMIT 1;");
    $query = mysql_query($sql);
    $row = mysql_fetch_array($query);
    $selected_level = $row['lieu'];
  } // End IF

  // Action Requested via Form?  
  $action = html_postget("action");
  //if (ADMIN_DEBUG) { echo("Action=$action, Access Level=$Access_Level<br>"); }

  // Delete Lamp
  if ($action=="DeleteLamp") {
    $LampD = html_get("LampD");
	$sql = mysql_real_escape_string("DELETE FROM `lumieres` " .
              " WHERE `id` = \"" . $LampD . "\";");
	$sql = str_replace(chr(92).chr(34),"'",$sql);
	if (!$query=mysql_query($sql)) { log_this("Erreur DB[$sql]"); }
  } // End IF
  
  // Add and Modify Lamps
  if (($action=="update") OR ($action=="AddLamp")) {
    // Graps form input
    $IdArray    = html_get("moveMe_id");
    $DescArray  = html_get("moveMe_desc");
    $XcoorArray = html_get("moveMe_xcoor");
    $YcoorArray = html_get("moveMe_ycoor");

    // Update DB
    $i = 1;
    while (isset($IdArray[$i])) { 
      $sql = mysql_real_escape_string("UPDATE `lumieres` SET `img_x` = \"" . 
             $YcoorArray[$i] . "\", " . "`img_y` = \"" .
             $XcoorArray[$i] . "\", `description` = \"" .
             $DescArray[$i]  . "\" " .
             "WHERE `id` = \"" . $IdArray[$i] . "\";");
	  $sql = str_replace(chr(92).chr(34),"'",$sql);
      if (!$query=mysql_query($sql)) { log_this("Erreur DB[$sql]"); }
      $i++;
    } // End While

    // Create a new Lamp on this level?
    if ($action=="AddLamp") {
	  $Lamp_Name = html_get("Lamp_Name");
	  $Intensity = dechex(html_get("Intensity"));
	  $Output    = html_get("Output");
	  
	  // Find Card
	  $sql = mysql_real_escape_string("SELECT Element.element_reference AS Sortie, Sub.Reference AS Carte " .
	         "FROM `ha_subsystem` AS Sub, `ha_element` AS Element WHERE Element.id=" . $Output .
			 " AND Element.card_id=Sub.id;");
	  $query = mysql_query($sql);
      $row = mysql_fetch_array($query);
      $Sortie = $row['Sortie'];
	  $Carte  = substr($row['Carte'],2,2);
	  
      $sql = mysql_real_escape_string("INSERT INTO `lumieres` (`id`, `carte`, `sortie`, `valeur_souhaitee`, " .
			 "`valeur`, `timer`, `timer_pid`, `localisation`, `img_x`, `img_y`, `description`) VALUES " .
			 "(NULL, \"" . $Carte . "\", \"" . $Sortie . "\", \"" . $Intensity . "\", \"00\", \"0\", \"0\", " .
			 "\"" . $selected_level . "\", \"1\", \"1\", \"" . $Lamp_Name . "\");");
	  $sql = str_replace(chr(92).chr(34),"'",$sql);
      if (!$query=mysql_query($sql)) { log_this("Erreur DB[$sql]"); }
    } // End IF
  } // End IF

  // Add Level
  if ($action=="AddLevel") {
    // Move File
	$Level_Name = html_postget("Level_Name");
	$File_Name = $_FILES['NewPlan']['name'];
	if (($Level_Name!="") AND ($File_Name)) {
      $Dest_Name = "../www/images/plans/plan_" . $Level_Name . ".png";
	  if(copy($_FILES['NewPlan']['tmp_name'], $Dest_Name)) {
	    // Create New DB Entry
        $sql = mysql_real_escape_string("INSERT INTO `domotique`.`localisation` (`id`, `lieu`) " .
		         "VALUES (NULL, \"" . $Level_Name . "\");");
		$sql = str_replace(chr(92).chr(34),"'",$sql);
		if (!$query=mysql_query($sql)) { log_this("Erreur DB[$sql]"); }
      } else {
	    log_this("Probleme de copie de Fichier");
      }	  // End IF
	} // End IF
  } // End IF
	
    // Modify Level
    if ($action=="ModifyLevel") {
      $Level = html_get("Level"); // original Name
	  $LName = html_get("LName"); // New Name
	  if (rename("../www/images/plans/plan_" . $Level . ".png" , "../www/images/plans/plan_" . $LName . ".png")) {
	    $sql = mysql_real_escape_string("UPDATE `localisation` SET `lieu` = \"" . $LName .
               "\" WHERE `lieu` = \"" . $Level . "\" LIMIT 1;");
		$sql = str_replace(chr(92).chr(34),"'",$sql);
		if (!$query=mysql_query($sql)) { log_this("Erreur DB[$sql]"); }
	    
		$sql = mysql_real_escape_string("UPDATE `lumieres` SET `localisation` = \"" . $LName .
               "\" WHERE `localisation` = \"" . $Level . "\";");
		$sql = str_replace(chr(92).chr(34),"'",$sql);
		if (!$query=mysql_query($sql)) { log_this("Erreur DB[$sql]"); }
		
		$selected_level = $LName;
		
	  } else {
	    log_this("Probleme de copie de Fichier");
      }	  // End IF
    } // End IF

    // Modify Level Image
    if ($action=="ChangeImg") {
	  $Lvl_Name = html_post("Lvl_Name");
	  $Dest_Name = "../www/images/plans/plan_" . $Lvl_Name . ".png";
	  if(!copy($_FILES['ImgFile']['tmp_name'], $Dest_Name)) {
	    log_this("Probleme de copie de Fichier");
      }	  // End IF
    } // End IF
  
  
    // Delete Level
    if ($action=="DeleteLevel") {
      $Level = html_get("Level"); // original Name
	  if (@unlink("../www/images/plans/plan_" . $Level . ".png")) {
	    $sql = mysql_real_escape_string("DELETE FROM `localisation` " .
               " WHERE `lieu` = \"" . $Level . "\";");
		$sql = str_replace(chr(92).chr(34),"'",$sql);
		if (!$query=mysql_query($sql)) { log_this("Erreur DB[$sql]"); }
	    
		// Remove Lights
		$sql = mysql_real_escape_string("DELETE FROM `lumieres` " .
               " WHERE `localisation` = \"" . $Level . "\";");
		$sql = str_replace(chr(92).chr(34),"'",$sql);
		if (!$query=mysql_query($sql)) { log_this("Erreur DB[$sql]"); }

		// Remove Temperature Probe
		$sql = mysql_real_escape_string("DELETE FROM `chauffage_sonde` " .
               " WHERE `localisation` = \"" . $Level . "\";");
		$sql = str_replace(chr(92).chr(34),"'",$sql);
		if (!$query=mysql_query($sql)) { log_this("Erreur DB[$sql]"); }

	  } else {
	    log_this("Impossible d'effacer le Fichier");
      }	  // End IF
    } // End IF
    
  // Start Build Page ...

  // Existing levels
  $sql = mysql_real_escape_string("SELECT * FROM `localisation` WHERE 1;");
  $query = mysql_query($sql);
  $i=0;
  while ( $row = mysql_fetch_array($query) ) {
    $level[$i] = $row['lieu'];
    $i++;
  } // End While

  echo("<h2 class='title'>" . ADMIN_LIGHT_PAGE_NAME . "</h2>");
  echo("<form name='ChangeName' id='ChangeName' action='./index.php?page=Lamps' method='get'>" .
       "<div class='post_info'>&nbsp;Niveau: " . 
       "<input type='text' name='LName' id='LName' value='" . $selected_level . "'/>&nbsp;&nbsp;" .
       "<a href='javascript:void();' onClick='ActiveLevelChange(\"LName\")'><img src='./images/edit.png'/></a>&nbsp;&nbsp;" .
       "<a href='javascript:void();' onClick='ActiveLevelDelete()'><img src='./images/drop.png'/></a>&nbsp;&nbsp;" .
       "</div><input type='hidden' name='action' id ='action' value=''/>" .
	   "<input type='hidden' name='page' id ='page' value='Lamps'/>" .
	   "<input type='hidden' name='Level' id ='Level' value='" . $selected_level . "'/>" .
	   "</form>" . CRLF);
  $ImgURL = "../www/images/plans/plan_" . $selected_level . ".png";
  $ImgURL = $ImgURL . "?" . rand(1,30000);
  $ImgURL = str_replace(" ",chr(37)."20",$ImgURL);
  echo("	<div class='postcontent' name='plan' " .
        "style='background-image: url(" . $ImgURL . "); position:relative; background-position: 50px 0px;" .
        " background-repeat: no-repeat; height: 355px;" .
        " width: 550px; margin-left: 50px;'>" . CRLF);
?>

<style>
img
{
position:relative;
}
</style>

<script type="text/javascript">
//document.onmousedown=coordinates;
document.onmouseup=mouseup;

function coordinates(object,e)
{
//movMeId=document.getElementById("moveMe");
//alert(object);
movMeId=document.getElementById(object);
if (e == null) { e = window.event;}
var sender = (typeof( window.event ) != "undefined" ) ? e.srcElement : e.target;
//alert(sender.id.substring(0,6));

if (sender.id.substring(0,6)=="moveMe")
  {
  //alert('-'+sender.id.substring(6,10)+'-');
  var ObjName = sender.id.substring(0,6);
  var ObjNber = sender.id.substring(6,10);

  document.pos.elements[ObjName + '_desc[' + ObjNber + ']'].style.backgroundColor='lime';

  mouseover=true;
  pleft=parseInt(movMeId.style.left);
  ptop=parseInt(movMeId.style.top);
  xcoor=e.clientX;
  ycoor=e.clientY;
  document.onmousemove=moveImage;
  return false;
  }
else { return false; }
}

function moveImage(e)
{
if (e == null) { e = window.event;}
var sender = (typeof( window.event ) != "undefined" ) ? e.srcElement : e.target;
//alert(sender.id.substring(6,7));
posx = (pleft+e.clientX-xcoor)+(39*(sender.id.substring(6,7)-1));
movMeId.style.left=pleft+e.clientX-xcoor+"px";
movMeId.style.top=ptop+e.clientY-ycoor+"px";
var sender = (typeof( window.event ) != "undefined" ) ? e.srcElement : e.target;

var ObjName = sender.id.substring(0,6);
var ObjNber = sender.id.substring(6,10);
document.pos.elements[ObjName + '_xcoor[' + ObjNber + ']'].value=posx+"px";
document.pos.elements[ObjName + '_ycoor[' + ObjNber + ']'].value=movMeId.style.top;
return false;
}

function mouseup(e) {
if (e == null) { e = window.event;}
document.onmousemove=null;
var sender = (typeof( window.event ) != "undefined" ) ? e.srcElement : e.target;

var ObjName = sender.id.substring(0,6);
var ObjNber = sender.id.substring(6,10);
document.pos.elements[ObjName + '_desc[' + ObjNber + ']'].style.backgroundColor='white';
document.pos.elements[ObjName + '_desc[' + ObjNber + ']'].focus();
document.pos.elements[ObjName + '_desc[' + ObjNber + ']'].setSelectionRange(300, 300);
}

function lampfocus(e)
{
document.getElementById("moveMe"+e).src="../www/images/lumiere_on.png";
//alert("focus"+e);
}

function lampUNfocus(e)
{
document.getElementById("moveMe"+e).src="../www/images/lumiere_off.png";
}
</script>

  <?PHP
  // Places Lamp Images on plan
  $sql = mysql_real_escape_string("SELECT * FROM `lumieres` WHERE " .
           "`localisation`=\"" . $selected_level . "\";");
  $sql = str_replace(chr(92).chr(34),"'",$sql);
  //echo("SQL=$sql<br>");
  $i=1;
  $query = mysql_query($sql);
  while ( $row = mysql_fetch_array($query) ) {
    $id[$i]          = $row['id'];
    $description[$i] = $row['description'];
    $img_x[$i]       = $row['img_x'];
    $img_y[$i]       = $row['img_y'];
    $web_offset[$i]  = ($i-1)*39;
    // Display on page
    echo("<img id='moveMe" . $i . "' " .
         "src='../www/images/lumiere_off.png' " .
         "onmousedown='coordinates(\"moveMe" . $i . "\")' />" . CRLF);
    echo("<script type='text/javascript'>" . CRLF);
    echo("//Movable image" . CRLF);
    echo("movMeId".$i."=document.getElementById(\"moveMe".$i."\");" . CRLF);
    echo("//image starting location" . CRLF);
    echo("movMeId".$i.".style.top=\"".$img_x[$i]."px\";" . CRLF);
    echo("movMeId".$i.".style.left=\"".
         ($img_y[$i]-$web_offset[$i])."px\";" . CRLF);
    echo("</script>" . CRLF);

    $i++;
  } // End While


  echo("<div class='clear'></div>" . CRLF);
//  echo("<a href='javascript:void();' onClick=''>" .
//      "<img src='./images/ChangeButton.jpg' width='70px' heigth='60px' /></a>");
  echo("</div>	<!-- end .postcontent -->" . CRLF);

?>
<div id="rss-3" class="block widget_rss">
<ul>
<?PHP
  $i=0;
  echo("<table width='100%'><tr><td width='70%'>");
  if (isset($level[$i])) { echo("Autres Niveaux:<br>"); }
  while (isset($level[$i])) {
    if ($level[$i]!=$selected_level) {
      echo("<li>&nbsp;<a class='rsswidget' href='./index.php?page=Lamps&selected_level=" .
			$level[$i] . "' title='Editer Niveau'>" .
            $level[$i] .
            "</a> <span class='rss-date'></span></li>");
    } // End IF
    $i++;
  } // End WHile
  echo("</td><td width='30%' style='vertical-align:middle'><a href='javascript:void();' " .
       "onClick=\"showOverlay('ChangeImg','Lvl_Name');\">" .
       "<img src='./images/ChangeButton.jpg' width='70px' heigth='60px' " .
	   "title=\"Modifier l'image de ce niveau\"/></a></td></tr></table>");
  
  echo("</ul>" . CRLF);
  echo("<div class='post_info'><a href='javascript:void();' onClick=\"showOverlay('AddLevel','Level_Name');\" id='clickMe'><img width='14' height='14' align='absmiddle' src='../www/images/ajouter.png' />&nbsp;Ajouter un Niveau</a></div>" . CRLF);
  echo("</div>" . CRLF);
?>

<div id="ChangeImg" style="visibility:hidden; z-index: 1; position: absolute; top: 200px; left: 185px; background: WhiteSmoke; width: 950px; height: 850px; opacity: .95; filter: alpha(opacity=80); -moz-opacity: .1; border-width: 2px; border-style: solid; border-color: #000000;">
<a href="javascript:void(1);" onClick="hideOverlay('ChangeImg');"><img width='14' height='14' align='absmiddle' src='../www/images/close.jpg' />&nbsp;Fermer</a>
<p align=center><h1 align=center>Modifier l'image du Niveau <?php echo($selected_level);?></h1></p>
<br><br><br><br>
<form id="NewImg" name="NewImg" enctype="multipart/form-data" action="./index.php?page=Lamps&selected_level=<?php echo($selected_level);?>" method="post" >
<table width="100%">
<tr><td width="20%">&nbsp;</td>
<td width="30%" align="right">Image du Niveau (Format .png , 500x355 Pixels):&nbsp;&nbsp;&nbsp;<br><br></td> 
<td width="50%"><input id="ImgFile" name="ImgFile" type="file" accept="image/png" /></td></tr>
<tr><td width="20%">&nbsp;<input type="hidden" name="FakeLvl_Name" id="FakeLvl_Name" value="01234567890azertyuiop"/>
<input type="hidden" name="Lvl_Name" id="Lvl_Name" value="<?php echo($selected_level);?>"/>
<input type="hidden" name="action" id="action" value="ChangeImg"/></td></td>
<td width="30%"><div class="postcontent" style="valign:absmiddle;">
  <span class="readmore_b">
    <a class="readmore" href="javascript:void(1);" onClick="CheckForm('NewImg','FakeLvl_Name','ImgFile');">Modifier</a></span>
  <div class="clear"></div>
</div>
</td>
<td width="50%" align="left"></td></tr>
</table><br>
</form>
</div>

<div id="AddLevel" style="visibility:hidden; z-index: 1; position: absolute; top: 200px; left: 185px; background: WhiteSmoke; width: 950px; height: 850px; opacity: .95; filter: alpha(opacity=80); -moz-opacity: .1; border-width: 2px; border-style: solid; border-color: #000000;">
<a href="javascript:void(1);" onClick="hideOverlay('AddLevel');"><img width='14' height='14' align='absmiddle' src='../www/images/close.jpg' />&nbsp;Fermer</a>
<p align=center><h1 align=center>Ajouter un Niveau</h1></p>
<br><br><br><br>

 <form id="NewLevel" name="NewLevel" enctype="multipart/form-data" action="./index.php?page=Lamps&action=AddLevel" method="post" >
<table width="100%">
<tr><td width="20%">&nbsp;</td>
<td width="30%" align="right">Nom du Niveau:&nbsp;&nbsp;&nbsp;<br><br></td>
<td width="50%"><input name="Level_Name" id="Level_Name" type="text" size="20" value="" /></td></tr>
<tr><td width="20%">&nbsp;</td>
<td width="30%" align="right">Image du Niveau (Format .png , 500x355 Pixels):&nbsp;&nbsp;&nbsp;<br><br></td> 
<td width="50%"><input id="NewPlan" name="NewPlan" type="file" accept="image/png" /></td></tr>
<tr><td width="20%">&nbsp;</td>
<td width="30%"><div class="postcontent">
  <span class="readmore_b">
    <a class="readmore" href="javascript:void(1);" onClick="CheckForm('NewLevel','Level_Name','NewPlan');">Ajouter</a></span>
  <div class="clear"></div>
</div>
</td>
<td width="50%" align="left"></td></tr>
</table><br>
</form>

</div>

<script type="text/javascript">
 var op = 0;
 
 function showOverlay(divID,Ifocus) {
 var o = document.getElementById(divID);
 SurImpose('main',divID);
 o.style.visibility = 'visible';
 o.style.opacity = 0.05;
 op=op+5;
 fadein(op,divID);
 document.getElementById(Ifocus).focus();
 }

function SurImpose(Ref,Obj) {
  oElement = document.getElementById(Ref);
  ToMove =  document.getElementById(Obj);
  var iReturnValue = 0; 
  while( oElement != null ) {
    iReturnValue += oElement.offsetTop;
    oElement = oElement.offsetParent;
  }
  ToMove.style.top = (iReturnValue+5)+"px";
  oElement = document.getElementById('header');
  iReturnValue = 0; 
  while( oElement != null ) {
    iReturnValue += oElement.offsetLeft;
    oElement = oElement.offsetParent;
  }
  ToMove.style.left = (iReturnValue+5)+"px";
  return true;
}
 
function fadein(op,divID) {

 var o = document.getElementById(divID);
 opa = op/100;
 
 o.style.opacity = opa;
 op=op+5;

 if(op>=105) { return; }
 var cmd = "fadein(" + op.toString() + ",'" + divID.toString() + "')";
 setTimeout(cmd,50);
}

 function hideOverlay(lID) {
 var o = document.getElementById(lID);
 o.style.visibility = 'hidden';
 }

function CheckLevelName(lID) {
   var Name   = document.getElementById(lID).value;
   // Empty?
   if (Name.length==0) {
	 alert("Nom du niveau Vide!");
     document.getElementById(lID).focus();
	 return;
   }
   // Correct Name?
   var NameOut = "";
   var j = 0;
   var d = "";

   var l = Name.length-1;
   while (Name.substr(l,1) == " ") {
     l = l - 1;
   }
   l =l + 1;

   for (i = 0 ; i < l ;i++) {
     c = Name.substr(i,1);
     if ((((c >= "0") && (c <= "9")) || ((c >= "a") 
      && (c <= "z")) || ((c >= "A") && (c <= "Z"))) || ((c==" ") 
      && (d!=" "))) {
        NameOut += c;
        d=c;
     }
   }
   if (NameOut != Name) {
    alert("Utilisez des noms contant Uniquement A-Z , a-z , 0-9, _ ou -");
    document.getElementById(lID).value = NameOut;
    document.getElementById(lID).focus();
    return;
   }
   

   // Level Exits?
   <?php
   $i   = 0;
   $var = "var Levels=new Array(";
   while ( isset($level[$i]) ) {
     if ($i>=1) { $var .= ","; }
     $var .= "\"" . $level[$i] . "\"";	
     $i++;
   }
   $i--;
   $var .= ");" . CRLF;
   if ($i>=0) {
   echo($var);
   ?>
   var i = 0;
   while (i<=<?php echo($i);?>) {
     if (Name==Levels[i]) {
	   alert("Ce niveau existe!");
	   document.getElementById(lID).focus();
       return;
	 }
	 i=i+1;
   }
   <?php } else { echo("// DB Empty ... so yes! ;-)" . CRLF); } // End IF ?>
   return true;
}

 function CheckForm(FormName,lName,ImgFile) {
   // Check Level Name
   if (!CheckLevelName(lName)) {alert("NOK"); return;}
   // File Well Selected?
   var FlName   = document.getElementById(ImgFile).value;
   if (FlName=="") {
     alert("Pas de fichier!");
	 return;	
   }
   

   // File = png?
   var hash = { 
     '.gif'  : 1,
	 '.jpg'  : 1,
	 '.jpeg' : 1,
	 '.wmf'  : 1,
	 '.pdf'  : 1,
   }; 
   var re = /\..+$/; 
   var ext = FlName.match(re);
   if (hash[ext]) { 
     alert("Utilisez UNIQUEMENT des fichiers .png!");
     var Name = document.getElementById(lName).value;	 
	 var fld  = document.getElementById(ImgFile);
     fld.form.reset();
	 document.getElementById(lName).value = Name;
     fld.focus();
     return;
   }
   // Form OK => Submit
   document.getElementById(FormName).submit();
 }

 function ActiveLevelChange(lID) {
  	if (CheckLevelName(lID)) {
	  document.getElementById("action").value="ModifyLevel";
	  document.getElementById("ChangeName").submit();
	}
 }
 
 function ActiveLevelDelete() {
   if (confirm("Etes vous certain?")) {
	 document.getElementById("action").value="DeleteLevel";
	 document.getElementById("ChangeName").submit();
   }	
 }
 
</script>
 
</div>
</div>


<div id="sidebar">
<div id="text-11" class="block widget_text"><br><br><br><h2>Points Lumineux:</h2>
<img width="258" height="16" alt="Sidebar Hr" src="./images/sidebar_hr.png" class="divider"/>
</div>

<div id="rss-3" class="block widget_rss">
<ul>
<?php
  // List Lamps
  echo("<form name='pos' method='get' id='ChangeLamp'".
       " action='" . htmlentities($_SERVER['PHP_SELF']) ."'>" . CRLF);
  echo("<input type='hidden' name='page' value='" .
        "Lamps'/>" . CRLF);
  echo("<input type='hidden' name='selected_level' value='" .
        $selected_level . "'/>" . CRLF);
  echo("<input type='hidden' name='LampD' value=''/>" . CRLF);
  $i = 1;
  while (isset($id[$i])) {
    echo("  <li><input type='text'" .
         " name='moveMe_desc[".$i."]'" .
         " onfocus='lampfocus(\"$i\")' " .
         " onblur='lampUNfocus(\"$i\")' " . 
         " value='" . $description[$i] . 
         "' />" . CRLF);
	echo("<a href='javascript:void();' onClick='LampDelete(".$id[$i].");'><img src='./images/drop.png'/></a></li>" . CRLF);
    echo("  <input type='hidden'" .
         " name='moveMe_id[".$i."]' value='" . $id[$i] . "' />" . CRLF);

    echo("  <input type='hidden'" .
         " name='moveMe_xcoor[".$i."]' value='" . $img_y[$i] .
         "px' />" . CRLF);
    echo("  <input type='hidden'" .
         " name='moveMe_ycoor[".$i."]' value='" . $img_x[$i] . 
         "px' />" . CRLF);
    $i++;
  } // End While
?>

&nbsp;&nbsp;<a href='javascript:void(1);' onClick='showOverlay("NewLamp","Lamp_Name");'><img width='14' height='14' align='absmiddle' src='../www/images/ajouter.png' />&nbsp;&nbsp;&nbsp;Ajouter une Lampe</a>
  </ul></div>


<div class="postcontent">

		<span class="readmore_b">
<a class="readmore" href="javascript:void(1);" style="color: white;" onclick="submitform('update');">Update</a></span>
		<div class="clear"></div>
	</div>

<script type="text/javascript">

// Submit Modify or Add Lamps
function LampDelete(id) {
  document.pos.LampD.value = id;
  submitform("DeleteLamp");
}

function CheckSubmitform(field,action) {
  var ll = document.getElementById(field).value;
  if (ll.length>=1) {
    submitform(action);
  } else {
    alert("Description Vide!");
	return;
  }
}

function submitform(action) {
  //alert("submit + Action="+action);
  document.pos.action.value = action;
  document.pos.submit();
}
</script>

  <input type="hidden" name="action" value="" />    

</div>


<div id="NewLamp" style="visibility:hidden; z-index: 1; position: absolute; top: 200px; left: 185px; background: WhiteSmoke; width: 950px; height: 850px; opacity: .95; filter: alpha(opacity=80); -moz-opacity: .1; border-width: 2px; border-style: solid; border-color: #000000;">
<a href="javascript:void(1);" onClick="hideOverlay('NewLamp');"><img width='14' height='14' align='absmiddle' src='../www/images/close.jpg' />&nbsp;Fermer</a>

<p align=center><h1 align=center>Ajouter une lampe au Niveau <?php echo($selected_level);?></h1></p>
<br><br><br><br>
<table width="100%">
<tr><td width="20%">&nbsp;</td>
<td width="30%" align="right">Description&nbsp;&nbsp;&nbsp;<br><br></td> 
<td width="50%"><input id="Lamp_Name" name="Lamp_Name" type="text"/></td></tr>
<tr><td width="20%">&nbsp;</td>
<td width="30%" align="right">Consigne&nbsp;&nbsp;&nbsp;<br><br></td> 
<td width="50%"><select name="Intensity" id="Intensity">
<?php
$i = 1;
while ($i<=50) {
$selected = ""; if ($i==50) { $selected = " Selected"; }
echo("<option value='" . $i . "' " . $selected . ">" .($i*2) . "%</option>" . CRLF);
$i++;
} // End While
?>
</select>
</td></tr>
<tr><td width="20%">&nbsp;</td>
<td width="30%" align="right">Sortie&nbsp;&nbsp;&nbsp;<br><br></td> 
<td width="50%"><select name="Output" id="Output">
<?php
$sql = mysql_real_escape_string("SELECT * FROM `ha_element` WHERE " .
        "(`element_type`=\"0x11\" OR `element_type`=\"0x12\");");
$sql = str_replace(chr(92).chr(34),"'",$sql);
$query = mysql_query($sql);
while ($row = mysql_fetch_array($query)) {
  $id           = $row['id'];
  $element_name = $row['element_name'];
  echo("<option value='" . $id . "'>" . $element_name . "</option>" . CRLF);
} // End While
?>
</select>
</td></tr>

<tr><td width="20%">&nbsp;
<td width="30%"><div class="postcontent">
  <span class="readmore_b"><p>
    <a class="readmore" href="javascript:void(1);" onClick="CheckSubmitform('Lamp_Name','AddLamp');">Ajouter</a></p></span>
  <div class="clear"></div>
</div>
</td>
<td width="50%" align="left"></td></tr>
</table><br>
</div>
</form>
<?php
  mysql_close();
} // End of Function plans