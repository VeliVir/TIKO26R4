<?php
ob_start();
require_once '../db/db_connection.php';

$data = json_decode(file_get_contents("php://input"), true);
$method = $data['real_method'] ?? $_SERVER['REQUEST_METHOD'];

switch ($method) {
    case 'GET':
        $sql_tarvikkeet = "SELECT ta.tarvike_id,
                                  ta.toimittaja_id,
                                  ta.nimi,
                                  ta.merkki,
                                  ta.yksikko,
                                  ta.hankintahinta,
                                  ta.varastossa,
                                  ta.alv,
                                  tj.nimi AS toimittaja_nimi
                           FROM Tarvike ta
                           JOIN Toimittaja tj ON tj.toimittaja_id = ta.toimittaja_id
                           WHERE ta.poistettu IS NULL
                           ORDER BY ta.nimi ASC";

        $result = pg_query($yhteys, $sql_tarvikkeet);
        if (!$result) {
            ob_clean();
            echo json_encode(['success' => false, 'error' => pg_last_error($yhteys)]);
            break;
        }
        $tarvikkeet = pg_fetch_all($result) ?: [];

        foreach ($tarvikkeet as &$row) {
            $row['hankintahinta'] = floatval($row['hankintahinta']);
            $alv_multiplier = floatval($row['alv']);
            $row['alv_prosentti'] = (int)round(($alv_multiplier - 1) * 100);
            $row['kokonaishinta'] = $row['hankintahinta'] * $alv_multiplier;
            $row['varastossa'] = (int)$row['varastossa'];
        }
        unset($row);

        $sql_toimittajat = "SELECT toimittaja_id, nimi FROM Toimittaja ORDER BY nimi ASC";
        $result_t = pg_query($yhteys, $sql_toimittajat);
        if (!$result_t) {
            ob_clean();
            echo json_encode(['success' => false, 'error' => pg_last_error($yhteys)]);
            break;
        }
        $toimittajat = pg_fetch_all($result_t) ?: [];

        ob_clean();
        echo json_encode([
            'success' => true,
            'accessories' => $tarvikkeet,
            'suppliers' => $toimittajat
        ]);
        break;

    case 'POST':
        $alv = 1 + ($data['alv_prosentti'] / 100);
        $sql = "INSERT INTO Tarvike (toimittaja_id, nimi, merkki, yksikko, hankintahinta, varastossa, alv)
                VALUES ($1, $2, $3, $4, $5, $6, $7)";
        $result = pg_query_params($yhteys, $sql, [
            $data['toimittaja_id'],
            $data['nimi'],
            $data['merkki'] ?? null,
            $data['yksikko'] ?? null,
            $data['hankintahinta'],
            $data['varastossa'] ?? 0,
            $alv
        ]);
        ob_clean();
        echo json_encode(['success' => (bool)$result]);
        break;

    case 'PUT':
        $alv = 1 + ($data['alv_prosentti'] / 100);
        $sql = "UPDATE Tarvike
                SET toimittaja_id = $1,
                    nimi = $2,
                    merkki = $3,
                    yksikko = $4,
                    hankintahinta = $5,
                    varastossa = $6,
                    alv = $7
                WHERE tarvike_id = $8";
        $result = pg_query_params($yhteys, $sql, [
            $data['toimittaja_id'],
            $data['nimi'],
            $data['merkki'] ?? null,
            $data['yksikko'] ?? null,
            $data['hankintahinta'],
            $data['varastossa'] ?? 0,
            $alv,
            $data['tarvike_id']
        ]);
        ob_clean();
        echo json_encode(['success' => (bool)$result]);
        break;
}
pg_close($yhteys);
?>
