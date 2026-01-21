<!DOCTYPE html>
<html lang="pt-BR">
    <?php
        require __DIR__ . '/vendor/autoload.php';
    ?>
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Simulador de Venda</title>

  <!-- Bootstrap 5 -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">

  <style>
    body {
      background: #f4f6f9;
    }
    .card {
      border-radius: 16px;
    }
    .resultado {
      background: #0d6efd;
      color: #fff;
      border-radius: 12px;
      padding: 16px;
    }
    .valor {
      font-size: 1.4rem;
      font-weight: 600;
    }
  </style>
</head>
<body>

<div class="container py-4">
  <div class="row justify-content-center">
    <div class="col-12 col-md-6">

      <div class="card shadow-sm">
        <div class="card-body">
          <h5 class="mb-3 text-center">Simulação de Venda</h5>

          <!-- Terreno -->
          <div class="mb-3">
            <label class="form-label">Terreno</label>
            <?php
                use Estrutura\ConexaoMySQL;
                $request = $_SERVER['REQUEST_URI'];
                $method = $_SERVER['REQUEST_METHOD'];
                $host = $_SERVER['HTTP_HOST'] ?? '';
                
                $producao=true;
                if (stripos($host, 'localhost') !== false) {
                    $producao=false; // homologação
                }
                $conexao = new ConexaoMySQL($producao);

                $sql="SELECT * 
                        FROM lot_imoveis 
                        WHERE codemp=12 
                        ORDER BY setor, quadra, lote ASC";
                echo $sql;
                try {
                    $result = $conexao->query($sql);
                    echo "Resultado: ";
                    print_r($result);
                    echo "Fim resultado";
                } catch( PDOException  $e ) {
                    echo $e->getMessage();
                }
                
                
                $imoveis = json_decode($result, true);
                print_r($imoveis);
                foreach ($imoveis as $imovel) {
                    echo "<option value=\"{$imovel['area']}|{$imovel['area']}\">Lote {$imovel['setor']} – {$imovel['area']}m²</option>";
                }
            
            ?>
            <select id="terreno" class="form-select">
              <option value="" selected disabled>Selecione o terreno</option>
              <?php
                
                
              ?>
              <option value="300|120">Lote A – 300m²</option>
              <option value="360|135">Lote B – 360m²</option>
              <option value="450|150">Lote C – 450m²</option>
            </select>
          </div>

          <!-- Entrada -->
          <div class="mb-3">
            <label class="form-label">Valor de Entrada (R$)</label>
            <input type="number" id="entrada" class="form-control" placeholder="Ex: 20000" />
          </div>

          <!-- Parcelas -->
          <div class="mb-3">
            <label class="form-label">Número de Parcelas</label>
            <select id="parcelas" class="form-select">
              <option value="12">12x</option>
              <option value="24">24x</option>
              <option value="36">36x</option>
              <option value="48">48x</option>
            </select>
          </div>

          <button class="btn btn-primary w-100" onclick="calcular()">Calcular</button>
        </div>
      </div>

      <!-- Resultado -->
      <div id="resultado" class="resultado mt-3 d-none">
        <div class="mb-2">Valor total do terreno</div>
        <div class="valor" id="valorTotal"></div>

        <hr class="border-light" />

        <div class="mb-2">Valor da parcela</div>
        <div class="valor" id="valorParcela"></div>
      </div>

    </div>
  </div>
</div>

<script>
  function calcular() {
    const terreno = document.getElementById('terreno').value;
    const entrada = parseFloat(document.getElementById('entrada').value || 0);
    const parcelas = parseInt(document.getElementById('parcelas').value);

    if (!terreno) {
      alert('Selecione um terreno');
      return;
    }

    const [metragem, valorM2] = terreno.split('|').map(Number);

    const valorTotal = metragem * valorM2;
    const saldo = valorTotal - entrada;
    const valorParcela = saldo / parcelas;

    document.getElementById('valorTotal').innerText = valorTotal.toLocaleString('pt-BR', {
      style: 'currency',
      currency: 'BRL'
    });

    document.getElementById('valorParcela').innerText = valorParcela.toLocaleString('pt-BR', {
      style: 'currency',
      currency: 'BRL'
    });

    document.getElementById('resultado').classList.remove('d-none');
  }
</script>

</body>
</html>