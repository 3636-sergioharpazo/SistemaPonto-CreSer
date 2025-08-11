<?php
session_start();
include_once("principal.php");
include_once("conexao.php");
//include_once("salvar_localizacao.php");

//header("Refresh: 60"); // Recarrega a página a cada 60 segundos



$idUsuario = $_SESSION['usuarioId'];
$nome = $_SESSION['nome'];



// Verifica se foi recarregada
if (!isset($_SESSION['recarregado'])) {
    $_SESSION['recarregado'] = true;
    echo "<script>location.reload();</script>";
    exit();
} else {
    unset($_SESSION['recarregado']); // Limpa para que funcione só uma vez
}


// Verifica se a variável de sessão idUsuario está definida
if(!isset($idUsuario)) {
    die("Usuário não está logado. Por favor, faça login novamente.");
}

// Executa consulta
$result_select = "SELECT * FROM tabela_usuarios WHERE idUsuario = '$idUsuario' LIMIT 1";
$result_result = mysqli_query($conn, $result_select);

// Verifica se a consulta foi bem-sucedida
if(!$result_result) {
    die("Erro na consulta ao banco de dados: " . mysqli_error($conn));
}



// Verifica se algum resultado foi retornado
$resultado = mysqli_fetch_assoc($result_result);
if(!$resultado) {
    die("Nenhum usuário encontrado com o idUsuario fornecido.");
}

// Valores do banco de dados
$dataNascimento = $resultado['data_nascimento'];
$contato = $resultado['contato'];
$hora_entrada_cad = substr($resultado['hora_entrada_cad'], 0, 5);
$hora_saida_cad   = substr($resultado['hora_saida_cad'], 0, 5);

// Mapeamento dos dias da semana SEM acento e minúsculos (como estão no banco)
$diasSemana = [
    1 => 'segunda',
    2 => 'terca',
    3 => 'quarta',
    4 => 'quinta',
    5 => 'sexta',
    6 => 'sabado',
    7 => 'domingo'
];

$hojeNumero = date('N'); // 1 (segunda) até 7 (domingo)
$hoje = $diasSemana[$hojeNumero] ?? null;

if (!empty($idUsuario) && $hoje !== null) {
    // Buscar horário_id do usuário
    $queryHorarioID = "SELECT horario_id FROM tabela_estudante WHERE idUsuario = '$idUsuario' LIMIT 1";
    $resHorarioID = mysqli_query($conn, $queryHorarioID);

    if ($resHorarioID && mysqli_num_rows($resHorarioID) > 0) {
        $linhaHorario = mysqli_fetch_assoc($resHorarioID);
        $horario_id = $linhaHorario['horario_id'] ?? null;

        if (!empty($horario_id)) {
            // Buscar horário personalizado do dia atual
            $queryHorarioDia = "
                SELECT entrada, saida 
                FROM horarios_dias 
                WHERE id_horario = '$horario_id' AND dia = '$hoje' 
                LIMIT 1
            ";
            $resHorarioDia = mysqli_query($conn, $queryHorarioDia);

            if ($resHorarioDia && mysqli_num_rows($resHorarioDia) > 0) {
                $linhaHorarioDia = mysqli_fetch_assoc($resHorarioDia);

                if (!empty($linhaHorarioDia['entrada']) && !empty($linhaHorarioDia['saida'])) {
                    $hora_entrada_cad = substr($linhaHorarioDia['entrada'], 0, 5);
                    $hora_saida_cad   = substr($linhaHorarioDia['saida'], 0, 5);
                }
            }
        }
    }
}

    $intervalo = $resultado['intervalo'];

    // Agora busca a descrição e os minutos do intervalo com base na letra
    $query_intervalo = "SELECT tempo, descricao FROM intervalos WHERE intervalo = '$intervalo' LIMIT 1";
    $resultado_intervalo = mysqli_query($conn, $query_intervalo);
    $dados_intervalo = mysqli_fetch_assoc($resultado_intervalo);

    // Define os valores com base no banco ou padrão
    $intervalo_almoco_minutos = $dados_intervalo ? $dados_intervalo['minutos'] : 0;
    $intervalo_almoco_texto   = $dados_intervalo ? $dados_intervalo['descricao'] : 'Não definido';



// Verifica se os campos de data de nascimento e telefone estão vazios
if(empty($dataNascimento) || empty($contato)) {
    // Redireciona o usuário para a página de perfil para completar os dados
    header("Location: perfil.php");
    exit();
}





// Verificação e inicialização das variáveis de sessão
if (!isset($_SESSION['mensagem'])) {
    $_SESSION['mensagem'] = '';
}

if (!isset($_SESSION['usuarioNome']) || !isset($_SESSION['usuarioId'])) {
    $_SESSION['mensagem'] = "Você precisa estar logado para acessar esta página.";
    header("Location: login.php");
    exit();
}

$usuarioNome = isset($_SESSION['usuarioNome']) ? $_SESSION['usuarioNome'] : '';
$usuarioId = isset($_SESSION['usuarioId']) ? $_SESSION['usuarioId'] : '';

date_default_timezone_set('America/Sao_Paulo');
$data_atual = date('Y-m-d');


$timestamp = strtotime($data_atual);

// Array com os nomes dos meses em português
$meses = [
    1 => 'janeiro', 
    2 => 'fevereiro', 
    3 => 'março', 
    4 => 'abril', 
    5 => 'maio', 
    6 => 'junho', 
    7 => 'julho', 
    8 => 'agosto', 
    9 => 'setembro', 
    10 => 'outubro', 
    11 => 'novembro', 
    12 => 'dezembro'
];

// Extrai o dia, mês e ano da data atual
$dia = date('d', $timestamp);
$mes = $meses[(int)date('m', $timestamp)];
$ano = date('Y', $timestamp);

// Monta a data por extenso
$data_extenso = "$dia de $mes de $ano";

// Verificação se já registrou entrada e saída hoje
$query_entrada = "SELECT * FROM pontos WHERE idUsuario = :id AND data_atual = :data_atual AND tipo_registro = 'entrada'";
$stmt_entrada = $pdo_conn->prepare($query_entrada);
$stmt_entrada->bindParam(':id', $usuarioId);
$stmt_entrada->bindParam(':data_atual', $data_atual);
$stmt_entrada->execute();
$ja_registrou_entrada = $stmt_entrada->rowCount() > 0;

$query_saida = "SELECT * FROM pontos WHERE idUsuario = :id AND data_atual = :data_atual AND hora_saida != ''";
$stmt_saida = $pdo_conn->prepare($query_saida);
$stmt_saida->bindParam(':id', $usuarioId);
$stmt_saida->bindParam(':data_atual', $data_atual);
$stmt_saida->execute();
$ja_registrou_saida = $stmt_saida->rowCount() > 0;

$registrou_ambos = $ja_registrou_entrada && $ja_registrou_saida;

$query_saida_almoco_entrada= "SELECT * FROM pontos WHERE idUsuario = :id AND data_atual = :data_atual AND hora_entrada_al !='' ";
$stmt_saida_almoco_entrada = $pdo_conn->prepare($query_saida_almoco_entrada);
$stmt_saida_almoco_entrada->bindParam(':id', $usuarioId);
$stmt_saida_almoco_entrada->bindParam(':data_atual', $data_atual);
$stmt_saida_almoco_entrada->execute();
$ja_registrou_almoco_entrada = $stmt_saida_almoco_entrada->rowCount() > 0;


$query_saida_almoco_saida= "SELECT * FROM pontos WHERE idUsuario = :id AND data_atual = :data_atual AND hora_saida_al !=''";
$stmt_saida_almoco_saida = $pdo_conn->prepare($query_saida_almoco_saida);
$stmt_saida_almoco_saida->bindParam(':id', $usuarioId);
$stmt_saida_almoco_saida->bindParam(':data_atual', $data_atual);
$stmt_saida_almoco_saida->execute();
$ja_registrou_almoco_saida = $stmt_saida_almoco_saida->rowCount() > 0;

// SAÍDA TEMPORÁRIA 1
$query_saida_temporaria = "SELECT * FROM pontos WHERE idUsuario = :id AND data_atual = :data_atual AND hora_saida_temp IS NOT NULL AND hora_saida_temp != ''";
$stmt_saida_temporaria = $pdo_conn->prepare($query_saida_temporaria);
$stmt_saida_temporaria->bindParam(':id', $usuarioId);
$stmt_saida_temporaria->bindParam(':data_atual', $data_atual);
$stmt_saida_temporaria->execute();
$ja_registrou_saida_temporaria = $stmt_saida_temporaria->rowCount() > 0;

// RETORNO SAÍDA TEMPORÁRIA 1
$query_retorno_temporaria = "SELECT * FROM pontos WHERE idUsuario = :id AND data_atual = :data_atual AND hora_saida_temp_retorno IS NOT NULL AND hora_saida_temp_retorno != ''";
$stmt_retorno_temporaria = $pdo_conn->prepare($query_retorno_temporaria);
$stmt_retorno_temporaria->bindParam(':id', $usuarioId);
$stmt_retorno_temporaria->bindParam(':data_atual', $data_atual);
$stmt_retorno_temporaria->execute();
$ja_registrou_retorno_saida_temporaria = $stmt_retorno_temporaria->rowCount() > 0;

$habilita_retorno_saida_temporaria = $ja_registrou_saida_temporaria && !$ja_registrou_retorno_saida_temporaria;

// SAÍDA TEMPORÁRIA 2
$query_saida_temporaria2 = "SELECT * FROM pontos WHERE idUsuario = :id AND data_atual = :data_atual AND hora_saida_temp2 IS NOT NULL AND hora_saida_temp2 != ''";
$stmt_saida_temporaria2 = $pdo_conn->prepare($query_saida_temporaria2);
$stmt_saida_temporaria2->bindParam(':id', $usuarioId);
$stmt_saida_temporaria2->bindParam(':data_atual', $data_atual);
$stmt_saida_temporaria2->execute();
$ja_registrou_saida_temporaria2 = $stmt_saida_temporaria2->rowCount() > 0;

// RETORNO SAÍDA TEMPORÁRIA 2
$query_retorno_temporaria2 = "SELECT * FROM pontos WHERE idUsuario = :id AND data_atual = :data_atual AND hora_saida_temp_retorno2 IS NOT NULL AND hora_saida_temp_retorno2 != ''";
$stmt_retorno_temporaria2 = $pdo_conn->prepare($query_retorno_temporaria2);
$stmt_retorno_temporaria2->bindParam(':id', $usuarioId);
$stmt_retorno_temporaria2->bindParam(':data_atual', $data_atual);
$stmt_retorno_temporaria2->execute();
$ja_registrou_retorno_saida_temporaria2 = $stmt_retorno_temporaria2->rowCount() > 0;

$habilita_retorno_saida_temporaria2 = $ja_registrou_saida_temporaria2 && !$ja_registrou_retorno_saida_temporaria2;



?>

<?php

// Seta a flag de login apenas na primeira vez
if (!isset($_SESSION['ja_logou'])) {
    $_SESSION['login_sucesso'] = true;
    $_SESSION['ja_logou'] = true;
}

$mensagemLogin = '';
$exibirNotificacao = false;

if (!empty($_SESSION['login_sucesso'])) {
    $mensagemLogin = "✅ Bem-vindo, {$usuarioNome}! Login realizado com sucesso.";
    unset($_SESSION['login_sucesso']); // só mostra uma vez
    $exibirNotificacao = true;
} else {
    $mensagemLogin = "🔄 Página atualizada com sucesso.";
    $exibirNotificacao = true;
}
?>

<script src="https://cdn.jsdelivr.net/npm/notiflix/dist/notiflix-aio-3.2.6.min.js"></script>
<script>
document.addEventListener("DOMContentLoaded", function () {
    const exibirNotificacao = <?= $exibirNotificacao ? 'true' : 'false' ?>;
    const mensagem = <?= json_encode($mensagemLogin) ?>;

    if (exibirNotificacao && mensagem) {
        Notiflix.Notify.success(mensagem, {
            timeout: 5000,
            position: 'right-top',
            cssAnimationStyle: 'zoom',
            width: '320px',
            fontSize: '16px',
            borderRadius: '10px',
            distance: '20px',
        });
    }
});
</script>




<!DOCTYPE html>
<html>
<head>
      <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/notiflix/dist/notiflix-3.2.6.min.css" />
    <title>Registro de Ponto</title>
    <style>
        
           
    .form {
        background-color: rgba(150, 215, 255, 0.95);
        padding: 30px 20px;
        margin: auto;
        max-width: 600px;
        border-radius: 10px;
        box-shadow: 0 0 15px rgba(0,0,0,0.2);
        height: auto;
    }

    h2 {
        margin-top: 0;
        text-align: center;
    }

    .relogio {
        font-size: 4em;
        text-align: center;
        margin: 20px 0;
    }

    .mensagem, .mensagem2 {
    font-size: 1.2em;
    text-align: center;
    margin: 12px auto;
    font-weight: 600;
    padding: 10px 20px;
    border-radius: 8px;
    max-width: 600px;
    box-shadow: 0 2px 6px rgba(0,0,0,0.1);
    font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
}

.mensagem {
    color: #a94442;
    background-color: #f2dede;
    border: 1px solid #ebccd1;
}
.mensagem2 {
    font-size: 1.2em;
    text-align: center;
    margin: 12px auto;
    font-weight: 600;
    padding: 15px 25px;
    border-radius: 8px;
    max-width: 600px;
    box-shadow: 0 2px 6px rgba(0, 0, 0, 0.1);
    font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
    color: #2e7d32;
    background-color: #e8f5e9;
    border: 1px solid #c8e6c9;
    position: relative;
}

.mensagem2::before {
    content: "✅ ";
    font-size: 1.3em;
    margin-right: 8px;
    vertical-align: middle;
}



    .btn-primary { background-color: #007bff; color: #fff; }
    .btn-info    { background-color: #17a2b8; color: #fff; }
    .btn-danger  { background-color: #dc3545; color: #fff; }

    #map {
        height: 300px;
        width: 100%;
        border-radius: 10px;
        margin-top: 20px;
    }

    #titulo {
        text-align: center;
        font-size: 22px;
        margin: 20px 0;
    }

    @media (max-width: 768px) {
        .form {
            padding: 20px 10px;
            margin: 10px;
            height: auto;
        }

        .relogio {
            font-size: 3em;
        }

        #titulo {
            font-size: 20px;
            margin-top: 10px;
        }
    }

    @media (max-width: 480px) {
        .relogio {
            font-size: 2.5em;
        }

        .btn-primary, .btn-info, .btn-danger {
            font-size: 1.2em;
            padding: 12px;
        }
    }
    
    #tela-bloqueio {
    display: none;
    position: fixed;
    top: 0; left: 0;
    width: 100vw;
    height: 100vh;
    background-color: rgba(0,0,0,0.8);
    color: white;
    font-size: 32px;
    z-index: 9999;
    justify-content: center;
    align-items: center;
    text-align: center;
    cursor: pointer;
}

#tela-bloqueio .mensagem small {
    font-size: 16px;
    display: block;
    margin-top: 10px;
}


    </style>
    
<script>
window.onload = function () {
    getLocation();
};

function getLocation() {
    if (navigator.geolocation) {
        navigator.geolocation.getCurrentPosition(
            showPosition,
            showError,
            {
                enableHighAccuracy: true,
                timeout: 10000,
                maximumAge: 0
            }
        );
    } else {
        document.getElementById("status").innerText = "Geolocalização não suportada pelo navegador.";
    }
}

function showPosition(position) {
    var lat = position.coords.latitude;
    var lon = position.coords.longitude;
    document.getElementById("status").innerHTML = `Latitude: ${lat}<br>Longitude: ${lon}`;
    getAddress(lat, lon);
}

function showError(error) {
    let message = "";
    switch (error.code) {
        case error.PERMISSION_DENIED:
            message = "Permissão negada para obter localização.";
            break;
        case error.POSITION_UNAVAILABLE:
            message = "Informação da localização indisponível.";
            break;
        case error.TIMEOUT:
            message = "Tempo de requisição expirado.";
            break;
        default:
            message = "Erro desconhecido.";
            break;
    }
    document.getElementById("status").innerText = message;
}

function getAddress(lat, lon) {
    const url = `https://nominatim.openstreetmap.org/reverse?format=jsonv2&lat=${lat}&lon=${lon}`;

    fetch(url)
        .then(response => response.json())
        .then(data => {
            if (data && data.display_name) {
                document.getElementById("endereco").value = data.display_name;
            } else {
                document.getElementById("status").innerText = "Não foi possível obter o endereço.";
            }
        })
        .catch(() => {
            document.getElementById("status").innerText = "Erro ao consultar o endereço.";
        });
}
</script>
<!-- Adicione isso no <head> ou antes do script -->
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/notiflix/dist/notiflix-3.2.6.min.css" />
<script src="https://cdn.jsdelivr.net/npm/notiflix/dist/notiflix-aio-3.2.6.min.js"></script>


</head>
<body role="document" background="pics/bg.GIF">
    <div id="tela-bloqueio" onclick="location.reload()">
        <i class="fas fa-lock" style="font-size: 60px;color: #f39c12;"></i>
    <div class="mensagem">Página bloqueada por inatividade<br><small>(Clique para recarregar)</small></div>
</div>


   
   <?php
  
 

$nome = htmlspecialchars($usuarioNome);
$nome_lower = strtolower(trim($nome));




// Lista de nomes femininos conhecidos que não terminam com "a"
$nomes_femininos_excecoes = ['ester', 'inês', 'ruth', 'noemi', 'miriam'];

if (in_array($nome_lower, $nomes_femininos_excecoes) || mb_substr($nome_lower, -1) === 'a') {
    $tratamento = 'Prezada';
} else {
    $tratamento = 'Prezado';
}

// Saudação baseada no horário
date_default_timezone_set('America/Sao_Paulo');
$horaAtual = date('H');
$saudacao = ($horaAtual < 12) ? '☀️ Bom dia' : (($horaAtual < 18) ? '🌤️ Boa tarde' : '🌙 Boa noite');

// Mensagem final com ícones
$mensagem = "$saudacao, $tratamento <b>$nome</b>! <i class='fas fa-briefcase'></i> Desejamos um excelente expediente! <i class='fas fa-smile'></i>";


//$hora_entrada_cad = date('H:i', strtotime($resultado['hora_entrada_cad']));
//$hora_saida_cad = date('H:i', strtotime($resultado['hora_saida_cad']));
?>




<div class="form">
   <h2><strong>REGISTRO DE PONTO</strong></h2>
<br>
  <p><?php echo $mensagem; ?></p>
  
   <div class="relogio" id="relogio"></div>

   <h2><b><?php echo $data_extenso; ?></b></h2>

   <h2 id="titulo"><b>APERTE O BOTÃO<br>PARA MARCAR O PONTO</b></h2>

   <div style="display: flex; justify-content: center; gap: 40px; align-items: center; margin-bottom: 10px; text-align: center;">
       <div>
           <i class="fas fa-sign-in-alt" style="color: green;"></i>
           <b> Entrada:</b> <?php echo $hora_entrada_cad; ?>
       </div>
       <div>
           <i class="fas fa-sign-out-alt" style="color: red;"></i>
           <b> Saída:</b> <?php echo $hora_saida_cad; ?>
       </div>
       <div>
           <i class="fas fa-utensils" style="color: orange;"></i>
           <b> Almoço:</b> <?php echo $intervalo_almoco_texto; ?>
       </div>
   </div>
</div>


<form action="registrar_ponto.php" id="registroForm" method="post">
    <input type="hidden" name="funcionario" value="<?php echo htmlspecialchars($usuarioNome); ?>">
    <input type="hidden" name="usuarioId" value="<?php echo htmlspecialchars($usuarioId); ?>">
    <input type="hidden" id="latitude" name="latitude" value="">
    <input type="hidden" id="longitude" name="longitude" value="">
    <input type="hidden" id="endereco" name="endereco" value="">
    <input type="hidden" name="ip_address" value="<?php echo htmlspecialchars($_SERVER['REMOTE_ADDR']); ?>">
   
    <p id="mensagem-erro" style="display: none;" class="mensagem-erro">
        ⚠️ Por favor, aguarde <span id="contador">60</span> segundos antes de realizar um novo registro.
    </p>

<style>
.container {
    display: flex;
    justify-content: center;
    align-items: center;
    flex-direction: column; /* empilha os botões verticalmente */
    gap: 20px; /* espaçamento entre botões */
    height: auto;
    margin-top: 40px;
}

.btn-round {
    position: relative;
    top: -50px; /* valor ajustável para subir mais */
    width: 400px;
    height: 400px;
    border-radius: 50%;
    font-size: 20px;
    font-weight: bold;
    text-transform: uppercase;
    color: white;
    background: url('botao.png') no-repeat center;
    background-size: cover;
    display: flex;
    align-items: center;
    justify-content: center;
    text-align: center;
    padding: 0;
    border: none;
    cursor: pointer;
    box-shadow: 5px 5px 15px rgba(0, 0, 0, 0.3);
    transition: transform 0.2s ease-in-out, box-shadow 0.2s ease-in-out;
}
.mensagem-erro {
    margin-top: 20px;
    color: #b30000;
    background-color: #ffe6e6;
    padding: 12px;
    border: 1px solid #ffcccc;
    border-radius: 6px;
    font-size: 16px;
    font-weight: 500;
    text-align: center;
}


/* Responsivo */
@media (max-width: 600px) {
    .btn-round {
        width: 300px;
        height: 300px;
        font-size: 18px;
    }
}
</style>
<div class="container">
  <input type="hidden" id="tipoRegistroInput" name="tipo_registro">

  <?php if (!($ja_registrou_entrada && $ja_registrou_saida)): ?>
    <div class="acoes-ponto">
      <?php if (!$ja_registrou_entrada): ?>
        <!-- ENTRADA -->
        <button type="button"
                class="btn btn-round confirmar-registro"
                data-tipo="entrada">
          REGISTRAR ENTRADA
        </button>
        <p class="mensagem font-lg">🚪 Por favor, registre sua <b>entrada</b> para iniciar o expediente.</p>

      <?php else: ?>

        <?php if ($ja_registrou_saida_temporaria && !$ja_registrou_retorno_saida_temporaria): ?>
          <!-- RETORNO SAÍDA TEMPORÁRIA 1 -->
          <button type="button"
                  class="btn btn-round confirmar-registro"
                  data-tipo="retorno_saida_temporaria">
            REGISTRAR RETORNO <br>SAÍDA TEMPORÁRIA
          </button>
          <p class="mensagem font-md">↩️ Retornou da <b>saída temporária</b>? Registre o retorno para continuar o expediente.</p>

        <?php elseif ($ja_registrou_retorno_saida_temporaria && !$ja_registrou_almoco_entrada): ?>
          <!-- INÍCIO DO ALMOÇO -->
          <button type="button"
                  class="btn btn-round confirmar-registro"
                  data-tipo="intervalo_almoco_entrada">
            INICIAR INTERVALO <br>DE ALMOÇO
          </button>
          <p class="mensagem font-lg">🍽️ Inicie seu intervalo de almoço com o registro abaixo.</p>

        <?php elseif ($ja_registrou_almoco_entrada && !$ja_registrou_almoco_saida): ?>
          <!-- RETORNO DO ALMOÇO -->
          <button type="button"
                  class="btn btn-round confirmar-registro"
                  data-tipo="intervalo_almoco_saida">
            REGISTRAR RETORNO <br>DO ALMOÇO
          </button>
          <p class="mensagem font-lg">⏰ Registre o retorno do <b>almoço</b> para retomar suas atividades.</p>

        <?php elseif ($ja_registrou_almoco_saida && !$ja_registrou_saida_temporaria2): ?>
          <!-- SAÍDA TEMPORÁRIA 2 OU SAÍDA FINAL -->
          <button type="button"
                  class="btn btn-round confirmar-duplo"
                  data-tipo1="nova_saida_temporaria"
                  data-tipo2="saida">
            REGISTRAR SAÍDA <br>(TEMPORÁRIA 2 OU FINAL)
          </button>
          <p class="mensagem font-md">🚶 Deseja registrar uma <b>segunda saída temporária</b> ou <b>ir embora</b>?</p>

        <?php elseif ($ja_registrou_saida_temporaria2 && !$ja_registrou_retorno_saida_temporaria2): ?>
          <!-- RETORNO SAÍDA TEMPORÁRIA 2 -->
          <button type="button"
                  class="btn btn-round confirmar-registro"
                  data-tipo="retorno_nova_saida_temporaria">
            REGISTRAR RETORNO <br>DA SAÍDA TEMPORÁRIA
          </button>
          <p class="mensagem font-md">↩️ Registre o retorno da <b>segunda saída temporária</b>.</p>

        <?php elseif (!$ja_registrou_saida_temporaria && !$ja_registrou_almoco_entrada): ?>
          <!-- SAÍDA TEMPORÁRIA 1 OU ALMOÇO -->
          <button type="button"
                  class="btn btn-round confirmar-duplo"
                  data-tipo1="saida_temporaria"
                  data-tipo2="intervalo_almoco_entrada">
            REGISTRAR SAÍDA <br>(ALMOÇO OU TEMPORÁRIA)
          </button>
          <p class="mensagem font-lg">🚶 Selecione o tipo de saída: <b>temporária</b> ou <b>intervalo de almoço</b>.</p>

        <?php elseif (!$ja_registrou_saida): ?>
          <!-- SAÍDA FINAL -->
          <button type="button"
                  class="btn btn-round confirmar-registro"
                  data-tipo="saida">
            REGISTRAR <br>SAÍDA FINAL
          </button>
          <p class="mensagem font-lg">📤 Finalize seu expediente registrando sua <b>saída</b>.</p>
        <?php endif; ?>

      <?php endif; ?>
    </div>
  <?php endif; ?>

  <?php if ($ja_registrou_entrada && $ja_registrou_saida): ?>
    <p class="mensagem2 font-lg"> Todos os registros de ponto do dia foram concluídos com sucesso.</p>  
</div>

 <script>
    document.addEventListener("DOMContentLoaded", function() {
        var titulo = document.getElementById("titulo");

        // Array com várias frases para cada dia da semana
        var frases = {
            "Segunda-feira": [
                "Vamos começar a semana com força total! Seu esforço é a chave para o sucesso.",
                "Novos desafios surgem, mas você é mais forte do que qualquer um deles!",
                "Segunda-feira é o momento perfeito para definir novas metas. Vamos com tudo!"
            ],
            "Terça-feira": [
                "Continue firme! A sua dedicação está levando você mais perto de suas conquistas.",
                "O caminho para o sucesso é construído com passos pequenos, mas firmes. Vamos lá!",
                "Terça-feira é o dia de manter o foco! A cada passo, você chega mais perto de seus objetivos."
            ],
            "Quarta-feira": [
                "A metade da semana já passou! Mantenha o ritmo e siga em frente.",
                "Cada dia é uma nova oportunidade de crescer. Você está indo muito bem!",
                "Quarta-feira é o dia de recarregar as energias e seguir com mais força!"
            ],
            "Quinta-feira": [
                "A jornada está quase no fim, mas o melhor ainda está por vir! Acredite no seu potencial.",
                "O esforço que você está colocando hoje trará grandes recompensas amanhã. Continue assim!",
                "Quinta-feira é o dia perfeito para dar o seu melhor. Você vai longe!"
            ],
            "Sexta-feira": [
                "Chegamos ao fim da semana! Parabéns pela sua dedicação e esforço.",
                "Seu trabalho árduo está valendo a pena. Descanse agora, você merece!",
                "Sexta-feira chegou, e com ela o reconhecimento pelo seu esforço. Bom descanso!"
            ],
            "Sábado": [
                "Um ótimo dia para refletir sobre o que conquistamos até aqui. Continue firme!",
                "Descanse um pouco, mas não pare de pensar nas suas conquistas. Você está indo bem!",
                "Sábado é dia de aproveitar o descanso, mas também de pensar nas próximas vitórias."
            ],
            "Domingo": [
                "Um dia para descansar e se preparar para mais uma semana de sucesso!",
                "Aproveite o descanso, você tem grandes coisas esperando por você na próxima semana!",
                "Domingo é o dia de recarregar as energias para a semana que vem. Você está pronto!"
            ]
        };

        // Pega o dia atual da semana (0 = Domingo, 1 = Segunda, etc.)
        var diaDaSemana = new Date().getDay();

        // Mapeia o dia da semana para o nome correspondente
        var dias = ["Domingo", "Segunda-feira", "Terça-feira", "Quarta-feira", "Quinta-feira", "Sexta-feira", "Sábado"];
        var nomeDia = dias[diaDaSemana];

        // Pega uma frase aleatória do array do dia correspondente
        var frasesDoDia = frases[nomeDia];
        var fraseAleatoria = frasesDoDia[Math.floor(Math.random() * frasesDoDia.length)];

        if (titulo) {
            // Atualiza o título com a frase motivacional e a frase do dia
            titulo.innerHTML = "<i class='fas fa-trophy' style='font-size: 48px; color: #000000;'></i> <strong style='color: #000000;'>VOCÊ BATEU TODOS OS PONTOS!</strong>" + 
                               "<p style='text-align: center; font-size: 20px; color: #000000;'>" + 
                               "<i class='fas fa-smile-wink' style='font-size: 48px; color: #ffcc00;'></i> " + 
                               nomeDia + "<br>" + fraseAleatoria + 
                               "</p>";
            titulo.classList.add("oculto");  // Oculta o título após a troca
        }
    });
</script>




<?php endif; ?>



    </div>
<!-- Campos escondidos -->
<input type="hidden" id="latitude">
<input type="hidden" id="longitude">
<input type="hidden" id="endereco"> <!-- Novo campo -->

<!-- Mapa -->
<div id="map" style="height: 400px; width: 100%; border: 1px solid #ccc;"></div>

<!-- Leaflet CSS + JS -->
<link rel="stylesheet" href="https://unpkg.com/leaflet/dist/leaflet.css" />
<script src="https://unpkg.com/leaflet/dist/leaflet.js"></script>
<script>
let horaAtual = null;

function atualizarExibicao() {
    if (!horaAtual) return;

    horaAtual.setSeconds(horaAtual.getSeconds() + 1);

    const horas = horaAtual.getHours().toString().padStart(2, '0');
    const minutos = horaAtual.getMinutes().toString().padStart(2, '0');
    const segundos = horaAtual.getSeconds().toString().padStart(2, '0');

    document.getElementById('relogio').textContent = `${horas}:${minutos}:${segundos}`;
}

// Atualiza a hora do servidor e sincroniza
function sincronizarComServidor() {
    fetch('relogio.php')
        .then(response => response.json())
        .then(data => {
            horaAtual = new Date(data.hora.replace(' ', 'T')); // transforma em objeto Date
        })
        .catch(error => {
            console.error("Erro ao buscar hora do servidor:", error);
            document.getElementById('relogio').textContent = "Erro ao buscar hora";
        });
}

// Inicia o relógio
sincronizarComServidor();
setInterval(atualizarExibicao, 1000);     // atualiza a cada segundo
setInterval(sincronizarComServidor, 60000); // sincroniza com o servidor a cada 1 minuto
</script>



<script>
    let map;
    let marker;
    let ultimoEndereco = '';

    function initMap() {
        if (navigator.geolocation) {
            atualizarLocalizacao(); // Chamada inicial imediata
            setInterval(atualizarLocalizacao, 15000); // Atualiza a cada 15s
        } else {
            alert("Geolocalização não é suportada por este navegador.");
        }
    }

    function atualizarLocalizacao() {
        navigator.geolocation.getCurrentPosition(showPosition, showError, {
            enableHighAccuracy: true,
            timeout: 10000,
            maximumAge: 0
        });
    }

    function showPosition(position) {
        const lat = position.coords.latitude;
        const lon = position.coords.longitude;

        document.getElementById('latitude').value = lat;
        document.getElementById('longitude').value = lon;

        // Inicializa mapa uma vez
        if (!map) {
            map = L.map('map').setView([lat, lon], 15);
            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                attribution: '© OpenStreetMap'
            }).addTo(map);
        } else {
            map.setView([lat, lon], 15);
        }

        // Atualiza marcador
        if (!marker) {
            marker = L.marker([lat, lon]).addTo(map);
        } else {
            marker.setLatLng([lat, lon]);
        }

        // Busca endereço e atualiza popup
        const url = `https://nominatim.openstreetmap.org/reverse?format=jsonv2&lat=${lat}&lon=${lon}`;
        fetch(url)
            .then(response => response.json())
            .then(data => {
                let endereco = "";
                if (data.address) {
                    const address = data.address;
                    const rua = address.road || address.residential || address.footway || address.pedestrian || "";
                    const bairro = address.suburb || address.neighbourhood || address.village || address.hamlet || "";

                    endereco = data.display_name;
                    if (bairro !== "") endereco += `, ${bairro}`;
                }

                if (endereco && endereco !== ultimoEndereco) {
                    ultimoEndereco = endereco;
                    document.getElementById("endereco").value = endereco;

                    const popupContent = `
                        <style>
                            @keyframes blink-text {
                                0%, 100% { opacity: 1; }
                                50% { opacity: 0; }
                            }
                            .blinking-text {
                                animation: blink-text 1.2s infinite;
                                color: red;
                                font-weight: bold;
                            }
                            .info-text {
                                font-size: 12px;
                                color: #666;
                                margin-top: 8px;
                            }
                            .reload-link {
                                color: #007BFF;
                                text-decoration: underline;
                                cursor: pointer;
                            }
                        </style>
                        <div style="font-family: 'Segoe UI', Arial, sans-serif; font-size: 14px; line-height: 1.6;">
                            Estimado(a): <strong><?php echo htmlspecialchars($nome, ENT_QUOTES, 'UTF-8'); ?></strong><br>
                            <span style="color: #444;">
                                <span class="blinking-light">✅</span> Você está aqui.
                            </span><br>
                            <div class="blinking-text">📍 ${endereco}</div>
                            <div class="info-text">
                                Se o endereço estiver incorreto, 
                                <span class="reload-link" onclick="Notiflix.Notify.info('Atualizando a localização...'); setTimeout(() => location.reload(), 2000);">clique aqui</span>.
                            </div>
                        </div>
                    `;
                    marker.bindPopup(popupContent).openPopup();
                }
            })
            .catch(() => {
                document.getElementById("endereco").value = "Erro ao obter endereço.";
            });
    }

    function showError(error) {
        switch (error.code) {
            case error.PERMISSION_DENIED:
                alert("Usuário negou a solicitação de Geolocalização.");
                break;
            case error.POSITION_UNAVAILABLE:
                alert("Informações de localização indisponíveis.");
                break;
            case error.TIMEOUT:
                alert("A solicitação expirou.");
                break;
            default:
                alert("Erro desconhecido.");
                break;
        }
    }

    // Inicia tudo ao carregar a página
    window.onload = initMap;
</script>
 
  <script>
const tempoInatividade = 20000; // 20 segundos

let timeout;

function resetarTimer() {
    clearTimeout(timeout);
    timeout = setTimeout(bloquearTela, tempoInatividade);
}

function bloquearTela() {
    document.getElementById('tela-bloqueio').style.display = 'flex';
}

['mousemove', 'keydown', 'scroll', 'click'].forEach(evt => {
    document.addEventListener(evt, resetarTimer);
});

resetarTimer();
</script>



<script>
    // Recarrega a página a cada 1 minuto
    setInterval(function() {
        location.reload();
    }, 60000); // 60000 ms = 1 minuto
</script>

<script>
    const hoje = new Date().toLocaleDateString(); // Ex: "14/05/2025"
    const chaveUltimoReload = "ultima_data_reload";

    // Verifica se já recarregou hoje
    if (localStorage.getItem(chaveUltimoReload) !== hoje) {
        localStorage.setItem(chaveUltimoReload, hoje);
        location.reload();
    }
</script>
<script>
if (navigator.geolocation) {
  navigator.geolocation.getCurrentPosition(function(pos) {
    const latitude = pos.coords.latitude;
    const longitude = pos.coords.longitude;

    fetch('salvar_localizacao_old.php', {
      method: 'POST',
      headers: {'Content-Type': 'application/json'},
      body: JSON.stringify({latitude, longitude})  // só envia lat e lon, id e nome vêm da sessão PHP
    })
    .then(res => res.json())
    .then(data => {
      if (data.status === 'ok') {
        console.log(`Localização salva para ${data.nome} (ID: ${data.idUsuario}), último acesso: ${data.ultimo_acesso}`);
      } else {
        console.error('Erro:', data.erro || 'Desconhecido');
      }
    })
    .catch(err => console.error('Falha na requisição:', err));

  }, function(erro) {
    alert('Erro ao obter localização: ' + erro.message);
  });
} else {
  alert('Seu navegador não suporta geolocalização.');
}
</script>
<script src="https://cdn.jsdelivr.net/npm/notiflix/dist/notiflix-aio-3.2.6.min.js"></script>
<script>
document.addEventListener("DOMContentLoaded", function () {
  const form         = document.getElementById("registroForm");
  const tipoInput    = document.getElementById("tipoRegistroInput");
  const mensagemErro = document.getElementById("mensagem-erro");
  const contadorSpan = document.getElementById("contador");
  const TEMPO_ESPERA = 60;

  // Calcula quantos segundos faltam até liberar novo registro
  function tempoRestante() {
    const t = sessionStorage.getItem("ultimoRegistro");
    if (!t) return 0;
    return Math.max(0, TEMPO_ESPERA - (Math.floor(Date.now()/1000) - parseInt(t, 10)));
  }

  // Exibe o contador e bloqueia botões enquanto > 0
  function iniciarContador(sec) {
    mensagemErro.style.display = "block";
    contadorSpan.textContent   = sec;
    const iv = setInterval(() => {
      sec--;
      if (sec <= 0) {
        clearInterval(iv);
        mensagemErro.style.display = "none";
      } else {
        contadorSpan.textContent = sec;
      }
    }, 1000);
  }

  // Se ainda estiver no bloqueio, inicia o contador na tela
  const secs = tempoRestante();
  if (secs > 0) iniciarContador(secs);

  // Se veio de um submit bem‑sucedido, exibe notificação
  if (sessionStorage.getItem("registroSucesso") === "1") {
    Notiflix.Notify.success("✅ Registro realizado com sucesso.");
    sessionStorage.removeItem("registroSucesso");
  }

  // Função genérica para confirmar e submeter
  function confirmarRegistro(tipo, label) {
    Notiflix.Confirm.show(
      'Confirmação de Registro',
      `Deseja realmente ${label.toLowerCase()}?`,
      'Sim', 'Não',
      () => {
        const now = Math.floor(Date.now()/1000).toString();
        if (tipo === "saida") {
          sessionStorage.removeItem("ultimoRegistro");
        } else {
          sessionStorage.setItem("ultimoRegistro", now);
        }
        sessionStorage.setItem("registroSucesso", "1");
        tipoInput.value = tipo;
        form.submit();
      },
      () => Notiflix.Notify.info('Ação cancelada.')
    );
  }

  // --------- BOTÕES SIMPLES ----------
  document.querySelectorAll(".confirmar-registro").forEach(btn => {
    btn.addEventListener("click", () => {
      if (tempoRestante() > 0) {
        return Notiflix.Notify.failure("⏳ Aguarde o bloqueio para novo registro.");
      }
      const tipo  = btn.getAttribute("data-tipo");
      const labels= {
        entrada:                   "REGISTRAR ENTRADA",
        intervalo_almoco_entrada:  "INICIAR INTERVALO DO ALMOÇO",
        intervalo_almoco_saida:    "RETORNAR DO ALMOÇO",
        saida:                     "REGISTRAR SAÍDA FINAL",
        saida_temporaria:          "REGISTRAR SAÍDA TEMPORÁRIA",
        retorno_saida_temporaria:  "RETORNAR DA SAÍDA TEMPORÁRIA",
        nova_saida_temporaria:     "REGISTRAR NOVA SAÍDA TEMPORÁRIA",
        retorno_nova_saida_temporaria: "RETORNAR DA NOVA SAÍDA TEMPORÁRIA"
      };
      confirmarRegistro(tipo, labels[tipo] || "REGISTRAR PONTO");
    });
  });

  // --------- BOTÕES DUPLO ----------
  document.querySelectorAll(".confirmar-duplo").forEach(btn => {
    btn.addEventListener("click", () => {
      if (tempoRestante() > 0) {
        return Notiflix.Notify.failure("⏳ Aguarde o bloqueio para novo registro.");
      }

      const t1 = btn.getAttribute("data-tipo1");
      const t2 = btn.getAttribute("data-tipo2");

      // Caso: ALMOÇO vs SAÍDA TEMPORÁRIA
      if (t1 === "saida_temporaria" && t2 === "intervalo_almoco_entrada") {
        Notiflix.Confirm.show(
          'Qual tipo de saída deseja registrar?',
          'Escolha uma opção:',
          'Almoço', 'Saída Temporária',
          () => { // Almoço
            const now = Math.floor(Date.now()/1000).toString();
            sessionStorage.setItem("ultimoRegistro", now);
            sessionStorage.setItem("registroSucesso", "1");
            tipoInput.value = t2;
            form.submit();
          },
          () => { // Temporária
            const now = Math.floor(Date.now()/1000).toString();
            sessionStorage.setItem("ultimoRegistro", now);
            sessionStorage.setItem("registroSucesso", "1");
            tipoInput.value = t1;
            form.submit();
          }
        );
        return;
      }

      // Caso: TEMPORÁRIA 2 vs SAÍDA FINAL
      if (t1 === "nova_saida_temporaria" && t2 === "saida") {
        Notiflix.Confirm.show(
          'Qual tipo de saída deseja registrar?',
          'Escolha uma opção:',
          'Temporária 2', 'Ir embora',
          () => { // Temporária 2
            const now = Math.floor(Date.now()/1000).toString();
            sessionStorage.setItem("ultimoRegistro", now);
            sessionStorage.setItem("registroSucesso", "1");
            tipoInput.value = t1;
            form.submit();
          },
          () => { // Saída final
            sessionStorage.removeItem("ultimoRegistro");
            sessionStorage.setItem("registroSucesso", "1");
            tipoInput.value = t2;
            form.submit();
          }
        );
        return;
      }

    });
  });

});
</script>
<?php include_once("rodape.php");?>
	</body>
	</html>



  
