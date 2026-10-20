<?php

require_once "../assets/php/proteger.php";
require_once "../assets/php/permissao.php";
require_once "../assets/php/conexao.php";
require_once "../assets/php/cabecalho.php";

if (!temPapel(['maquinista', 'gestor'])) {
    http_response_code(403);
    echo "Acesso negado.";
    exit;
}

$sql = "SELECT
            carga.id_carga,
            carga.nome_carga,
            carga.data_transporte,
            carga.peso,
            trens.prefixo
        FROM carga
        INNER JOIN trens ON trens.id_trem = carga.id_trem
        ORDER BY carga.data_transporte DESC";

$resultado = $conexao->query($sql);

?>
<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Relatórios de Carga — Ferrovias</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../paginas/cabecalho.css">
</head>

<body class="bg-body-tertiary">

<?php gerarCabecalho(); ?>

<nav class="navbar navbar-expand-lg bg-white border-bottom sticky-top">
    <div class="container-fluid gap-2">
        <button class="btn btn-outline-secondary d-lg-none" type="button"
                data-bs-toggle="offcanvas" data-bs-target="#menuLateral"
                aria-controls="menuLateral" aria-label="Abrir menu">
            <i class="bi bi-list fs-4"></i>
        </button>

        <a class="navbar-brand d-flex align-items-center gap-2 me-auto" href="painel-gestor.php">
            <img src="../assets/img/logo.png" alt="Ferrovias" width="32" height="32" class="object-fit-contain">
            <span class="fw-semibold">Ferrovias</span>
        </a>

        <div class="d-flex align-items-center gap-1">
            <button class="btn btn-sm btn-outline-secondary border-0 position-relative"
                    type="button" title="Notificações" aria-label="Notificações"
                    onclick="window.location.href='alertas.php'">
                <i class="bi bi-bell fs-5"></i>
                <span class="position-absolute top-0 start-100 translate-middle p-1 bg-danger border border-light rounded-circle"></span>
            </button>

            <div class="vr mx-2 d-none d-sm-block"></div>

            <a class="btn btn-sm btn-outline-primary d-none d-sm-inline-flex align-items-center gap-1" href="perfil.php">
                <i class="bi bi-person-circle"></i>
                <span class="d-none d-md-inline">Minha conta</span>
            </a>
        </div>
    </div>
</nav>

<div class="container-fluid">
    <div class="row">

        <aside class="col-lg-3 col-xl-2 d-none d-lg-block bg-white border-end vh-100 position-sticky top-0 p-0">
            <nav class="p-3 overflow-auto h-100">
                <ul class="nav nav-pills flex-column">
                    <li class="nav-item mt-3 mb-1">
                        <span class="text-uppercase small fw-semibold text-secondary px-3">Painéis</span>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link text-body" href="painel-gestor.php"><i class="bi bi-speedometer2 me-2"></i>Painel do Gestor</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link text-body" href="painel-maquinista.php"><i class="bi bi-person-badge me-2"></i>Painel do Maquinista</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link text-body" href="painel-cliente.php"><i class="bi bi-person me-2"></i>Painel do Cliente</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link text-body" href="dashboard.php"><i class="bi bi-grid-1x2 me-2"></i>Dashboard Geral</a>
                    </li>

                    <li class="nav-item mt-3 mb-1">
                        <span class="text-uppercase small fw-semibold text-secondary px-3">Operação</span>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link text-body" href="gestao-rotas.php"><i class="bi bi-signpost-split me-2"></i>Gestão de Rotas</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link text-body" href="adicionar-rota.php"><i class="bi bi-plus-square me-2"></i>Adicionar Rota</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link text-body" href="monitoramento-cargas.php"><i class="bi bi-box-seam me-2"></i>Monitoramento de Cargas</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link text-body" href="trens.php"><i class="bi bi-train-front me-2"></i>Trens</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link text-body" href="trens-cadastrados.php"><i class="bi bi-list-ul me-2"></i>Trens Cadastrados</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link text-body" href="alertas.php"><i class="bi bi-bell me-2"></i>Alertas e Notificações</a>
                    </li>

                    <li class="nav-item mt-3 mb-1">
                        <span class="text-uppercase small fw-semibold text-secondary px-3">Sensores</span>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link text-body" href="sensores.php"><i class="bi bi-cpu me-2"></i>Gerenciar Sensores</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link text-body" href="adicionar-sensor.php"><i class="bi bi-plus-circle me-2"></i>Adicionar Sensor</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link text-body" href="editar-sensor.php"><i class="bi bi-pencil me-2"></i>Editar Sensor</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link text-body" href="remover-sensor.php"><i class="bi bi-dash-circle me-2"></i>Remover Sensor</a>
                    </li>

                    <li class="nav-item mt-3 mb-1">
                        <span class="text-uppercase small fw-semibold text-secondary px-3">Relatórios</span>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link active bg-primary text-white" href="relatorios.php" aria-current="page">
                            <i class="bi bi-file-earmark-bar-graph me-2"></i>Relatórios
                        </a>
                    </li>

                    <li class="nav-item mt-3 mb-1">
                        <span class="text-uppercase small fw-semibold text-secondary px-3">Usuários</span>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link text-body" href="usuarios.php"><i class="bi bi-people me-2"></i>Status de Usuários</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link text-body" href="gerenciamento-usuarios.php"><i class="bi bi-person-gear me-2"></i>Gerenciar Usuários</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link text-body" href="adicionar-usuario.php"><i class="bi bi-person-plus me-2"></i>Adicionar Usuário</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link text-body" href="perfil.php"><i class="bi bi-person-circle me-2"></i>Meu Perfil</a>
                    </li>

                    <li class="nav-item mt-3 mb-1">
                        <span class="text-uppercase small fw-semibold text-secondary px-3">Cliente</span>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link text-body" href="pagamento.php"><i class="bi bi-credit-card me-2"></i>Pagamento</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link text-body" href="passagens.php"><i class="bi bi-ticket-perforated me-2"></i>Passagens</a>
                    </li>
                </ul>
            </nav>
        </aside>

        <div class="offcanvas offcanvas-start d-lg-none" tabindex="-1" id="menuLateral" aria-labelledby="tituloMenu">
            <div class="offcanvas-header border-bottom">
                <h5 class="offcanvas-title" id="tituloMenu">Menu</h5>
                <button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="Fechar"></button>
            </div>
            <div class="offcanvas-body">
                <a class="nav-link" href="painel-gestor.php">Painel do Gestor</a>
                <a class="nav-link" href="painel-maquinista.php">Painel do Maquinista</a>
                <a class="nav-link" href="dashboard.php">Dashboard Geral</a>
                <a class="nav-link" href="gestao-rotas.php">Gestão de Rotas</a>
                <a class="nav-link" href="monitoramento-cargas.php">Monitoramento de Cargas</a>
                <a class="nav-link" href="trens.php">Trens</a>
                <a class="nav-link" href="alertas.php">Alertas e Notificações</a>
                <a class="nav-link" href="sensores.php">Gerenciar Sensores</a>
                <a class="nav-link active" href="relatorios.php">Relatórios</a>
                <a class="nav-link" href="perfil.php">Meu Perfil</a>
            </div>
        </div>

        <main class="col-12 col-lg-9 col-xl-10 py-4 px-3 px-md-4">
            <div class="d-flex flex-wrap align-items-center justify-content-between gap-2 mb-4">
                <div class="d-flex align-items-center gap-2">
                    <a class="btn btn-outline-secondary btn-sm" href="relatorios.php" aria-label="Voltar">
                        <i class="bi bi-arrow-left"></i>
                    </a>
                    <div>
                        <h1 class="h3 mb-0">Relatórios de Carga</h1>
                        <p class="text-secondary mb-0">Cargas cadastradas e seus respectivos trens.</p>
                    </div>
                </div>
            </div>

            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th scope="col">Carga</th>
                                    <th scope="col">Data</th>
                                    <th scope="col">Peso</th>
                                    <th scope="col">Trem</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if ($resultado && $resultado->num_rows > 0): ?>
                                    <?php while ($carga = $resultado->fetch_assoc()): ?>
                                        <tr>
                                            <td class="fw-semibold">
                                                <?= htmlspecialchars($carga['nome_carga']) ?>
                                            </td>
                                            <td>
                                                <span class="badge text-bg-light border">
                                                    <?= date('d/m/Y', strtotime($carga['data_transporte'])) ?>
                                                </span>
                                            </td>
                                            <td>
                                                <?= number_format((float) $carga['peso'], 2, ',', '.') ?> kg
                                            </td>
                                            <td>
                                                <?= htmlspecialchars($carga['prefixo']) ?>
                                            </td>
                                        </tr>
                                    <?php endwhile; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="4" class="text-center text-secondary py-4">
                                            Nenhuma carga cadastrada.
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
