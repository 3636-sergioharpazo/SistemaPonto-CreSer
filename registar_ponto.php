<?php
session_start();
include_once("principal.php");
include_once("conexao.php");

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $funcionario = $_POST['funcionario'];
    $tipo_registro = $_POST['tipo_registro'];
    $latitude = $_POST['latitude'];
    $longitude = $_POST['longitude'];
    $endereco = $_POST['endereco'];
    $ip_address = $_SERVER['REMOTE_ADDR'];

    date_default_timezone_set('America/Sao_Paulo');
    $data_atual = date('Y-m-d');
    $hora_atual = date('H:i:s');
    $servico = $_SESSION['projeto'];
    $departamento = $_SESSION['usuarioNivelAcesso'];
    $usuarioId = $_SESSION['usuarioId'];
    
    switch ($tipo_registro) {
    case 'entrada':
        registrarEntrada($pdo_conn, $usuarioId, $funcionario, $data_atual, $hora_atual, $latitude, $longitude, $endereco, $ip_address, $servico, $departamento);
        break;

    case 'saida':
        registrarSaida($pdo_conn, $usuarioId, $funcionario, $data_atual, $hora_atual, $latitude, $longitude, $endereco, $ip_address, $servico, $departamento);
        break;

    case 'intervalo_almoco_entrada':
        registrarInicioIntervaloAlmoco($pdo_conn, $usuarioId, $funcionario, $data_atual, $hora_atual, $latitude, $longitude, $endereco, $ip_address, $servico, $departamento);
        break;

    case 'intervalo_almoco_saida':
        registrarFimIntervaloAlmoco($pdo_conn, $usuarioId, $funcionario, $data_atual, $hora_atual, $latitude, $longitude, $endereco, $ip_address, $servico, $departamento);
        break;

  case 'saida_temporaria':
    $query = "UPDATE pontos SET hora_saida_temp = :hora, end_saida_temp = :endereco 
              WHERE idUsuario = :usuario AND data_atual = :data";
    $stmt = $pdo_conn->prepare($query);
    $stmt->bindValue(':hora', $hora_atual);
    $stmt->bindValue(':endereco', $endereco);
    $stmt->bindValue(':usuario', $usuarioId);
    $stmt->bindValue(':data', $data_atual);
    $stmt->execute();
    break;

case 'retorno_saida_temporaria':
    $query = "UPDATE pontos 
              SET hora_saida_temp_retorno = :hora, end_saida_temp_retorno = :endereco 
              WHERE idUsuario = :usuario AND data_atual = :data ";

    $stmt = $pdo_conn->prepare($query);
    $stmt->bindValue(':hora', $hora_atual);
    $stmt->bindValue(':endereco', $endereco);
    $stmt->bindValue(':usuario', $usuarioId);
    $stmt->bindValue(':data', $data_atual);

    if ($stmt->execute()) {
        if ($stmt->rowCount() > 0) {
            setSessionMessage('success', 'Retorno da saída temporária registrado com sucesso.');
        } else {
            setSessionMessage('warning', 'Nenhum registro encontrado para atualizar o retorno da saída temporária.');
        }
    } else {
        $errorInfo = $stmt->errorInfo();
        setSessionMessage('danger', 'Erro ao registrar retorno da saída temporária: ' . $errorInfo[2]);
    }
    break;

case 'nova_saida_temporaria':
    $query = "UPDATE pontos 
              SET hora_saida_temp2 = :hora, end_saida_temp2 = :endereco 
              WHERE idUsuario = :usuario AND data_atual = :data";
    $stmt = $pdo_conn->prepare($query);
    $stmt->bindValue(':hora', $hora_atual);
    $stmt->bindValue(':endereco', $endereco);
    $stmt->bindValue(':usuario', $usuarioId);
    $stmt->bindValue(':data', $data_atual);
    if ($stmt->execute()) {
        setSessionMessage('success', 'Segunda saída temporária registrada com sucesso.');
    } else {
        $errorInfo = $stmt->errorInfo();
        setSessionMessage('danger', 'Erro ao registrar segunda saída temporária: ' . $errorInfo[2]);
    }
    break;

case 'retorno_nova_saida_temporaria':
    $query = "UPDATE pontos 
              SET hora_saida_temp_retorno2 = :hora, end_saida_temp_retorno2 = :endereco 
              WHERE idUsuario = :usuario AND data_atual = :data";
    $stmt = $pdo_conn->prepare($query);
    $stmt->bindValue(':hora', $hora_atual);
    $stmt->bindValue(':endereco', $endereco);
    $stmt->bindValue(':usuario', $usuarioId);
    $stmt->bindValue(':data', $data_atual);
    if ($stmt->execute()) {
        if ($stmt->rowCount() > 0) {
            setSessionMessage('success', 'Retorno da segunda saída temporária registrado com sucesso.');
        } else {
            setSessionMessage('warning', 'Nenhum registro encontrado para atualizar o retorno da segunda saída temporária.');
        }
    } else {
        $errorInfo = $stmt->errorInfo();
        setSessionMessage('danger', 'Erro ao registrar retorno da segunda saída temporária: ' . $errorInfo[2]);
    }
    break;

    default:
        setSessionMessage('danger', 'Tipo de registro inválido.');
        header("Location: inicio.php");
        exit();
}

// ✅ Redireciona para a página inicial após qualquer registro válido
header("Location: inicio.php");
exit();
}
// Função para registrar entrada
function registrarEntrada($pdo_conn, $usuarioId, $funcionario, $data_atual, $hora_atual, $latitude, $longitude, $endereco, $ip_address, $servico, $departamento) {
    $query_check_entrada = "SELECT COUNT(*) as count FROM pontos WHERE idUsuario = :idUsuario AND data_atual = :data_atual AND tipo_registro = 'entrada'";
    $stmt_check_entrada = $pdo_conn->prepare($query_check_entrada);
    $stmt_check_entrada->bindParam(':idUsuario', $usuarioId);
    $stmt_check_entrada->bindParam(':data_atual', $data_atual);
    $stmt_check_entrada->execute();
    $result_check_entrada = $stmt_check_entrada->fetch(PDO::FETCH_ASSOC);

    if ($result_check_entrada['count'] > 0) {
        setSessionMessage('warning', 'Registro de entrada já existe para hoje.');
        header("Location: inicio.php");
        exit();
    }

    $query = "INSERT INTO pontos (idUsuario, nome, data_atual, hora_atual, tipo_registro, latitude, longitude, endereco, ip_address, servico, departamento)
              VALUES (:idUsuario, :nome, :data_atual, :hora_atual, 'entrada', :latitude, :longitude, :endereco, :ip_address, :servico, :departamento)";

    $stmt = $pdo_conn->prepare($query);
    $stmt->bindParam(':idUsuario', $usuarioId);
    $stmt->bindParam(':nome', $funcionario);
    $stmt->bindParam(':data_atual', $data_atual);
    $stmt->bindParam(':hora_atual', $hora_atual);
    $stmt->bindParam(':latitude', $latitude);
    $stmt->bindParam(':longitude', $longitude);
    $stmt->bindParam(':endereco', $endereco);
    $stmt->bindParam(':ip_address', $ip_address);
    $stmt->bindParam(':servico', $servico);
    $stmt->bindParam(':departamento', $departamento);

    if ($stmt->execute()) {
        setSessionMessage('success', 'Registro de entrada realizado com sucesso.');
        calcularSaldoHoras($pdo_conn, $usuarioId, $data_atual);
        header("Location: inicio.php");
        exit();
    } else {
        setSessionMessage('danger', 'Ocorreu um erro ao registrar a entrada.');
        header("Location: inicio.php");
        exit();
    }
}

// Função para registrar saída
function registrarSaida($pdo_conn, $usuarioId, $funcionario, $data_atual, $hora_atual, $latitude, $longitude, $endereco, $ip_address, $servico, $departamento) {
    try {
        $query_update = "UPDATE pontos 
                         SET hora_saida = :hora_saida, ip_saida = :ip_saida, end_saida = :end_saida
                         WHERE idUsuario = :idUsuario AND data_atual = :data_atual AND tipo_registro = 'entrada'";

        $stmt_update = $pdo_conn->prepare($query_update);
        $stmt_update->bindParam(':hora_saida', $hora_atual);
        $stmt_update->bindParam(':ip_saida', $ip_address);
        $stmt_update->bindParam(':end_saida', $endereco);
        $stmt_update->bindParam(':idUsuario', $usuarioId);
        $stmt_update->bindParam(':data_atual', $data_atual);

        if ($stmt_update->execute()) {
            setSessionMessage('success', 'Saída registrada com sucesso.');
            calcularSaldoHoras($pdo_conn, $usuarioId, $data_atual);
            header("Location: inicio.php");
            exit();
        } else {
            $errorInfo = $stmt_update->errorInfo();
            setSessionMessage('danger', 'Falha ao registrar a saída: ' . $errorInfo[2]);
            header("Location: inicio.php");
            exit();
        }
    } catch (PDOException $e) {
        setSessionMessage('danger', 'Erro no banco de dados: ' . $e->getMessage());
        header("Location: inicio.php");
        exit();
    }
}

// Função para registrar início do intervalo de almoço
function registrarInicioIntervaloAlmoco($pdo_conn, $usuarioId, $funcionario, $data_atual, $hora_atual, $latitude, $longitude, $endereco, $ip_address, $servico, $departamento) {
    try {
        $query_update = "UPDATE pontos 
                         SET hora_entrada_al = :hora_entrada_al, ip_almoco = :ip_almoco, end_almoco = :end_almoco
                         WHERE idUsuario = :idUsuario AND data_atual = :data_atual AND tipo_registro = 'entrada'";

        $stmt_update = $pdo_conn->prepare($query_update);
        $stmt_update->bindParam(':hora_entrada_al', $hora_atual);
        $stmt_update->bindParam(':ip_almoco', $ip_address);
        $stmt_update->bindParam(':end_almoco', $endereco);
        $stmt_update->bindParam(':idUsuario', $usuarioId);
        $stmt_update->bindParam(':data_atual', $data_atual);

        if ($stmt_update->execute()) {
            setSessionMessage('success', 'Início do intervalo de almoço registrado com sucesso.');
            calcularSaldoHoras($pdo_conn, $usuarioId, $data_atual);
            header("Location: inicio.php");
            exit();
        } else {
            $errorInfo = $stmt_update->errorInfo();
            setSessionMessage('danger', 'Falha ao registrar início do intervalo de almoço: ' . $errorInfo[2]);
            header("Location: inicio.php");
            exit();
        }
    } catch (PDOException $e) {
        setSessionMessage('danger', 'Erro no banco de dados: ' . $e->getMessage());
        header("Location: inicio.php");
        exit();
    }
}

// Função para registrar fim do intervalo de almoço
function registrarFimIntervaloAlmoco($pdo_conn, $usuarioId, $funcionario, $data_atual, $hora_atual, $latitude, $longitude, $endereco, $ip_address, $servico, $departamento) {
    try {
        $query_update = "UPDATE pontos 
                         SET hora_saida_al = :hora_saida_al, ip_almoco_saida = :ip_almoco_saida, end_almoco_saida = :end_almoco_saida
                         WHERE idUsuario = :idUsuario AND data_atual = :data_atual AND tipo_registro = 'entrada'";

        $stmt_update = $pdo_conn->prepare($query_update);
        $stmt_update->bindParam(':hora_saida_al', $hora_atual);
        $stmt_update->bindParam(':ip_almoco_saida', $ip_address);
        $stmt_update->bindParam(':end_almoco_saida', $endereco);
        $stmt_update->bindParam(':idUsuario', $usuarioId);
        $stmt_update->bindParam(':data_atual', $data_atual);

        if ($stmt_update->execute()) {
            setSessionMessage('success', 'Fim do intervalo de almoço registrado com sucesso.');
            calcularSaldoHoras($pdo_conn, $usuarioId, $data_atual);
            header("Location: inicio.php");
            exit();
        } else {
            $errorInfo = $stmt_update->errorInfo();
            setSessionMessage('danger', 'Falha ao registrar fim do intervalo de almoço: ' . $errorInfo[2]);
            header("Location: inicio.php");
            exit();
        }
    } catch (PDOException $e) {
        setSessionMessage('danger', 'Erro no banco de dados: ' . $e->getMessage());
        header("Location: inicio.php");
        exit();
    }
}


// Função para calcular saldo de horas
function calcularSaldoHoras($pdo_conn, $usuarioId, $data_atual) {
    $stmtUsuarios = $pdo_conn->prepare("SELECT DISTINCT idUsuario FROM pontos");
    $stmtUsuarios->execute();
    $usuarios = $stmtUsuarios->fetchAll(PDO::FETCH_ASSOC);

    foreach ($usuarios as $usuario) {
        $idUsuario = $usuario['idUsuario'];
        $saldo_acumulado = 0;

        $stmtPontos = $pdo_conn->prepare("SELECT data_atual, hora_atual, hora_saida FROM pontos WHERE idUsuario = :idUsuario ORDER BY data_atual ASC");
        $stmtPontos->bindParam(':idUsuario', $idUsuario);
        $stmtPontos->execute();
        $pontos = $stmtPontos->fetchAll(PDO::FETCH_ASSOC);

        $stmtJornada = $pdo_conn->prepare("SELECT hora_entrada_cad, hora_saida_cad FROM tabela_usuarios WHERE idUsuario = :idUsuario");
        $stmtJornada->bindParam(':idUsuario', $idUsuario);
        $stmtJornada->execute();
        $dadosJornada = $stmtJornada->fetch(PDO::FETCH_ASSOC);

        if ($dadosJornada) {
            $hora_entrada_cad = strtotime($dadosJornada['hora_entrada_cad']);
            $hora_saida_cad = strtotime($dadosJornada['hora_saida_cad']);
            $jornada_diaria = ($hora_saida_cad - $hora_entrada_cad) / 3600;
        } else {
            $jornada_diaria = 8;
        }

        foreach ($pontos as $ponto) {
            $data = $ponto['data_atual'];
            $hora_entrada = $ponto['hora_atual'];
            $hora_saida = $ponto['hora_saida'];

            if (!empty($hora_entrada) && !empty($hora_saida)) {
                $hora_entrada_dt = strtotime($hora_entrada);
                $hora_saida_dt = strtotime($hora_saida);
                $tolerancia_segundos = 10 * 60; // tolerância 10 minutos

                // Aplica tolerância na entrada
                $diferenca_entrada = abs($hora_entrada_dt - $hora_entrada_cad);
                if ($diferenca_entrada <= $tolerancia_segundos) {
                    $hora_entrada_dt = $hora_entrada_cad;
                }

                // Aplica tolerância na saída
                $diferenca_saida = abs($hora_saida_dt - $hora_saida_cad);
                if ($diferenca_saida <= $tolerancia_segundos) {
                    $hora_saida_dt = $hora_saida_cad;
                }

                $horas_trabalhadas = ($hora_saida_dt - $hora_entrada_dt) / 3600;
                $saldo_horas = round($horas_trabalhadas - $jornada_diaria, 2);
                $horas_extras = ($saldo_horas > 0) ? $saldo_horas : 0;

                if ($saldo_acumulado < 0 && $saldo_horas > 0) {
                    $saldo_acumulado = 0;
                }

                if ($saldo_horas > 0) {
                    $saldo_acumulado += $saldo_horas;
                }

                if ($saldo_acumulado < -$jornada_diaria) {
                    $saldo_acumulado = -$jornada_diaria;
                }

                $stmtUpdate = $pdo_conn->prepare("UPDATE pontos SET saldo_horas = :saldo_horas, horas_extras = :horas_extras WHERE idUsuario = :idUsuario AND data_atual = :data");
                $stmtUpdate->bindParam(':saldo_horas', $saldo_acumulado);
                $stmtUpdate->bindParam(':horas_extras', $horas_extras);
                $stmtUpdate->bindParam(':idUsuario', $idUsuario);
                $stmtUpdate->bindParam(':data', $data);
                $stmtUpdate->execute();
            }
        }
    }
}


// Função para mensagens via sessão
function setSessionMessage($type, $message) {
    $_SESSION['msg'] = "<div class='alert alert-$type' role='alert'>$message</div>";
}
?>
