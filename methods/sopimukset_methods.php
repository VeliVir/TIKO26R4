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
                            t.varastossa,
                            st.hintatekija,
                            t.hankintahinta,
                            t.hankintahinta * 1.25 AS myyntihinta,
                            t.alv
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
                            s.suoritus_id,
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
        $tarvike_alv_sql = getTarvikeALVSumSQL();

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
                a.sahkoposti,
                a.puhelinnro,
                t.osoite,
                a.osoite AS a_osoite,
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

    case 'POST': // CREATE / UPDATE
        $sopimus_id = $data['sopimus_id'] ?? null;
        $tyyppi = $data['tyyppi'];
        $osia_laskussa = $data['osia_laskussa'];
        $kohde_id = $data['kohde_id'];

        // Estää virheiden tapauksissa "roskadatan" tietokannassa
        pg_query($yhteys, "BEGIN");

        try {
            if ($sopimus_id) {
                // MUOKKAUS
                $sql_sopimus = "UPDATE Sopimus SET tyyppi = $1, osia_laskussa = $2, kohde_id = $3 WHERE sopimus_id = $4";
                pg_query_params($yhteys, $sql_sopimus, [$tyyppi, $osia_laskussa, $kohde_id, $sopimus_id]);

                // Palautetaan vanhat tarvikkeet varastoon ennen poistoa
                $res_vanhat = pg_query_params($yhteys,
                    "SELECT tarvike_id, maara FROM Sopimus_tarvike WHERE sopimus_id = $1",
                    [$sopimus_id]
                );
                while ($vanha = pg_fetch_assoc($res_vanhat)) {
                    pg_query_params($yhteys,
                        "UPDATE Tarvike SET varastossa = varastossa + $1 WHERE tarvike_id = $2",
                        [(int)$vanha['maara'], $vanha['tarvike_id']]
                    );
                }
                
                // Tyhjennetään vanhat rivit suoritus ja tarvike liitostauluista
                pg_query_params($yhteys, "DELETE FROM Sopimus_tarvike WHERE sopimus_id = $1", [$sopimus_id]);
                pg_query_params($yhteys, "DELETE FROM Sopimus_suoritus WHERE sopimus_id = $1", [$sopimus_id]);
            } else {
                // UUSI
                $sql_sopimus = "INSERT INTO Sopimus (tyyppi, osia_laskussa, kohde_id) VALUES ($1, $2, $3) RETURNING sopimus_id";
                $res = pg_query_params($yhteys, $sql_sopimus, [$tyyppi, $osia_laskussa, $kohde_id]);
                $row = pg_fetch_assoc($res);
                $sopimus_id = $row['sopimus_id'];
            }

            // Tarvikkeet + tarkistaa onko varastossa tarpeeksi
            if (!empty($data['tarvikkeet'])) {
                $vajeet = [];

                foreach ($data['tarvikkeet'] as $t) {
                    $res = pg_query_params($yhteys, 
                        "SELECT nimi, varastossa FROM Tarvike WHERE tarvike_id = $1", 
                        [$t['tarvike_id']]
                    );
                    $tarvike = pg_fetch_assoc($res);

                    if ($tarvike['varastossa'] < $t['maara']) {
                        $vajeet[] = [
                            'nimi'       => $tarvike['nimi'],
                            'varastossa' => $tarvike['varastossa'],
                            'vaadittu'   => $t['maara']
                        ];
                    }
                    $hintatekija = 1 + ($t['alennus'] / 100);
                    $sql_t = "INSERT INTO Sopimus_tarvike (sopimus_id, tarvike_id, maara, hintatekija) VALUES ($1, $2, $3, $4)";
                    pg_query_params($yhteys, $sql_t, [$sopimus_id, $t['tarvike_id'], $t['maara'], $hintatekija]);
                }

                if (!empty($vajeet)) {
                    pg_query($yhteys, "ROLLBACK");
                    echo json_encode([
                        'success'       => false,
                        'varastovirhe'  => true,
                        'vajeet'        => $vajeet
                    ]);
                    break;
                }

                foreach ($data['tarvikkeet'] as $t) {
                    $sql_tarv = "UPDATE Tarvike SET varastossa = varastossa - $1 WHERE tarvike_id = $2";
                    pg_query_params($yhteys, $sql_tarv, [$t['maara'], $t['tarvike_id']]);
                }
            }

            if (!empty($data['tyot'])) {
                foreach ($data['tyot'] as $tyo) {
                    if ($tyyppi === 'Urakka') {
                        $sql_w = "INSERT INTO Sopimus_suoritus (sopimus_id, suoritus_id, urakka_hinta) VALUES ($1, $2, $3)";
                        pg_query_params($yhteys, $sql_w, [$sopimus_id, $tyo['suoritus_id'], $tyo['urakka_hinta']]);
                    } else {
                        $hintatekija = 1 + ($tyo['alennus'] / 100);
                        $sql_w = "INSERT INTO Sopimus_suoritus (sopimus_id, suoritus_id, tyomaara_tunneilla, hintatekija) VALUES ($1, $2, $3, $4)";
                        pg_query_params($yhteys, $sql_w, [$sopimus_id, $tyo['suoritus_id'], $tyo['maara'], $hintatekija]);
                    }
                }
            }
            pg_query($yhteys, "COMMIT");
            echo json_encode(['success' => true, 'sopimus_id' => $sopimus_id]);

        } catch (Exception $e) {
            pg_query($yhteys, "ROLLBACK");
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        }
        break;

    case 'DELETE':
        $sopimus_id = $data['sopimus_id'];
        pg_query($yhteys, "BEGIN");
        pg_query_params($yhteys, "UPDATE Lasku SET edellinen_lasku_id = NULL WHERE edellinen_lasku_id IN (SELECT lasku_id FROM Lasku WHERE sopimus_id = $1)", [$sopimus_id]);
        pg_query_params($yhteys, "DELETE FROM Lasku WHERE sopimus_id = $1", [$sopimus_id]);
        pg_query_params($yhteys, "DELETE FROM Sopimus_suoritus WHERE sopimus_id = $1", [$sopimus_id]);
        pg_query_params($yhteys, "DELETE FROM Sopimus_tarvike WHERE sopimus_id = $1", [$sopimus_id]);
        $result = pg_query_params($yhteys, "DELETE FROM Sopimus WHERE sopimus_id = $1", [$sopimus_id]);
        if ($result) {
            pg_query($yhteys, "COMMIT");
            echo json_encode(['success' => true]);
        } else {
            pg_query($yhteys, "ROLLBACK");
            echo json_encode(['success' => false, 'error' => pg_last_error($yhteys)]);
        }
        break;
}
pg_close($yhteys);
?>