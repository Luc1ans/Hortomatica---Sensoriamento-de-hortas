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

// Verifica se dispositivo existe
$stmtCheck = $pdo->prepare("SELECT idDispositivo FROM Dispositivo WHERE idDispositivo = ?");
$stmtCheck->execute([$idDispositivo]);
if (!$stmtCheck->fetch()) {
    die("Dispositivo $idDispositivo não existe");
}

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
      (hora_leitura, data_leitura, nome_sensor, valor_leitura, Dispositivo_idDispositivo, fonte)
    VALUES
      (:hora_leitura, :data_leitura, :nome_sensor, :valor_leitura, :disp, 'CSV')
");

$linhasInseridas = 0;
$contadorLinhas = 0;

while (($raw = fgets($handle)) !== false) {
    $contadorLinhas++;
    $line = trim($raw);
    if ($line === '') continue;

    // Formato esperado: "15/5/2025, hora: 8:23:30 Nome Sensor: Valor"
    $cols = explode(',', $line, 2);
    if (count($cols) < 2) {
        error_log("Linha $contadorLinhas: Formato inválido - $line");
        continue;
    }
    
    $dataLeitura = trim($cols[0]);
    $rest = trim($cols[1]);

    // Extração robusta usando regex (permite espaço após "hora:")
    if (!preg_match('/hora:\s*(\d{1,2}:\d{1,2}:\d{1,2})\s+(.+)/', $rest, $matches)) {
        error_log("Linha $contadorLinhas: Padrão de hora não encontrado - $rest");
        continue;
    }
    
    $horaLeitura = trim($matches[1]);
    $sensorInfo = trim($matches[2]);

    // Parse correto da data/hora (formato brasileiro)
    $tsCsv = DateTime::createFromFormat('d/m/Y H:i:s', "$dataLeitura $horaLeitura");
    if (!$tsCsv) {
        error_log("Linha $contadorLinhas: Formato de data inválido - $dataLeitura $horaLeitura");
        continue;
    }

    // Verifica se é mais recente que última leitura
    if ($ultimaLeitura && $tsCsv <= $ultimaLeitura) {
        continue;
    }

    // Cada linha contém APENAS UM sensor - formato: "Nome Sensor: Valor"
    $sensorParts = explode(':', $sensorInfo, 2);
    if (count($sensorParts) < 2) {
        error_log("Linha $contadorLinhas: Formato de sensor inválido - $sensorInfo");
        continue;
    }
    
    $nomeSensor = trim($sensorParts[0]);
    $valorSensor = trim($sensorParts[1]);

    // Limpeza do valor numérico (preserva números, pontos e negativos)
    $valorFormatado = preg_replace('/[^\d\.\-]/', '', $valorSensor);
    $valorFormatado = $valorFormatado !== '' ? floatval($valorFormatado) : 0;

    try {
        $stmt->execute([
            ':data_leitura' => $tsCsv->format('Y-m-d'),
            ':hora_leitura' => $tsCsv->format('H:i:s'),
            ':nome_sensor' => $nomeSensor,
            ':valor_leitura' => $valorFormatado,
            ':disp' => $idDispositivo,
        ]);
        $linhasInseridas++;
    } catch (PDOException $e) {
        error_log("Erro na inserção (Linha $contadorLinhas): " . $e->getMessage());
    }
}

fclose($handle);

// Redireciona com feedback
$parametros = http_build_query([
    'page' => 'analise',
    'idHorta' => $idHorta,
    'imported' => $linhasInseridas,
    'dispositivos[]' => $idDispositivo
]);

header("Location: ../index.php?$parametros");
exit;