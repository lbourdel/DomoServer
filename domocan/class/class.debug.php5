<?php

class debug {

  /*
    DEBUG
  */
  function envoyer($niveau, $titre, $arg) {
    if ( $niveau <= DEBUG ) {
      echo "[" . date('d/m/y : H:i:s') . "] - DEBUG " . $niveau . " : " . $titre . " : " . $arg . "\n";
    }
  }

}

?>
