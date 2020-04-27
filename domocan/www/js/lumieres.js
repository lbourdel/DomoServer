<script type="text/javascript">

function receptionmodule(cle, valeur) {

  if ( cle == 'modenuit' ) {
    modenuit(valeur);
  }

  if ( cle == 'lumiere' ) {
    allumage(valeur);
  }

}

function allumage(data) {
  tab = data.split(',');
  carte = tab[0];
  sortie = tab[1];
  valeur = tab[2];
  if ( valeur == 0 ) {
    img = './images/lumiere_off.png';
  } else {
    img = './images/lumiere_on.png';
  }
  $('#' + carte + '_' + sortie).attr({ src: img });
}

function modenuit(data) {
      if ( data == 'on' ) {
        $('#modenuit').text('Activé');
      } 
      else if ( data == 'off' ) {
        $('#modenuit').text('Désactivé');
      }      
  }
</script>
