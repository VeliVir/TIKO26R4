<?php
require_once '../db/db_connection.php';

$data = json_decode(file_get_contents("php://input"), true);
$action = $data['real_method'] ?? $_SERVER['REQUEST_METHOD'];

switch ($action) {

    case 'POST': // CREATE
        $sql = "INSERT INTO Tyokohde (nimi, osoite, asiakas_id)
                VALUES ($1, $2, $3)";
        pg_query_params($yhteys, $sql, [
            $data['nimi'],
            $data['osoite'],
            $data['asiakas_id']
        ]);
        echo json_encode(['success' => true]);
        break;

    case 'PUT': // UPDATE
        $sql = "UPDATE Tyokohde
                SET nimi = $1,
                    osoite = $2,
                    asiakas_id = $3,
                    muokattu = CURRENT_TIMESTAMP
                WHERE kohde_id = $4";

        pg_query_params($yhteys, $sql, [
            $data['nimi'],
            $data['osoite'],
            $data['asiakas_id'],
            $data['kohde_id']
        ]);
        echo json_encode(['success' => true]);
        break;
}