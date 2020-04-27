<?php

  /* CONFIGURATIONS ET DEPENDANCES */
  include_once './conf/config.php';
  include_once PATH . 'lib/xajax/xajax_core/xajax.inc.php';
  include_once PATH . 'lib/xtemplate/xtemplate.class.php';


  /* XAJAX */
  $xajax = new xajax();
  $xajax->configure("javascript URI", URI . "/lib/xajax/");
  $xajax->setFlag('debug', DEBUG_AJAX);


  /* AUCUN THEME > TABLETTE PAR DEFAUT */
  if ( !isset($_GET['theme']) )
  {
    $_GET['theme'] = 'tablette';
  }


  /* OUVRE LE XTEMPLATE */
  $_XTemplate = new XTemplate(PATH . 'html/' . $_GET['theme'] . '/structure.html');


  /* SI AUCUNE PAGE N'EST DEMANDEE OU QUE L'ARGUMENT CONTIENT UNE ERREUR */
  if ( !isset($_GET['page']) || !preg_match('`[[:alnum:]]{4,20}$`', $_GET['page']) )
  {
    $_GET['page'] = 'lumieres';
  }


  /* INCLUSION DU FICHIER HTML (PAGE DEMANDEE) */
  if ( is_file('./html/' . $_GET['theme'] . '/' . $_GET['page'] . '.html') )
  {
    $_XTemplate->assign_file('content', './html/' . $_GET['theme'] . '/' . $_GET['page'] . '.html');
  }
  else {
    $_XTemplate->assign_file('content', './html/' . $_GET['theme'] . '/pasinstalle.html');
  }


  /* DEFINIR PAGE DEMANDEE (POUR APPEL CSS & JS SPECIFIQUE) */
  $_XTemplate->assign('PAGE', $_GET['page']);

  if ( is_file('./js/' . $_GET['page'] . '.js') )
  {
    $_XTemplate->assign_file('JAVASCRIPT', './js/' . $_GET['page'] . '.js');
  }

  if ( is_file('./css/' . $_GET['theme'] . '/' . $_GET['page'] . '.css') )
  {
    $_XTemplate->parse('main.css_specific_file');
  }


  /* INCLUSION DU FICHIER PHP (PAGE DEMANDEE) */
  if ( file_exists('./php/' . $_GET['page'] . '.php') ) {
    include_once('./php/' . $_GET['page'] . '.php');
  }

  $_XTemplate->assign('TITRE', $titre);


  /* PROCESSEUR ET AFFICHAGE XAJAX */
  $xajax->processRequest();
  $_XTemplate->assign('XAJAX', $xajax->getJavascript());


  /* PARSE LE BLOC 'main' */
  $_XTemplate->parse('main');


  /* AFFICHE LE RESULTAT */
  $_XTemplate->Out('main');

?>
