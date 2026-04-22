<?php
require_once __DIR__ . '/../config.php';

$yhteys = pg_connect($y_tiedot);

if (!$yhteys) {
    echo "Ei yhteyttä tietokantaan<br>";
} else {
    pg_query($yhteys, "SET SESSION CHARACTERISTICS AS TRANSACTION ISOLATION LEVEL REPEATABLE READ");
}
?>


