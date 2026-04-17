<?php
require_once '../db/db_connection.php';

$data = json_decode(file_get_contents("php://input"), true);
$method = $data['real_method'] ?? $_SERVER['REQUEST_METHOD'];

switch ($method) {

    case 'GET': // READ
        // Kohteet
        $sql_kohteet = "SELECT t.kohde_id, 
                               t.nimi, 
                               t.osoite, 
                               (a.etunimi || ' ' || a.sukunimi) AS asiakas_nimi,
                               t.asiakas_id
                        FROM Tyokohde t
                        JOIN Asiakas a ON t.asiakas_id = a.asiakas_id
                        ORDER BY t.nimi ASC";

        $result_kohteet = pg_query($yhteys, $sql_kohteet);
        $kohteet_data = pg_fetch_all($result_kohteet);
        
        // Asiakkaat
        $sql_asiakkaat = "SELECT asiakas_id, 
                                 (etunimi || ' ' || sukunimi) AS nimi 
                        FROM Asiakas 
                        ORDER BY sukunimi, etunimi ASC";

        $result_asiakkaat = pg_query($yhteys, $sql_asiakkaat);
        $asiakkaat_data = pg_fetch_all($result_asiakkaat);

        echo json_encode([
            'success' => true,
            'customers' => $asiakkaat_data ?: [],
            'locations' => $kohteet_data ?: []
        ]);
        break;

    case 'POST': // CREATE
        $sql = "INSERT INTO Tyokohde (nimi, osoite, asiakas_id)
                VALUES ($1, $2, $3)";
        pg_query_params($yhteys, $sql, [
            $data['nimi'],
            $data['osoite'],
            $data['asiakas_id']
        ]);
        echo json_encode(['success' => true]);
        break;

    case 'PUT': // UPDATE
        $sql = "UPDATE Tyokohde
                SET nimi = $1,
                    osoite = $2,
                    asiakas_id = $3,
                    muokattu = CURRENT_TIMESTAMP
                WHERE kohde_id = $4";

        pg_query_params($yhteys, $sql, [
            $data['nimi'],
            $data['osoite'],
            $data['asiakas_id'],
            $data['kohde_id']
        ]);
        echo json_encode(['success' => true]);
        break;
}
pg_close($yhteys);
?>