<?php

class UsuarioController {
    private $db;

    public function __construct($db) {
        $this->db = $db;
    }

    // Processar cadastro
    public function register($usuario, $email, $senha, $telefone, $tipo_usuario = 'comum') {
        // Verifica se o e-mail já existe
        $stmt = $this->db->prepare("SELECT * FROM Usuario WHERE email = :email");
        $stmt->bindParam(':email', $email);
        $stmt->execute();
        $existingUser = $stmt->fetch();

        if ($existingUser) {
            return "E-mail já está cadastrado!";
        }

        // Hash da senha antes de salvar
        $senhaHash = password_hash($senha, PASSWORD_DEFAULT);

        // Realiza o registro
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

    // Processar login
    public function login($email, $senha) {
        // Busca usuário pelo e-mail
        $stmt = $this->db->prepare("SELECT * FROM Usuario WHERE email = :email");
        $stmt->bindParam(':email', $email);
        $stmt->execute();
        $usuario = $stmt->fetch();

        if (!$usuario) {
            return "Usuário não encontrado!";
        }

        // Verifica a senha
        if (!password_verify($senha, $usuario['senha'])) {
            return "Senha inválida!";
        }

        // Inicia a sessão e armazena os dados do usuário
        session_start();
        $_SESSION['idUsuario'] = $usuario['idUsuario'];
        $_SESSION['tipo_usuario'] = $usuario['tipo_usuario'];

        header("Location: index.php"); // Redireciona para a página inicial
        exit();
    }
}
