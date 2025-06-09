<?php
include_once '../../config/cors.php';
include_once '../../config/database.php';
include_once '../../models/Admin.php';

$database = new Database();
$db = $database->getConnection();

$admin = new Admin($db);

$method = $_SERVER['REQUEST_METHOD'];

switch($method) {
    case 'GET':
        // Read all admins
        $stmt = $admin->readAll();
        $num = $stmt->rowCount();
        
        if($num > 0) {
            $admins_arr = array();
            
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                extract($row);
                
                $admin_item = array(
                    "id" => $id,
                    "nome_usuario" => $nome_usuario,
                    "funcao" => $funcao,
                    "criado_em" => $criado_em
                );
                
                array_push($admins_arr, $admin_item);
            }
            
            http_response_code(200);
            echo json_encode(array(
                "sucesso" => true,
                "dados" => $admins_arr
            ));
        } else {
            http_response_code(200);
            echo json_encode(array(
                "sucesso" => true,
                "dados" => array()
            ));
        }
        break;
        
    case 'POST':
        // Create admin
        $data = json_decode(file_get_contents("php://input"));
        
        if(!empty($data->username) && !empty($data->password) && !empty($data->role)) {
            
            $admin->nome_usuario = $data->username;
            
            // Check if username exists
            if($admin->usernameExists()) {
                http_response_code(400);
                echo json_encode(array(
                    "sucesso" => false,
                    "mensagem" => "Nome de usuário já existe!"
                ));
                break;
            }
            
            $admin->senha = $data->password;
            $admin->funcao = $data->role;

            if($admin->create()) {
                http_response_code(201);
                echo json_encode(array(
                    "sucesso" => true,
                    "mensagem" => "Administrador criado com sucesso!",
                    "id" => $admin->id
                ));
            } else {
                http_response_code(500);
                echo json_encode(array(
                    "sucesso" => false,
                    "mensagem" => "Erro ao criar administrador"
                ));
            }
        } else {
            http_response_code(400);
            echo json_encode(array(
                "sucesso" => false,
                "mensagem" => "Dados incompletos"
            ));
        }
        break;
        
    case 'DELETE':
        // Delete admin
        $data = json_decode(file_get_contents("php://input"));
        
        if(!empty($data->id)) {
            $admin->id = $data->id;

            if($admin->delete()) {
                http_response_code(200);
                echo json_encode(array(
                    "sucesso" => true,
                    "mensagem" => "Administrador excluído com sucesso!"
                ));
            } else {
                http_response_code(500);
                echo json_encode(array(
                    "sucesso" => false,
                    "mensagem" => "Erro ao excluir administrador"
                ));
            }
        } else {
            http_response_code(400);
            echo json_encode(array(
                "sucesso" => false,
                "mensagem" => "ID do administrador é obrigatório"
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