<?php
class Admin {
    private $conn;
    private $table_name = "usuarios_admin";

    public $id;
    public $nome_usuario;
    public $senha;
    public $funcao;
    public $criado_em;

    public function __construct($db) {
        $this->conn = $db;
    }

    // Criar admin
    function create() {
        $query = "INSERT INTO " . $this->table_name . " 
                  (nome_usuario, senha, funcao) 
                  VALUES (:nome_usuario, :senha, :funcao)";

        $stmt = $this->conn->prepare($query);

        // Sanitizar
        $this->nome_usuario = htmlspecialchars(strip_tags($this->nome_usuario));
        $this->senha = password_hash($this->senha, PASSWORD_DEFAULT);
        $this->funcao = htmlspecialchars(strip_tags($this->funcao));

        // Bind values
        $stmt->bindParam(":nome_usuario", $this->nome_usuario);
        $stmt->bindParam(":senha", $this->senha);
        $stmt->bindParam(":funcao", $this->funcao);

        if($stmt->execute()) {
            $this->id = $this->conn->lastInsertId();
            return true;
        }

        return false;
    }

    // Login admin
    function login($nome_usuario, $senha) {
        $query = "SELECT id, nome_usuario, senha, funcao, criado_em 
                  FROM " . $this->table_name . " 
                  WHERE nome_usuario = :nome_usuario";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":nome_usuario", $nome_usuario);
        $stmt->execute();

        if($stmt->rowCount() > 0) {
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if(password_verify($senha, $row['senha'])) {
                $this->id = $row['id'];
                $this->nome_usuario = $row['nome_usuario'];
                $this->funcao = $row['funcao'];
                $this->criado_em = $row['criado_em'];
                return true;
            }
        }

        return false;
    }

    // Obter todos os admins
    function readAll() {
        $query = "SELECT id, nome_usuario, funcao, criado_em 
                  FROM " . $this->table_name . " 
                  ORDER BY criado_em DESC";

        $stmt = $this->conn->prepare($query);
        $stmt->execute();

        return $stmt;
    }

    // Excluir admin
    function delete() {
        $query = "DELETE FROM " . $this->table_name . " WHERE id = :id";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":id", $this->id);

        if($stmt->execute()) {
            return true;
        }

        return false;
    }

    // Verificar se nome de usuário existe
    function usernameExists() {
        $query = "SELECT id FROM " . $this->table_name . " 
                  WHERE nome_usuario = :nome_usuario";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":nome_usuario", $this->nome_usuario);
        $stmt->execute();

        return $stmt->rowCount() > 0;
    }
}
?>