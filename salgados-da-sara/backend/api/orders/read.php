<?php
include_once '../../config/cors.php';
include_once '../../config/database.php';
include_once '../../models/Order.php';

$database = new Database();
$db = $database->getConnection();

$order = new Order($db);

// Check if user_id is provided for filtering
$user_id = isset($_GET['user_id']) ? $_GET['user_id'] : null;

if($user_id) {
    $stmt = $order->readByUser($user_id);
} else {
    $stmt = $order->readAll();
}

$num = $stmt->rowCount();

if($num > 0) {
    $orders_arr = array();
    
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        extract($row);
        
        $order_item = array(
            "id" => $id,
            "numero_pedido" => $numero_pedido,
            "usuario_id" => $usuario_id,
            "dados_cliente" => json_decode($dados_cliente, true),
            "itens" => json_decode($itens, true),
            "subtotal" => floatval($subtotal),
            "taxa_entrega" => floatval($taxa_entrega),
            "total" => floatval($total),
            "eh_entrega" => $eh_entrega,
            "metodo_pagamento" => $metodo_pagamento,
            "status" => $status,
            "motivo_rejeicao" => $motivo_rejeicao,
            "criado_em" => $criado_em
        );
        
        // Add status history
        $order->id = $id;
        $history_stmt = $order->getStatusHistory();
        $status_history = array();
        
        while($history_row = $history_stmt->fetch(PDO::FETCH_ASSOC)) {
            $status_history[] = array(
                "status" => $history_row['status'],
                "descricao" => $history_row['descricao'],
                "criado_em" => $history_row['criado_em']
            );
        }
        
        $order_item["historico_status"] = $status_history;
        
        array_push($orders_arr, $order_item);
    }
    
    http_response_code(200);
    echo json_encode(array(
        "sucesso" => true,
        "dados" => $orders_arr
    ));
} else {
    http_response_code(200);
    echo json_encode(array(
        "sucesso" => true,
        "dados" => array()
    ));
}
?>