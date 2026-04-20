<?php
ob_start();
require_once '../db/db_connection.php';

// For multipart/form-data (file uploads) $_POST holds form fields; for JSON requests use body
$json_input = file_get_contents("php://input");
$data   = $json_input ? json_decode($json_input, true) : null;
$method = $_POST['real_method'] ?? ($data['real_method'] ?? $_SERVER['REQUEST_METHOD']);

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
        // muokattu is updated automatically by the DB trigger trg_paivita_muokattu_tarvike
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

    case 'DELETE':
        $result = pg_query_params($yhteys,
            "UPDATE Tarvike SET poistettu = CURRENT_TIMESTAMP WHERE tarvike_id = $1",
            [$data['tarvike_id']]);
        ob_clean();
        echo json_encode(['success' => (bool)$result]);
        break;

    case 'XML_IMPORT':
        if (!isset($_FILES['xmlFile']) || $_FILES['xmlFile']['error'] !== UPLOAD_ERR_OK) {
            ob_clean();
            echo json_encode(['success' => false, 'error' => 'Tiedostolataus epäonnistui']);
            break;
        }

        libxml_use_internal_errors(true);
        $xml = simplexml_load_file($_FILES['xmlFile']['tmp_name']);

        if ($xml === false) {
            $err = libxml_get_errors();
            ob_clean();
            echo json_encode(['success' => false, 'error' => 'Virheellinen XML: ' . ($err[0]->message ?? 'tuntematon')]);
            break;
        }

        // --- Resolve supplier ---
        $toim_nimi   = trim((string)$xml->toimittaja->toim_nimi);
        $toim_osoite = trim((string)$xml->toimittaja->osoite);

        $r_toim = pg_query_params($yhteys,
            "SELECT toimittaja_id FROM Toimittaja WHERE nimi = $1",
            [$toim_nimi]);

        if (!$r_toim) {
            ob_clean();
            echo json_encode(['success' => false, 'error' => pg_last_error($yhteys)]);
            break;
        }

        $toim_row = pg_fetch_assoc($r_toim);
        if ($toim_row) {
            $toimittaja_id = (int)$toim_row['toimittaja_id'];
        } else {
            $r_ins = pg_query_params($yhteys,
                "INSERT INTO Toimittaja (nimi, osoite) VALUES ($1, $2) RETURNING toimittaja_id",
                [$toim_nimi, $toim_osoite ?: null]);
            if (!$r_ins) {
                ob_clean();
                echo json_encode(['success' => false, 'error' => pg_last_error($yhteys)]);
                break;
            }
            $toimittaja_id = (int)pg_fetch_assoc($r_ins)['toimittaja_id'];
        }

        // --- Process each tarvike ---
        $inserted  = 0;
        $updated   = 0;
        $unchanged = 0;

        foreach ($xml->tarvike as $tarvike) {
            $t      = $tarvike->ttiedot;
            $xml_id = (int)(string)$t->id;
            $nimi   = trim((string)$t->nimi);
            $merkki = trim((string)$t->merkki);
            $hinta  = floatval((string)$t->hinta);
            $yksikko = trim((string)$t->yksikko);

            // Check for an active record matching by name within the same supplier
            $r_existing = pg_query_params($yhteys,
                "SELECT tarvike_id, nimi, merkki, hankintahinta, yksikko, varastossa, alv
                 FROM Tarvike
                 WHERE nimi = $1 AND toimittaja_id = $2 AND poistettu IS NULL",
                [$nimi, $toimittaja_id]);

            if (!$r_existing) {
                ob_clean();
                echo json_encode(['success' => false, 'error' => pg_last_error($yhteys)]);
                break 2;
            }

            $existing = pg_fetch_assoc($r_existing);

            if (!$existing) {
                // Brand-new item — insert with DB-default alv (1.24)
                pg_query_params($yhteys,
                    "INSERT INTO Tarvike (toimittaja_id, nimi, merkki, yksikko, hankintahinta, varastossa)
                     VALUES ($1, $2, $3, $4, $5, 0)",
                    [$toimittaja_id, $nimi, $merkki ?: null, $yksikko ?: null, $hinta]);
                $inserted++;
            } else {
                // Compare key fields — ignore tyyppi (not in DB schema)
                $changed = (
                    $existing['nimi']   !== $nimi ||
                    ($existing['merkki']  ?? '') !== $merkki ||
                    abs(floatval($existing['hankintahinta']) - $hinta) > 0.001 ||
                    ($existing['yksikko'] ?? '') !== $yksikko
                );

                if ($changed) {
                    // Move old record to history by setting poistettu
                    pg_query_params($yhteys,
                        "UPDATE Tarvike SET poistettu = CURRENT_TIMESTAMP WHERE tarvike_id = $1",
                        [$existing['tarvike_id']]);

                    // Insert updated record, preserving stock count and alv from old row
                    pg_query_params($yhteys,
                        "INSERT INTO Tarvike (toimittaja_id, nimi, merkki, yksikko, hankintahinta, varastossa, alv)
                         VALUES ($1, $2, $3, $4, $5, $6, $7)",
                        [
                            $toimittaja_id,
                            $nimi,
                            $merkki ?: null,
                            $yksikko ?: null,
                            $hinta,
                            (int)$existing['varastossa'],
                            $existing['alv']
                        ]);
                    $updated++;
                } else {
                    $unchanged++;
                }
            }
        }

        ob_clean();
        echo json_encode([
            'success'   => true,
            'inserted'  => $inserted,
            'updated'   => $updated,
            'unchanged' => $unchanged
        ]);
        break;
}
pg_close($yhteys);
?>
