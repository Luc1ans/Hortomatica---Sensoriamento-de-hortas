<?php
header('Content-Type: application/json');

$servername = "localhost";
$username = "root";       
$password = "root";           
$dbname = "dbhortomatica";    

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die(json_encode(["status" => "erro", "mensagem" => "Falha na conexão com o banco de dados: " . $conn->connect_error]));
}

$data = json_decode(file_get_contents('php://input'), true);

if (isset($data["idDispositivo"], $data["data"], $data["hora"], $data["GPSLongitude"], $data["GPSLatitude"], 
          $data["umidadeSolo"], $data["umidadeAr"], $data["temperatura"], $data["chuvaDigital"], $data["chuvaAnalogico"])) {
    
    // ID do dispositivo
    $idDispositivo = $conn->real_escape_string($data["id_dispositivo"]);

    // Data e Hora da leitura
    $data_leitura = $conn->real_escape_string($data["data"]);
    $hora_leitura = $conn->real_escape_string($data["hora"]);

    // Localização concatenada (Longitude, Latitude)
    $gpsLongitude = $conn->real_escape_string($data["GPSLongitude"]);
    $gpsLatitude = $conn->real_escape_string($data["GPSLatitude"]);
    $localizacao = "$gpsLongitude,$gpsLatitude";

    // Verifica se o dispositivo já existe
    $checkDeviceQuery = "SELECT idDispositivo FROM dispositivo WHERE idDispositivo = '$id_dispositivo'";
    $result = $conn->query($checkDeviceQuery);

    if ($result->num_rows == 0) {
        // Insere na tabela dispositivo se não existir
        $sql_dispositivo = "INSERT INTO dispositivo (idDispositivo, Localizacao) VALUES ('$id_dispositivo', '$localizacao')";
        if (!$conn->query($sql_dispositivo)) {
            die(json_encode(["status" => "erro", "mensagem" => "Erro ao inserir dispositivo: " . $conn->error]));
        }
    }

    // Insere os dados na tabela leitura_sensores para cada sensor
    $sensores = [
        "Umidade do Solo" => $data["umidadeSolo"],
        "Umidade do Ar" => $data["umidadeAr"],
        "Temperatura" => $data["temperatura"],
        "Chuva Digital" => $data["chuvaDigital"],
        "Chuva Analógico" => $data["chuvaAnalogico"]
    ];

    foreach ($sensores as $nome_sensor => $valor_leitura) {
        $nome_sensor_esc = $conn->real_escape_string($nome_sensor);
        $valor_leitura_esc = $conn->real_escape_string($valor_leitura);

        $sql_leitura = "INSERT INTO leitura_sensores (data_leitura, hora_leitura, nome_sensor, valor_leitura) 
                        VALUES ('$data_leitura', '$hora_leitura', '$nome_sensor_esc', '$valor_leitura_esc')";

        if (!$conn->query($sql_leitura)) {
            die(json_encode(["status" => "erro", "mensagem" => "Erro ao inserir leitura do sensor $nome_sensor: " . $conn->error]));
        }
    }

    echo json_encode(["status" => "sucesso", "mensagem" => "Dados inseridos com sucesso"]);
} else {
    echo json_encode(["status" => "erro", "mensagem" => "Dados incompletos"]);
}

$conn->close();
?>
