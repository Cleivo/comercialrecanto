<?php
    namespace Estrutura;
    use PDO;
    use PDOException;
    use PDOStatement;
    use Estrutura\IConexao;

    class ConexaoMySQL implements IConexao {
        private $pdo;

        private $pdoMaster;
        private $pdoSlaves = [];
        private $env;

        public function __construct($producao) {
            $this->parseEnvFile();

            $dbname = $this->env['DB_NAME'];
            $user = $this->env['DB_USER'];
            $pass = $this->env['DB_PASS'];
            if($producao) {
                // MASTER
                $dsnMaster = "mysql:host={$this->env['DB_HOST_MASTER']};port={$this->env['DB_PORT_MASTER']};dbname=$dbname;charset=utf8mb4";
                $this->pdoMaster = new PDO($dsnMaster, $user, $pass);
                $this->pdoMaster->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

                // SLAVES
                $slaves = explode(',', $this->env['DB_HOST_SLAVES']);
                $slavePorts = explode(',', $this->env['DB_PORT_SLAVES']);

                foreach ($slaves as $index => $host) {
                    $port = $slavePorts[$index] ?? '3306';
                    $dsnSlave = "mysql:host=$host;port=$port;dbname=$dbname;charset=utf8mb4";
                    $pdoSlave = new PDO($dsnSlave, $user, $pass);
                    $pdoSlave->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
                    $this->pdoSlaves[] = $pdoSlave;
                }
            } else {
                $host=$this->env['DB_HOST_HOMOLOG'];
                $port=$this->env['DB_PORT_HOMOLOG'];

                $dsnMaster = "mysql:host=$host;port=$port;dbname=$dbname;charset=utf8mb4";
                $this->pdoMaster = new PDO($dsnMaster, $user, $pass);
                $this->pdoMaster->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
                
                $dsnSlave = "mysql:host=$host;port=$port;dbname=$dbname;charset=utf8mb4";
                //echo $dsnSlave;
                $pdoSlave = new PDO($dsnSlave, $user, $pass);
                $pdoSlave->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
                $this->pdoSlaves[] = $pdoSlave;
            }
            

        }

        private function parseEnvFile() {
            $this->env = parse_ini_file(__DIR__."/../.env");
        }

        public function query($sql) {
            $stmt = $this->prepare($sql);
    
            if ($stmt) {
                $data = $stmt->fetchAll(PDO::FETCH_ASSOC); // Busca os dados como array associativo
                return json_encode($data); // Converte para JSON e retorna
            } else {
                // Em caso de erro na consulta, retorna um JSON com mensagem de erro
                return json_encode([
                    'error' => true,
                    'message' => 'Erro ao executar a consulta SQL.'
                ]);
            }
        }

        private function isSelectQuery(string $sql): bool {
            return preg_match('/^\s*SELECT/i', $sql);
        }
        
        private function getPdoConnection(string $sql): PDO {
            if ($this->isSelectQuery($sql) && count($this->pdoSlaves) > 0) {
                $this->pdo=$this->pdoSlaves[array_rand($this->pdoSlaves)];
                return $this->pdo;
            }
            $this->pdo=$this->pdoMaster;
            return $this->pdoMaster;
        }

        // Prepara e retorna um statement para bind de parâmetros
        public function prepare(string $sql): PDOStatement {
            $pdo = $this->getPdoConnection($sql);
            return $pdo->prepare($sql);
        }

        public function beginTransaction() {
            return $this->pdo->beginTransaction();
        }

        public function lastInsertId() {
            return $this->pdo->lastInsertId();
        }

        public function commit() {
            return $this->pdo->commit();
        }

        public function close() {
            $this->pdo = null;
        }
    }
?>