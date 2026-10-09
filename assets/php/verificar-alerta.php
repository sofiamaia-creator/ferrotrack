<?php

require_once __DIR__ . "/../../includes/limites.php";

function verificarAlerta($conexao, $id_leitura, $tipo_sensor, $valor_lido)
{
    $classe = classificar($tipo_sensor, $valor_lido);

    if ($classe === 'normal') {
        return;
    }

    $limite = buscarLimite($tipo_sensor);

    $valor_maximo = (float) $limite['valor_maximo'];
    $unidade = $limite['unidade'];
    $valor_lido = (float) $valor_lido;

 
    $severidade = ($classe === 'critico') ? 'crítico' : 'atenção';

    $mensagem = "O sensor de " . $tipo_sensor
              . " registrou " . $valor_lido . " " . $unidade
              . ", acima do limite máximo de " . $valor_maximo . " " . $unidade;

    if ($classe === 'atencao') {
        $mensagem .= ", na faixa de atenção (até 10% acima do limite).";
    } else {
        $mensagem .= ".";
    }

    $tipo = $tipo_sensor;

    $stmt = $conexao->prepare(
        "INSERT INTO alertas
         (id_leitura, tipo_sensor, valor_lido,
          valor_limite, mensagem, tipo, severidade)
         VALUES (?, ?, ?, ?, ?, ?, ?)"
    );

    if (!$stmt) {
        throw new Exception("Erro ao preparar alerta: " . $conexao->error);
    }

    $stmt->bind_param(
        "isddsss",
        $id_leitura,
        $tipo_sensor,
        $valor_lido,
        $valor_maximo,
        $mensagem,
        $tipo,
        $severidade
    );

    if (!$stmt->execute()) {
        $erro = $stmt->error;
        $stmt->close();
        throw new Exception("Erro ao registrar alerta: " . $erro);
    }

    $stmt->close();
}
?>