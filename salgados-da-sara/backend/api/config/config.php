<?php
include_once '../../config/cors.php';
include_once '../../config/database.php';
include_once '../../models/Config.php';

$database = new Database();
$db = $database->getConnection();

$config = new Config($db);

$method = $_SERVER['REQUEST_METHOD'];

switch($method) {
    case 'GET':
        // Get all config or specific key
        $key = isset($_GET['key']) ? $_GET['key'] : null;
        
        if($key) {
            $value = $config->getValue($key);
            if($value !== null) {
                http_response_code(200);
                echo json_encode(array(
                    "sucesso" => true,
                    "dados" => array($key => $value)
                ));
            } else {
                http_response_code(404);
                echo json_encode(array(
                    "sucesso" => false,
                    "mensagem" => "Configuração não encontrada"
                ));
            }
        } else {
            $all_config = $config->getAll();
            http_response_code(200);
            echo json_encode(array(
                "sucesso" => true,
                "dados" => $all_config
            ));
        }
        break;
        
    case 'POST':
        // Set config value
        $data = json_decode(file_get_contents("php://input"));
        
        if(!empty($data->key) && isset($data->value)) {
            
            if($config->setValue($data->key, $data->value)) {
                http_response_code(200);
                echo json_encode(array(
                    "sucesso" => true,
                    "mensagem" => "Configuração atualizada com sucesso!"
                ));
            } else {
                http_response_code(500);
                echo json_encode(array(
                    "sucesso" => false,
                    "mensagem" => "Erro ao atualizar configuração"
                ));
            }
        } else {
            http_response_code(400);
            echo json_encode(array(
                "sucesso" => false,
                "mensagem" => "Chave e valor são obrigatórios"
            ));
        }
        break;
        
    default:
        http_response_code(405);
        echo json_encode(array(
            "sucesso" => false,
            "mensagem" => "Método não permitido"
        ));
        break;
}
?>