<?php

class UsuarioController {
    private $db;

    public function __construct($db) {
        $this->db = $db;
    }

    public function register($usuario, $email, $senha, $telefone, $tipo_usuario = 'comum') {
        $stmt = $this->db->prepare("SELECT * FROM Usuario WHERE email = :email");
        $stmt->bindParam(':email', $email);
        $stmt->execute();
        $existingUser = $stmt->fetch();

        if ($existingUser) {
            return "E-mail já está cadastrado!";
        }

        $senhaHash = password_hash($senha, PASSWORD_DEFAULT);

        $stmt = $this->db->prepare("INSERT INTO Usuario (usuario, email, senha, telefone, tipo_usuario) 
                                    VALUES (:usuario, :email, :senha, :telefone, :tipo_usuario)");
        $stmt->bindParam(':usuario', $usuario);
        $stmt->bindParam(':email', $email);
        $stmt->bindParam(':senha', $senhaHash);
        $stmt->bindParam(':telefone', $telefone);
        $stmt->bindParam(':tipo_usuario', $tipo_usuario);

        if ($stmt->execute()) {
            header("Location: login.php?success=1"); // Redireciona para login com sucesso
            exit();
        } else {
            return "Erro ao cadastrar usuário!";
        }
    }

    public function login($email, $senha) {
        $stmt = $this->db->prepare("SELECT * FROM Usuario WHERE email = :email");
        $stmt->bindParam(':email', $email);
        $stmt->execute();
        $usuario = $stmt->fetch();

        if (!$usuario) {
            return "Usuário não encontrado!";
        }

        if (!password_verify($senha, $usuario['senha'])) {
            return "Senha inválida!";
        }
        session_start();
        $_SESSION['idUsuario'] = $usuario['idUsuario'];
        $_SESSION['tipo_usuario'] = $usuario['tipo_usuario'];

        header("Location: index.php"); 
        exit();
    }
}
