<?php
include_once '../../config/cors.php';
include_once '../../config/database.php';
include_once '../../models/Order.php';

$database = new Database();
$db = $database->getConnection();

$order = new Order($db);

// Get posted data
$data = json_decode(file_get_contents("php://input"));

if(!empty($data->user_id) && !empty($data->items) && !empty($data->total)) {
    
    $order->usuario_id = $data->user_id;
    $order->dados_cliente = $data->customer_data;
    $order->itens = $data->items;
    $order->subtotal = $data->subtotal;
    $order->taxa_entrega = $data->delivery_fee ?? 0;
    $order->total = $data->total;
    $order->eh_entrega = $data->is_delivery ?? false;
    $order->metodo_pagamento = $data->payment_method ?? 'dinheiro';
    $order->status = 'pendente';

    if($order->create()) {
        http_response_code(201);
        echo json_encode(array(
            "sucesso" => true,
            "mensagem" => "Pedido criado com sucesso!",
            "id_pedido" => $order->id,
            "numero_pedido" => $order->numero_pedido
        ));
    } else {
        http_response_code(500);
        echo json_encode(array(
            "sucesso" => false,
            "mensagem" => "Erro ao criar pedido"
        ));
    }
} else {
    http_response_code(400);
    echo json_encode(array(
        "sucesso" => false,
        "mensagem" => "Dados incompletos"
    ));
}
?>