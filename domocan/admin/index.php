<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Frameset//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-frameset.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" dir="ltr" lang="fr-FR">

<META HTTP-EQUIV="expires" CONTENT="Wed, 09 Aug 2000 08:21:57 GMT">
<META HTTP-EQUIV="Pragma" CONTENT="no-cache">
<META HTTP-EQUIV="Cache-Control" CONTENT="no-cache">
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>DomoCAN EM Admin</title>

<link rel="stylesheet" href="./reset.css" type="text/css" media="screen" />
<link rel="stylesheet" href="./style.css" type="text/css" media="screen" />
<link rel='stylesheet' id='et-shortcodes-css-css'  href='./shortcodes.css?ver=1.6' type='text/css' media='all' />

<script type='text/javascript' src='./js/jquery.easy-slider.js?ver=3.3.1'></script>

<script type='text/javascript' src='./js/jquery-1.4.2.min.js?ver=1.4.2'></script>



</head>

<?php
// PHP Error Reporting
error_reporting(E_ALL);
ini_set('display_errors', '1');

// Includes
include_once '../www/conf/config.php';
include_once './Modules/mod.Lamps.php';
include_once './Modules/mod.Temps.php';
include_once './Includes/func.misc.php';

// Connect DB
$DB = mysql_connect(MYSQL_HOST, MYSQL_LOGIN, MYSQL_PWD);
mysql_set_charset('utf8',$DB); 
mysql_select_db(MYSQL_DB);
  
// Security
$Access_Level = 0;     // => Visitor
$PassOK=0;            // Password NOK
// Authentication
session_start();
if(isset($_GET['logout']))
 {
   unset($_SESSION["login"]);
   session_destroy();
   //echo "<font color='white' size='16pt'>Acc&egrave;s Interdit ... ";
   //echo "[<a style='color:#ffffff; font-style: bold; size: 16pt;' href='" . $_SERVER['PHP_SELF'] . "'>Login</a>]</font>";
   //exit;
 }

 
if (!isset($_SERVER['PHP_AUTH_USER']) || !isset($_SERVER['PHP_AUTH_PW']) || !isset($_SESSION["login"]))
 {
   header("WWW-Authenticate: Basic realm=\"" . ADMIN_INTERFACE_NAME . "\"");
   header("HTTP/1.0 401 Unauthorized");
   //session_destroy();
   $_SESSION["login"] = true;
   echo "<font color='white' size='16pt'>Acc&egrave;s Interdit ... ";
   echo "[<a style='color:#ffffff; font-style: bold; size: 16pt;' href='" . $_SERVER['PHP_SELF'] . "'>Login</a>]</font>";
   //exit;
 }
 else
 {
   $SubmitUser = $_SERVER['PHP_AUTH_USER'];
   $SubmitPass = $_SERVER['PHP_AUTH_PW'];
   $sql = "SELECT COUNT(*) AS PassOK FROM `users` WHERE (Alias='" .
          $SubmitUser . "' AND Password=PASSWORD('". $SubmitPass ."'));";
   $query = mysql_query($sql);
   $row = mysql_fetch_array($query);
   $PassOK = $row['PassOK'];
   
   if($PassOK<=1)
   {
     //echo "You have logged in ... ";
	 $Access_Level = 8;
     //echo "[<a href='" . $_SERVER['PHP_SELF'] . "?logout'>Logout</a>]";
   }
   else
   {
     unset($_SESSION["login"]);
	 session_destroy();
     header("Location: " . $_SERVER['PHP_SELF']);
   }
 }
 
// Starts HTML Body
echo("<body class='home blog ie'>" . CRLF); 
echo("<div id='top'>" . CRLF);
echo("<div id='header'>" . CRLF);
echo("<p><h1 align='center'><font color='white'><b>" . ADMIN_INTERFACE_NAME . "</b></font></h1></p>" . CRLF);

// Acess Level OK?
if ($Access_Level>=1) {

	// On Which Page?
	$Html_Page    = html_postget("page");
	$Html_SubPage = html_postget("SubMenu");

	// Build top menu
	$Top_Menu = array();
	$Top_Menu["Tag"][1] = "Status"; $Top_Menu["Text"][1] = "Status";              $Top_Menu["URL"][01] = "./index.php?page=Status";
	$Top_Menu["Tag"][2] = "2";      $Top_Menu["Text"][2] = "###2###";             $Top_Menu["URL"][2] = "./index.php?page=2";
	$Top_Menu["Tag"][3] = "Logics"; $Top_Menu["Text"][3] = "Logiques";            $Top_Menu["URL"][3] = "./index.php?page=Logics";
	$Top_Menu["Tag"][4] = "Temps";  $Top_Menu["Text"][4] = "Temp&eacute;ratures"; $Top_Menu["URL"][4] = "./index.php?page=Temps";
	$Top_Menu["Tag"][5] = "Ins";    $Top_Menu["Text"][5] = "Entr&eacute;es";      $Top_Menu["URL"][5] = "./index.php?page=Ins";
	$Top_Menu["Tag"][6] = "Lamps";  $Top_Menu["Text"][6] = "Lampes";              $Top_Menu["URL"][6] = "./index.php?page=Lamps";
	$Top_Menu["Tag"][7] = "Outs";   $Top_Menu["Text"][7] = "Sorties";             $Top_Menu["URL"][7] = "./index.php?page=Outs";
	$Top_Menu["Tag"][8] = "Blinds"; $Top_Menu["Text"][8] = "Volets";              $Top_Menu["URL"][8] = "./index.php?page=Blinds";
	$Top_Menu["Tag"][9] = "SysMap"; $Top_Menu["Text"][9] = "System Map";          $Top_Menu["URL"][9] = "./index.php?page=SysMap";
	$Top_Menu["Tag"][10]= "Admin";  $Top_Menu["Text"][10]= "Admin";               $Top_Menu["URL"][10]= "./index.php?page=Admin";

	$Top_SubMenu = array();
	$Top_SubMenu[10]["Text"][01] = "Backup Config Cartes";  $Top_SubMenu[10]["URL"][01] = "./index.php?page=Admin&SubMenu=1";
	$Top_SubMenu[10]["Text"][02] = "Restore Config Cartes"; $Top_SubMenu[10]["URL"][02] = "./index.php?page=Admin&SubMenu=2";
	$Top_SubMenu[10]["Text"][03] = "This is Submenu 3";     $Top_SubMenu[10]["URL"][03] = "./index.php?page=Admin&SubMenu=3";
	$Top_SubMenu[10]["Text"][04] = "This is Submenu 4";     $Top_SubMenu[10]["URL"][04] = "./index.php?page=Admin&SubMenu=4";
	$Top_SubMenu[10]["Text"][05] = "This is Submenu 5";     $Top_SubMenu[10]["URL"][05] = "./index.php?page=Admin&SubMenu=5";
	$Top_SubMenu[10]["Text"][06] = "LogOut";                $Top_SubMenu[10]["URL"][06] = $_SERVER['PHP_SELF'] . "?logout";


	echo("<!-- Start Menu -->" . CRLF);

	echo("<ul id='menu-domocan-ME' class='sf-menu'>" . CRLF);

	$i = 1; $MenuCount = 16600; $SubCount = 25898;
	while (isset($Top_Menu["Text"][$i])) {
	  $Selected = ""; if ($Html_Page == $Top_Menu["Tag"][$i]) { $Selected = " selectedLava"; }
	  echo("<li id='menu-item-" . $MenuCount . "' class='menu-item menu-item-type-taxonomy menu-item-object-category menu-item-" .
		   $MenuCount . $Selected .
		   "'><a href='" . $Top_Menu["URL"][$i] . "' >" . $Top_Menu["Text"][$i] . "</a>");
	  $j = 1;
	  
	  if (isset($Top_SubMenu[$i]["Text"][1])) { echo(CRLF . "<ul class='sub-menu'>" . CRLF); } else { echo("</li>" . CRLF);} // End If
	  // --- SubMenus ---
	  while (isset($Top_SubMenu[$i]["Text"][$j])) {
		echo("<li id='menu-item-" . $SubCount . "' class='menu-item menu-item-type-post_type menu-item-object-page menu-item-" . $SubCount . "'>" .
			 "<a href='" . $Top_SubMenu[$i]["URL"][$j] ."' >" . $Top_SubMenu[$i]["Text"][$j] . "</a></li>" . CRLF);
		$SubCount++;
		$j++;
	  } // End While
	  if (isset($Top_SubMenu[$i]["Text"][1])) { echo("</ul></li>" . CRLF); $MenuCount = $SubCount; }
	  $MenuCount++; $i++;
	} // End While

	echo("</ul>" . CRLF);

	echo("			<!-- End Menu -->	" . CRLF);
	echo("<div id='wrap'>" . CRLF);
	echo("<!-- Main Content-->" . CRLF);
	echo("	<img src='./images/content-top.gif' alt='content top' class='content-wrap' />" . CRLF);
	echo("	<div id='content'>" . CRLF);
	echo("		<!-- Start Main Window -->" . CRLF);
	echo("		<div id='main'>" . CRLF);
	echo("<!-- Contenu -->" . CRLF);
	echo("<div class='new_post'>" . CRLF);
		
	// --- Main Page Content


	if ($Html_Page=="Temps") { Temps(); }
	if ($Html_Page=="Lamps") { Lamps(); }
	
	if (($Html_Page=="") OR ($Html_Page=="Status")) {
	  ?>
	  <div id="PCInt" style="z-index: 1; position: absolute; top: 200px; left: 185px; background: WhiteSmoke; width: 965px; height: 560px; border-width: 2px; border-style: solid; border-color: #000000;">
	  <iframe width="966" height="560" src="../www/pc.html"></iframe>
	  </div>
	  
	  <script type="text/javascript">
	    window.onload=function showOverlay() {
		  var divID = 'PCInt';
		  var o = document.getElementById(divID);
		  SurImpose('main',divID);
		}

		function SurImpose(Ref,Obj) {
		  oElement = document.getElementById(Ref);
		  ToMove =  document.getElementById(Obj);
		  var iReturnValue = 0; 
		  while( oElement != null ) {
			iReturnValue += oElement.offsetTop;
			oElement = oElement.offsetParent;
		  }
		  ToMove.style.top = (iReturnValue-10)+"px";
		  oElement = document.getElementById('header');
		  iReturnValue = 0; 
		  while( oElement != null ) {
			iReturnValue += oElement.offsetLeft;
			oElement = oElement.offsetParent;
		  }
		  ToMove.style.left = (iReturnValue-5)+"px";
		  return true;
		}

	  </script>
	  <?php
	} //End If
	?>

	</div>
		<!-- End Content -->
		<img src="./images/content-bottom.gif" alt="content top" class="content-wrap" />




	<script src="./js/jquery.lavalamp.1.3.3-min.js" type="text/javascript" charset="utf-8"></script>
	<script src="./js/jquery.cycle.all.min.js" type="text/javascript" charset="utf-8"></script>
	<script src="./js/superfish.js" type="text/javascript" charset="utf-8"></script>   
	<script src="./js/jquery.easing.1.3.js" type="text/javascript" charset="utf-8"></script>

	<script type="text/javascript">
	//<![CDATA[
	 
	jQuery(function(){

			jQuery.noConflict();
		
			jQuery('ul.sf-menu').superfish({
				delay:       200,                            // one second delay on mouseout 
				animation:   {'marginLeft':'0px',opacity:'show',height:'show'},  // fade-in and slide-down animation 
				speed:       'fast',                          // faster animation speed 
				autoArrows:  true,                           // disable generation of arrow mark-up 
				onBeforeShow:      function(){ this.css('marginLeft','20px'); },
				dropShadows: false                            // disable drop shadows 
			});
			
			jQuery('ul.sf-menu ul > li').addClass('noLava');
			jQuery('ul.sf-menu > li').addClass('top-level');
			
			jQuery('ul.sf-menu > li > a.sf-with-ul').parent('li').addClass('sf-ul');
			
			jQuery("ul.sf-menu > li > ul").prev("a").attr("href","#");		
			if (!(jQuery("#footer_widgets .block_b").length == 0)) {
				jQuery("#footer_widgets .block_b").each(function (index, domEle) {
					// domEle == this
					if ((index+1)%3 == 0) jQuery(domEle).after("<div class='clear'></div>");
				});
			};
			
			/* search form */
			
			jQuery('#search').toggle(
				function () {jQuery('#searchbox').animate({opacity:'toggle', marginLeft:'-210px'},500);},
				function () {jQuery('#searchbox').animate({opacity:'toggle', marginLeft:'-200px'}, 500);}
			);
			
			var $searchinput = jQuery("#header #searchbox input");
			var $searchvalue = $searchinput.val();
			
			$searchinput.focus(function(){
				if (jQuery(this).val() == $searchvalue) jQuery(this).val("");
			}).blur(function(){
				if (jQuery(this).val() == "") jQuery(this).val($searchvalue);
			});
			
		
			jQuery('ul.sf-menu li ul').append('<li class="bottom_bg noLava"></li>');
			
			var active_subpage = jQuery('ul.sf-menu ul li.current-cat, ul.sf-menu ul li.current_page_item').parents('li.top-level').prevAll().length;
			var isHome = 1; 
			
			if (active_subpage) jQuery('ul.sf-menu').lavaLamp({ startItem: active_subpage });
			else if (isHome === 1) jQuery('ul.sf-menu').lavaLamp({ startItem: 0 });
			else jQuery('ul.sf-menu').lavaLamp();
				
			
						
				/* featured slider */
				
				jQuery('#spotlight').cycle({
					timeout: 0,
					speed: 1000, 
					fx: 'cover'
				});
				
				var $featured_item = jQuery('div.featitem');
				var $slider_control = jQuery('div#f_menu');
				var ordernum;
				var pause_scroll = false;
				var $featured_area = jQuery('div#featured_content');			
		 
				function gonext(this_element){
					$slider_control
					.children("div.featitem.active")
					.removeClass('active');
					this_element.addClass('active');
					ordernum = this_element.find("span.order").html();
					jQuery('#spotlight').cycle(ordernum - 1);
				} 
				
				$featured_item.click(function() {
					clearInterval(interval);
					gonext(jQuery(this)); 
					return false;
				});
				
				jQuery('a#previous, a#next').click(function() {
					clearInterval(interval);
					if (jQuery(this).attr("id") === 'next') {
						auto_number = $slider_control.children("div.featitem.active").prevAll().length+1;
						if (auto_number === $featured_item.length) auto_number = 0;
					} else {
						auto_number = $slider_control.children("div.featitem.active").prevAll().length-1;
						if (auto_number === -1) auto_number = $featured_item.length-1;
					};
					gonext($featured_item.eq(auto_number));
					return false;
				});

							
					$featured_area.mouseover(function(){
						pause_scroll = true;
					}).mouseout(function(){
						pause_scroll = false;
					});
					
				
				var auto_number;
				var interval;
				
				$featured_item.bind('autonext', function autonext(){
					if (!(pause_scroll)) gonext(jQuery(this)); 
					return false;
				});
				
								interval = setInterval(function () {
						auto_number = $slider_control.find("div.featitem.active span.order").html();
						if (auto_number == $featured_item.length) auto_number = 0;
						$featured_item.eq(auto_number).trigger('autonext');
					}, 6000);
						
			});
	//]]>
	</script>
	<script type="text/javascript">
		  /* <![CDATA[ */
		  jQuery('div.gallery').easyslider({
				style:'fadein',
				showloading:true,
				replacegallery:false,
				gallerystyle:'default'
		  });
		  /* ]]> */
	</script>
<?php
} // End IF	
echo("</body>".CRLF);
echo("</html>".CRLF);