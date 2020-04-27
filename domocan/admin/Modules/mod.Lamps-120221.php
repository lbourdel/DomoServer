<?PHP

function Lamps() {

  // Variables Passed Globally
  global $Access_Level;


  // Variables passed within the <Form> or URL
  $selected_level = html_postget("selected_level");
  if ($selected_level=="") { $selected_level = "RDC"; }
  
  $action = html_postget("action");
  if (ADMIN_DEBUG) { echo("Action=$action, Access Level=$Access_Level<br>"); }

  // Add Level
  if ($action=="AddLevel") {
    // Move File
	$Level_Name = html_postget("Level_Name");
	$File_Name = $_FILES['NewPlan']['name'];
	if (($Level_Name!="") AND ($File_Name)) {
      $Dest_Name = "../www/images/plans/plan_" . $Level_Name . ".png";
	  if(copy($_FILES['NewPlan']['tmp_name'], $Dest_Name)) {
	    // Create New DB Entry
        $sql = "INSERT INTO `domotique`.`localisation` (`id`, `lieu`) VALUES (NULL, '" . $Level_Name . "');";
		if (ADMIN_DEBUG) { echo("sql=$sql<br>" . CRLF); }
		if (!$query=mysql_query($sql)) { echo("Erreur DB!<br>" . CRLF); }
      } else {
	    echo("Probleme de copie de Fichier!<br>");
      }	  // End IF
	} // End IF
  } // End IF
	
    // Modify Level
    if ($action=="ModifyLevel") {
      $Level = html_get("Level"); // original Name
	  $LName = html_get("LName"); // New Name
	  if (rename("../www/images/plans/plan_" . $Level . ".png" , "../www/images/plans/plan_" . $LName . ".png")) {
	    $sql = "UPDATE `localisation` SET `lieu` = '" . $LName .
               "' WHERE `lieu` = '" . $Level . "' LIMIT 1;";
	    if (ADMIN_DEBUG) { echo("sql=$sql<br>" . CRLF); }
		if (!$query=mysql_query($sql)) { echo("Erreur DB!<br>" . CRLF); }
	  } else {
	    echo("Probleme de copie de Fichier!<br>");
      }	  // End IF
    } // End IF

    // Modify Level Image
    if ($action=="ChangeImg") {
	  $Lvl_Name = html_post("Lvl_Name");
	  $Dest_Name = "../www/images/plans/plan_" . $Lvl_Name . ".png";
	  if(!copy($_FILES['ImgFile']['tmp_name'], $Dest_Name)) {
	    echo("Probleme de copie de Fichier!<br>");
      }	  // End IF
    } // End IF
  
  
    // Delete Level
    if ($action=="DeleteLevel") {
      $Level = html_get("Level"); // original Name
	  if (@unlink("../www/images/plans/plan_" . $Level . ".png")) {
	    $sql = "DELETE FROM `localisation` " .
               " WHERE `lieu` = '" . $Level . "';";
	    if (ADMIN_DEBUG) { echo("sql=$sql<br>" . CRLF); }
		if (!$query=mysql_query($sql)) { echo("Erreur DB!<br>" . CRLF); }
	  } else {
	    echo("Impossible d'effacer le Fichier!<br>");
      }	  // End IF
    } // End IF
    
  
  
  // Avoid SQL Injection
  //$name_bad = mysql_real_escape_string($name_bad);

  // Existing levels
  $sql = "SELECT * FROM `localisation` WHERE 1;";
  $query = mysql_query($sql);
  $i=0;
  while ( $row = mysql_fetch_array($query) ) {
    $level[$i] = $row['lieu'];
    $i++;
  } // End While

  echo("<h2 class='title'>Position des points lumineux</h2>");
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
//alert(sender.id);
  document.pos.elements[sender.id + '_xcoor'].style.backgroundColor='lime';
  document.pos.elements[sender.id + '_ycoor'].style.backgroundColor='lime';
  document.pos.elements[sender.id + '_desc'].style.backgroundColor='lime';

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
posx = (pleft+e.clientX-xcoor)+(38*(sender.id.substring(6,7)-1));
movMeId.style.left=pleft+e.clientX-xcoor+"px";
movMeId.style.top=ptop+e.clientY-ycoor+"px";
var sender = (typeof( window.event ) != "undefined" ) ? e.srcElement : e.target;
document.pos.elements[sender.id + '_xcoor'].value=posx+"px";
document.pos.elements[sender.id + '_ycoor'].value=movMeId.style.top;
return false;
}

function mouseup(e)
{
document.onmousemove=null;
var sender = (typeof( window.event ) != "undefined" ) ? e.srcElement : e.target;

document.pos.elements[sender.id + '_xcoor'].style.backgroundColor='white';
document.pos.elements[sender.id + '_ycoor'].style.backgroundColor='white';
document.pos.elements[sender.id + '_desc'].style.backgroundColor='white';
document.pos.elements[sender.id + '_desc'].focus();
document.pos.elements[sender.id + '_desc'].setSelectionRange(300, 300);
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
  $sql = "SELECT * FROM `lumieres` WHERE " .
           "`localisation`='" . $selected_level . "';";
  //echo("SQL=$sql<br>");
  $i=1;
  $query = mysql_query($sql);
  while ( $row = mysql_fetch_array($query) ) {
    $id[$i]          = $row['id'];
    $description[$i] = $row['description'];
    $img_x[$i]       = $row['img_x'];
    $img_y[$i]       = $row['img_y'];
    $web_offset[$i]  = ($i-1)*38;
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

<div id="ChangeImg" style="visibility:hidden; z-index: 1; position: absolute; top: 200px; left: 185px; background: WhiteSmoke; width: 950px; height: 650px; opacity: .95; filter: alpha(opacity=80); -moz-opacity: .1; border-width: 2px; border-style: solid; border-color: #000000;">
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
<td width="30%"><div class="postcontent">
  <span class="readmore_b">
    <a class="readmore" href="javascript:void(1);" onClick="CheckForm('NewImg','FakeLvl_Name','ImgFile');">Modifier</a></span>
  <div class="clear"></div>
</div>
</td>
<td width="50%" align="left"></td></tr>
</table><br>
</form>
</div>

<div id="AddLevel" style="visibility:hidden; z-index: 1; position: absolute; top: 200px; left: 185px; background: WhiteSmoke; width: 950px; height: 650px; opacity: .95; filter: alpha(opacity=80); -moz-opacity: .1; border-width: 2px; border-style: solid; border-color: #000000;">
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
 o.style.visibility = 'visible';
 o.style.opacity = 0.05;
 op=op+5;
 fadein(op,divID);
 document.getElementById(Ifocus).focus();
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
<div id="text-11" class="block widget_text"><br><br><br><h2>
Niveaux</h2>
<img width="258" height="16" alt="Sidebar Hr" src="./images/sidebar_hr.png" class="divider"/>
</div>

<div id="rss-3" class="block widget_rss">
<ul>
<?php
  // List Lamps
  echo("<form name='pos' method='post' id='ChangeLamp'".
       " action='" . htmlentities($_SERVER['PHP_SELF']) ."?page=Lamps'>" . CRLF);
  $i = 1;
  while (isset($id[$i])) {
    echo("  <li><input type='text'" .
         " name='moveMe".$i."_desc'" .
         " onfocus='lampfocus(\"$i\")' " .
         " onblur='lampUNfocus(\"$i\")' " . 
         " value='" . $description[$i] . 
         "' /></li>" . CRLF);
    echo("  <input type='hidden'" .
         " name='moveMe".$i."_id' value='" . $i . "' />" . CRLF);

    echo("  <input type='hidden'" .
         " name='moveMe".$i."_xcoor' value='" . $img_y[$i] .
         "px' />" . CRLF);
    echo("  <input type='hidden'" .
         " name='moveMe".$i."_ycoor' value='" . $img_x[$i] . 
         "px' />" . CRLF);
    $i++;
  } // End While
?>

&nbsp;&nbsp;<a href='./index.php?page=Lamps&action=AddLamp'><img width='14' height='14' align='absmiddle' src='../www/images/ajouter.png' />&nbsp;&nbsp;&nbsp;Ajouter une Lampe</a>
  </ul></div>


<div class="postcontent">

		<span class="readmore_b">
<a class="readmore" href="javascript:void(1);" style="color: white;" onclick="submitform('update');">Update</a></span>
		<div class="clear"></div>
	</div>

<script type="text/javascript">
function submitform(action)
{
  //alert("submit="+document.pos.moveMe1_xcoor.value);
  document.pos.action.value = action;
  document.pos.submit();
}
</script>

  <input type="hidden" name="action" value="" />    


</form>
</div>

<?php
  mysql_close();
} // End of Function plans