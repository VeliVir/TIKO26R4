<?php
// HUOM!!!!! 
// Tämä skripti alustaa tietokannan ja luo tarvittavat taulut.
// HUOM!!!!!
require_once 'db_connection.php'; 

$sql_file = '../sql/schema.sql';

if (!file_exists($sql_file)) {
    echo "Tiedostoa $sql_file ei löytynyt<br>";
    exit;
}

$sql_content = file_get_contents($sql_file);

$result = pg_query($yhteys, $sql_content);

if (!$result) {
    echo "Virhe tietokannan alustuksessa: " . pg_last_error($yhteys);
} else {
    echo "Tietokanta tyhjennetty ja pöydät luotu<br>";
}

$testdata_file = '../sql/testdata.sql';

if (!file_exists($testdata_file)) {
    echo "Tiedostoa $testdata_file ei löytynyt<br>";
    exit;
}

$testdata_content = file_get_contents($testdata_file);
$result = pg_query($yhteys, $testdata_content);

if (!$result) {
    echo "Virhe testidataa lisättäessä: " . pg_last_error($yhteys);
} else {
    echo "Testidata lisätty<br>";
}

pg_close($yhteys);
?>