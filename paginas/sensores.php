<?php

require_once "../assets/php/proteger.php";
require_once "../assets/php/permissao.php";
require_once "../assets/php/cabecalho.php";
require_once "../assets/php/menu.php";
require_once "../includes/limites.php";
require_once "../assets/php/conexao.php";

if (!temPapel(['gestor', 'maquinista'])) {
    http_response_code(403);
    echo "Acesso negado.";
    exit;
}

$tipoFiltro = $_GET['tipo'] ?? '';

$sql = "SELECT
            sensores.*,
            trens.prefixo,
            trens.modelo,
            (
                SELECT l.valor
                FROM leituras l
                WHERE l.id_sensor = sensores.id_sensor
                ORDER BY l.data_hora DESC, l.id_leitura DESC
                LIMIT 1
            ) AS ultima_leitura,
            (
                SELECT l.unidade
                FROM leituras l
                WHERE l.id_sensor = sensores.id_sensor
                ORDER BY l.data_hora DESC, l.id_leitura DESC
                LIMIT 1
            ) AS unidade_leitura
        FROM sensores
        LEFT JOIN trens ON trens.id_trem = sensores.id_trem";

if ($tipoFiltro !== '') {
    $sql .= " WHERE sensores.tipo = ?";
}

$sql .= " ORDER BY trens.prefixo, sensores.codigo";

$stmt = $conexao->prepare($sql);

if (!$stmt) {
    die("Erro ao preparar consulta: " . $conexao->error);
}

if ($tipoFiltro !== '') {
    $stmt->bind_param("s", $tipoFiltro);
}

$stmt->execute();
$sensores = $stmt->get_result();

$tipos = $conexao->query(
    "SELECT DISTINCT tipo FROM sensores ORDER BY tipo"
);

if (!$tipos) {
    die("Erro ao consultar os tipos de sensores: " . $conexao->error);
}

?>

<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>Gerenciamento de Sensores — FerroTrack</title>

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
                    <h1 class="h3 mb-0">Gerenciamento de Sensores</h1>

                    <p class="text-secondary mb-0 small">
                        Sensores instalados na malha
                    </p>
                </div>

            </div>

            <div class="row g-3">

                <div class="col-12">

                    <div class="d-flex flex-column flex-sm-row gap-2">

                        <a href="adicionar-sensor.php" class="btn btn-primary flex-fill">
                            <i class="bi bi-plus-circle me-1"></i>
                            Adicionar sensor
                        </a>

                        <a href="editar-sensor.php" class="btn btn-outline-primary flex-fill">
                            <i class="bi bi-pencil me-1"></i>
                            Editar sensor
                        </a>

                        <a href="remover-sensor.php" class="btn btn-outline-danger flex-fill">
                            <i class="bi bi-dash-circle me-1"></i>
                            Remover sensor
                        </a>

                    </div>

                </div>

                <div class="col-12">

                    <div class="card border-0 shadow-sm">

                        <div class="card-body">

                            <form method="GET" class="row g-2 align-items-end mb-4">

                                <div class="col-12 col-md-6 col-lg-4">

                                    <label for="tipo" class="form-label">
                                        Filtrar por tipo
                                    </label>

                                    <select class="form-select" name="tipo" id="tipo">

                                        <option value="">
                                            Todos os tipos
                                        </option>

                                        <?php while ($tipo = $tipos->fetch_assoc()): ?>

                                            <option
                                                value="<?= htmlspecialchars($tipo['tipo']) ?>"
                                                <?= $tipoFiltro === $tipo['tipo'] ? 'selected' : '' ?>
                                            >
                                                <?= htmlspecialchars($tipo['tipo']) ?>
                                            </option>

                                        <?php endwhile; ?>

                                    </select>

                                </div>

                                <div class="col-12 col-md-auto">

                                    <button type="submit" class="btn btn-primary">
                                        <i class="bi bi-funnel me-1"></i>
                                        Filtrar
                                    </button>

                                </div>

                                <?php if ($tipoFiltro !== ''): ?>

                                    <div class="col-12 col-md-auto">

                                        <a href="sensores.php" class="btn btn-outline-secondary">
                                            Limpar filtro
                                        </a>

                                    </div>

                                <?php endif; ?>

                            </form>

                            <div class="table-responsive">

                                <table class="table table-hover align-middle mb-0">

                                    <thead class="table-light">

                                        <tr>
                                            <th scope="col">Código</th>
                                            <th scope="col">Trem</th>
                                            <th scope="col">Tipo</th>
                                            <th scope="col">Localização</th>
                                            <th scope="col">Segmento</th>
                                            <th scope="col">Última leitura</th>
                                        </tr>

                                    </thead>

                                    <tbody>

                                        <?php if ($sensores->num_rows > 0): ?>

                                            <?php while ($sensor = $sensores->fetch_assoc()): ?>

                                                <?php

                                                $valorLeitura = $sensor['ultima_leitura'];
                                                $severidade = 'Sem leitura';
                                                $classeLeitura = 'bg-secondary-subtle text-secondary-emphasis';

                                                if ($valorLeitura !== null) {

                                                    try {

                                                        $classificacao = strtolower(
                                                            classificar(
                                                                $sensor['tipo'],
                                                                (float)$valorLeitura
                                                            )
                                                        );

                                                        if ($classificacao === 'normal') {
                                                            $severidade = 'Normal';
                                                            $classeLeitura = 'bg-success-subtle text-success-emphasis';
                                                        } elseif ($classificacao === 'atencao' || $classificacao === 'atenção') {
                                                            $severidade = 'Atenção';
                                                            $classeLeitura = 'bg-warning-subtle text-warning-emphasis';
                                                        } else {
                                                            $severidade = 'Crítico';
                                                            $classeLeitura = 'bg-danger-subtle text-danger-emphasis';
                                                        }

                                                    } catch (Throwable $erro) {

                                                        $severidade = 'Limite indisponível';
                                                        $classeLeitura = 'bg-secondary-subtle text-secondary-emphasis';

                                                    }

                                                }

                                                ?>

                                                <tr>

                                                    <td class="fw-semibold">
                                                        <?= htmlspecialchars($sensor['codigo'] ?? '') ?>
                                                    </td>

                                                    <td>

                                                        <?= htmlspecialchars($sensor['prefixo'] ?? 'Sem trem associado') ?>

                                                        <?php if (!empty($sensor['modelo'])): ?>
                                                            — <?= htmlspecialchars($sensor['modelo']) ?>
                                                        <?php endif; ?>

                                                    </td>

                                                    <td>
                                                        <?= htmlspecialchars($sensor['tipo'] ?? '') ?>
                                                    </td>

                                                    <td>
                                                        <?= htmlspecialchars($sensor['localizacao'] ?? '') ?>
                                                    </td>

                                                    <td>
                                                        <?= htmlspecialchars($sensor['segmento'] ?? '') ?>
                                                    </td>

                                                    <td>

                                                        <span class="badge <?= $classeLeitura ?>">

                                                            <?php if ($valorLeitura === null): ?>

                                                                Sem leitura

                                                            <?php else: ?>

                                                                <?= htmlspecialchars((string)$valorLeitura) ?>

                                                                <?= htmlspecialchars($sensor['unidade_leitura'] ?? '') ?>

                                                                — <?= htmlspecialchars($severidade) ?>

                                                            <?php endif; ?>

                                                        </span>

                                                    </td>

                                                </tr>

                                            <?php endwhile; ?>

                                        <?php else: ?>

                                            <tr>

                                                <td colspan="6" class="text-center text-secondary py-4">
                                                    Nenhum sensor encontrado.
                                                </td>

                                            </tr>

                                        <?php endif; ?>

                                    </tbody>

                                </table>

                            </div>

                        </div>

                    </div>

                </div>

            </div>

        </main>

    </div>

</div>

<?php
$stmt->close();
?>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"></script>

</body>
</html>