<?php
    namespace Estrutura;

    use PDOStatement;

    interface IConexao {
        public function query($sql);
        public function prepare(string $sql): PDOStatement;
        public function beginTransaction();
        public function commit();
        public function lastInsertId();
    }
?>