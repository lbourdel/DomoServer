<script type="text/javascript">

function receptionmodule(cle,valeur) {

  if ( cle == 'sonde' ) {
    sonde(valeur);
  }

}


function sonde(data) {
  tab = data.split(',');
  idsonde = tab[0];
  valeur = tab[1];
  if ( idsonde == '7F8785020000' ) {
    $('#mezanine').text(valeur);
  }
  if ( idsonde == '746085020000' ) {
    $('#entree').text(valeur);
  }
}

</script>
