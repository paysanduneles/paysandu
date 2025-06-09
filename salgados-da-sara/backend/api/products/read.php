<?php
include_once '../../config/cors.php';
include_once '../../config/database.php';
include_once '../../models/Product.php';

$database = new Database();
$db = $database->getConnection();

$product = new Product($db);

$stmt = $product->readAll();
$num = $stmt->rowCount();

if($num > 0) {
    $products_arr = array();
    
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        extract($row);
        
        $product_item = array(
            "id" => $id,
            "nome" => $nome,
            "preco" => floatval($preco),
            "categoria" => $categoria,
            "descricao" => $descricao,
            "eh_porcionado" => $eh_porcionado,
            "eh_personalizado" => $eh_personalizado,
            "criado_em" => $criado_em
        );
        
        array_push($products_arr, $product_item);
    }
    
    http_response_code(200);
    echo json_encode(array(
        "sucesso" => true,
        "dados" => $products_arr
    ));
} else {
    http_response_code(200);
    echo json_encode(array(
        "sucesso" => true,
        "dados" => array()
    ));
}
?>