<?php
namespace Model;

use PDO;

class Leitura
{
    private PDO $conn;

    public function __construct(PDO $pdo)
    {
        $this->conn = $pdo;
    }

    /**
     * Insere uma nova leitura na tabela.
     */
    public function create(
        string $hora_leitura,
        string $data_leitura,
        string $nome_sensor,
        float  $valor_leitura,
        int    $dispositivo_id
    ): bool {
        $sql = "
            INSERT INTO LeituraSensores
               (hora_leitura, data_leitura, nome_sensor, valor_leitura, Dispositivo_idDispositivo)
            VALUES
               (:hora, :data, :sensor, :valor, :disp)
        ";
        $stmt = $this->conn->prepare($sql);
        $stmt->bindParam(':hora',    $hora_leitura);
        $stmt->bindParam(':data',    $data_leitura);
        $stmt->bindParam(':sensor',  $nome_sensor);
        $stmt->bindParam(':valor',   $valor_leitura);
        $stmt->bindParam(':disp',    $dispositivo_id, PDO::PARAM_INT);
        return $stmt->execute();
    }

    /**
     * Retorna todas as leituras, sem filtro.
     */
    public function getAll(): array
    {
        $stmt = $this->conn->query("SELECT * FROM LeituraSensores");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Retorna uma leitura específica pelo seu ID (se houver PK).
     */
    public function getById(int $idLeitura): ?array
    {
        $stmt = $this->conn->prepare(
            "SELECT * FROM LeituraSensores WHERE idLeitura = :id LIMIT 1"
        );
        $stmt->bindParam(':id', $idLeitura, PDO::PARAM_INT);
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ?: null;
    }

    /**
     * Retorna leituras de um dispositivo, com filtros opcionais de sensor e data.
     */
    public function getByDispositivo(
        int    $idDispositivo,
        string $sensor      = '',
        string $dataInicial = '',
        string $dataFinal   = ''
    ): array {
        $sql = "
          SELECT hora_leitura, data_leitura, nome_sensor, valor_leitura, Dispositivo_idDispositivo
          FROM LeituraSensores
          WHERE Dispositivo_idDispositivo = :idDisp
            AND nome_sensor NOT IN ('GPS Latitude','GPS Longitude')
        ";
        if ($sensor !== '') {
            $sql .= " AND nome_sensor = :sensor ";
        }
        if ($dataInicial !== '' && $dataFinal !== '') {
            $sql .= " AND data_leitura BETWEEN :di AND :df ";
        } elseif ($dataInicial !== '') {
            $sql .= " AND data_leitura >= :di ";
        } elseif ($dataFinal !== '') {
            $sql .= " AND data_leitura <= :df ";
        }

        $stmt = $this->conn->prepare($sql);
        $stmt->bindParam(':idDisp', $idDispositivo, PDO::PARAM_INT);
        if ($sensor      !== '') $stmt->bindParam(':sensor', $sensor);
        if ($dataInicial !== '') $stmt->bindParam(':di',      $dataInicial);
        if ($dataFinal   !== '') $stmt->bindParam(':df',      $dataFinal);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Retorna as últimas N leituras de um dispositivo.
     */
    public function getLatestByDispositivo(int $idDispositivo, int $limit = 5): array
    {
        $stmt = $this->conn->prepare(
            "SELECT * 
               FROM LeituraSensores 
               WHERE Dispositivo_idDispositivo = :idDisp 
               ORDER BY data_leitura DESC, hora_leitura DESC
               LIMIT :lim"
        );
        $stmt->bindParam(':idDisp', $idDispositivo, PDO::PARAM_INT);
        $stmt->bindParam(':lim',    $limit,          PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * (Opcional) Exclui leituras anteriores a uma data, para limpeza de tabela.
     */
    public function deleteOlderThan(string $dataLimite): bool
    {
        $stmt = $this->conn->prepare(
            "DELETE FROM LeituraSensores WHERE data_leitura < :dataLimite"
        );
        $stmt->bindParam(':dataLimite', $dataLimite);
        return $stmt->execute();
    }
}
