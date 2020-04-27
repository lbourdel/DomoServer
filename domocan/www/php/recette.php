<?php


  $titre = "Gestion de la musique";

  /* CONNEXION BDD */
  mysql_connect(MYSQL_HOST, MYSQL_LOGIN, MYSQL_PWD);
  mysql_select_db('recettes');

  /* RECUPERATION DE LA RECETTE ET AFFICHAGE */
  $retour = mysql_query("SELECT `TxtRecette` FROM `RECETTES` WHERE `NumRecette` = '" . $_GET['idrecette'] . "'");
  $row = mysql_fetch_array($retour);
  mysql_close();

  /* NETTOYAGE ET MISE EN FORME */
  $texte = str_replace('.', '.<br/><br/>', $row['TxtRecette']);
  $texte = str_replace('€', '', $texte);

  $_XTemplate->assign('TEXTERECETTE', $texte);

?>
