<?php

class Canteiro
{
    private $pdo;

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    // Cria um novo canteiro vinculado a uma horta
    public function createCanteiro($idHorta, $culturaArray, $dataPlantioArray, $dataColheitaArray)
    {
        try {
            $this->pdo->beginTransaction();

            foreach ($culturaArray as $index => $cultura) {

                $dataPlantio = $dataPlantioArray[$index] ?? null;
                $dataColheita = $dataColheitaArray[$index] ?? null;

                $stmt = $this->pdo->prepare("
                   INSERT INTO canteiros 
                    (horta_idHorta, Cultura, DataPlantio, DataColheira)
                    VALUES (?, ?, ?, ?)
                ");

                if (
                    !$stmt->execute([
                        $idHorta,
                        $cultura,
                        $dataPlantio,
                        $dataColheita
                    ])
                ) {
                    $this->pdo->rollBack();

                    return false;
                }
            }

            $this->pdo->commit();
            return true;
        } catch (PDOException $e) {
            $this->pdo->rollBack();
            echo "Erro ao criar canteiro: " . $e->getMessage();
            return false;
        }
    }

    // Obtém todos os canteiros de uma horta
    public function getCanteirosByHorta($idHorta)
    {
        try {
            $stmt = $this->pdo->prepare("
                SELECT * FROM canteiros 
                WHERE horta_idHorta = ?
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
                WHERE idCanteiro = :canteido_id
            ");
            $stmt->execute([
                'canteiro_id' => $idCanteiro
            ]);
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Erro ao buscar canteiro: " . $e->getMessage());
            return false;
        }
    }

    // Atualiza um canteiro existente
    public function updateCanteiro($idCanteiro, $Cultura, $DataPlantio, $DataColheita)
    {
        try {
            $stmt = $this->pdo->prepare("
                UPDATE canteiros SET
                    Cultura = ?,
                    DataPlantio = ?,
                    DataColheira = ?
                WHERE idCanteiros = ?
            ");

            return $stmt->execute([
                $Cultura,
                $DataPlantio,
                $DataColheita,
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
            $this->pdo->beginTransaction();
            $stmt1 = $this->pdo->prepare("
            UPDATE Dispositivo
               SET canteiro_id = NULL
             WHERE canteiro_id = ?
        ");
            $stmt1->execute([$idCanteiro]);
            $stmt2 = $this->pdo->prepare("
            DELETE FROM canteiros
             WHERE idCanteiros = ?
        ");
            $stmt2->execute([$idCanteiro]);

            $this->pdo->commit();
            return true;
        } catch (PDOException $e) {
            $this->pdo->rollBack();
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
            SET canteiro_id = :canteiro_id
            WHERE idDispositivo = :idDispositivo
        ");
            return $stmt->execute([
                ':canteiro_id' => $idCanteiro,
                ':idDispositivo' => $idDispositivo
            ]);
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
            SET canteiro_id = NULL
            WHERE idDispositivo = :idDispositivo
        ");
            return $stmt->execute([':idDispositivo' => $idDispositivo]);
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
            WHERE canteiro_id = :canteiro_id
        ");
            $stmt->execute([':canteiro_id' => $idCanteiro]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Erro ao buscar dispositivos: " . $e->getMessage());
            return [];
        }
    }
}

?>