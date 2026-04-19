<?php
ob_start();
require_once '../db/db_connection.php';

$data = json_decode(file_get_contents("php://input"), true);
$method = $data['real_method'] ?? $_SERVER['REQUEST_METHOD'];

switch ($method) {

    case 'GET':
        $sql_asiakkaat = "SELECT asiakas_id,
                                 (etunimi || ' ' || sukunimi) AS nimi,
                                 etunimi,
                                 sukunimi,
                                 puhelinnro,
                                 sahkoposti,
                                 osoite
                          FROM Asiakas
                          ORDER BY sukunimi, etunimi ASC";

        $result_asiakkaat = pg_query($yhteys, $sql_asiakkaat);
        if (!$result_asiakkaat) {
            ob_clean();
            echo json_encode(['success' => false, 'error' => pg_last_error($yhteys)]);
            break;
        }
        $asiakkaat_data = pg_fetch_all($result_asiakkaat) ?: [];

        $sql_kohteet = "SELECT kohde_id, asiakas_id, nimi, osoite FROM Tyokohde ORDER BY nimi ASC";
        $result_kohteet = pg_query($yhteys, $sql_kohteet);
        if (!$result_kohteet) {
            ob_clean();
            echo json_encode(['success' => false, 'error' => pg_last_error($yhteys)]);
            break;
        }
        $kohteet_data = pg_fetch_all($result_kohteet) ?: [];

        ob_clean();
        echo json_encode([
            'success' => true,
            'customers' => $asiakkaat_data,
            'locations' => $kohteet_data
        ]);
        break;

    case 'POST':
        $sql = "INSERT INTO Asiakas (etunimi, sukunimi, puhelinnro, sahkoposti, osoite)
                VALUES ($1, $2, $3, $4, $5)";
        $result = pg_query_params($yhteys, $sql, [
            $data['etunimi'],
            $data['sukunimi'],
            $data['puhelinnro'],
            $data['sahkoposti'],
            $data['osoite'] ?? null
        ]);
        ob_clean();
        echo json_encode(['success' => (bool)$result]);
        break;

    case 'PUT':
        $sql = "UPDATE Asiakas
                SET etunimi = $1,
                    sukunimi = $2,
                    puhelinnro = $3,
                    sahkoposti = $4,
                    osoite = $5,
                    muokattu = CURRENT_TIMESTAMP
                WHERE asiakas_id = $6";
        $result = pg_query_params($yhteys, $sql, [
            $data['etunimi'],
            $data['sukunimi'],
            $data['puhelinnro'],
            $data['sahkoposti'],
            $data['osoite'] ?? null,
            $data['asiakas_id']
        ]);
        ob_clean();
        echo json_encode(['success' => (bool)$result]);
        break;
}
pg_close($yhteys);
?>
