<?php

require_once "../assets/php/proteger.php";
require_once "../assets/php/permissao.php";
require_once "../assets/php/conexao.php";

if (!temPapel(['maquinista', 'gestor'])) {
    http_response_code(403);
    exit("Acesso negado.");
}

$tiposPermitidos = [
    'passagens' => 'Passagens',
    'manutencao' => 'Manutenção',
    'carga' => 'Carga',
    'operacional' => 'Operacional'
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

if (isset($_GET['exportar']) && $_GET['exportar'] === 'csv') {
}

$rotas = [];
$totalRotas = 0;
$rotasConcluidas = 0;
$rotasNaoConcluidas = 0;
$taxaConclusao = 0;

$passagens = [];
$totalPassagens = 0;
$totalPassageiros = 0;
$valorArrecadado = 0;
$destinos = [];
$tiposTicket = [];

$manutencoes = [];
$totalManutencoes = 0;
$manutencoesConcluidas = 0;
$manutencoesEmAndamento = 0;
$taxaConclusaoManutencao = 0;

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

if ($idTrem !== '' && (!ctype_digit($idTrem) || (int) $idTrem <= 0)) {
    $erro = 'Trem inválido.';
}

if ($tipo === 'passagens' && $erro === '') {
    $sql = "SELECT
                t.id_ticket,
                t.local_partida,
                t.local_destino,
                t.data_hora_ticket,
                t.quantidade_passageiros,
                t.valor_ticket,
                t.quantidade_tickets,
                t.forma_pagamento,
                t.lugares_mais_comprados,
                t.tipo_ticket,
                r.id_trem,
                tr.prefixo
            FROM ticket t
            LEFT JOIN rotas r ON r.id_rota = t.id_rota
            LEFT JOIN trens tr ON tr.id_trem = r.id_trem
            WHERE 1 = 1";

    $parametros = [];
    $tipos = '';

    if ($dataInicio !== '') {
        $sql .= " AND t.data_hora_ticket >= ?";
        $parametros[] = $dataInicio . ' 00:00:00';
        $tipos .= 's';
    }

    if ($dataFim !== '') {
        $sql .= " AND t.data_hora_ticket < DATE_ADD(?, INTERVAL 1 DAY)";
        $parametros[] = $dataFim . ' 00:00:00';
        $tipos .= 's';
    }

    if ($idTrem !== '') {
        $sql .= " AND r.id_trem = ?";
        $parametros[] = (int) $idTrem;
        $tipos .= 'i';
    }

    $sql .= " ORDER BY t.data_hora_ticket DESC, t.id_ticket DESC";

    $stmt = $conexao->prepare($sql);

    if ($stmt) {
        if ($parametros) {
            $stmt->bind_param($tipos, ...$parametros);
        }

        if ($stmt->execute()) {
            $resultado = $stmt->get_result();

            while ($linha = $resultado->fetch_assoc()) {
                $passagens[] = $linha;
                $totalPassagens += (int) $linha['quantidade_tickets'];
                $totalPassageiros += (int) $linha['quantidade_passageiros'];
                $valorArrecadado += (float) $linha['valor_ticket'] * (int) $linha['quantidade_tickets'];
            }
        } else {
            $erro = 'Não foi possível consultar as passagens.';
        }

        $stmt->close();
    } else {
        $erro = 'Não foi possível preparar a consulta das passagens.';
    }

    if ($erro === '') {
        $sql = "SELECT
                    t.lugares_mais_comprados AS destino,
                    SUM(t.quantidade_tickets) AS quantidade
                FROM ticket t
                LEFT JOIN rotas r ON r.id_rota = t.id_rota
                WHERE t.lugares_mais_comprados IS NOT NULL
                  AND t.lugares_mais_comprados <> ''";

        $parametros = [];
        $tipos = '';

        if ($dataInicio !== '') {
            $sql .= " AND t.data_hora_ticket >= ?";
            $parametros[] = $dataInicio . ' 00:00:00';
            $tipos .= 's';
        }

        if ($dataFim !== '') {
            $sql .= " AND t.data_hora_ticket < DATE_ADD(?, INTERVAL 1 DAY)";
            $parametros[] = $dataFim . ' 00:00:00';
            $tipos .= 's';
        }

        if ($idTrem !== '') {
            $sql .= " AND r.id_trem = ?";
            $parametros[] = (int) $idTrem;
            $tipos .= 'i';
        }

        $sql .= " GROUP BY t.lugares_mais_comprados ORDER BY quantidade DESC";

        $stmt = $conexao->prepare($sql);

        if ($stmt) {
            if ($parametros) {
                $stmt->bind_param($tipos, ...$parametros);
            }

            if ($stmt->execute()) {
                $resultado = $stmt->get_result();

                while ($linha = $resultado->fetch_assoc()) {
                    $destinos[] = $linha;
                }
            } else {
                $erro = 'Não foi possível consultar os destinos.';
            }

            $stmt->close();
        } else {
            $erro = 'Não foi possível preparar a consulta dos destinos.';
        }
    }

    if ($erro === '') {
        $sql = "SELECT
                    t.tipo_ticket,
                    SUM(t.quantidade_tickets) AS quantidade
                FROM ticket t
                LEFT JOIN rotas r ON r.id_rota = t.id_rota
                WHERE t.tipo_ticket IS NOT NULL
                  AND t.tipo_ticket <> ''";

        $parametros = [];
        $tipos = '';

        if ($dataInicio !== '') {
            $sql .= " AND t.data_hora_ticket >= ?";
            $parametros[] = $dataInicio . ' 00:00:00';
            $tipos .= 's';
        }

        if ($dataFim !== '') {
            $sql .= " AND t.data_hora_ticket < DATE_ADD(?, INTERVAL 1 DAY)";
            $parametros[] = $dataFim . ' 00:00:00';
            $tipos .= 's';
        }

        if ($idTrem !== '') {
            $sql .= " AND r.id_trem = ?";
            $parametros[] = (int) $idTrem;
            $tipos .= 'i';
        }

        $sql .= " GROUP BY t.tipo_ticket ORDER BY quantidade DESC";

        $stmt = $conexao->prepare($sql);

        if ($stmt) {
            if ($parametros) {
                $stmt->bind_param($tipos, ...$parametros);
            }

            if ($stmt->execute()) {
                $resultado = $stmt->get_result();

                while ($linha = $resultado->fetch_assoc()) {
                    $tiposTicket[] = $linha;
                }
            } else {
                $erro = 'Não foi possível consultar os tipos de passagem.';
            }

            $stmt->close();
        } else {
            $erro = 'Não foi possível preparar a consulta dos tipos de passagem.';
        }
    }
}

if ($tipo === 'carga' && $erro === '') {
    $sql = "SELECT
                carga.nome_carga,
                carga.data_transporte,
                carga.peso,
                trens.prefixo
            FROM carga
            INNER JOIN trens ON trens.id_trem = carga.id_trem
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
        if ($parametros) {
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
            $erro = 'Não foi possível consultar as cargas.';
        }

        $stmt->close();
    } else {
        $erro = 'Não foi possível preparar a consulta das cargas.';
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
            INNER JOIN trens ON trens.id_trem = rotas.id_trem
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
        if ($parametros) {
            $stmt->bind_param($tipos, ...$parametros);
        }

        if ($stmt->execute()) {
            $resultado = $stmt->get_result();

            while ($linha = $resultado->fetch_assoc()) {
                $rotas[] = $linha;
                $totalRotas++;

                $status = mb_strtolower(trim($linha['status'] ?? ''), 'UTF-8');

                if (in_array($status, ['concluída', 'concluida'], true)) {
                    $rotasConcluidas++;
                }
            }

            $rotasNaoConcluidas = $totalRotas - $rotasConcluidas;

            if ($totalRotas > 0) {
                $taxaConclusao = ($rotasConcluidas / $totalRotas) * 100;
            }
        } else {
            $erro = 'Não foi possível consultar as viagens.';
        }

        $stmt->close();
    } else {
        $erro = 'Não foi possível preparar a consulta das viagens.';
    }
}

if ($tipo === 'manutencao' && $erro === '') {
    $sql = "SELECT
                m.id_manutencao,
                m.id_trem,
                m.descricao,
                m.data_inicio,
                m.data_conclusao,
                m.status,
                m.observacoes,
                tr.prefixo
            FROM manutencoes m
            INNER JOIN trens tr ON tr.id_trem = m.id_trem
            WHERE 1 = 1";

    $parametros = [];
    $tipos = '';

    if ($dataInicio !== '') {
        $sql .= " AND m.data_inicio >= ?";
        $parametros[] = $dataInicio;
        $tipos .= 's';
    }

    if ($dataFim !== '') {
        $sql .= " AND m.data_inicio <= ?";
        $parametros[] = $dataFim;
        $tipos .= 's';
    }

    if ($idTrem !== '') {
        $sql .= " AND m.id_trem = ?";
        $parametros[] = (int) $idTrem;
        $tipos .= 'i';
    }

    $sql .= " ORDER BY m.data_inicio DESC, m.id_manutencao DESC";

    $stmt = $conexao->prepare($sql);

    if (!$stmt) {
        $erro = 'Erro ao preparar consulta de manutenção: ' . $conexao->error;
    } else {
        if ($parametros) {
            $stmt->bind_param($tipos, ...$parametros);
        }

        if (!$stmt->execute()) {
            $erro = 'Erro ao consultar manutenções: ' . $stmt->error;
        } else {
            $resultado = $stmt->get_result();

            while ($linha = $resultado->fetch_assoc()) {
                $manutencoes[] = $linha;
                $totalManutencoes++;

                if ($linha['status'] === 'Concluída') {
                    $manutencoesConcluidas++;
                } elseif ($linha['status'] === 'Em andamento') {
                    $manutencoesEmAndamento++;
                }
            }

            if ($totalManutencoes > 0) {
                $taxaConclusaoManutencao =
                    ($manutencoesConcluidas / $totalManutencoes) * 100;
            }
        }

        $stmt->close();
    }
}

if (isset($_GET['exportar']) && $_GET['exportar'] === 'csv' && $erro === '') {
    $arquivo = 'relatorio-' . $tipo . '-' . date('Y-m-d') . '.csv';

    header('Content-Type: text/csv; charset=UTF-8');
    header('Content-Disposition: attachment; filename="' . $arquivo . '"');

    $saida = fopen('php://output', 'w');

    // BOM para o Excel reconhecer acentos
    fwrite($saida, "\xEF\xBB\xBF");

    if ($tipo === 'passagens') {
        fputcsv($saida, [
            'Data',
            'Origem',
            'Destino',
            'Passageiros',
            'Quantidade',
            'Valor unitário',
            'Pagamento',
            'Tipo',
            'Trem'
        ], ';');

        foreach ($passagens as $item) {
            fputcsv($saida, [
                $item['data_hora_ticket'],
                $item['local_partida'],
                $item['local_destino'],
                $item['quantidade_passageiros'],
                $item['quantidade_tickets'],
                $item['valor_ticket'],
                $item['forma_pagamento'],
                $item['tipo_ticket'],
                $item['prefixo']
            ], ';');
        }
    } elseif ($tipo === 'carga') {
        fputcsv($saida, ['Carga', 'Data do transporte', 'Peso', 'Trem'], ';');

        foreach ($cargas as $item) {
            fputcsv($saida, [
                $item['nome_carga'],
                $item['data_transporte'],
                $item['peso'],
                $item['prefixo']
            ], ';');
        }
    } elseif ($tipo === 'operacional') {
        fputcsv($saida, [
            'Origem',
            'Destino',
            'Data',
            'Partida',
            'Chegada',
            'Status',
            'Trem'
        ], ';');

        foreach ($rotas as $item) {
            fputcsv($saida, [
                $item['origem'],
                $item['destino'],
                $item['data_viagem'],
                $item['hora_partida'],
                $item['hora_chegada'],
                $item['status'],
                $item['prefixo']
            ], ';');
        }
    } elseif ($tipo === 'manutencao') {
        fputcsv($saida, [
            'Descrição',
            'Trem',
            'Data de início',
            'Data de conclusão',
            'Status',
            'Observações'
        ], ';');

        foreach ($manutencoes as $item) {
            fputcsv($saida, [
                $item['descricao'],
                $item['prefixo'],
                $item['data_inicio'],
                $item['data_conclusao'],
                $item['status'],
                $item['observacoes']
            ], ';');
        }
    }

    fclose($saida);
    exit;
}

?>
<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Relatório <?= htmlspecialchars($tiposPermitidos[$tipo], ENT_QUOTES, 'UTF-8') ?> — FerroTrack</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">

    <link rel="stylesheet" href="../paginas/cabecalho.css">

    <style>
        @media print {
            body {
                background: white !important;
            }

            .nao-imprimir {
                display: none !important;
            }

            .container {
                width: 100% !important;
                max-width: 100% !important;
                padding: 0 !important;
            }

            .card {
                box-shadow: none !important;
                border: 1px solid #ddd !important;
                break-inside: avoid;
            }

            table {
                font-size: 10pt;
            }

            h1,
            h2 {
                color: black !important;
            }
        }
    </style>
</head>

</head>

<body class="bg-body-tertiary">

    <div class="container py-4">

        <div class="d-flex align-items-center gap-3 mb-4">
            <a href="relatorios.php" class="btn btn-outline-secondary">
                <i class="bi bi-arrow-left"></i>
            </a>

            <div>
                <h1 class="h3 mb-1">
                    Relatório de <?= htmlspecialchars($tiposPermitidos[$tipo], ENT_QUOTES, 'UTF-8') ?>
                </h1>
                <p class="text-secondary mb-0">Resultado dos filtros selecionados.</p>
            </div>
        </div>
        <?php if ($erro === ''): ?>
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-body">
                <h2 class="h5 mb-3">Gráfico do relatório</h2>
                <div style="position: relative; height: 350px;">
                    <canvas id="graficoRelatorio"></canvas>
                </div>
            </div>
        </div>
        <?php endif; ?>
        <?php if ($erro !== ''): ?>

            <div class="alert alert-danger">
                <?= htmlspecialchars($erro, ENT_QUOTES, 'UTF-8') ?>
            </div>



        <?php elseif ($tipo === 'passagens'): ?>

            <div class="row g-3 mb-4">
                <div class="col-md-4">
                    <div class="card border-0 shadow-sm h-100">
                        <div class="card-body">
                            <p class="text-secondary mb-1">Total de passagens</p>
                            <h2 class="h3 mb-0"><?= $totalPassagens ?></h2>
                        </div>
                    </div>
                </div>

                <div class="col-md-4">
                    <div class="card border-0 shadow-sm h-100">
                        <div class="card-body">
                            <p class="text-secondary mb-1">Total de passageiros</p>
                            <h2 class="h3 mb-0"><?= $totalPassageiros ?></h2>
                        </div>
                    </div>
                </div>

                <div class="col-md-4">
                    <div class="card border-0 shadow-sm h-100">
                        <div class="card-body">
                            <p class="text-secondary mb-1">Valor arrecadado</p>
                            <h2 class="h3 mb-0">R$ <?= number_format($valorArrecadado, 2, ',', '.') ?></h2>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card border-0 shadow-sm mb-4">
                <div class="card-body">
                    <h2 class="h5 mb-3">Destinos mais comprados</h2>
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>Destino ou lugar</th>
                                    <th>Quantidade de passagens</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if ($destinos): ?>
                                    <?php foreach ($destinos as $destino): ?>
                                        <tr>
                                            <td><?= htmlspecialchars($destino['destino'], ENT_QUOTES, 'UTF-8') ?></td>
                                            <td><?= (int) $destino['quantidade'] ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="2" class="text-center text-secondary py-4">
                                            Nenhum destino encontrado para os filtros selecionados.
                                        </td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <div class="card border-0 shadow-sm mb-4">
                <div class="card-body">
                    <h2 class="h5 mb-3">Tipos de passagem</h2>
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>Tipo</th>
                                    <th>Quantidade</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if ($tiposTicket): ?>
                                    <?php foreach ($tiposTicket as $item): ?>
                                        <tr>
                                            <td><?= htmlspecialchars($item['tipo_ticket'], ENT_QUOTES, 'UTF-8') ?></td>
                                            <td><?= (int) $item['quantidade'] ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="2" class="text-center text-secondary py-4">
                                            Nenhum tipo de passagem encontrado para os filtros selecionados.
                                        </td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <h2 class="h5 mb-3">Passagens registradas</h2>
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>Data</th>
                                    <th>Origem</th>
                                    <th>Destino</th>
                                    <th>Passageiros</th>
                                    <th>Quantidade</th>
                                    <th>Valor unitário</th>
                                    <th>Pagamento</th>
                                    <th>Tipo</th>
                                    <th>Trem</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if ($passagens): ?>
                                    <?php foreach ($passagens as $passagem): ?>
                                        <tr>
                                            <td>
                                                <?= !empty($passagem['data_hora_ticket'])
                                                    ? date('d/m/Y H:i', strtotime($passagem['data_hora_ticket']))
                                                    : '—' ?>
                                            </td>
                                            <td><?= htmlspecialchars($passagem['local_partida'] ?? '—', ENT_QUOTES, 'UTF-8') ?></td>
                                            <td><?= htmlspecialchars($passagem['local_destino'] ?? '—', ENT_QUOTES, 'UTF-8') ?></td>
                                            <td><?= (int) $passagem['quantidade_passageiros'] ?></td>
                                            <td><?= (int) $passagem['quantidade_tickets'] ?></td>
                                            <td>R$ <?= number_format((float) $passagem['valor_ticket'], 2, ',', '.') ?></td>
                                            <td><?= htmlspecialchars($passagem['forma_pagamento'] ?? '—', ENT_QUOTES, 'UTF-8') ?></td>
                                            <td><?= htmlspecialchars($passagem['tipo_ticket'] ?? '—', ENT_QUOTES, 'UTF-8') ?></td>
                                            <td><?= htmlspecialchars($passagem['prefixo'] ?? '—', ENT_QUOTES, 'UTF-8') ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="9" class="text-center text-secondary py-4">
                                            Nenhuma passagem encontrada para os filtros selecionados.
                                        </td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

        <?php elseif ($tipo === 'carga'): ?>

            <div class="row g-3 mb-4">
                <div class="col-md-6">
                    <div class="card border-0 shadow-sm h-100">
                        <div class="card-body">
                            <p class="text-secondary mb-1">Total de cargas</p>
                            <h2 class="h3 mb-0"><?= $totalCargas ?></h2>
                        </div>
                    </div>
                </div>

                <div class="col-md-6">
                    <div class="card border-0 shadow-sm h-100">
                        <div class="card-body">
                            <p class="text-secondary mb-1">Peso total</p>
                            <h2 class="h3 mb-0"><?= number_format($pesoTotal, 2, ',', '.') ?> kg</h2>
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
                                <?php if ($cargas): ?>
                                    <?php foreach ($cargas as $carga): ?>
                                        <tr>
                                            <td class="fw-semibold"><?= htmlspecialchars($carga['nome_carga'], ENT_QUOTES, 'UTF-8') ?></td>
                                            <td><?= date('d/m/Y', strtotime($carga['data_transporte'])) ?></td>
                                            <td><?= number_format((float) $carga['peso'], 2, ',', '.') ?> kg</td>
                                            <td><?= htmlspecialchars($carga['prefixo'], ENT_QUOTES, 'UTF-8') ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="4" class="text-center text-secondary py-4">
                                            Nenhuma carga encontrada para os filtros selecionados.
                                        </td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

        <?php elseif ($tipo === 'operacional'): ?>

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
                            <h2 class="h3 mb-0"><?= number_format($taxaConclusao, 1, ',', '.') ?>%</h2>
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
                                <?php if ($rotas): ?>
                                    <?php foreach ($rotas as $rota): ?>
                                        <tr>
                                            <td><?= htmlspecialchars($rota['origem'], ENT_QUOTES, 'UTF-8') ?></td>
                                            <td><?= htmlspecialchars($rota['destino'], ENT_QUOTES, 'UTF-8') ?></td>
                                            <td><?= date('d/m/Y', strtotime($rota['data_viagem'])) ?></td>
                                            <td><?= !empty($rota['hora_partida']) ? htmlspecialchars(substr($rota['hora_partida'], 0, 5), ENT_QUOTES, 'UTF-8') : '—' ?></td>
                                            <td><?= !empty($rota['hora_chegada']) ? htmlspecialchars(substr($rota['hora_chegada'], 0, 5), ENT_QUOTES, 'UTF-8') : '—' ?></td>
                                            <td><?= htmlspecialchars($rota['status'] ?? '', ENT_QUOTES, 'UTF-8') ?></td>
                                            <td><?= htmlspecialchars($rota['prefixo'], ENT_QUOTES, 'UTF-8') ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="7" class="text-center text-secondary py-4">
                                            Nenhuma viagem encontrada para os filtros selecionados.
                                        </td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

        <?php elseif ($tipo === 'manutencao'): ?>

            <div class="row g-3 mb-4">
                <div class="col-md-6 col-xl-3">
                    <div class="card border-0 shadow-sm h-100">
                        <div class="card-body">
                            <p class="text-secondary mb-1">Total de manutenções cadastradas</p>
                            <h2 class="h3 mb-0"><?= $totalManutencoes ?></h2>
                        </div>
                    </div>
                </div>

                <div class="col-md-6 col-xl-3">
                    <div class="card border-0 shadow-sm h-100">
                        <div class="card-body">
                            <p class="text-secondary mb-1">Manutenções concluídas</p>
                            <h2 class="h3 mb-0"><?= $manutencoesConcluidas ?></h2>
                        </div>
                    </div>
                </div>

                <div class="col-md-6 col-xl-3">
                    <div class="card border-0 shadow-sm h-100">
                        <div class="card-body">
                            <p class="text-secondary mb-1">Manutenções em andamento</p>
                            <h2 class="h3 mb-0"><?= $manutencoesEmAndamento ?></h2>
                        </div>
                    </div>
                </div>

                <div class="col-md-6 col-xl-3">
                    <div class="card border-0 shadow-sm h-100">
                        <div class="card-body">
                            <p class="text-secondary mb-1">Percentual de conclusão</p>
                            <h2 class="h3 mb-0"><?= number_format($taxaConclusaoManutencao, 1, ',', '.') ?>%</h2>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <h2 class="h5 mb-3">Manutenções cadastradas</h2>
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>Descrição</th>
                                    <th>Trem</th>
                                    <th>Data de início</th>
                                    <th>Data de conclusão</th>
                                    <th>Status</th>
                                    <th>Observações</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if ($manutencoes): ?>
                                    <?php foreach ($manutencoes as $manutencao): ?>
                                        <tr>
                                            <td><?= htmlspecialchars($manutencao['descricao'], ENT_QUOTES, 'UTF-8') ?></td>
                                            <td><?= htmlspecialchars($manutencao['prefixo'], ENT_QUOTES, 'UTF-8') ?></td>
                                            <td><?= date('d/m/Y', strtotime($manutencao['data_inicio'])) ?></td>
                                            <td>
                                                <?= !empty($manutencao['data_conclusao'])
                                                    ? date('d/m/Y', strtotime($manutencao['data_conclusao']))
                                                    : '—' ?>
                                            </td>
                                            <td>
                                                <?php if ($manutencao['status'] === 'Concluída'): ?>
                                                    <span class="badge text-bg-success">Concluída</span>
                                                <?php else: ?>
                                                    <span class="badge text-bg-warning">Em andamento</span>
                                                <?php endif; ?>
                                            </td>
                                            <td><?= htmlspecialchars($manutencao['observacoes'] ?? '—', ENT_QUOTES, 'UTF-8') ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="6" class="text-center text-secondary py-4">
                                            Nenhuma manutenção encontrada para os filtros selecionados.
                                        </td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

        <?php endif; ?>


        <div class="d-flex flex-wrap gap-2 mt-4 nao-imprimir">
            <a href="gera-relatorio.php" class="btn btn-primary">
                <i class="bi bi-funnel me-1"></i>
                Novo relatório
            </a>

            <a href="?<?= htmlspecialchars(http_build_query([
                            'tipo' => $tipo,
                            'data_inicio' => $dataInicio,
                            'data_fim' => $dataFim,
                            'id_trem' => $idTrem,
                            'exportar' => 'csv'
                        ]), ENT_QUOTES, 'UTF-8') ?>" class="btn btn-success">
                <i class="bi bi-file-earmark-spreadsheet me-1"></i>
                Exportar CSV
            </a>

            <button type="button" class="btn btn-outline-secondary" onclick="window.print()">
                <i class="bi bi-printer me-1"></i>
                Imprimir
            </button>
        </div>


    </div>

    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    const tipoRelatorio = <?= json_encode($tipo) ?>;

    let labels = [];
    let valores = [];
    let tituloGrafico = '';

    if (tipoRelatorio === 'passagens') {
        labels = <?= json_encode(array_column($destinos, 'destino'), JSON_UNESCAPED_UNICODE) ?>;
        valores = <?= json_encode(array_map('intval', array_column($destinos, 'quantidade'))) ?>;
        tituloGrafico = 'Passagens por destino';

    } else if (tipoRelatorio === 'carga') {
        labels = <?= json_encode(array_column($cargas, 'nome_carga'), JSON_UNESCAPED_UNICODE) ?>;
        valores = <?= json_encode(array_map('floatval', array_column($cargas, 'peso'))) ?>;
        tituloGrafico = 'Peso das cargas';

    } else if (tipoRelatorio === 'operacional') {
        labels = ['Concluídas', 'Não concluídas'];
        valores = [
            <?= (int) $rotasConcluidas ?>,
            <?= (int) $rotasNaoConcluidas ?>
        ];
        tituloGrafico = 'Situação das viagens';

    } else if (tipoRelatorio === 'manutencao') {
        labels = ['Concluídas', 'Em andamento'];
        valores = [
            <?= (int) $manutencoesConcluidas ?>,
            <?= (int) $manutencoesEmAndamento ?>
        ];
        tituloGrafico = 'Situação das manutenções';
    }
</script>
<script>
    const elementoGrafico = document.getElementById('graficoRelatorio');

    console.log('Tipo:', tipoRelatorio);
console.log('Labels:', labels);
console.log('Valores:', valores);

if (elementoGrafico && labels.length > 0) {
        new Chart(elementoGrafico, {
            type: tipoRelatorio === 'operacional' || tipoRelatorio === 'manutencao'
                ? 'doughnut'
                : 'bar',

            data: {
                labels: labels,
                datasets: [{
                    label: tituloGrafico,
                    data: valores,
                    borderWidth: 1
                }]
            },

            options: {
                responsive: true,
                maintainAspectRatio: false,

                plugins: {
                    legend: {
                        display: tipoRelatorio === 'operacional' || tipoRelatorio === 'manutencao'
                    },
                    title: {
                        display: true,
                        text: tituloGrafico
                    }
                },

                scales: {
                    y: {
                        beginAtZero: true
                    }
                }
            }
        });
    }
</script>
</body>

</html>