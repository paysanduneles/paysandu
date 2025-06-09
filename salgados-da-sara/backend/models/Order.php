<?php
class Order {
    private $conn;
    private $table_name = "pedidos";

    public $id;
    public $numero_pedido;
    public $usuario_id;
    public $dados_cliente;
    public $itens;
    public $subtotal;
    public $taxa_entrega;
    public $total;
    public $eh_entrega;
    public $metodo_pagamento;
    public $status;
    public $motivo_rejeicao;
    public $criado_em;

    public function __construct($db) {
        $this->conn = $db;
    }

    // Criar pedido
    function create() {
        $query = "INSERT INTO " . $this->table_name . " 
                  (numero_pedido, usuario_id, dados_cliente, itens, subtotal, taxa_entrega, total, eh_entrega, metodo_pagamento, status) 
                  VALUES (:numero_pedido, :usuario_id, :dados_cliente, :itens, :subtotal, :taxa_entrega, :total, :eh_entrega, :metodo_pagamento, :status)";

        $stmt = $this->conn->prepare($query);

        // Gerar número do pedido se não fornecido
        if(empty($this->numero_pedido)) {
            $this->numero_pedido = $this->generateOrderNumber();
        }

        // Sanitizar
        $this->numero_pedido = htmlspecialchars(strip_tags($this->numero_pedido));
        $this->metodo_pagamento = htmlspecialchars(strip_tags($this->metodo_pagamento));
        $this->status = $this->status ?? 'pendente';
        $this->eh_entrega = $this->eh_entrega ?? false;

        // Converter arrays para JSON
        $dados_cliente_json = json_encode($this->dados_cliente);
        $itens_json = json_encode($this->itens);

        // Bind values
        $stmt->bindParam(":numero_pedido", $this->numero_pedido);
        $stmt->bindParam(":usuario_id", $this->usuario_id);
        $stmt->bindParam(":dados_cliente", $dados_cliente_json);
        $stmt->bindParam(":itens", $itens_json);
        $stmt->bindParam(":subtotal", $this->subtotal);
        $stmt->bindParam(":taxa_entrega", $this->taxa_entrega);
        $stmt->bindParam(":total", $this->total);
        $stmt->bindParam(":eh_entrega", $this->eh_entrega, PDO::PARAM_BOOL);
        $stmt->bindParam(":metodo_pagamento", $this->metodo_pagamento);
        $stmt->bindParam(":status", $this->status);

        if($stmt->execute()) {
            $this->id = $this->conn->lastInsertId();
            
            // Criar entrada no histórico de status
            $this->createStatusHistory('pendente', 'Pedido recebido - Aguardando confirmação');
            
            return true;
        }

        return false;
    }

    // Ler todos os pedidos
    function readAll() {
        $query = "SELECT p.*, u.nome as nome_usuario 
                  FROM " . $this->table_name . " p
                  LEFT JOIN usuarios u ON p.usuario_id = u.id
                  ORDER BY p.criado_em DESC";

        $stmt = $this->conn->prepare($query);
        $stmt->execute();

        return $stmt;
    }

    // Ler pedidos por usuário
    function readByUser($usuario_id) {
        $query = "SELECT * FROM " . $this->table_name . " 
                  WHERE usuario_id = :usuario_id 
                  ORDER BY criado_em DESC";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":usuario_id", $usuario_id);
        $stmt->execute();

        return $stmt;
    }

    // Ler um pedido
    function readOne() {
        $query = "SELECT * FROM " . $this->table_name . " 
                  WHERE id = :id";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":id", $this->id);
        $stmt->execute();

        if($stmt->rowCount() > 0) {
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            
            $this->numero_pedido = $row['numero_pedido'];
            $this->usuario_id = $row['usuario_id'];
            $this->dados_cliente = json_decode($row['dados_cliente'], true);
            $this->itens = json_decode($row['itens'], true);
            $this->subtotal = $row['subtotal'];
            $this->taxa_entrega = $row['taxa_entrega'];
            $this->total = $row['total'];
            $this->eh_entrega = $row['eh_entrega'];
            $this->metodo_pagamento = $row['metodo_pagamento'];
            $this->status = $row['status'];
            $this->motivo_rejeicao = $row['motivo_rejeicao'];
            $this->criado_em = $row['criado_em'];
            return true;
        }

        return false;
    }

    // Atualizar status do pedido
    function updateStatus($novo_status, $descricao = null, $motivo_rejeicao = null) {
        $query = "UPDATE " . $this->table_name . " 
                  SET status = :status";
        
        if($motivo_rejeicao) {
            $query .= ", motivo_rejeicao = :motivo_rejeicao";
        }
        
        $query .= " WHERE id = :id";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":status", $novo_status);
        $stmt->bindParam(":id", $this->id);
        
        if($motivo_rejeicao) {
            $stmt->bindParam(":motivo_rejeicao", $motivo_rejeicao);
        }

        if($stmt->execute()) {
            // Criar entrada no histórico de status
            $desc = $descricao ?? $this->getStatusDescription($novo_status);
            if($motivo_rejeicao) {
                $desc = "Pedido recusado: " . $motivo_rejeicao;
            }
            $this->createStatusHistory($novo_status, $desc);
            
            $this->status = $novo_status;
            if($motivo_rejeicao) {
                $this->motivo_rejeicao = $motivo_rejeicao;
            }
            return true;
        }

        return false;
    }

    // Criar entrada no histórico de status
    private function createStatusHistory($status, $descricao) {
        $query = "INSERT INTO historico_status_pedido 
                  (pedido_id, status, descricao) 
                  VALUES (:pedido_id, :status, :descricao)";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":pedido_id", $this->id);
        $stmt->bindParam(":status", $status);
        $stmt->bindParam(":descricao", $descricao);
        $stmt->execute();
    }

    // Obter histórico de status
    function getStatusHistory() {
        $query = "SELECT * FROM historico_status_pedido 
                  WHERE pedido_id = :pedido_id 
                  ORDER BY criado_em ASC";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":pedido_id", $this->id);
        $stmt->execute();

        return $stmt;
    }

    // Gerar número do pedido
    private function generateOrderNumber() {
        $query = "SELECT COUNT(*) as count FROM " . $this->table_name;
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        
        $numeroPedido = $row['count'] + 1;
        $data = date('dmY');
        
        return sprintf("#%03d-%s", $numeroPedido, $data);
    }

    // Obter descrição do status
    private function getStatusDescription($status) {
        $descricoes = [
            'pendente' => 'Aguardando Confirmação',
            'confirmado' => 'Em Preparação',
            'pronto' => 'Pronto',
            'entregue' => 'Entregue',
            'rejeitado' => 'Recusado'
        ];
        
        return $descricoes[$status] ?? $status;
    }
}
?>