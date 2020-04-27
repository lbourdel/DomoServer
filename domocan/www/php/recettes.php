<?php


  $titre = "Recettes de cuisine";

  /* DECLARATION DES FONCTIONS EN AJAX */
  $xajax->register(XAJAX_FUNCTION, 'listerCategories');
  $xajax->register(XAJAX_FUNCTION, 'listerRecettes');

  /* LISTER LES CATEGORIES */
  function listerCategories($suite = '0') {
    $objResponse = new xajaxResponse();

    mysql_connect(MYSQL_HOST, MYSQL_LOGIN, MYSQL_PWD);
    mysql_select_db('recettes');
    $retour = mysql_query("SELECT `NumAbrev`,`Libelle` FROM `Abreviations` LIMIT " . $suite . ",10");
    $i = 0;

    while ( $row = mysql_fetch_array($retour) ) {
      $classe = ($i % 2) ? ' class="paire"' : '';
      $contenu .= "<p" . $classe . "><span onClick=\"traitement(); xajax_listerRecettes('" . $row['NumAbrev'] . "');\">" . $row['Libelle'] . "</span></p>";
      $i++;
    }

    if ( $suite != '0' ) {
      $v = $suite - 1;
      $c = $suite - 10;
      $retour = mysql_query("SELECT `NumAbrev` FROM `Abreviations` LIMIT " . $v . ",1");
      $row2 = mysql_fetch_array($retour);
      if ( $row2['NumAbrev'] != "" ) {
        $contenu .= "<img class=\"direction\" style=\"left: 40px;\" src=\"./images/precedent.png\" onClick=\"traitement(); xajax_listerCategories('" . $c . "');\">";
      }
    }

    $a = $suite + 11;
    $b = $suite + 10;
    $retour = mysql_query("SELECT `NumAbrev` FROM `Abreviations` LIMIT " . $a . ",1");
    $row = mysql_fetch_array($retour);
    if ( $row['NumAbrev'] != "" ) {
      $contenu .= "<img class=\"direction\" style=\"left: 460px;\" src=\"./images/suivant.png\" onClick=\"traitement(); xajax_listerCategories('" . $b . "');\">";
    }

    $objResponse->assign("categories","innerHTML", $contenu);
    $objResponse->script("$('#traitement').css('display', 'none')");
    mysql_close();
    return $objResponse;
  }

  /* LISTER LES RECETTES */
  function listerRecettes($cat, $suite = '0') {
    $objResponse = new xajaxResponse();

    mysql_connect(MYSQL_HOST, MYSQL_LOGIN, MYSQL_PWD);
    mysql_select_db('recettes');
    $retour = mysql_query("SELECT `NumRecette` FROM `RECETTES_CATEGORIES` WHERE `NumAbrev` = '" . $cat . "' LIMIT " . $suite . ",10");
    $i = 0;

    while ( $row = mysql_fetch_array($retour) ) {
      $retour2 = mysql_query("SELECT `TitreRecette` FROM `RECETTES` WHERE `NumRecette` = '" . $row['NumRecette'] . "'");
      $row2 = mysql_fetch_array($retour2);
      $classe = ($i % 2) ? ' class="paire"' : '';
      $contenu .= "<p" . $classe . "><span onClick=\"traitement(); go('recette&idrecette=" . $row['NumRecette'] . "');\">" . $row2['TitreRecette'] . "</span></p>";
      $i++;
    }
    if ( $suite != '0' ) {
      $c = $suite - 10;
      $v = $suite - 1;
      $retour = mysql_query("SELECT `NumRecette` FROM `RECETTES_CATEGORIES` WHERE `NumAbrev` = '" . $cat . "' LIMIT " . $v . ",1");
      $row2 = mysql_fetch_array($retour);
      if ( $row2['NumRecette'] != "" ) {
        $contenu .= "<img class=\"direction\" style=\"left: 40px;\" src=\"./images/precedent.png\" onClick=\"traitement(); xajax_listerRecettes($cat, '" . $c . "');\">";
      }
    }

    $a = $suite + 11;
    $b = $suite + 10;
    $retour = mysql_query("SELECT `NumRecette` FROM `RECETTES_CATEGORIES` WHERE `NumAbrev` = '" . $cat . "' LIMIT " . $a . ",1");
    $row = mysql_fetch_array($retour);
    if ( $row['NumRecette'] != "" ) {
      $contenu .= "<img class=\"direction\" style=\"left: 460px;\" src=\"./images/suivant.png\" onClick=\"traitement(); xajax_listerRecettes($cat, '" . $b . "');\">";
    }

    $objResponse->assign("categories","innerHTML", $contenu);
    $objResponse->script("$('#traitement').css('display', 'none')");
    mysql_close();
    return $objResponse;
  }

  mysql_connect(MYSQL_HOST, MYSQL_LOGIN, MYSQL_PWD);
  mysql_select_db('recettes');

  $retour = mysql_query("SELECT `NumAbrev`,`Libelle` FROM `Abreviations` LIMIT 0,10");
  mysql_close();
  $i = 0;

  while ( $row = mysql_fetch_array($retour) ) {

    $classe = ($i % 2) ? ' class="paire"' : '';
    $_XTemplate->assign('PAIRE', $classe);
    $_XTemplate->assign('IDCATEGORIE', $row['NumAbrev']);
    $_XTemplate->assign('CATEGORIE', $row['Libelle']);
    $_XTemplate->parse('main.CATEGORIES');
    $i++;
  }

?>
