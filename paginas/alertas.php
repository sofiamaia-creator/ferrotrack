
<?php

require_once "../assets/php/proteger.php";
require_once "../assets/php/permissao.php";
require_once "../assets/php/cabecalho.php";
require_once "../assets/php/conexao.php";

if (!temPapel(['gestor', 'maquinista', 'cliente'])) {
    http_response_code(403);
    exit("Acesso negado.");
}

// Pesquisa de alertas
$busca = trim($_GET['busca'] ?? '');

if ($busca !== '') {
    $termo = "%" . $busca . "%";

    $sql = "SELECT id_alerta, id_leitura, tipo_sensor,
                   valor_lido, valor_limite, mensagem,
                   data_hora, status
            FROM alertas
            WHERE tipo_sensor LIKE ?
               OR mensagem LIKE ?
               OR status LIKE ?
            ORDER BY data_hora DESC";

    $stmt = $conexao->prepare($sql);

    if (!$stmt) {
        exit("Erro ao consultar alertas: " . $conexao->error);
    }

    $stmt->bind_param("sss", $termo, $termo, $termo);

} else {

    $sql = "SELECT id_alerta, id_leitura, tipo_sensor,
                   valor_lido, valor_limite, mensagem,
                   data_hora, status
            FROM alertas
            ORDER BY data_hora DESC";

    $stmt = $conexao->prepare($sql);

    if (!$stmt) {
        exit("Erro ao consultar alertas: " . $conexao->error);
    }
}

if (!$stmt->execute()) {
    exit("Erro ao carregar alertas: " . $stmt->error);
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

    <link
        href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css"
        rel="stylesheet"
    >

    <link
        href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css"
        rel="stylesheet"
    >

    <link rel="stylesheet" href="../paginas/cabecalho.css">
</head>

<body class="bg-body-tertiary">

    <?php gerarCabecalho(); ?>

    <!-- Barra superior -->
    <nav class="navbar navbar-expand-lg bg-white border-bottom sticky-top">
        <div class="container-fluid gap-2">

            <button
                class="btn btn-outline-secondary d-lg-none"
                type="button"
                data-bs-toggle="offcanvas"
                data-bs-target="#menuLateral"
                aria-controls="menuLateral"
                aria-label="Abrir menu"
            >
                <i class="bi bi-list fs-4"></i>
            </button>

            <a
                class="navbar-brand d-flex align-items-center gap-2 me-auto"
                href="dashboard.php"
            >
                <img
                    src="../assets/img/logo.png"
                    alt="FerroTrack"
                    width="32"
                    height="32"
                    class="object-fit-contain"
                >

                <span class="fw-semibold">FerroTrack</span>
            </a>

            <div class="d-flex align-items-center gap-1">

                <a
                    class="btn btn-sm btn-outline-secondary border-0"
                    href="alertas.php"
                    title="Notificações"
                    aria-label="Notificações"
                >
                    <i class="bi bi-bell fs-5"></i>
                </a>

                <div class="vr mx-2"></div>

                <a
                    class="btn btn-sm btn-outline-primary d-inline-flex align-items-center gap-1"
                    href="perfil.php"
                >
                    <i class="bi bi-person-circle"></i>
                    <span>Minha conta</span>
                </a>

            </div>
        </div>
    </nav>

    <div class="container-fluid">
        <div class="row">

            <!-- Menu lateral para computadores -->
            <aside
                class="col-lg-3 col-xl-2 d-none d-lg-block bg-white border-end p-0"
            >
                <nav class="p-3">

                    <ul class="nav nav-pills flex-column gap-1">

                        <li class="nav-item mt-2 mb-1">
                            <span class="text-uppercase small fw-semibold text-secondary px-3">
                                Painéis
                            </span>
                        </li>

                        <li class="nav-item">
                            <a class="nav-link text-body" href="painel-gestor.php">
                                <i class="bi bi-speedometer2 me-2"></i>
                                Painel do Gestor
                            </a>
                        </li>

                        <li class="nav-item">
                            <a class="nav-link text-body" href="painel-maquinista.php">
                                <i class="bi bi-person-badge me-2"></i>
                                Painel do Maquinista
                            </a>
                        </li>

                        <li class="nav-item">
                            <a class="nav-link text-body" href="painel-cliente.php">
                                <i class="bi bi-person me-2"></i>
                                Painel do Cliente
                            </a>
                        </li>

                        <li class="nav-item">
                            <a class="nav-link text-body" href="dashboard.php">
                                <i class="bi bi-grid-1x2 me-2"></i>
                                Dashboard Geral
                            </a>
                        </li>

                        <li class="nav-item mt-3 mb-1">
                            <span class="text-uppercase small fw-semibold text-secondary px-3">
                                Operação
                            </span>
                        </li>

                        <li class="nav-item">
                            <a class="nav-link text-body" href="gestao-rotas.php">
                                <i class="bi bi-signpost-split me-2"></i>
                                Gestão de Rotas
                            </a>
                        </li>

                        <li class="nav-item">
                            <a class="nav-link text-body" href="trens.php">
                                <i class="bi bi-train-front me-2"></i>
                                Trens
                            </a>
                        </li>

                        <li class="nav-item">
                            <a class="nav-link text-body" href="trens-cadastrados.php">
                                <i class="bi bi-list-ul me-2"></i>
                                Trens Cadastrados
                            </a>
                        </li>

                        <li class="nav-item">
                            <a
                                class="nav-link active bg-primary text-white"
                                href="alertas.php"
                                aria-current="page"
                            >
                                <i class="bi bi-bell me-2"></i>
                                Alertas e Notificações
                            </a>
                        </li>

                        <li class="nav-item mt-3 mb-1">
                            <span class="text-uppercase small fw-semibold text-secondary px-3">
                                Sensores
                            </span>
                        </li>

                        <li class="nav-item">
                            <a class="nav-link text-body" href="sensores.php">
                                <i class="bi bi-cpu me-2"></i>
                                Gerenciar Sensores
                            </a>
                        </li>

                        <li class="nav-item">
                            <a class="nav-link text-body" href="relatorios.php">
                                <i class="bi bi-file-earmark-bar-graph me-2"></i>
                                Relatórios
                            </a>
                        </li>

                        <li class="nav-item mt-3 mb-1">
                            <span class="text-uppercase small fw-semibold text-secondary px-3">
                                Conta
                            </span>
                        </li>

                        <li class="nav-item">
                            <a class="nav-link text-body" href="perfil.php">
                                <i class="bi bi-person-circle me-2"></i>
                                Meu Perfil
                            </a>
                        </li>

                    </ul>

                </nav>
            </aside>

            <!-- Menu lateral para celular -->
            <div
                class="offcanvas offcanvas-start d-lg-none"
                tabindex="-1"
                id="menuLateral"
                aria-labelledby="tituloMenu"
            >
                <div class="offcanvas-header border-bottom">
                    <h5 class="offcanvas-title" id="tituloMenu">
                        Menu FerroTrack
                    </h5>

                    <button
                        type="button"
                        class="btn-close"
                        data-bs-dismiss="offcanvas"
                        aria-label="Fechar"
                    ></button>
                </div>

                <div class="offcanvas-body">

                    <nav class="nav nav-pills flex-column gap-2">

                        <a class="nav-link text-body" href="dashboard.php">
                            <i class="bi bi-grid-1x2 me-2"></i>
                            Dashboard
                        </a>

                        <a class="nav-link text-body" href="trens.php">
                            <i class="bi bi-train-front me-2"></i>
                            Trens
                        </a>

                        <a class="nav-link active" href="alertas.php">
                            <i class="bi bi-bell me-2"></i>
                            Alertas e Notificações
                        </a>

                        <a class="nav-link text-body" href="sensores.php">
                            <i class="bi bi-cpu me-2"></i>
                            Sensores
                        </a>

                        <a class="nav-link text-body" href="relatorios.php">
                            <i class="bi bi-file-earmark-bar-graph me-2"></i>
                            Relatórios
                        </a>

                        <a class="nav-link text-body" href="perfil.php">
                            <i class="bi bi-person-circle me-2"></i>
                            Meu Perfil
                        </a>

                    </nav>

                </div>
            </div>

            <!-- Conteúdo principal -->
            <main class="col-12 col-lg-9 col-xl-10 py-4 px-3 px-md-4">

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

                <!-- Pesquisa -->
                <div class="card border-0 shadow-sm mb-4">

                    <div class="card-body">

                        <form
                            class="row g-2 align-items-end"
                            method="GET"
                            action="alertas.php"
                        >

                            <div class="col-12 col-md-9 col-lg-10">

                                <label for="buscaAlerta" class="form-label">
                                    Buscar alertas
                                </label>

                                <input
                                    type="search"
                                    class="form-control"
                                    id="buscaAlerta"
                                    name="busca"
                                    placeholder="Buscar por tipo, mensagem ou status"
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

                <!-- Lista de alertas reais do banco -->
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
                                                    <?= htmlspecialchars($alerta['tipo_sensor'], ENT_QUOTES, 'UTF-8') ?>
                                                </h2>

                                                <p class="mb-3">
                                                    <?= htmlspecialchars($alerta['mensagem'], ENT_QUOTES, 'UTF-8') ?>
                                                </p>

                                            </div>

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

                                        <a
                                            href="alertas.php"
                                            class="btn btn-outline-primary mt-3"
                                        >
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