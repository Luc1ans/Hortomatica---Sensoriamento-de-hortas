<?php
require_once '../Controller/Database.php'; 

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!isset($_FILES['csvFile']) || $_FILES['csvFile']['error'] !== UPLOAD_ERR_OK) {
        die("Erro ao enviar o arquivo CSV.");
    }

    $csvFile = $_FILES['csvFile']['tmp_name'];
    $handle = fopen($csvFile, "r");
    if (!$handle) {
        die("Não foi possível abrir o arquivo.");
    }

    $header = fgets($handle);

    $deviceLine = fgets($handle);
    $deviceParts = explode(":", $deviceLine);
    if (count($deviceParts) < 2) {
        die("ID do dispositivo não encontrado no arquivo CSV.");
    }
    $idDispositivo = trim($deviceParts[1]);

    $idHorta = $_POST['idHorta'] ?? $_GET['idHorta'] ?? null;

    $pdo = Database::connect();

    $query = "
        SELECT CONCAT(data_leitura, ' ', hora_leitura) AS ultima_leitura 
        FROM LeituraSensores 
        WHERE Dispositivo_idDispositivo = :idDispositivo
        ORDER BY CONCAT(data_leitura, ' ', hora_leitura) DESC 
        LIMIT 1
    ";
    $stmtLast = $pdo->prepare($query);
    $stmtLast->execute([':idDispositivo' => $idDispositivo]);
    $row = $stmtLast->fetch(PDO::FETCH_ASSOC);
    if ($row && !empty($row['ultima_leitura'])) {
        $ultimaLeitura = new DateTime($row['ultima_leitura']);
    } else {
        $ultimaLeitura = null;
    }

    $stmt = $pdo->prepare("
        INSERT INTO LeituraSensores 
            (data_leitura, hora_leitura, nome_sensor, valor_leitura, Dispositivo_idDispositivo, fonte)
        VALUES 
            (:data_leitura, :hora_leitura, :nome_sensor, :valor_leitura, :Dispositivo_idDispositivo, 'CSV')
    ");

    while (($line = fgets($handle)) !== false) {
        $line = trim($line);
        if (empty($line)) {
            continue;
        }

        $linhaPartes = explode(',', $line);
        if (count($linhaPartes) < 2) {
            continue;
        }

        $dataLeitura = trim($linhaPartes[0]);
        $restante = trim($linhaPartes[1]);
        if (strpos($restante, 'hora:') !== false) {
            list(, $resto) = explode('hora:', $restante, 2);
            $subPartes = explode(' ', trim($resto), 2);
            $horaLeitura = $subPartes[0];
            $infoSensor = isset($subPartes[1]) ? trim($subPartes[1]) : '';
        } else {
            continue;
        }

        $dataHoraCSV = new DateTime(date("Y-m-d H:i:s", strtotime($dataLeitura . ' ' . $horaLeitura)));
        if ($ultimaLeitura !== null && $dataHoraCSV <= $ultimaLeitura) {
            continue;
        }

        $sensores = explode(',', $infoSensor);
        foreach ($sensores as $sensorInfo) {
            $parts = explode(':', $sensorInfo, 2);
            if (count($parts) < 2) {
                continue;
            }
            $nomeSensor = trim($parts[0]);
            $valorSensor = trim($parts[1]);
            $valorFormatado = str_replace(['%', 'C'], '', $valorSensor);
            if (is_numeric($valorFormatado)) {
                $valorFormatado = (float) $valorFormatado;
            }

            // Insere a leitura no banco
            $stmt->execute([
                ':data_leitura'              => date("Y-m-d", strtotime($dataLeitura)),
                ':hora_leitura'              => date("H:i:s", strtotime($horaLeitura)),
                ':nome_sensor'               => $nomeSensor,
                ':valor_leitura'             => $valorFormatado,
                ':Dispositivo_idDispositivo' => $idDispositivo
            ]);
        }
    }

    fclose($handle);

    header("Location: ../View/AnaliseDados.php?idHorta=$idHorta&imported=1&dispositivos[]=1");
    exit();
}
?>
