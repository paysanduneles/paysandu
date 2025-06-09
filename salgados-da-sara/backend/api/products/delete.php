<?php
include_once '../../config/cors.php';
include_once '../../config/database.php';
include_once '../../models/Product.php';

$database = new Database();
$db = $database->getConnection();

$product = new Product($db);

// Get posted data
$data = json_decode(file_get_contents("php://input"));

if(!empty($data->id)) {
    
    $product->id = $data->id;

    if($product->delete()) {
        http_response_code(200);
        echo json_encode(array(
            "sucesso" => true,
            "mensagem" => "Produto excluído com sucesso!"
        ));
    } else {
        http_response_code(500);
        echo json_encode(array(
            "sucesso" => false,
            "mensagem" => "Erro ao excluir produto ou produto não é personalizado"
        ));
    }
} else {
    http_response_code(400);
    echo json_encode(array(
        "sucesso" => false,
        "mensagem" => "ID do produto é obrigatório"
    ));
}
?>