<?php
    require __DIR__ . '/vendor/autoload.php';
    
    // preciso ler o input e trabalhar...
    $setor = $_POST['setor'];
    $quadra = $_POST['quadra'];
    $lote = $_POST['lote'];
    $entrada = floatval($_POST['entrada']);
    $parcelas = intval($_POST['parcelas']);
    $valorTotal = 0;

    // aqui eu faria uma consulta no banco de dados para pegar o valor do terreno
    use Estrutura\ConexaoMySQL;

    $request = $_SERVER['REQUEST_URI'];
    $method = $_SERVER['REQUEST_METHOD'];
    $host = $_SERVER['HTTP_HOST'] ?? '';

    $producao=true;
    if (stripos($host, 'localhost') !== false) {
        $producao=false; // homologação
    }
    $conexao=new ConexaoMySQL($producao);

    $sql = "SELECT cfg.jurofinan, cfg.valorm2, t.area 
                FROM lot_imoveis as t
                INNER JOIN config as cfg on cfg.codemp = t.codemp
                WHERE setor = :setor AND quadra = :quadra AND lote = :lote";
    $stmt = $conexao->prepare($sql);
    $stmt->bindParam(':setor', $setor);
    $stmt->bindParam(':quadra', $quadra);
    $stmt->bindParam(':lote', $lote);
    $stmt->execute();
    $row = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($row) {
        $valorTotal = $row['area'] * $row['valorm2'];
        $juro = $row['jurofinan'] / 100;
        $saldo = $valorTotal - $entrada;
        
        // preciso calcular o pmt
        $pmt = ($juro * $saldo) / (1 - pow(1 + $juro, -$parcelas));

        $valorParcela = $pmt;


        echo json_encode([
            'valorTotal' => number_format($valorTotal, 2, ',', '.'),
            'parcelas' => $parcelas,
            'valorParcela' => number_format($valorParcela, 2, ',', '.')
        ]);
    } else {
        echo json_encode([
            'error' => true,
            'message' => 'Terreno não encontrado.'
        ]);
    }
?>