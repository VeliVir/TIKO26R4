<?php
require_once '../db/db_connection.php';

$data = json_decode(file_get_contents("php://input"), true);
$method = $data['real_method'] ?? $_SERVER['REQUEST_METHOD'];

switch ($method) {
    case 'GET':
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
        $sql = "INSERT INTO Toimittaja (nimi, osoite) VALUES ($1, $2)";
        pg_query_params($yhteys, $sql, [
            $data['nimi'],
            $data['osoite'] ?? null
        ]);
        echo json_encode(['success' => true]);
        break;

    case 'PUT':
        $sql = "UPDATE Toimittaja SET nimi = $1, osoite = $2 WHERE toimittaja_id = $3";
        pg_query_params($yhteys, $sql, [
            $data['nimi'],
            $data['osoite'] ?? null,
            $data['toimittaja_id']
        ]);
        echo json_encode(['success' => true]);
        break;
}
pg_close($yhteys);
?>
