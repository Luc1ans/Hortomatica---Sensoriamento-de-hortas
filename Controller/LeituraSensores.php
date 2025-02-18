<?php
require_once 'Database.php';

class LeituraSensores
{
    private $conn;

    public function __construct()
    {
        $this->conn = Database::connect();
    }

    public function getAllLeituras()
    {
        $query = "SELECT * FROM LeituraSensores";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function addLeitura($hora_leitura, $data_leitura, $nome_sensor, $valor_leitura, $dispositivo_id)
    {
        $query = "INSERT INTO LeituraSensores (hora_leitura, data_leitura, nome_sensor, valor_leitura, Dispositivo_idDispositivo) VALUES (:hora_leitura, :data_leitura, :nome_sensor, :valor_leitura, :dispositivo_id)";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':hora_leitura', $hora_leitura);
        $stmt->bindParam(':data_leitura', $data_leitura);
        $stmt->bindParam(':nome_sensor', $nome_sensor);
        $stmt->bindParam(':valor_leitura', $valor_leitura);
        $stmt->bindParam(':dispositivo_id', $dispositivo_id);
        return $stmt->execute();
    }

    public function getLeiturasByDispositivo($idDispositivo, $sensor = '', $dataInicial = '', $dataFinal = '')
    {
        $query = "SELECT * FROM LeituraSensores WHERE Dispositivo_idDispositivo = :idDispositivo";

        // Adiciona o filtro de sensor apenas se ele não estiver vazio
        if (!empty($sensor)) {
            $query .= " AND nome_sensor = :sensor";
        }

        // Adiciona o filtro de data
        if (!empty($dataInicial) && !empty($dataFinal)) {
            $query .= " AND data_leitura BETWEEN :dataInicial AND :dataFinal";
        } elseif (!empty($dataInicial)) {
            $query .= " AND data_leitura >= :dataInicial";
        } elseif (!empty($dataFinal)) {
            $query .= " AND data_leitura <= :dataFinal";
        }

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':idDispositivo', $idDispositivo);

        // Bind do filtro de sensor apenas se ele não estiver vazio
        if (!empty($sensor)) {
            $stmt->bindParam(':sensor', $sensor);
        }

        // Bind dos filtros de data
        if (!empty($dataInicial)) {
            $stmt->bindParam(':dataInicial', $dataInicial);
        }
        if (!empty($dataFinal)) {
            $stmt->bindParam(':dataFinal', $dataFinal);
        }

        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    public function getUltimasLeituras($idDispositivo, $limit = 5)
    {
        $query = "SELECT * FROM LeituraSensores WHERE Dispositivo_idDispositivo = :idDispositivo ORDER BY data_leitura DESC, hora_leitura DESC LIMIT :limit";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':idDispositivo', $idDispositivo);
        $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
?>