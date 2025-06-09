<?php
include_once '../../config/cors.php';
include_once '../../config/database.php';
include_once '../../models/Order.php';

$database = new Database();
$db = $database->getConnection();

$order = new Order($db);

// Get posted data
$data = json_decode(file_get_contents("php://input"));

if(!empty($data->id) && !empty($data->status)) {
    
    $order->id = $data->id;
    
    // Read current order data
    if($order->readOne()) {
        $descricao = $data->description ?? null;
        $motivo_rejeicao = $data->rejection_reason ?? null;
        
        if($order->updateStatus($data->status, $descricao, $motivo_rejeicao)) {
            http_response_code(200);
            echo json_encode(array(
                "sucesso" => true,
                "mensagem" => "Status do pedido atualizado com sucesso!"
            ));
        } else {
            http_response_code(500);
            echo json_encode(array(
                "sucesso" => false,
                "mensagem" => "Erro ao atualizar status do pedido"
            ));
        }
    } else {
        http_response_code(404);
        echo json_encode(array(
            "sucesso" => false,
            "mensagem" => "Pedido não encontrado"
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