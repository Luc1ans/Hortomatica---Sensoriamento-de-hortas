<?php
require_once __DIR__ . '/../Controller/Database.php';


class Horta
{
    private $db;

    public function __construct($pdo)
    {
        $this->db = $pdo;
    }

    public function createHorta($nomeHorta, $observacoes, $usuarioId)
    {
        $sql = "INSERT INTO Horta (nome_horta, observacoes, Usuario_idUsuario) 
                VALUES (:nome_horta, :observacoes, :Usuario_idUsuario)";
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':nome_horta', $nomeHorta);
        $stmt->bindParam(':observacoes', $observacoes);
        $stmt->bindParam(':Usuario_idUsuario', $usuarioId);
        return $stmt->execute();
    }

    public function getHortasByUsuario($usuarioId)
    {
        $sql = "SELECT * FROM Horta WHERE Usuario_idUsuario = :usuario_id";
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':usuario_id', $usuarioId);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function deleteHorta($idHorta)
    {
        $sql = "DELETE FROM Horta WHERE idHorta = :id";
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':id', $idHorta);
        return $stmt->execute();
    }
    public function updateHorta($idHorta, $nomeHorta, $observacoes)
    {
        $sql = "UPDATE Horta SET nome_horta = :nome_horta, observacoes = :observacoes WHERE idHorta = :idHorta";
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':idHorta', $idHorta);
        $stmt->bindParam(':nome_horta', $nomeHorta);
        $stmt->bindParam(':observacoes', $observacoes);
        return $stmt->execute();
    }
}
?>