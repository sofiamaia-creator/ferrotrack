<?php

require_once "../assets/php/proteger.php";
require_once "../assets/php/permissao.php";
require_once "../assets/php/cabecalho.php";
require_once "../assets/php/menu.php";
require_once "../assets/php/conexao.php";
require_once "../includes/limites.php";

if (!temPapel(['gestor', 'maquinista', 'cliente'])) {
    http_response_code(403);
    exit("Acesso negado.");
}

$trensPorStatus = [];
$alertasPorSeveridade = [];
$sensoresCriticos = 0;
$viagensEmAndamento = 0;
$velocidadeMedia = null;

$resultado = $conexao->query("
    SELECT status, COUNT(*) AS quantidade
    FROM trens
    GROUP BY status
    ORDER BY quantidade DESC
");

if ($resultado) {
    while ($linha = $resultado->fetch_assoc()) {
        $trensPorStatus[] = $linha;
    }
}

$resultado = $conexao->query("
    SELECT severidade, COUNT(*) AS quantidade
    FROM alertas
    WHERE status = 'Pendente'
    GROUP BY severidade
    ORDER BY quantidade DESC
");

if ($resultado) {
    while ($linha = $resultado->fetch_assoc()) {
        $alertasPorSeveridade[] = $linha;
    }
}

$resultado = $conexao->query("
    SELECT s.id_sensor, s.tipo, l.valor
    FROM sensores s
    INNER JOIN leituras l ON l.id_leitura = (
        SELECT l2.id_leitura
        FROM leituras l2
        WHERE l2.id_sensor = s.id_sensor
        ORDER BY l2.data_hora DESC, l2.id_leitura DESC
        LIMIT 1
    )
");

if ($resultado) {
    while ($linha = $resultado->fetch_assoc()) {
        try {
            if (strtolower(classificar($linha['tipo'], (float) $linha['valor'])) === 'critico') {
                $sensoresCriticos++;
            }
        } catch (Throwable $erro) {
        }
    }
}

$resultado = $conexao->query("
    SELECT COUNT(*) AS quantidade
    FROM rotas
    WHERE LOWER(TRIM(status)) IN ('em andamento', 'andamento', 'em_andamento')
");

if ($resultado) {
    $linha = $resultado->fetch_assoc();
    $viagensEmAndamento = (int) $linha['quantidade'];
}

$tipoSensor = 'Velocidade';
$dataLimite = date('Y-m-d H:i:s', strtotime('-24 hours'));

$stmt = $conexao->prepare("
    SELECT ROUND(AVG(leituras.valor), 1) AS media
    FROM leituras
    INNER JOIN sensores ON sensores.id_sensor = leituras.id_sensor
    WHERE sensores.tipo = ?
      AND leituras.data_hora >= ?
");

if ($stmt) {
    $stmt->bind_param("ss", $tipoSensor, $dataLimite);
    $stmt->execute();

    $resultado = $stmt->get_result();
    $linha = $resultado->fetch_assoc();

    if ($linha && $linha['media'] !== null) {
        $velocidadeMedia = (float) $linha['media'];
    }

    $stmt->close();
}

function escapar($valor)
{
    return htmlspecialchars((string) $valor, ENT_QUOTES, 'UTF-8');
}

?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Dashboard Geral — FerroTrack</title>
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

        .indicador {
            border: 0;
            border-radius: 12px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.06);
            height: 100%;
        }

        .indicador-icone {
            width: 46px;
            height: 46px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.4rem;
        }

        .indicador-valor {
            font-size: 1.8rem;
            font-weight: 700;
            color: #1e3a8a;
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

<nav class="navbar navbar-expand-lg bg-white border-bottom sticky-top">
    <div class="container-fluid gap-2">
        <button class="btn btn-outline-secondary d-lg-none" type="button" data-bs-toggle="offcanvas" data-bs-target="#menuLateral" aria-controls="menuLateral" aria-label="Abrir menu">
            <i class="bi bi-list fs-4"></i>
        </button>

        <a class="navbar-brand d-flex align-items-center gap-2 me-auto" href="dashboard.php">
            <img src="../assets/img/logo.png" alt="FerroTrack" width="32" height="32" class="object-fit-contain">
            <span class="fw-semibold">FerroTrack</span>
        </a>

        <div class="d-flex align-items-center gap-2">
            <a class="btn btn-sm btn-outline-secondary border-0" href="alertas.php" title="Alertas">
                <i class="bi bi-bell fs-5"></i>
            </a>

            <a class="btn btn-sm btn-outline-primary" href="perfil.php">
                <i class="bi bi-person-circle"></i>
                <span class="d-none d-md-inline">Minha conta</span>
            </a>
        </div>
    </div>
</nav>

<div class="container-fluid px-0">
    <div class="layout-principal">
        <?php renderizarMenu(); ?>

        <div class="offcanvas offcanvas-start d-lg-none" tabindex="-1" id="menuLateral" aria-labelledby="tituloMenu">
            <div class="offcanvas-header border-bottom">
                <h5 class="offcanvas-title" id="tituloMenu">Menu</h5>
                <button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="Fechar"></button>
            </div>

            <div class="offcanvas-body">
                <ul class="nav nav-pills flex-column gap-2">
                    <li class="nav-item">
                        <a class="nav-link active" href="dashboard.php">
                            <i class="bi bi-grid-1x2 me-2"></i>Dashboard Geral
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="trens.php">
                            <i class="bi bi-train-front me-2"></i>Trens
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="sensores.php">
                            <i class="bi bi-cpu me-2"></i>Sensores
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="alertas.php">
                            <i class="bi bi-bell me-2"></i>Alertas
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="relatorios.php">
                            <i class="bi bi-file-earmark-bar-graph me-2"></i>Relatórios
                        </a>
                    </li>
                </ul>
            </div>
        </div>

        <main class="conteudo-principal">
            <div class="mb-4">
                <h1 class="h3 mb-1">Painel principal</h1>
                <p class="text-secondary mb-0">Visão consolidada da operação ferroviária</p>
            </div>

            <div class="row g-3 mb-4">
                <div class="col-12 col-md-6 col-xl-4">
                    <div class="card indicador">
                        <div class="card-body">
                            <div class="d-flex align-items-center gap-3 mb-3">
                                <div class="indicador-icone bg-primary-subtle text-primary">
                                    <i class="bi bi-train-front"></i>
                                </div>
                                <h2 class="h6 mb-0">Trens por status</h2>
                            </div>

                            <?php if (empty($trensPorStatus)): ?>
                                <p class="text-secondary mb-0">Sem dados</p>
                            <?php else: ?>
                                <?php foreach ($trensPorStatus as $trem): ?>
                                    <div class="d-flex justify-content-between align-items-center border-bottom py-2">
                                        <span><?= escapar($trem['status'] ?? 'Sem status') ?></span>
                                        <strong><?= (int) $trem['quantidade'] ?></strong>
                                    </div>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

                <div class="col-12 col-md-6 col-xl-4">
                    <div class="card indicador">
                        <div class="card-body">
                            <div class="d-flex align-items-center gap-3 mb-3">
                                <div class="indicador-icone bg-danger-subtle text-danger">
                                    <i class="bi bi-bell"></i>
                                </div>
                                <h2 class="h6 mb-0">Alertas ativos por severidade</h2>
                            </div>

                            <?php if (empty($alertasPorSeveridade)): ?>
                                <p class="text-secondary mb-0">Sem dados</p>
                            <?php else: ?>
                                <?php foreach ($alertasPorSeveridade as $alerta): ?>
                                    <div class="d-flex justify-content-between align-items-center border-bottom py-2">
                                        <span><?= escapar($alerta['severidade'] ?: 'Sem severidade') ?></span>
                                        <strong><?= (int) $alerta['quantidade'] ?></strong>
                                    </div>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

                <div class="col-12 col-md-6 col-xl-4">
                    <div class="card indicador">
                        <div class="card-body">
                            <div class="d-flex align-items-center gap-3 mb-3">
                                <div class="indicador-icone bg-danger-subtle text-danger">
                                    <i class="bi bi-exclamation-triangle"></i>
                                </div>
                                <h2 class="h6 mb-0">Sensores com leitura crítica</h2>
                            </div>

                            <div class="indicador-valor"><?= $sensoresCriticos ?></div>
                            <p class="text-secondary small mb-0">Com base na leitura mais recente de cada sensor</p>
                        </div>
                    </div>
                </div>

                <div class="col-12 col-md-6 col-xl-6">
                    <div class="card indicador">
                        <div class="card-body">
                            <div class="d-flex align-items-center gap-3 mb-3">
                                <div class="indicador-icone bg-success-subtle text-success">
                                    <i class="bi bi-signpost-split"></i>
                                </div>
                                <h2 class="h6 mb-0">Viagens em andamento</h2>
                            </div>

                            <div class="indicador-valor"><?= $viagensEmAndamento ?></div>
                            <p class="text-secondary small mb-0">Rotas atualmente em andamento</p>
                        </div>
                    </div>
                </div>

                <div class="col-12 col-md-6 col-xl-6">
                    <div class="card indicador">
                        <div class="card-body">
                            <div class="d-flex align-items-center gap-3 mb-3">
                                <div class="indicador-icone bg-info-subtle text-info">
                                    <i class="bi bi-speedometer2"></i>
                                </div>
                                <h2 class="h6 mb-0">Velocidade média da frota</h2>
                            </div>

                            <div class="indicador-valor">
                                <?= $velocidadeMedia !== null ? number_format($velocidadeMedia, 1, ',', '.') . ' km/h' : 'Sem dados' ?>
                            </div>

                            <p class="text-secondary small mb-0">Média das leituras das últimas 24 horas</p>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>