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
        pg_query($yhteys, "BEGIN");
        $sql = "INSERT INTO Asiakas (etunimi, sukunimi, puhelinnro, sahkoposti, osoite)
                VALUES ($1, $2, $3, $4, $5)";
        $result = pg_query_params($yhteys, $sql, [
            $data['etunimi'],
            $data['sukunimi'],
            $data['puhelinnro'],
            $data['sahkoposti'],
            $data['osoite'] ?? null
        ]);
        if ($result) {
            pg_query($yhteys, "COMMIT");
            ob_clean();
            echo json_encode(['success' => true]);
        } else {
            pg_query($yhteys, "ROLLBACK");
            ob_clean();
            echo json_encode(['success' => false, 'error' => pg_last_error($yhteys)]);
        }
        break;

    case 'PUT':
        pg_query($yhteys, "BEGIN");
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
        if ($result) {
            pg_query($yhteys, "COMMIT");
            ob_clean();
            echo json_encode(['success' => true]);
        } else {
            pg_query($yhteys, "ROLLBACK");
            ob_clean();
            echo json_encode(['success' => false, 'error' => pg_last_error($yhteys)]);
        }
        break;

    case 'DELETE':
        $asiakas_id = $data['asiakas_id'];
        pg_query($yhteys, "BEGIN");
        $res = pg_query_params($yhteys,
            "SELECT s.sopimus_id FROM Sopimus s JOIN Tyokohde t ON s.kohde_id = t.kohde_id WHERE t.asiakas_id = $1",
            [$asiakas_id]);
        $sopimukset = pg_fetch_all($res) ?: [];
        foreach ($sopimukset as $s) {
            $sid = $s['sopimus_id'];
            pg_query_params($yhteys, "UPDATE Lasku SET edellinen_lasku_id = NULL WHERE edellinen_lasku_id IN (SELECT lasku_id FROM Lasku WHERE sopimus_id = $1)", [$sid]);
            pg_query_params($yhteys, "DELETE FROM Lasku WHERE sopimus_id = $1", [$sid]);
            pg_query_params($yhteys, "DELETE FROM Sopimus_suoritus WHERE sopimus_id = $1", [$sid]);
            pg_query_params($yhteys, "DELETE FROM Sopimus_tarvike WHERE sopimus_id = $1", [$sid]);
            pg_query_params($yhteys, "DELETE FROM Sopimus WHERE sopimus_id = $1", [$sid]);
        }
        pg_query_params($yhteys, "DELETE FROM Tyokohde WHERE asiakas_id = $1", [$asiakas_id]);
        $result = pg_query_params($yhteys, "DELETE FROM Asiakas WHERE asiakas_id = $1", [$asiakas_id]);
        if ($result) {
            pg_query($yhteys, "COMMIT");
            ob_clean();
            echo json_encode(['success' => true]);
        } else {
            pg_query($yhteys, "ROLLBACK");
            ob_clean();
            echo json_encode(['success' => false, 'error' => pg_last_error($yhteys)]);
        }
        break;
}
pg_close($yhteys);
?>
