<?php
    namespace Estrutura;
    
    /*
        // 2xx - Sucesso
        OK = 200;
        CREATED = 201;
        NO_CONTENT = 204;

        // 4xx - Erros do cliente
        BAD_REQUEST = 400;
        UNAUTHORIZED = 401;
        FORBIDDEN = 403;
        NOT_FOUND = 404;
        UNPROCESSABLE_ENTITY = 422;

        // 5xx - Erros do servidor
        INTERNAL_SERVER_ERROR = 500;
        SERVICE_UNAVAILABLE = 503;
    */

    class Resposta { 
        public static function msg($statusHttp, $mensagem, $dados = null) {
            http_response_code($statusHttp);
            header('Content-Type: application/json');

            echo json_encode([
                'success' => $statusHttp < 400,
                'mensagem' => $mensagem,
                'dados' => $dados
            ]);
            exit;
        }
    }

?>