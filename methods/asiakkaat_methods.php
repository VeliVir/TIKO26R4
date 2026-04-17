<?php
require_once '../db/db_connection.php';

$data = json_decode(file_get_contents("php://input"), true);
$method = $data['real_method'] ?? $_SERVER['REQUEST_METHOD'];

switch ($method) {
    
    case 'GET': // READ
        // Asiakkaat
        $sql_asiakkaat = "SELECT asiakas_id, 
                                 (etunimi || ' ' || sukunimi) AS nimi,
                                 puhelinnro,
                                 sahkoposti,
                                 osoite
                        FROM Asiakas 
                        ORDER BY sukunimi, etunimi ASC";

        $result_asiakkaat = pg_query($yhteys, $sql_asiakkaat);
        $asiakkaat_data = pg_fetch_all($result_asiakkaat);

        // Kohteet
        $sql_kohteet = "SELECT kohde_id, asiakas_id, nimi, osoite FROM Tyokohde";
        $result_kohteet = pg_query($yhteys, $sql_kohteet);
        $kohteet_data = pg_fetch_all($result_kohteet);

        echo json_encode([
            'success' => true,
            'customers' => $asiakkaat_data ?: [],
            'locations' => $kohteet_data ?: []
        ]);
        break;

    case 'POST': // CREATE
        $sql = "INSERT INTO Asiakas (etunimi, sukunimi, puhelinnro, sahkoposti)
                VALUES ($1, $2, $3, $4)";
        pg_query_params($yhteys, $sql, [
            $data['etunimi'],
            $data['sukunimi'],
            $data['puhelinnro'],
            $data['sahkoposti']
        ]);
        echo json_encode(['success' => true]);
        break;

    case 'PUT': // UPDATE
        $sql = "UPDATE Asiakas
                SET etunimi = $1,
                    sukunimi = $2,
                    puhelinnro = $3,
                    sahkoposti = $4,
                    osoite = $5,
                    muokattu = CURRENT_TIMESTAMP
                WHERE asiakas_id = $6";

        pg_query_params($yhteys, $sql, [
            $data['etunimi'],
            $data['sukunimi'],
            $data['puhelinnro'],
            $data['sahkoposti'],
            $data['osoite'],
            $data['asiakas_id'],
            $data['kohde_id']
        ]);
        echo json_encode(['success' => true]);
        break;
}
pg_close($yhteys);
?>