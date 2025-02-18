<?php
header('Content-Type: application/json');

$servername = "localhost";
$username = "root";       
$password = "root";           
$dbname = "hortadb";    

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die(json_encode(["status" => "erro", "mensagem" => "Falha na conexão com o banco de dados: " . $conn->connect_error]));
}

$data = json_decode(file_get_contents('php://input'), true);

if (isset($data["data"], $data["hora"], $data["gpsInfo"], $data["umidadeSolo"], $data["umidadeAr"], $data["temperatura"], $data["chuvaDigital"], $data["chuvaAnalogico"])) {
    $data_str = $conn->real_escape_string($data["data"]);
    $hora = $conn->real_escape_string($data["hora"]);
    $gpsInfo = $conn->real_escape_string($data["gpsInfo"]);
    $umidadeSolo = $conn->real_escape_string($data["umidadeSolo"]);
    $umidadeAr = $conn->real_escape_string($data["umidadeAr"]);
    $temperatura = $conn->real_escape_string($data["temperatura"]);
    $chuvaDigital = $conn->real_escape_string($data["chuvaDigital"]);
    $chuvaAnalogico = $conn->real_escape_string($data["chuvaAnalogico"]);

    $sql = "INSERT INTO leitura_sensores (data, hora, gpsInfo, umidadeSolo, umidadeAr, temperatura, chuvaDigital, chuvaAnalogico)
            VALUES ('$data_str', '$hora', '$gpsInfo', '$umidadeSolo', '$umidadeAr', '$temperatura', '$chuvaDigital', '$chuvaAnalogico')";

    if ($conn->query($sql) === TRUE) {
        echo json_encode(["status" => "sucesso", "mensagem" => "Dados inseridos com sucesso"]);
    } else {
        echo json_encode(["status" => "erro", "mensagem" => "Erro ao inserir dados: " . $conn->error]);
    }
} else {
    echo json_encode(["status" => "erro", "mensagem" => "Dados incompletos"]);
}

$conn->close();
?>
