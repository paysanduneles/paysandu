<?php
class Product {
    private $conn;
    private $table_name = "produtos";

    public $id;
    public $nome;
    public $preco;
    public $categoria;
    public $descricao;
    public $eh_porcionado;
    public $eh_personalizado;
    public $criado_em;

    public function __construct($db) {
        $this->conn = $db;
    }

    // Criar produto
    function create() {
        $query = "INSERT INTO " . $this->table_name . " 
                  (nome, preco, categoria, descricao, eh_porcionado, eh_personalizado) 
                  VALUES (:nome, :preco, :categoria, :descricao, :eh_porcionado, :eh_personalizado)";

        $stmt = $this->conn->prepare($query);

        // Sanitizar
        $this->nome = htmlspecialchars(strip_tags($this->nome));
        $this->categoria = htmlspecialchars(strip_tags($this->categoria));
        $this->descricao = htmlspecialchars(strip_tags($this->descricao));
        $this->eh_porcionado = $this->eh_porcionado ?? false;
        $this->eh_personalizado = $this->eh_personalizado ?? true;

        // Bind values
        $stmt->bindParam(":nome", $this->nome);
        $stmt->bindParam(":preco", $this->preco);
        $stmt->bindParam(":categoria", $this->categoria);
        $stmt->bindParam(":descricao", $this->descricao);
        $stmt->bindParam(":eh_porcionado", $this->eh_porcionado, PDO::PARAM_BOOL);
        $stmt->bindParam(":eh_personalizado", $this->eh_personalizado, PDO::PARAM_BOOL);

        if($stmt->execute()) {
            $this->id = $this->conn->lastInsertId();
            return true;
        }

        return false;
    }

    // Ler todos os produtos
    function readAll() {
        $query = "SELECT id, nome, preco, categoria, descricao, eh_porcionado, eh_personalizado, criado_em 
                  FROM " . $this->table_name . " 
                  ORDER BY categoria, nome";

        $stmt = $this->conn->prepare($query);
        $stmt->execute();

        return $stmt;
    }

    // Ler um produto
    function readOne() {
        $query = "SELECT id, nome, preco, categoria, descricao, eh_porcionado, eh_personalizado, criado_em 
                  FROM " . $this->table_name . " 
                  WHERE id = :id";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":id", $this->id);
        $stmt->execute();

        if($stmt->rowCount() > 0) {
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            
            $this->nome = $row['nome'];
            $this->preco = $row['preco'];
            $this->categoria = $row['categoria'];
            $this->descricao = $row['descricao'];
            $this->eh_porcionado = $row['eh_porcionado'];
            $this->eh_personalizado = $row['eh_personalizado'];
            $this->criado_em = $row['criado_em'];
            return true;
        }

        return false;
    }

    // Atualizar produto
    function update() {
        $query = "UPDATE " . $this->table_name . " 
                  SET nome = :nome, preco = :preco, categoria = :categoria, 
                      descricao = :descricao, eh_porcionado = :eh_porcionado 
                  WHERE id = :id";

        $stmt = $this->conn->prepare($query);

        // Sanitizar
        $this->nome = htmlspecialchars(strip_tags($this->nome));
        $this->categoria = htmlspecialchars(strip_tags($this->categoria));
        $this->descricao = htmlspecialchars(strip_tags($this->descricao));
        $this->eh_porcionado = $this->eh_porcionado ?? false;

        // Bind values
        $stmt->bindParam(":nome", $this->nome);
        $stmt->bindParam(":preco", $this->preco);
        $stmt->bindParam(":categoria", $this->categoria);
        $stmt->bindParam(":descricao", $this->descricao);
        $stmt->bindParam(":eh_porcionado", $this->eh_porcionado, PDO::PARAM_BOOL);
        $stmt->bindParam(":id", $this->id);

        if($stmt->execute()) {
            return true;
        }

        return false;
    }

    // Excluir produto
    function delete() {
        $query = "DELETE FROM " . $this->table_name . " WHERE id = :id AND eh_personalizado = true";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":id", $this->id);

        if($stmt->execute()) {
            return true;
        }

        return false;
    }
}
?>