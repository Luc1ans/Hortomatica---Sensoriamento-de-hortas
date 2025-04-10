<?php
require_once __DIR__ . '/../Controller/Database.php';

class CanteiroController
{
    private $pdo;

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    // Cria um novo canteiro vinculado a uma horta
    public function createCanteiro($idHorta, $cultura, $dataPlantio, $dataColheitaPrevista)
    {
        try {
            $stmt = $this->pdo->prepare("
                INSERT INTO Canteiro 
                (idHorta, cultura, data_plantio, data_colheita_prevista) 
                VALUES (?, ?, ?, ?)
            ");

            $success = $stmt->execute([
                $idHorta,
                $cultura,
                $dataPlantio,
                $dataColheitaPrevista
            ]);

            return $success ? $this->pdo->lastInsertId() : false;
        } catch (PDOException $e) {
            error_log("Erro ao criar canteiro: " . $e->getMessage());
            return false;
        }
    }

    // Obtém todos os canteiros de uma horta
    public function getCanteirosByHorta($idHorta)
    {
        try {
            $stmt = $this->pdo->prepare("
                SELECT * FROM Canteiro 
                WHERE idHorta = ?
            ");
            $stmt->execute([$idHorta]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Erro ao buscar canteiros: " . $e->getMessage());
            return [];
        }
    }

    // Obtém um canteiro específico pelo ID
    public function getCanteiroById($idCanteiro)
    {
        try {
            $stmt = $this->pdo->prepare("
                SELECT * FROM Canteiro 
                WHERE idCanteiro = ?
            ");
            $stmt->execute([$idCanteiro]);
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Erro ao buscar canteiro: " . $e->getMessage());
            return false;
        }
    }

    // Atualiza um canteiro existente
    public function updateCanteiro($idCanteiro, $cultura, $dataPlantio, $dataColheitaPrevista)
    {
        try {
            $stmt = $this->pdo->prepare("
                UPDATE Canteiro SET
                    cultura = ?,
                    data_plantio = ?,
                    data_colheita_prevista = ?
                WHERE idCanteiro = ?
            ");

            return $stmt->execute([
                $cultura,
                $dataPlantio,
                $dataColheitaPrevista,
                $idCanteiro
            ]);
        } catch (PDOException $e) {
            error_log("Erro ao atualizar canteiro: " . $e->getMessage());
            return false;
        }
    }

    // Exclui um canteiro
    public function deleteCanteiro($idCanteiro)
    {
        try {
            $stmt = $this->pdo->prepare("
                DELETE FROM Canteiro 
                WHERE idCanteiro = ?
            ");
            return $stmt->execute([$idCanteiro]);
        } catch (PDOException $e) {
            error_log("Erro ao excluir canteiro: " . $e->getMessage());
            return false;
        }
    }

    // Vincula um dispositivo a um canteiro
    public function linkDispositivo($idCanteiro, $idDispositivo)
    {
        try {
            $stmt = $this->pdo->prepare("
                UPDATE Dispositivo 
                SET idCanteiro = ?
                WHERE idDispositivo = ?
            ");
            return $stmt->execute([$idCanteiro, $idDispositivo]);
        } catch (PDOException $e) {
            error_log("Erro ao vincular dispositivo: " . $e->getMessage());
            return false;
        }
    }

    // Remove vínculo de um dispositivo
    public function unlinkDispositivo($idDispositivo)
    {
        try {
            $stmt = $this->pdo->prepare("
                UPDATE Dispositivo 
                SET idCanteiro = NULL 
                WHERE idDispositivo = ?
            ");
            return $stmt->execute([$idDispositivo]);
        } catch (PDOException $e) {
            error_log("Erro ao desvincular dispositivo: " . $e->getMessage());
            return false;
        }
    }

    // Obtém todos dispositivos de um canteiro
    public function getDispositivosByCanteiro($idCanteiro)
    {
        try {
            $stmt = $this->pdo->prepare("
                SELECT * FROM Dispositivo 
                WHERE idCanteiro = ?
            ");
            $stmt->execute([$idCanteiro]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Erro ao buscar dispositivos: " . $e->getMessage());
            return [];
        }
    }
}