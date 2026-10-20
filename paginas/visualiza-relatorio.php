<?php

require_once "../assets/php/proteger.php";
require_once "../assets/php/permissao.php";
require_once "../assets/php/conexao.php";

if (!temPapel(['maquinista', 'gestor'])) {
    http_response_code(403);
    exit("Acesso negado.");
}

require_once "../assets/php/cabecalho.php";

$tiposPermitidos = [
    'passagens' => 'Passagens',
    'manutencao' => 'Manutenção',
    'consumo' => 'Carga',
    'eficiencia' => 'Operacional'
];

$tipo = $_GET['tipo'] ?? '';
$dataInicio = $_GET['data_inicio'] ?? '';
$dataFim = $_GET['data_fim'] ?? '';
$idTrem = $_GET['id_trem'] ?? '';

if (
    !is_string($tipo) ||
    !is_string($dataInicio) ||
    !is_string($dataFim) ||
    !is_string($idTrem)
) {
    http_response_code(400);
    exit("Parâmetros inválidos.");
}

if (!isset($tiposPermitidos[$tipo])) {
    http_response_code(400);
    exit("Tipo de relatório inválido.");
}

$erro = '';

$cargas = [];
$totalCargas = 0;
$pesoTotal = 0;

$rotas = [];
$totalRotas = 0;
$rotasConcluidas = 0;
$rotasNaoConcluidas = 0;
$taxaConclusao = 0;

$validarData = function ($data) {
    if ($data === '') {
        return true;
    }

    $dataObjeto = DateTime::createFromFormat('!Y-m-d', $data);

    return $dataObjeto !== false
        && $dataObjeto->format('Y-m-d') === $data;
};

if (!$validarData($dataInicio) || !$validarData($dataFim)) {
    $erro = 'Uma das datas informadas é inválida.';
}

if (
    $erro === '' &&
    $dataInicio !== '' &&
    $dataFim !== '' &&
    $dataInicio > $dataFim
) {
    $erro = 'A data inicial não pode ser posterior à data final.';
}

if ($idTrem !== '' && !ctype_digit($idTrem)) {
    $erro = 'Trem inválido.';
}

if ($tipo === 'carga' && $erro === '') {

    $sql = "SELECT
                carga.nome_carga,
                carga.data_transporte,
                carga.peso,
                trens.prefixo
            FROM carga
            INNER JOIN trens
                ON trens.id_trem = carga.id_trem
            WHERE 1 = 1";

    $parametros = [];
    $tipos = '';

    if ($dataInicio !== '') {
        $sql .= " AND carga.data_transporte >= ?";
        $parametros[] = $dataInicio;
        $tipos .= 's';
    }

    if ($dataFim !== '') {
        $sql .= " AND carga.data_transporte <= ?";
        $parametros[] = $dataFim;
        $tipos .= 's';
    }

    if ($idTrem !== '') {
        $sql .= " AND carga.id_trem = ?";
        $parametros[] = (int) $idTrem;
        $tipos .= 'i';
    }

    $sql .= " ORDER BY carga.data_transporte DESC";

    $stmt = $conexao->prepare($sql);

    if ($stmt) {

        if (!empty($parametros)) {
            $stmt->bind_param($tipos, ...$parametros);
        }

        if ($stmt->execute()) {

            $resultado = $stmt->get_result();

            while ($linha = $resultado->fetch_assoc()) {
                $cargas[] = $linha;
                $totalCargas++;
                $pesoTotal += (float) $linha['peso'];
            }

        } else {
            $erro = 'Não foi possível consultar os dados do relatório.';
        }

        $stmt->close();

    } else {
        $erro = 'Não foi possível preparar a consulta do relatório.';
    }
}

if ($tipo === 'operacional' && $erro === '') {

    $sql = "SELECT
                rotas.origem,
                rotas.destino,
                rotas.data_viagem,
                rotas.hora_partida,
                rotas.hora_chegada,
                rotas.status,
                trens.prefixo
            FROM rotas
            INNER JOIN trens
                ON trens.id_trem = rotas.id_trem
            WHERE 1 = 1";

    $parametros = [];
    $tipos = '';

    if ($dataInicio !== '') {
        $sql .= " AND rotas.data_viagem >= ?";
        $parametros[] = $dataInicio;
        $tipos .= 's';
    }

    if ($dataFim !== '') {
        $sql .= " AND rotas.data_viagem <= ?";
        $parametros[] = $dataFim;
        $tipos .= 's';
    }

    if ($idTrem !== '') {
        $sql .= " AND rotas.id_trem = ?";
        $parametros[] = (int) $idTrem;
        $tipos .= 'i';
    }

    $sql .= " ORDER BY rotas.data_viagem DESC, rotas.hora_partida DESC";

    $stmt = $conexao->prepare($sql);

    if ($stmt) {

        if (!empty($parametros)) {
            $stmt->bind_param($tipos, ...$parametros);
        }

        if ($stmt->execute()) {

            $resultado = $stmt->get_result();

            while ($linha = $resultado->fetch_assoc()) {
                $rotas[] = $linha;
                $totalRotas++;

                if (
                    mb_strtolower(trim($linha['status']), 'UTF-8')
                    === 'concluída'
                ) {
                    $rotasConcluidas++;
                }
            }

            $rotasNaoConcluidas = $totalRotas - $rotasConcluidas;

            if ($totalRotas > 0) {
                $taxaConclusao = ($rotasConcluidas / $totalRotas) * 100;
            }

        } else {
            $erro = 'Não foi possível consultar os dados do relatório.';
        }

        $stmt->close();

    } else {
        $erro = 'Não foi possível preparar a consulta do relatório.';
    }
}

?>
<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>Visualizar Relatório — FerroTrack</title>

    <link
        href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css"
        rel="stylesheet">

    <link
        href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css"
        rel="stylesheet">

    <link rel="stylesheet" href="../paginas/cabecalho.css">
</head>

<body class="bg-body-tertiary">

    <?php gerarCabecalho(); ?>

    <div class="container py-4">

        <div class="d-flex align-items-center gap-3 mb-4">

            <a
                href="gera-relatorio.php"
                class="btn btn-outline-secondary">
                <i class="bi bi-arrow-left"></i>
            </a>

            <div>
                <h1 class="h3 mb-1">
                    Relatório de
                    <?= htmlspecialchars(
                        $tiposPermitidos[$tipo],
                        ENT_QUOTES,
                        'UTF-8'
                    ) ?>
                </h1>

                <p class="text-secondary mb-0">
                    Resultado dos filtros selecionados.
                </p>
            </div>

        </div>

        <?php if ($erro !== ''): ?>

            <div class="alert alert-danger">
                <?= htmlspecialchars($erro, ENT_QUOTES, 'UTF-8') ?>
            </div>

        <?php elseif ($tipo === 'carga'): ?>

            <div class="row g-3 mb-4">

                <div class="col-md-6">
                    <div class="card border-0 shadow-sm h-100">
                        <div class="card-body">
                            <p class="text-secondary mb-1">
                                Total de cargas
                            </p>

                            <h2 class="h3 mb-0">
                                <?= $totalCargas ?>
                            </h2>
                        </div>
                    </div>
                </div>

                <div class="col-md-6">
                    <div class="card border-0 shadow-sm h-100">
                        <div class="card-body">
                            <p class="text-secondary mb-1">
                                Peso total
                            </p>

                            <h2 class="h3 mb-0">
                                <?= number_format(
                                    $pesoTotal,
                                    2,
                                    ',',
                                    '.'
                                ) ?> kg
                            </h2>
                        </div>
                    </div>
                </div>

            </div>

            <div class="card border-0 shadow-sm">
                <div class="card-body">

                    <div class="table-responsive">

                        <table class="table table-hover align-middle mb-0">

                            <thead class="table-light">
                                <tr>
                                    <th>Carga</th>
                                    <th>Data do transporte</th>
                                    <th>Peso</th>
                                    <th>Trem</th>
                                </tr>
                            </thead>

                            <tbody>

                                <?php if (count($cargas) > 0): ?>

                                    <?php foreach ($cargas as $carga): ?>

                                        <tr>
                                            <td class="fw-semibold">
                                                <?= htmlspecialchars(
                                                    $carga['nome_carga'],
                                                    ENT_QUOTES,
                                                    'UTF-8'
                                                ) ?>
                                            </td>

                                            <td>
                                                <?= date(
                                                    'd/m/Y',
                                                    strtotime($carga['data_transporte'])
                                                ) ?>
                                            </td>

                                            <td>
                                                <?= number_format(
                                                    (float) $carga['peso'],
                                                    2,
                                                    ',',
                                                    '.'
                                                ) ?> kg
                                            </td>

                                            <td>
                                                <?= htmlspecialchars(
                                                    $carga['prefixo'],
                                                    ENT_QUOTES,
                                                    'UTF-8'
                                                ) ?>
                                            </td>
                                        </tr>

                                    <?php endforeach; ?>

                                <?php else: ?>

                                    <tr>
                                        <td
                                            colspan="4"
                                            class="text-center text-secondary py-4">
                                            Nenhuma carga encontrada para os
                                            filtros selecionados.
                                        </td>
                                    </tr>

                                <?php endif; ?>

                            </tbody>

                        </table>

                    </div>

                </div>
            </div>

        <?php elseif ($tipo === 'eficiencia'): ?>

            <div class="row g-3 mb-4">

                <div class="col-md-6 col-xl-3">
                    <div class="card border-0 shadow-sm h-100">
                        <div class="card-body">
                            <p class="text-secondary mb-1">Total de viagens</p>
                            <h2 class="h3 mb-0"><?= $totalRotas ?></h2>
                        </div>
                    </div>
                </div>

                <div class="col-md-6 col-xl-3">
                    <div class="card border-0 shadow-sm h-100">
                        <div class="card-body">
                            <p class="text-secondary mb-1">Viagens concluídas</p>
                            <h2 class="h3 mb-0"><?= $rotasConcluidas ?></h2>
                        </div>
                    </div>
                </div>

                <div class="col-md-6 col-xl-3">
                    <div class="card border-0 shadow-sm h-100">
                        <div class="card-body">
                            <p class="text-secondary mb-1">Não concluídas</p>
                            <h2 class="h3 mb-0"><?= $rotasNaoConcluidas ?></h2>
                        </div>
                    </div>
                </div>

                <div class="col-md-6 col-xl-3">
                    <div class="card border-0 shadow-sm h-100">
                        <div class="card-body">
                            <p class="text-secondary mb-1">Taxa de conclusão</p>
                            <h2 class="h3 mb-0">
                                <?= number_format(
                                    $taxaConclusao,
                                    1,
                                    ',',
                                    '.'
                                ) ?>%
                            </h2>
                        </div>
                    </div>
                </div>

            </div>

            <div class="card border-0 shadow-sm">
                <div class="card-body">

                    <h2 class="h5 mb-3">Viagens registradas</h2>

                    <div class="table-responsive">

                        <table class="table table-hover align-middle mb-0">

                            <thead class="table-light">
                                <tr>
                                    <th>Origem</th>
                                    <th>Destino</th>
                                    <th>Data</th>
                                    <th>Partida</th>
                                    <th>Chegada</th>
                                    <th>Status</th>
                                    <th>Trem</th>
                                </tr>
                            </thead>

                            <tbody>

                                <?php if (count($rotas) > 0): ?>

                                    <?php foreach ($rotas as $rota): ?>

                                        <tr>
                                            <td>
                                                <?= htmlspecialchars(
                                                    $rota['origem'],
                                                    ENT_QUOTES,
                                                    'UTF-8'
                                                ) ?>
                                            </td>

                                            <td>
                                                <?= htmlspecialchars(
                                                    $rota['destino'],
                                                    ENT_QUOTES,
                                                    'UTF-8'
                                                ) ?>
                                            </td>

                                            <td>
                                                <?= date(
                                                    'd/m/Y',
                                                    strtotime($rota['data_viagem'])
                                                ) ?>
                                            </td>

                                            <td>
                                                <?= $rota['hora_partida']
                                                    ? htmlspecialchars(
                                                        substr($rota['hora_partida'], 0, 5),
                                                        ENT_QUOTES,
                                                        'UTF-8'
                                                    )
                                                    : '—' ?>
                                            </td>

                                            <td>
                                                <?= $rota['hora_chegada']
                                                    ? htmlspecialchars(
                                                        substr($rota['hora_chegada'], 0, 5),
                                                        ENT_QUOTES,
                                                        'UTF-8'
                                                    )
                                                    : '—' ?>
                                            </td>

                                            <td>
                                                <?= htmlspecialchars(
                                                    $rota['status'],
                                                    ENT_QUOTES,
                                                    'UTF-8'
                                                ) ?>
                                            </td>

                                            <td>
                                                <?= htmlspecialchars(
                                                    $rota['prefixo'],
                                                    ENT_QUOTES,
                                                    'UTF-8'
                                                ) ?>
                                            </td>
                                        </tr>

                                    <?php endforeach; ?>

                                <?php else: ?>

                                    <tr>
                                        <td
                                            colspan="7"
                                            class="text-center text-secondary py-4">
                                            Nenhuma viagem encontrada para os
                                            filtros selecionados.
                                        </td>
                                    </tr>

                                <?php endif; ?>

                            </tbody>

                        </table>

                    </div>

                </div>
            </div>

        <?php else: ?>

            <div class="alert alert-info">
                A consulta para o relatório de
                <?= htmlspecialchars(
                    $tiposPermitidos[$tipo],
                    ENT_QUOTES,
                    'UTF-8'
                ) ?>
                ainda precisa ser implementada.
            </div>

        <?php endif; ?>

        <div class="d-flex flex-wrap gap-2 mt-4">

            <a href="gera_relatorio.php" class="btn btn-primary">
                <i class="bi bi-funnel me-1"></i>
                Novo relatório
            </a>

            <button
                type="button"
                class="btn btn-outline-secondary"
                onclick="window.print()">
                <i class="bi bi-printer me-1"></i>
                Imprimir
            </button>

        </div>

    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"></script>

</body>

</html>
