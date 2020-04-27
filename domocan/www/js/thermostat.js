<script type="text/javascript">

function receptionmodule(cle,valeur) {

  if ( cle == 'sonde' ) {
    sonde(valeur);
  }
  if ( cle == 'chaudiere' ) {
    chaudiere(valeur);
  }
  if ( cle == 'temperaturevoulue' ) {
    temperaturevoulue(valeur);
  }

}


function sonde(data) {
  tab = data.split(',');
  idsonde = tab[0];
  valeur = tab[1];
  $('#' + idsonde).text(valeur);
  xajax_moyenne();
}

function chaudiere(data) {
  $('#chaudiere').text(data);
}

function temperaturevoulue(data) {
  $('#temperature').text(data);
}

</script>
