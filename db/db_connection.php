<?php
require_once '../../config.php';

$yhteys = pg_connect($y_tiedot);

if (!$yhteys) {
    echo "Ei yhteyttä tietokantaan<br>";
}
?>


