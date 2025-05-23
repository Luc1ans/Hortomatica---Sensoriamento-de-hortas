<?php
namespace Model;

use PDO;

require_once __DIR__ . '/../Controller/Database.php';

class Dispositivo
{
    private $db;

    public function __construct($pdo)
    {
        $this->db = $pdo;
    }

    public function getDispositivoById($idDispositivo)
    {
        $sql = "SELECT * FROM dispositivo WHERE idDispositivo = :id";
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':id', $idDispositivo, PDO::PARAM_INT);
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$result) {
            return ['nome_dispositivo' => 'Desconhecido'];
        }
        return $result;
    }

    public function createDispositivo($idDispositivo, $nome, $localizacao, $status, $data_instalacao, $userId)
    {
        $sql = "UPDATE dispositivo
               SET nome_dispositivo = :nome_dispositivo,
                   localizacao = :localizacao,
                   status = :status,
                   data_instalacao = :data_instalacao,
                   user_id = :user_id
               WHERE idDispositivo = :idDispositivo";

        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':idDispositivo', $idDispositivo, PDO::PARAM_INT);
        $stmt->bindParam(':nome_dispositivo', $nome, PDO::PARAM_STR);
        $stmt->bindParam(':localizacao', $localizacao, PDO::PARAM_STR);
        $stmt->bindParam(':status', $status, PDO::PARAM_STR);
        $stmt->bindParam(':data_instalacao', $data_instalacao, PDO::PARAM_STR);
        $stmt->bindParam(':user_id', $userId, PDO::PARAM_INT);

        return $stmt->execute();
    }

    public function updateDispositivo($idDispositivo, $nome, $localizacao, $status, $data_instalacao, $userId)
    {
        // Reaproveita mesma lógica de atribuição (atualização de dados)
        return $this->createDispositivo(
            $idDispositivo,
            $nome,
            $localizacao,
            $status,
            $data_instalacao,
            $userId
        );
    }

    public function getAllDispositivos()
    {
        $sql = "SELECT idDispositivo, localizacao FROM dispositivo WHERE user_id IS NULL";
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function deleteDispositivo($idDispositivo)
    {
        $sql = "UPDATE dispositivo
                SET user_id = NULL,
                    Horta_idHorta = NULL
                WHERE idDispositivo = :idDispositivo";
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':idDispositivo', $idDispositivo, PDO::PARAM_INT);
        return $stmt->execute();
    }

    public function getAllDispositivosid($userId)
    {
        $sql = "SELECT idDispositivo, nome_dispositivo, localizacao, status, data_instalacao
                FROM dispositivo
                WHERE user_id = :user_id";
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':user_id', $userId, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    public function getAvailableDispositivos($userId)
    {
        $sql = "SELECT idDispositivo, nome_dispositivo, localizacao 
                FROM dispositivo 
                WHERE user_id = :user_id 
                  AND canteiro_id IS NULL";
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':user_id', $userId, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getDispositivoByCanteiro($idCanteiros)
    {
        $sql = "SELECT * FROM dispositivo WHERE canteiro_id = :idCanteiros";
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':idCanteiros', $idCanteiros, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
?>
