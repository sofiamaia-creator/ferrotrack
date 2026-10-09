<?php

require_once __DIR__ . "/../assets/php/conexao.php";

const MARGEM_ATENCAO = 0.10;


function buscarLimite($tipo)
{
    global $conexao;
    static $cache = [];

    if (array_key_exists($tipo, $cache)) {
        return $cache[$tipo];
    }

    $stmt = $conexao->prepare(
        "SELECT valor_maximo, unidade FROM limites WHERE tipo_sensor = ?"
    );

    if (!$stmt) {
        throw new Exception("Erro ao consultar limites: " . $conexao->error);
    }

    $stmt->bind_param("s", $tipo);

    if (!$stmt->execute()) {
        $erro = $stmt->error;
        $stmt->close();
        throw new Exception("Erro ao consultar limites: " . $erro);
    }

    $limite = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    $cache[$tipo] = $limite ?: null;

    return $cache[$tipo];
}

function classificar($tipo, $valor)
{
    $limite = buscarLimite($tipo);


    if ($limite === null) {
        return 'normal';
    }

    $maximo = (float) $limite['valor_maximo'];
    $valor = (float) $valor;

    if ($valor <= $maximo) {
        return 'normal';
    }

    if ($valor <= round($maximo * (1 + MARGEM_ATENCAO), 2)) {
        return 'atencao';
    }

    return 'critico';
}
?>