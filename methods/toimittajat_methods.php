<?php
require_once '../db/db_connection.php';

$data = json_decode(file_get_contents("php://input"), true);
$method = $data['real_method'] ?? $_SERVER['REQUEST_METHOD'];

switch ($method) {
    case 'GET':
        if (isset($_GET['toimittaja_id'])) {
            $toimittaja_id = (int)$_GET['toimittaja_id'];
            $sql_products = "SELECT ta.nimi AS tarvike_nimi,
                                    ta.yksikko,
                                    tk.nimi AS kohde_nimi,
                                    a.etunimi || ' ' || a.sukunimi AS asiakas_nimi,
                                    st.maara
                             FROM Tarvike ta
                             JOIN Sopimus_tarvike st ON st.tarvike_id = ta.tarvike_id
                             JOIN Sopimus s ON s.sopimus_id = st.sopimus_id
                             JOIN Tyokohde tk ON tk.kohde_id = s.kohde_id
                             JOIN Asiakas a ON a.asiakas_id = tk.asiakas_id
                             WHERE ta.toimittaja_id = $1 AND ta.poistettu IS NULL
                             ORDER BY ta.nimi ASC, tk.nimi ASC";
            $result_p = pg_query_params($yhteys, $sql_products, [$toimittaja_id]);
            if (!$result_p) {
                echo json_encode(['success' => false, 'error' => pg_last_error($yhteys)]);
                break;
            }
            $products = pg_fetch_all($result_p) ?: [];
            echo json_encode(['success' => true, 'products' => $products]);
            break;
        }

        $sql = "SELECT t.toimittaja_id,
                       t.nimi,
                       t.osoite,
                       COUNT(ta.tarvike_id) AS tarvike_maara
                FROM Toimittaja t
                LEFT JOIN Tarvike ta ON ta.toimittaja_id = t.toimittaja_id
                GROUP BY t.toimittaja_id, t.nimi, t.osoite
                ORDER BY t.nimi ASC";

        $result = pg_query($yhteys, $sql);
        $rows = pg_fetch_all($result);

        if ($rows) {
            foreach ($rows as &$row) {
                $row['tarvike_maara'] = (int)$row['tarvike_maara'];
            }
        }

        echo json_encode([
            'success' => true,
            'suppliers' => $rows ?: []
        ]);
        break;

    case 'POST':
        pg_query($yhteys, "BEGIN");
        $sql = "INSERT INTO Toimittaja (nimi, osoite) VALUES ($1, $2)";
        $result = pg_query_params($yhteys, $sql, [
            $data['nimi'],
            $data['osoite'] ?? null
        ]);
        if ($result) {
            pg_query($yhteys, "COMMIT");
            echo json_encode(['success' => true]);
        } else {
            pg_query($yhteys, "ROLLBACK");
            echo json_encode(['success' => false, 'error' => pg_last_error($yhteys)]);
        }
        break;

    case 'PUT':
        pg_query($yhteys, "BEGIN");
        $sql = "UPDATE Toimittaja SET nimi = $1, osoite = $2 WHERE toimittaja_id = $3";
        $result = pg_query_params($yhteys, $sql, [
            $data['nimi'],
            $data['osoite'] ?? null,
            $data['toimittaja_id']
        ]);
        if ($result) {
            pg_query($yhteys, "COMMIT");
            echo json_encode(['success' => true]);
        } else {
            pg_query($yhteys, "ROLLBACK");
            echo json_encode(['success' => false, 'error' => pg_last_error($yhteys)]);
        }
        break;

    case 'DELETE':
        pg_query($yhteys, "BEGIN");
        $result = pg_query_params($yhteys,
            "DELETE FROM Toimittaja WHERE toimittaja_id = $1",
            [$data['toimittaja_id']]);
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
