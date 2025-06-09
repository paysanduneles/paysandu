<?php
class Config {
    private $conn;
    private $table_name = "configuracoes_app";

    public $id;
    public $chave_config;
    public $valor_config;
    public $atualizado_em;

    public function __construct($db) {
        $this->conn = $db;
    }

    // Obter valor de configuração
    function getValue($chave) {
        $query = "SELECT valor_config FROM " . $this->table_name . " 
                  WHERE chave_config = :chave";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":chave", $chave);
        $stmt->execute();

        if($stmt->rowCount() > 0) {
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            return $row['valor_config'];
        }

        return null;
    }

    // Definir valor de configuração
    function setValue($chave, $valor) {
        // Verificar se a chave existe
        $check_query = "SELECT id FROM " . $this->table_name . " 
                        WHERE chave_config = :chave";
        $check_stmt = $this->conn->prepare($check_query);
        $check_stmt->bindParam(":chave", $chave);
        $check_stmt->execute();

        if($check_stmt->rowCount() > 0) {
            // Atualizar existente
            $query = "UPDATE " . $this->table_name . " 
                      SET valor_config = :valor, atualizado_em = CURRENT_TIMESTAMP 
                      WHERE chave_config = :chave";
        } else {
            // Inserir novo
            $query = "INSERT INTO " . $this->table_name . " 
                      (chave_config, valor_config) 
                      VALUES (:chave, :valor)";
        }

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":chave", $chave);
        $stmt->bindParam(":valor", $valor);

        return $stmt->execute();
    }

    // Obter todas as configurações
    function getAll() {
        $query = "SELECT chave_config, valor_config FROM " . $this->table_name;
        $stmt = $this->conn->prepare($query);
        $stmt->execute();

        $config = [];
        while($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $config[$row['chave_config']] = $row['valor_config'];
        }

        return $config;
    }
}
?>