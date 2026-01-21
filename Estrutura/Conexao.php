<?php
    namespace Estrutura;
    use Estrutura\ConexaoMySQL;
    
    class Conexao {  
        private $cnx;

        public function __construct($producao) {
            // Define o fuso horário do PHP
            date_default_timezone_set('America/Porto_Velho'); // ou 'America/Sao_Paulo' conforme necessário
            
            $this->cnx=new ConexaoMySQL($producao);
        }

        public function getConexao() {
            return $this->cnx;
        }
    }

?>