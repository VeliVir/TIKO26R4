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

        // Tarvikkeet
        $sql_tarvikeet = "SELECT 
                            st.sopimus_id,
                            t.tarvike_id,
                            t.nimi,
                            t.yksikko,
                            st.maara,
                            st.hintatekija,
                            t.hankintahinta * 1.25 AS myyntihinta
                        FROM Sopimus_tarvike st
                        JOIN Tarvike t ON t.tarvike_id = st.tarvike_id";

        $result_tarvikeet = pg_query($yhteys, $sql_tarvikeet);
        $tarvikeet_data = pg_fetch_all($result_tarvikeet);

        if (!$tarvikeet_data) {
            $tarvikeet_data = [];
        }
        
        $sql_tarvikeTyypit = "SELECT tarvike_id, nimi FROM Tarvike";

        $result_tarvikeTyypit = pg_query($yhteys, $sql_tarvikeTyypit);
        $tarvikeTyypit_data = pg_fetch_all($result_tarvikeTyypit);

        if (!$tarvikeTyypit_data) {
            $tarvikeTyypit_data = [];
        }

        // Suoritukset
        $sql_suoritukset = "SELECT 
                            ss.sopimus_id,
                            s.nimi,
                            ss.tyomaara_tunneilla,
                            ss.hintatekija,
                            ss.urakka_hinta,
                            s.hinta
                        FROM Sopimus_suoritus ss
                        JOIN Suoritus s ON s.suoritus_id = ss.suoritus_id";

        $result_suoritukset = pg_query($yhteys, $sql_suoritukset);
        $suoritukset_data = pg_fetch_all($result_suoritukset);

        if (!$suoritukset_data) {
            $suoritukset_data = [];
        }

        $sql_suoritusTyypit = "SELECT suoritus_id, nimi FROM Suoritus";

        $result_suoritusTyypit = pg_query($yhteys, $sql_suoritusTyypit);
        $suoritusTyypit_data = pg_fetch_all($result_suoritusTyypit);

        if (!$suoritusTyypit_data) {
            $suoritusTyypit_data = [];
        }
        
        // Sopimukset
        $tarvike_sql = getTarvikeSumSQL();
        $suoritus_sql = getSuoritusSumSQL();
        $tarvike_alv_sql = getTarvikeSumWithAlvSQL();

        // :DDD Älkää ottako mallia
        $sql_sopimukset = "
            SELECT s.sopimus_id,
                s.kohde_id,
                s.tyyppi,
                s.osia_laskussa,
                DATE(s.luotu) AS luotu,
                DATE(s.muokattu) AS muokattu,
                t.nimi AS kohde_nimi,
                a.etunimi || ' ' || a.sukunimi AS asiakas_nimi,
                (COALESCE(tarvike_laskenta.t_summa, 0) + COALESCE(suoritus_laskenta.s_summa, 0)) AS kokonaishinta,
                (COALESCE(tarvike_alv.t_summa_alv, 0)  
                + COALESCE((suoritus_laskenta.s_summa * 0.24), 0)) AS alv,
                CASE 
                    WHEN EXISTS (
                        SELECT 1 
                        FROM Lasku l 
                        WHERE l.sopimus_id = s.sopimus_id
                    ) THEN 1
                    ELSE 0
                END AS laskutettu
            FROM Sopimus s
            JOIN Tyokohde t ON t.kohde_id = s.kohde_id
            JOIN Asiakas a ON a.asiakas_id = t.asiakas_id
            LEFT JOIN $tarvike_sql AS tarvike_laskenta 
                ON tarvike_laskenta.sopimus_id = s.sopimus_id
            LEFT JOIN $suoritus_sql AS suoritus_laskenta 
                ON suoritus_laskenta.sopimus_id = s.sopimus_id
            LEFT JOIN $tarvike_alv_sql AS tarvike_alv 
                ON tarvike_alv.sopimus_id = s.sopimus_id
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
            'agreements' => $sopimukset_data,
            'accessories' => $tarvikeet_data,
            'work' => $suoritukset_data,
            'uniqueAccessories' => $tarvikeTyypit_data,
            'uniqueWorkTypes' => $suoritusTyypit_data
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