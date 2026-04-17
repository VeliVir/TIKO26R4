<?php
require_once '../db/db_connection.php';
require_once 'cost_calculator.php';

$data = json_decode(file_get_contents("php://input"), true);
$method = $data['real_method'] ?? $_SERVER['REQUEST_METHOD'];

switch ($method) {
    
    case 'GET': // READ

        // Asiakkaat
        $sql_asiakkaat = "SELECT asiakas_id, 
                                (etunimi || ' ' || sukunimi) AS nimi
                        FROM Asiakas 
                        ORDER BY sukunimi, etunimi ASC";

        $result_asiakkaat = pg_query($yhteys, $sql_asiakkaat);
        $asiakkaat_data = pg_fetch_all($result_asiakkaat);

        if (!$asiakkaat_data) {
            $asiakkaat_data = [];
        }

        // Kohteet
        $sql_kohteet = "SELECT kohde_id, 
                            asiakas_id, 
                            nimi, 
                            osoite 
                        FROM Tyokohde";

        $result_kohteet = pg_query($yhteys, $sql_kohteet);
        $kohteet_data = pg_fetch_all($result_kohteet);

        if (!$kohteet_data) {
            $kohteet_data = [];
        }
        
        // Sopimukset
        $tarvike_sql = getTarvikeSumSQL();
        $suoritus_sql = getSuoritusSumSQL();

        $sql_sopimukset = "
            SELECT s.sopimus_id,
                s.kohde_id,
                s.tyyppi,
                s.osia_laskussa,
                s.luotu,
                s.muokattu,
                t.nimi AS kohde_nimi,
                a.etunimi || ' ' || a.sukunimi AS asiakas_nimi,
                (tarvike_laskenta.t_summa + suoritus_laskenta.s_summa) AS kokonaishinta
            FROM Sopimus s
            JOIN Tyokohde t ON t.kohde_id = s.kohde_id
            JOIN Asiakas a ON a.asiakas_id = t.asiakas_id
            LEFT JOIN $tarvike_sql AS tarvike_laskenta 
                ON tarvike_laskenta.sopimus_id = s.sopimus_id
            LEFT JOIN $suoritus_sql AS suoritus_laskenta 
                ON suoritus_laskenta.sopimus_id = s.sopimus_id
            ORDER BY s.luotu DESC
        ";

        $result_sopimukset = pg_query($yhteys, $sql_sopimukset);
        $sopimukset_data = pg_fetch_all($result_sopimukset);

        if (!$sopimukset_data) {
            $sopimukset_data = [];
        }

        echo json_encode([
            'success' => true,
            'customers' => $asiakkaat_data,
            'locations' => $kohteet_data,
            'agreements' => $sopimukset_data
        ]);

        break;

    /*case 'POST': // CREATE
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
        break; */
}
pg_close($yhteys);
?>