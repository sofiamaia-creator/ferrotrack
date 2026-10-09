<?php

function verificarAlerta($conexao, $id_leitura, $tipo_sensor, $valor_lido)
{
    $sql = "SELECT valor_maximo, unidade
            FROM limites
            WHERE tipo_sensor = ?";

    $stmt = $conexao->prepare($sql);

    if (!$stmt) {
        throw new Exception(
            "Erro ao consultar limites: " . $conexao->error
        );
    }

    $stmt->bind_param("s", $tipo_sensor);

    if (!$stmt->execute()) {
        throw new Exception(
            "Erro ao consultar limites: " . $stmt->error
        );
    }

    $resultado = $stmt->get_result();
    $limite = $resultado->fetch_assoc();
    $stmt->close();

    if (!$limite) {
        return;
    }

    $valor_maximo = (float) $limite["valor_maximo"];
    $valor_lido = (float) $valor_lido;

    if ($valor_lido <= $valor_maximo) {
        return;
    }

    $mensagem = "O sensor de "
              . $tipo_sensor
              . " registrou "
              . $valor_lido
              . " "
              . $limite["unidade"]
              . ", acima do limite máximo de "
              . $valor_maximo
              . " "
              . $limite["unidade"]
              . ".";

    $tipo = $tipo_sensor;
    $severidade = "crítico";

    $sql = "INSERT INTO alertas
            (id_leitura, tipo_sensor, valor_lido,
             valor_limite, mensagem, tipo, severidade)
            VALUES (?, ?, ?, ?, ?, ?, ?)";

    $stmt = $conexao->prepare($sql);

    if (!$stmt) {
        throw new Exception(
            "Erro ao preparar alerta: " . $conexao->error
        );
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

        throw new Exception(
            "Erro ao registrar alerta: " . $erro
        );
    }

    $stmt->close();
}
?>