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
                              l.Pvm,
                              l.erapaiva,
                              l.maksupaiva,
                              (a.etunimi || ' ' || a.sukunimi) AS asiakas_nimi,
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

        echo json_encode([
            'success' => true,
            'invoices' => $laskut_data ?: []
        ]);
        break;

    /*case 'POST': // CREATE
        $sql = "INSERT INTO Lasku (sopimus_id, edellinen_lasku_id, pvm, erapaiva, maksupaiva)
                VALUES ($1, $2, $3, $4, $5)";
        pg_query_params($yhteys, $sql, [
            $data['sopimus_id'],
            $data['edellinen_lasku_id'],
            $data['pvm'],
            $data['erapaiva'],
            $data['maksupaiva']
        ]);
        echo json_encode(['success' => true]);
        break;

    case 'PUT': // UPDATE
        $sql = "UPDATE Lasku
                SET sopimus_id = $1,
                    edellinen_lasku_id = $2,
                    pvm = $3,
                    erapaiva = $4,
                    maksupaiva = $5,
                    muokattu = CURRENT_TIMESTAMP
                WHERE lasku_id = $6";

        pg_query_params($yhteys, $sql, [
            $data['sopimus_id'],
            $data['edellinen_lasku_id'],
            $data['pvm'],
            $data['erapaiva'],
            $data['maksupaiva'],
            $data['lasku_id']
        ]);
        echo json_encode(['success' => true]);
        break;*/
}
pg_close($yhteys);
?>