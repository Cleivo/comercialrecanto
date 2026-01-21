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


    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery.mask/1.14.16/jquery.mask.min.js"></script>

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
            <select id="terreno" class="form-select">
                <option value="" selected disabled>Selecione o terreno</option>
                <?php
                    use Estrutura\ConexaoMySQL;
                    $request = $_SERVER['REQUEST_URI'];
                    $method = $_SERVER['REQUEST_METHOD'];
                    $host = $_SERVER['HTTP_HOST'] ?? '';

                    $producao=true;
                    if (stripos($host, 'localhost') !== false) {
                        $producao=false; // homologação
                    }

                    echo "<br>Produção: ";
                    echo var_dump($producao)."<br>";
                    $conexao = new ConexaoMySQL($producao);

                    $sql="SELECT * 
                            FROM lot_imoveis 
                            WHERE codemp=12 
                            ORDER BY setor, quadra, lote ASC";
                    //echo $sql."<br>";
                    try {
                        $result = $conexao->query($sql);
                    } catch( PDOException  $e ) {
                        echo $e->getMessage();
                    }
                    
                    
                    $imoveis = json_decode($result, true);
                    foreach ($imoveis as $imovel) {
                        echo "<option setor='{$imovel['setor']}' quadra='{$imovel['quadra']}' lote='{$imovel['lote']}' value='{$imovel['area']}'>Quadra {$imovel['quadra']} - Lote {$imovel['lote']} – {$imovel['area']}m²</option>";
                    }
                ?>
              
            </select>
          </div>

          <!-- Entrada -->
          <div class="mb-3">
            <label class="form-label">Valor de Entrada (R$)</label>
            <input type="text" id="entrada" class="form-control" placeholder="Ex: 20000" />
          </div>

          <!-- Parcelas -->
          <div class="mb-3">
            <label class="form-label">Número de Parcelas</label>
            <select id="parcelas" class="form-select">
              <option value="12">12x</option>
              <option value="24">24x</option>
              <option value="36">36x</option>
              <option value="48">48x</option>
              <option value="60">60x</option>
              <option value="72">72x</option>
              <option value="84">84x</option>
              <option value="96">96x</option>
              <option value="108">108x</option>
              <option value="120">120x</option>
              <option value="132">132x</option>
              <option value="144">144x</option>
              <option value="156">156x</option>
              <option value="168">168x</option>
              <option value="180">180x</option>
            </select>
          </div>

          <button class="btn btn-primary w-100" id="enviar">Calcular</button>
        </div>
      </div>

      <!-- Resultado -->
      <div id="resultado" class="resultado mt-3 d-none">
        <div class="mb-2">Valor total do terreno</div>
        <div class="valor" id="valorTotal"></div>

        <hr class="border-light" />

        <div class="mb-2">Resumo da proposta:</div>
        <div class="valor" id="valorParcela"></div>
      </div>

    </div>
  </div>
</div>

<script>

    $('#entrada').mask('#.##0,00', { reverse: true });

    $('#enviar').on('click', function() {

        calcular();
    });

    function calcular(elemento) {
        // Preciso pegar os dados do terreno selecionado, valor de entrada e número de parcelas

        setor=$('#terreno option:selected').attr('setor');
        quadra=$('#terreno option:selected').attr('quadra');
        lote=$('#terreno option:selected').attr('lote');
        entrada=$('#entrada').val().replace(/\./g, '').replace(',', '.');
        parcelas=$('#parcelas').val();

        // preciso fazer uma requisição ajax para o servidor
        $.ajax({
            url: 'simulacao_venda.php',
            type: 'POST',
            data: {
                setor: setor,
                quadra: quadra,
                lote: lote,
                entrada: entrada,
                parcelas: parcelas
            },
            success: function(response) {
                // Aqui eu recebo a resposta do servidor
                const data = JSON.parse(response);

                if (data.error) {
                    alert(data.message);
                    return;
                }

                // Atualizo os valores na tela
                document.getElementById('valorTotal').innerText = "R$ " +data.valorTotal;

                document.getElementById('valorParcela').innerText = data.parcelas + 'x de R$ ' + data.valorParcela;

                document.getElementById('resultado').classList.remove('d-none');
            },
            error: function() {
                alert('Erro ao comunicar com o servidor.');
            }
        });

        /*
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

        */
    }
</script>

</body>
</html>