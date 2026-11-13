<?php

require_once "../assets/php/proteger.php";
require_once "../assets/php/permissao.php";
require_once "../assets/php/cabecalho.php";
require_once "../assets/php/menu.php";
require_once "../assets/php/conexao.php";

if (!temPapel(['gestor'])) {
    http_response_code(403);
    exit("Acesso negado.");
}

$status = $_GET['status'] ?? '';
$id_trem = (int) ($_GET['id_trem'] ?? 0);
$data_inicio = $_GET['data_inicio'] ?? '';
$data_fim = $_GET['data_fim'] ?? '';
$busca = trim($_GET['busca'] ?? '');

$condicoes = [];
$valores = [];
$tipos = '';

if ($status !== '') {
    $condicoes[] = 'rotas.status = ?';
    $valores[] = $status;
    $tipos .= 's';
}

if ($id_trem > 0) {
    $condicoes[] = 'rotas.id_trem = ?';
    $valores[] = $id_trem;
    $tipos .= 'i';
}

if ($data_inicio !== '' && $data_fim !== '') {
    $condicoes[] = 'rotas.data_viagem BETWEEN ? AND ?';
    $valores[] = $data_inicio;
    $valores[] = $data_fim;
    $tipos .= 'ss';
}

if ($busca !== '') {
    $condicoes[] = '(rotas.origem LIKE ? OR rotas.destino LIKE ?)';
    $termo = '%' . $busca . '%';
    $valores[] = $termo;
    $valores[] = $termo;
    $tipos .= 'ss';
}

$sql = "SELECT rotas.*, trens.prefixo, trens.modelo
        FROM rotas
        INNER JOIN trens ON trens.id_trem = rotas.id_trem";

if (!empty($condicoes)) {
    $sql .= " WHERE " . implode(" AND ", $condicoes);
}

$sql .= " ORDER BY rotas.data_viagem DESC, rotas.hora_partida";

$stmt = $conexao->prepare($sql);

if (!$stmt) {
    exit("Erro ao preparar a consulta de rotas.");
}

if (!empty($valores)) {
    $stmt->bind_param($tipos, ...$valores);
}

$stmt->execute();
$rotas = $stmt->get_result();

$trens = $conexao->query(
    "SELECT id_trem, prefixo, modelo
     FROM trens
     ORDER BY prefixo"
);

if (!$trens) {
    exit("Erro ao consultar os trens.");
}

?>

<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Gestão de Rotas — FerroTrack</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../paginas/cabecalho.css">

    <style>
        .layout-principal {
            display: flex;
            align-items: flex-start;
            min-height: calc(100vh - 64px);
        }

        .layout-principal > aside {
            flex: 0 0 25%;
            max-width: 25%;
            min-width: 0;
            height: calc(100vh - 64px);
            overflow-y: auto;
            position: sticky;
            top: 64px;
            background-color: #fff;
            border-right: 1px solid #dee2e6;
        }

        .conteudo-principal {
            flex: 1 1 0;
            min-width: 0;
            padding: 24px;
        }

        @media (min-width: 1200px) {
            .layout-principal > aside {
                flex-basis: 16.666667%;
                max-width: 16.666667%;
            }
        }

        @media (max-width: 991.98px) {
            .layout-principal > aside {
                display: none !important;
            }

            .conteudo-principal {
                width: 100%;
                padding: 16px;
            }
        }
    </style>
</head>

<body class="bg-body-tertiary">

    <?php gerarCabecalho(); ?>

    <div class="container-fluid px-0">
        <div class="layout-principal">

            <?php renderizarMenu(); ?>

            <main class="conteudo-principal">

                <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">

                    <div>
                        <h1 class="h3 fw-bold mb-1">Gestão de Rotas</h1>
                        <p class="text-secondary mb-0">Rotas e viagens programadas</p>
                    </div>

                    <div class="d-flex flex-wrap gap-2">
                        <a href="dashboard.php" class="btn btn-outline-secondary">
                            <i class="bi bi-arrow-left me-1"></i>
                            
                        

                        <a href="adicionar-rota.php" class="btn btn-primary">
                            <i class="bi bi-plus-circle me-1"></i>
                            Adicionar rota
                        </a>
                    </div>

                </div>

                <div class="card shadow-sm border-0 mb-4">
                    <div class="card-body">

                        <h5 class="card-title mb-3">Filtros</h5>

                        <form method="GET">

                            <div class="row g-3">

                                <div class="col-12 col-md-3">
                                    <label for="status" class="form-label">Status</label>

                                    <select name="status" id="status" class="form-select">
                                        <option value="">Todos</option>

                                        <option value="programada"
                                            <?= $status === 'programada' ? 'selected' : '' ?>>
                                            Programada
                                        </option>

                                        <option value="em andamento"
                                            <?= $status === 'em andamento' ? 'selected' : '' ?>>
                                            Em andamento
                                        </option>

                                        <option value="concluída"
                                            <?= $status === 'concluída' ? 'selected' : '' ?>>
                                            Concluída
                                        </option>

                                        <option value="cancelada"
                                            <?= $status === 'cancelada' ? 'selected' : '' ?>>
                                            Cancelada
                                        </option>
                                    </select>
                                </div>

                                <div class="col-12 col-md-3">
                                    <label for="id_trem" class="form-label">Trem</label>

                                    <select name="id_trem" id="id_trem" class="form-select">
                                        <option value="">Todos</option>

                                        <?php while ($trem = $trens->fetch_assoc()): ?>

                                            <option
                                                value="<?= (int) $trem['id_trem'] ?>"
                                                <?= $id_trem == $trem['id_trem'] ? 'selected' : '' ?>
                                            >
                                                <?= htmlspecialchars($trem['prefixo']) ?> -
                                                <?= htmlspecialchars($trem['modelo']) ?>
                                            </option>

                                        <?php endwhile; ?>

                                    </select>
                                </div>

                                <div class="col-12 col-md-3">
                                    <label for="data_inicio" class="form-label">Data inicial</label>

                                    <input
                                        type="date"
                                        name="data_inicio"
                                        id="data_inicio"
                                        class="form-control"
                                        value="<?= htmlspecialchars($data_inicio) ?>"
                                    >
                                </div>

                                <div class="col-12 col-md-3">
                                    <label for="data_fim" class="form-label">Data final</label>

                                    <input
                                        type="date"
                                        name="data_fim"
                                        id="data_fim"
                                        class="form-control"
                                        value="<?= htmlspecialchars($data_fim) ?>"
                                    >
                                </div>

                                <div class="col-12">
                                    <label for="busca" class="form-label">Rota</label>

                                    <input
                                        type="text"
                                        name="busca"
                                        id="busca"
                                        class="form-control"
                                        placeholder="Digite a origem ou destino"
                                        value="<?= htmlspecialchars($busca) ?>"
                                    >
                                </div>

                                <div class="col-12 d-flex flex-wrap gap-2">

                                    <button type="submit" class="btn btn-primary">
                                        <i class="bi bi-funnel me-1"></i>
                                        Filtrar
                                    </button>

                                    <a href="gestao-rotas.php" class="btn btn-secondary">
                                        <i class="bi bi-x-circle me-1"></i>
                                        Limpar filtros
                                    </a>

                                </div>

                            </div>

                        </form>

                    </div>
                </div>

                <div class="card shadow-sm border-0">

                    <div class="card-body">

                        <h5 class="card-title mb-3">Rotas cadastradas</h5>

                        <div class="table-responsive">

                            <table class="table table-hover align-middle">

                                <thead>
                                    <tr>
                                        <th>Trem</th>
                                        <th>Origem</th>
                                        <th>Destino</th>
                                        <th>Data</th>
                                        <th>Partida</th>
                                        <th>Chegada</th>
                                        <th>Status</th>
                                        <th>Ações</th>
                                    </tr>
                                </thead>

                                <tbody>

                                    <?php if ($rotas->num_rows > 0): ?>

                                        <?php while ($rota = $rotas->fetch_assoc()): ?>

                                            <tr>

                                                <td>
                                                    <strong>
                                                        <?= htmlspecialchars($rota['prefixo']) ?>
                                                    </strong>

                                                    <br>

                                                    <small class="text-secondary">
                                                        <?= htmlspecialchars($rota['modelo']) ?>
                                                    </small>
                                                </td>

                                                <td>
                                                    <?= htmlspecialchars($rota['origem']) ?>
                                                </td>

                                                <td>
                                                    <?= htmlspecialchars($rota['destino']) ?>
                                                </td>

                                                <td>
                                                    <?= date('d/m/Y', strtotime($rota['data_viagem'])) ?>
                                                </td>

                                                <td>
                                                    <?= htmlspecialchars($rota['hora_partida']) ?>
                                                </td>

                                                <td>
                                                    <?= $rota['hora_chegada']
                                                        ? htmlspecialchars($rota['hora_chegada'])
                                                        : '-' ?>
                                                </td>

                                                <td>

                                                    <?php
                                                    $classeStatus = 'bg-secondary';

                                                    if ($rota['status'] === 'programada') {
                                                        $classeStatus = 'bg-primary';
                                                    } elseif ($rota['status'] === 'em andamento') {
                                                        $classeStatus = 'bg-warning text-dark';
                                                    } elseif ($rota['status'] === 'concluída') {
                                                        $classeStatus = 'bg-success';
                                                    } elseif ($rota['status'] === 'cancelada') {
                                                        $classeStatus = 'bg-danger';
                                                    }
                                                    ?>

                                                    <span class="badge <?= $classeStatus ?>">
                                                        <?= htmlspecialchars(ucfirst($rota['status'])) ?>
                                                    </span>

                                                </td>

                                                <td>
                                                    <div class="d-flex flex-wrap gap-2">

                                                        <a
                                                            href="editar-rota.php?id=<?= (int) $rota['id_rota'] ?>"
                                                            class="btn btn-sm btn-outline-primary"
                                                        >
                                                            Editar
                                                        </a>

                                                        <a
                                                            href="excluir-rota.php?id=<?= (int) $rota['id_rota'] ?>"
                                                            class="btn btn-sm btn-outline-danger"
                                                            onclick="return confirm('Tem certeza que deseja excluir esta rota?');"
                                                        >
                                                            Excluir
                                                        </a>

                                                    </div>
                                                </td>

                                            </tr>

                                        <?php endwhile; ?>

                                    <?php else: ?>

                                        <tr>
                                            <td colspan="8" class="text-center text-secondary py-4">
                                                Nenhuma rota encontrada.
                                            </td>
                                        </tr>

                                    <?php endif; ?>

                                </tbody>

                            </table>

                        </div>

                    </div>

                </div>

            </main>

        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"></script>

</body>
</html>
