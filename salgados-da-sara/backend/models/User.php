<?php
class User {
    private $conn;
    private $table_name = "usuarios";

    public $id;
    public $nome;
    public $telefone;
    public $email;
    public $endereco;
    public $numero;
    public $complemento;
    public $cidade;
    public $senha;
    public $eh_admin;
    public $criado_em;

    public function __construct($db) {
        $this->conn = $db;
    }

    // Criar usuário
    function create() {
        $query = "INSERT INTO " . $this->table_name . " 
                  (nome, telefone, email, endereco, numero, complemento, cidade, senha, eh_admin) 
                  VALUES (:nome, :telefone, :email, :endereco, :numero, :complemento, :cidade, :senha, :eh_admin)";

        $stmt = $this->conn->prepare($query);

        // Sanitizar
        $this->nome = htmlspecialchars(strip_tags($this->nome));
        $this->telefone = htmlspecialchars(strip_tags($this->telefone));
        $this->email = htmlspecialchars(strip_tags($this->email));
        $this->endereco = htmlspecialchars(strip_tags($this->endereco));
        $this->numero = htmlspecialchars(strip_tags($this->numero));
        $this->complemento = htmlspecialchars(strip_tags($this->complemento));
        $this->cidade = htmlspecialchars(strip_tags($this->cidade));
        $this->senha = password_hash($this->senha, PASSWORD_DEFAULT);
        $this->eh_admin = $this->eh_admin ?? false;

        // Bind values
        $stmt->bindParam(":nome", $this->nome);
        $stmt->bindParam(":telefone", $this->telefone);
        $stmt->bindParam(":email", $this->email);
        $stmt->bindParam(":endereco", $this->endereco);
        $stmt->bindParam(":numero", $this->numero);
        $stmt->bindParam(":complemento", $this->complemento);
        $stmt->bindParam(":cidade", $this->cidade);
        $stmt->bindParam(":senha", $this->senha);
        $stmt->bindParam(":eh_admin", $this->eh_admin, PDO::PARAM_BOOL);

        if($stmt->execute()) {
            $this->id = $this->conn->lastInsertId();
            return true;
        }

        return false;
    }

    // Login usuário
    function login($telefone, $senha) {
        $query = "SELECT id, nome, telefone, email, endereco, numero, complemento, cidade, senha, eh_admin, criado_em 
                  FROM " . $this->table_name . " 
                  WHERE telefone = :telefone";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":telefone", $telefone);
        $stmt->execute();

        if($stmt->rowCount() > 0) {
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if(password_verify($senha, $row['senha'])) {
                $this->id = $row['id'];
                $this->nome = $row['nome'];
                $this->telefone = $row['telefone'];
                $this->email = $row['email'];
                $this->endereco = $row['endereco'];
                $this->numero = $row['numero'];
                $this->complemento = $row['complemento'];
                $this->cidade = $row['cidade'];
                $this->eh_admin = $row['eh_admin'];
                $this->criado_em = $row['criado_em'];
                return true;
            }
        }

        return false;
    }

    // Verificar se usuário existe
    function userExists() {
        $query = "SELECT id FROM " . $this->table_name . " 
                  WHERE telefone = :telefone OR email = :email";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":telefone", $this->telefone);
        $stmt->bindParam(":email", $this->email);
        $stmt->execute();

        return $stmt->rowCount() > 0;
    }

    // Obter usuário por ID
    function readOne() {
        $query = "SELECT id, nome, telefone, email, endereco, numero, complemento, cidade, eh_admin, criado_em 
                  FROM " . $this->table_name . " 
                  WHERE id = :id";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":id", $this->id);
        $stmt->execute();

        if($stmt->rowCount() > 0) {
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            
            $this->nome = $row['nome'];
            $this->telefone = $row['telefone'];
            $this->email = $row['email'];
            $this->endereco = $row['endereco'];
            $this->numero = $row['numero'];
            $this->complemento = $row['complemento'];
            $this->cidade = $row['cidade'];
            $this->eh_admin = $row['eh_admin'];
            $this->criado_em = $row['criado_em'];
            return true;
        }

        return false;
    }

    // Obter usuário por telefone para recuperação de senha
    function getByPhone($telefone) {
        $query = "SELECT id, nome, telefone, email FROM " . $this->table_name . " 
                  WHERE telefone = :telefone";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":telefone", $telefone);
        $stmt->execute();

        if($stmt->rowCount() > 0) {
            return $stmt->fetch(PDO::FETCH_ASSOC);
        }

        return false;
    }
}
?>