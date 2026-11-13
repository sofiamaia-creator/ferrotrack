<?php

require_once "../assets/php/proteger.php";
require_once "../assets/php/permissao.php";
require_once "../assets/php/cabecalho.php";
require_once "../assets/php/conexao.php";
require_once "../assets/php/menu.php";

if (!temPapel(['gestor', 'maquinista', 'cliente'])) {
    http_response_code(403);
    exit("Acesso negado.");
}

$busca = trim($_GET['busca'] ?? '');

$sql = "SELECT
            a.id_alerta,
            a.id_leitura,
            a.tipo_sensor,
            a.valor_lido,
            a.valor_limite,
            a.mensagem,
            a.data_hora,
            a.status,
            a.tipo,
            a.severidade,
            s.codigo AS codigo_sensor,
            s.tipo AS tipo_do_sensor,
            t.nome_trem,
            t.prefixo
        FROM alertas a
        INNER JOIN leituras l ON a.id_leitura = l.id_leitura
        INNER JOIN sensores s ON l.id_sensor = s.id_sensor
        INNER JOIN trens t ON s.id_trem = t.id_trem";

if ($busca !== '') {
    $termo = "%" . $busca . "%";

    $sql .= " WHERE a.tipo_sensor LIKE ?
              OR a.mensagem LIKE ?
              OR a.status LIKE ?
              OR a.severidade LIKE ?
              OR s.codigo LIKE ?
              OR t.nome_trem LIKE ?
              OR t.prefixo LIKE ?
              ORDER BY a.data_hora DESC";

    $stmt = $conexao->prepare($sql);

    if (!$stmt) {
        exit("Não foi possível preparar a consulta de alertas.");
    }

    $stmt->bind_param(
        "sssssss",
        $termo,
        $termo,
        $termo,
        $termo,
        $termo,
        $termo,
        $termo
    );
} else {
    $sql .= " ORDER BY a.data_hora DESC";

    $stmt = $conexao->prepare($sql);

    if (!$stmt) {
        exit("Não foi possível preparar a consulta de alertas.");
    }
}

if (!$stmt->execute()) {
    exit("Não foi possível carregar os alertas.");
}

$resultado = $stmt->get_result();
$totalAlertas = $resultado->num_rows;

?>

<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Alertas e Notificações — FerroTrack</title>

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

                <div class="d-flex flex-wrap align-items-center justify-content-between gap-2 mb-4">
                    <div>
                        <h1 class="h3 mb-1">Alertas e Notificações</h1>
                        <p class="text-secondary mb-0">
                            Ocorrências registradas pelos sensores do sistema
                        </p>
                    </div>

                    <span class="badge text-bg-warning fs-6">
                        <?= $totalAlertas ?> alerta(s)
                    </span>
                </div>

                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-body">
                        <form class="row g-2 align-items-end" method="GET" action="alertas.php">

                            <div class="col-12 col-md-9 col-lg-10">
                                <label for="buscaAlerta" class="form-label">
                                    Buscar alertas
                                </label>

                                <input
                                    type="search"
                                    class="form-control"
                                    id="buscaAlerta"
                                    name="busca"
                                    placeholder="Buscar por tipo, mensagem, status, sensor ou trem"
                                    value="<?= htmlspecialchars($busca, ENT_QUOTES, 'UTF-8') ?>"
                                >
                            </div>

                            <div class="col-12 col-md-3 col-lg-2 d-grid">
                                <button type="submit" class="btn btn-primary">
                                    <i class="bi bi-search me-1"></i>
                                    Buscar
                                </button>
                            </div>

                        </form>
                    </div>
                </div>

                <div class="row g-3">

                    <?php if ($totalAlertas > 0): ?>

                        <?php while ($alerta = $resultado->fetch_assoc()): ?>

                            <div class="col-12 col-xl-6">
                                <div class="card border-0 shadow-sm h-100">
                                    <div class="card-body">

                                        <div class="d-flex align-items-start gap-3">
                                            <div class="text-warning fs-3">
                                                <i class="bi bi-exclamation-triangle-fill"></i>
                                            </div>

                                            <div class="flex-grow-1">
                                                <h2 class="h5 mb-2">
                                                    Alerta de
                                                    <?= htmlspecialchars($alerta['tipo'] ?: $alerta['tipo_sensor'], ENT_QUOTES, 'UTF-8') ?>
                                                </h2>

                                                <p class="mb-3">
                                                    <?= htmlspecialchars($alerta['mensagem'], ENT_QUOTES, 'UTF-8') ?>
                                                </p>
                                            </div>
                                        </div>

                                        <div class="mb-3">
                                            <div class="small text-secondary">Sensor</div>
                                            <div class="fw-semibold">
                                                <?= htmlspecialchars($alerta['codigo_sensor'], ENT_QUOTES, 'UTF-8') ?>
                                                — <?= htmlspecialchars($alerta['tipo_do_sensor'], ENT_QUOTES, 'UTF-8') ?>
                                            </div>
                                        </div>

                                        <div class="mb-3">
                                            <div class="small text-secondary">Trem de origem</div>
                                            <div class="fw-semibold">
                                                <?= htmlspecialchars($alerta['nome_trem'], ENT_QUOTES, 'UTF-8') ?>
                                                (<?= htmlspecialchars($alerta['prefixo'], ENT_QUOTES, 'UTF-8') ?>)
                                            </div>
                                        </div>

                                        <div class="mb-3">
                                            <div class="small text-secondary">Severidade</div>

                                            <?php
                                            $severidade = $alerta['severidade'] ?: 'crítico';

                                            $classeSeveridade = match ($severidade) {
                                                'normal' => 'text-bg-success',
                                                'atenção' => 'text-bg-warning',
                                                'crítico' => 'text-bg-danger',
                                                default => 'text-bg-secondary'
                                            };
                                            ?>

                                            <span class="badge <?= $classeSeveridade ?>">
                                                <?= htmlspecialchars($severidade, ENT_QUOTES, 'UTF-8') ?>
                                            </span>
                                        </div>

                                        <hr>

                                        <div class="row g-3">

                                            <div class="col-sm-6">
                                                <span class="text-secondary small">
                                                    Valor registrado
                                                </span>

                                                <p class="fw-semibold mb-0">
                                                    <?= htmlspecialchars((string) $alerta['valor_lido'], ENT_QUOTES, 'UTF-8') ?>
                                                    <?= $alerta['tipo_sensor'] === 'Temperatura' ? '°C' : '' ?>
                                                </p>
                                            </div>

                                            <div class="col-sm-6">
                                                <span class="text-secondary small">
                                                    Limite máximo
                                                </span>

                                                <p class="fw-semibold mb-0">
                                                    <?= htmlspecialchars((string) $alerta['valor_limite'], ENT_QUOTES, 'UTF-8') ?>
                                                    <?= $alerta['tipo_sensor'] === 'Temperatura' ? '°C' : '' ?>
                                                </p>
                                            </div>

                                            <div class="col-sm-6">
                                                <span class="text-secondary small">
                                                    Data e hora
                                                </span>

                                                <p class="mb-0">
                                                    <?= htmlspecialchars($alerta['data_hora'], ENT_QUOTES, 'UTF-8') ?>
                                                </p>
                                            </div>

                                            <div class="col-sm-6">
                                                <span class="text-secondary small">
                                                    Status
                                                </span>

                                                <p class="mb-0">
                                                    <?php if ($alerta['status'] === 'Pendente'): ?>
                                                        <span class="badge text-bg-warning">
                                                            Pendente
                                                        </span>
                                                    <?php else: ?>
                                                        <span class="badge text-bg-secondary">
                                                            <?= htmlspecialchars($alerta['status'], ENT_QUOTES, 'UTF-8') ?>
                                                        </span>
                                                    <?php endif; ?>
                                                </p>
                                            </div>

                                        </div>

                                        <div class="mt-3 text-secondary small">
                                            Código do alerta:
                                            <?= (int) $alerta['id_alerta'] ?>
                                        </div>

                                    </div>
                                </div>
                            </div>

                        <?php endwhile; ?>

                    <?php else: ?>

                        <div class="col-12">
                            <div class="card border-0 shadow-sm">
                                <div class="card-body text-center py-5">
                                    <i class="bi bi-bell-slash fs-1 text-secondary"></i>

                                    <h2 class="h5 mt-3">
                                        Nenhum alerta encontrado
                                    </h2>

                                    <p class="text-secondary mb-0">
                                        Não existem alertas correspondentes à sua pesquisa.
                                    </p>

                                    <?php if ($busca !== ''): ?>
                                        <a href="alertas.php" class="btn btn-outline-primary mt-3">
                                            Limpar pesquisa
                                        </a>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>

                    <?php endif; ?>

                </div>

            </main>

        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"></script>

</body>
</html>

<?php
$stmt->close();
?>