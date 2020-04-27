<script type="text/javascript">

function receptionmodule(cle,valeur) {

  if ( cle == 'fete' ) {
    $('#fete').text(valeur);
  }

}

$(document).ready(function() {
  $('#meteo').jdigiclock({
    clockImagesPath: './lib/meteo/images/clock/',
    weatherImagesPath: './lib/meteo/images/weather/',
    weatherLocationCode: 'EUR|BE|BE003|BRUXELLES',
    weatherUpdate: '30'
  });
});

</script> 
