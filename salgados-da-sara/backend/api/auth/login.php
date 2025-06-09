<?php
include_once '../../config/cors.php';
include_once '../../config/database.php';
include_once '../../models/User.php';

$database = new Database();
$db = $database->getConnection();

$user = new User($db);

// Get posted data
$data = json_decode(file_get_contents("php://input"));

if(!empty($data->phone) && !empty($data->password)) {
    
    if($user->login($data->phone, $data->password)) {
        
        // Create response array
        $response = array(
            "sucesso" => true,
            "mensagem" => "Login realizado com sucesso!",
            "usuario" => array(
                "id" => $user->id,
                "nome" => $user->nome,
                "telefone" => $user->telefone,
                "email" => $user->email,
                "endereco" => $user->endereco,
                "numero" => $user->numero,
                "complemento" => $user->complemento,
                "cidade" => $user->cidade,
                "eh_admin" => $user->eh_admin,
                "criado_em" => $user->criado_em
            )
        );
        
        http_response_code(200);
        echo json_encode($response);
    } else {
        http_response_code(401);
        echo json_encode(array(
            "sucesso" => false,
            "mensagem" => "Telefone ou senha incorretos"
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