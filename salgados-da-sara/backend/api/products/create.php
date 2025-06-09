<?php
include_once '../../config/cors.php';
include_once '../../config/database.php';
include_once '../../models/Product.php';

$database = new Database();
$db = $database->getConnection();

$product = new Product($db);

// Get posted data
$data = json_decode(file_get_contents("php://input"));

if(!empty($data->name) && !empty($data->price) && !empty($data->category)) {
    
    $product->nome = $data->name;
    $product->preco = $data->price;
    $product->categoria = $data->category;
    $product->descricao = $data->description ?? '';
    $product->eh_porcionado = $data->is_portioned ?? false;
    $product->eh_personalizado = true;

    if($product->create()) {
        http_response_code(201);
        echo json_encode(array(
            "sucesso" => true,
            "mensagem" => "Produto criado com sucesso!",
            "id" => $product->id
        ));
    } else {
        http_response_code(500);
        echo json_encode(array(
            "sucesso" => false,
            "mensagem" => "Erro ao criar produto"
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