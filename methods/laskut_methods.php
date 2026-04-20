<?php
require_once '../db/db_connection.php';
require_once 'cost_calculator.php';

$data = json_decode(file_get_contents("php://input"), true);
$method = $data['real_method'] ?? $_SERVER['REQUEST_METHOD'];

switch ($method) {
    case 'GET': // READ
        $suoritus_sql = getSuoritusSumSQL();
        $tarvike_sql = getTarvikeSumSQL();

        $sql_laskut = "SELECT l.lasku_id, 
                              l.sopimus_id,
                              l.edellinen_lasku_id,
                              l.Pvm,
                              l.erapaiva,
                              l.maksupaiva,
                              (a.etunimi || ' ' || a.sukunimi) AS asiakas_nimi,
                              a.osoite AS asiakas_osoite,
                              CASE WHEN l.maksupaiva IS NOT NULL THEN true ELSE false END AS paid,
                              COALESCE(tarvike_laskenta.t_summa, 0) + COALESCE(suoritus_laskenta.s_summa, 0) AS amount
                        FROM Lasku l
                        JOIN Sopimus s ON l.sopimus_id = s.sopimus_id
                        JOIN Tyokohde t ON s.kohde_id = t.kohde_id
                        JOIN Asiakas a ON t.asiakas_id = a.asiakas_id
                        LEFT JOIN $tarvike_sql AS tarvike_laskenta 
                            ON tarvike_laskenta.sopimus_id = s.sopimus_id
                        LEFT JOIN $suoritus_sql AS suoritus_laskenta 
                            ON suoritus_laskenta.sopimus_id = s.sopimus_id
                        ORDER BY l.Pvm ASC";

        $result_laskut = pg_query($yhteys, $sql_laskut);
        $laskut_data = pg_fetch_all($result_laskut);

        // Convert 't'/'f' strings to boolean and calculate invoice numbers and types
        if ($laskut_data) {
            foreach ($laskut_data as &$row) {
                $row['paid'] = $row['paid'] === 't';
                
                // Laskun numero
                $invoice_number = 1;
                $current_id = $row['edellinen_lasku_id'];
                while ($current_id) {
                    $invoice_number++;
                    $prev_invoice = array_filter($laskut_data, function($inv) use ($current_id) {
                        return $inv['lasku_id'] == $current_id;
                    });
                    $prev_invoice = reset($prev_invoice);
                    $current_id = $prev_invoice ? $prev_invoice['edellinen_lasku_id'] : null;
                }
                $row['invoice_number'] = $invoice_number;
                
                // Laskun tyyppi
                if ($invoice_number == 1) {
                    $row['invoice_type'] = 'Lasku';
                } elseif ($invoice_number == 2) {
                    $row['invoice_type'] = 'Muistutuslasku';
                } else {
                    $row['invoice_type'] = 'Karhulasku';
                }
                
                $base_amount = floatval($row['amount']);
                
                // Laskutuslisä 5€
                $laskutuslisa = 0;
                $temp_id = $row['edellinen_lasku_id'];
                while ($temp_id) {
                    $prev = array_filter($laskut_data, function($inv) use ($temp_id) {
                        return $inv['lasku_id'] == $temp_id;
                    });
                    $prev = reset($prev);
                    if ($prev && !$prev['paid']) {
                        $laskutuslisa += 5;
                    }
                    $temp_id = $prev ? $prev['edellinen_lasku_id'] : null;
                }
                
                // Viivästyskorko: 16% jos Karhulasku
                $viivastyskorko = 0;
                if ($invoice_number >= 3) {
                    $viivastyskorko = $base_amount * 0.16;
                }
                
                $total = $base_amount + $laskutuslisa + $viivastyskorko;
                
                $row['pricing'] = [
                    'base_amount' => $base_amount,
                    'laskutuslisa' => $laskutuslisa,
                    'viivastyskorko' => $viivastyskorko,
                    'total' => $total
                ];
            }
        }

        // Fetch sopimukset with customer info
        $sql_sopimukset = "SELECT s.sopimus_id, 
                                   s.tyyppi,
                                   (a.etunimi || ' ' || a.sukunimi) AS asiakas_nimi,
                                   t.nimi AS kohde_nimi
                            FROM Sopimus s
                            JOIN Tyokohde t ON s.kohde_id = t.kohde_id
                            JOIN Asiakas a ON t.asiakas_id = a.asiakas_id
                            ORDER BY a.sukunimi, a.etunimi, t.nimi ASC";

        $result_sopimukset = pg_query($yhteys, $sql_sopimukset);
        $sopimukset_data = pg_fetch_all($result_sopimukset);

        // Fetch detailed items and work for each invoice
        $detailed_data = [];
        foreach ($laskut_data as $lasku) {
            $sopimus_id = $lasku['sopimus_id'];
            
            // Get items
            $sql_items = "SELECT t.nimi AS tarvike_nimi, 
                                 st.maara, 
                                 t.hankintahinta, 
                                 st.hintatekija, 
                                 (st.maara * t.hankintahinta * st.hintatekija) AS kokonaishinta
                         FROM Sopimus_tarvike st
                         JOIN Tarvike t ON t.tarvike_id = st.tarvike_id
                         WHERE st.sopimus_id = $1";
            $result_items = pg_query_params($yhteys, $sql_items, [$sopimus_id]);
            $items = pg_fetch_all($result_items) ?: [];
            
            // Get work
            $sql_work = "SELECT s.nimi AS suoritus_nimi, 
                                ss.tyomaara_tunneilla, 
                                s.hinta AS tuntiveloitus, 
                                ss.urakka_hinta, 
                                ss.hintatekija,
                                (
                                    CASE 
                                        WHEN ss.urakka_hinta IS NOT NULL 
                                            THEN ss.urakka_hinta
                                        ELSE ss.tyomaara_tunneilla * s.hinta
                                    END
                                ) * ss.hintatekija AS kokonaishinta
                        FROM Sopimus_suoritus ss
                        JOIN Suoritus s ON s.suoritus_id = ss.suoritus_id
                        WHERE ss.sopimus_id = $1";
            $result_work = pg_query_params($yhteys, $sql_work, [$sopimus_id]);
            $work = pg_fetch_all($result_work) ?: [];
            
            $detailed_data[$lasku['lasku_id']] = [
                'items' => $items,
                'work' => $work
            ];
        }

        echo json_encode([
            'success' => true,
            'invoices' => $laskut_data ?: [],
            'sopimukset' => $sopimukset_data ?: [],
            'details' => $detailed_data
        ]);
        break;

    
    case 'POST': // CREATE
        $sql = "INSERT INTO Lasku (sopimus_id, edellinen_lasku_id, pvm, erapaiva, maksupaiva)
                VALUES ($1, $2, $3, $4, $5)";
        pg_query_params($yhteys, $sql, [
            $data['sopimus_id'],
            $data['edellinen_lasku_id'] ?? null,
            $data['pvm'],
            $data['erapaiva'],
            $data['maksupaiva'] ?? null
        ]);
        echo json_encode(['success' => true]);
        break;

    case 'PUT': // UPDATE
        $sql = "UPDATE Lasku
                SET sopimus_id = $1,
                    edellinen_lasku_id = $2,
                    pvm = $3,
                    erapaiva = $4,
                    maksupaiva = $5
                WHERE lasku_id = $6";

        pg_query_params($yhteys, $sql, [
            $data['sopimus_id'],
            $data['edellinen_lasku_id'] ?: null,
            $data['pvm'],
            $data['erapaiva'],
            $data['maksupaiva'] ?: null,
            $data['lasku_id']
        ]);
        echo json_encode(['success' => true]);
        break;

    case 'DELETE':
        $lasku_id = $data['lasku_id'];
        pg_query($yhteys, "BEGIN");
        pg_query_params($yhteys, "UPDATE Lasku SET edellinen_lasku_id = NULL WHERE edellinen_lasku_id = $1", [$lasku_id]);
        $result = pg_query_params($yhteys, "DELETE FROM Lasku WHERE lasku_id = $1", [$lasku_id]);
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