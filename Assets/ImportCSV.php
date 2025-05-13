<?php
require __DIR__ . '/../vendor/autoload.php';

use Controller\Database;

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ' . BASE_PATH . '/index.php');
    exit;
}

if (empty($_FILES['csvFile']) || $_FILES['csvFile']['error'] !== UPLOAD_ERR_OK) {
    die("Erro ao enviar o arquivo CSV.");
}

$handle = fopen($_FILES['csvFile']['tmp_name'], 'r');
if (!$handle) {
    die("Não foi possível abrir o arquivo.");
}

// Pula header
fgets($handle);

// Lê a linha do dispositivo
$deviceLine = fgets($handle);
$parts = explode(':', $deviceLine, 2);
if (count($parts) < 2) {
    die("ID do dispositivo não encontrado no CSV.");
}
$idDispositivo = trim($parts[1]);

$idHorta = $_POST['idHorta'] ?? null;
if (!$idHorta) {
    die("ID da horta não fornecido.");
}

$pdo = Database::connect();

// Recupera última leitura
$stmtLast = $pdo->prepare("
    SELECT CONCAT(data_leitura,' ',hora_leitura) AS ultima_leitura
      FROM LeituraSensores
     WHERE Dispositivo_idDispositivo = :id
     ORDER BY data_leitura DESC, hora_leitura DESC
     LIMIT 1
");
$stmtLast->execute([':id' => $idDispositivo]);
$row = $stmtLast->fetch(PDO::FETCH_ASSOC);
$ultimaLeitura = $row
    ? new DateTime($row['ultima_leitura'])
    : null;

// Prepara insert
$stmt = $pdo->prepare("
    INSERT INTO LeituraSensores
      (data_leitura, hora_leitura, nome_sensor, valor_leitura, Dispositivo_idDispositivo, fonte)
    VALUES
      (:data_leitura, :hora_leitura, :nome_sensor, :valor_leitura, :disp, 'CSV')
");

while (($raw = fgets($handle)) !== false) {
    $line = trim($raw);
    if ($line === '') {
        continue;
    }

    $cols = explode(',', $line, 2);
    if (count($cols) < 2) {
        continue;
    }
    [$dataLeitura, $rest] = $cols;
    if (strpos($rest, 'hora:') === false) {
        continue;
    }
    [, $afterHora] = explode('hora:', $rest, 2);
    $parts = explode(' ', trim($afterHora), 2);
    $horaLeitura = $parts[0];
    $infoSensor = $parts[1] ?? '';

    $tsCsv = new DateTime("$dataLeitura $horaLeitura");
    if ($ultimaLeitura && $tsCsv <= $ultimaLeitura) {
        continue;
    }

    foreach (explode(',', $infoSensor) as $sensorInfo) {
        $sensorParts = explode(':', $sensorInfo, 2) + [1 => ''];
        $nomeSensor = trim($sensorParts[0]);
        $valorSensor = trim($sensorParts[1]);
        $valorFormatado = floatval(str_replace(['%', 'C'], '', $valorSensor));

        $stmt->execute([
            ':data_leitura' => $tsCsv->format('Y-m-d'),
            ':hora_leitura' => $tsCsv->format('H:i:s'),
            ':nome_sensor' => $nomeSensor,
            ':valor_leitura' => $valorFormatado,
            ':disp' => $idDispositivo,
        ]);
    }
}

fclose($handle);
header(
    "Location: ../index.php?page=analise&idHorta={$idHorta}&imported=1&dispositivos[]={$idDispositivo}"
);
exit;
