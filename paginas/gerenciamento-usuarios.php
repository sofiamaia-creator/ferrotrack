<?php
require_once "../assets/php/proteger.php";
require_once "../assets/php/permissao.php";
require_once "../assets/php/conexao.php";
require_once "../assets/php/cabecalho.php";

if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

if (!temPapel(['gestor'])) {
    http_response_code(403);
    exit("Acesso negado.");
}

if (empty($_SESSION['csrf_usuarios'])) {
    $_SESSION['csrf_usuarios'] = bin2hex(random_bytes(32));
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $token = $_POST['csrf_token'] ?? '';
    $idUsuario = filter_input(INPUT_POST, 'id_usuario', FILTER_VALIDATE_INT);
    $acao = $_POST['acao'] ?? '';
    $idLogado = (int) ($_SESSION['id_usuario'] ?? 0);

    if (
        !is_string($token) ||
        !hash_equals($_SESSION['csrf_usuarios'], $token)
    ) {
        http_response_code(403);
        exit("Solicitação inválida. Atualize a página e tente novamente.");
    }

    if (
        !$idUsuario ||
        !in_array($acao, ['ativar', 'desativar'], true)
    ) {
        header("Location: gerenciamento-usuarios.php?erro=solicitacao");
        exit;
    }

    if ($idUsuario === $idLogado && $acao === 'desativar') {
        header("Location: gerenciamento-usuarios.php?erro=propria_conta");
        exit;
    }

    $novoStatus = ($acao === 'ativar') ? 1 : 0;

    $stmt = $conexao->prepare(
        "UPDATE usuarios SET ativo = ? WHERE id_usuario = ?"
    );

    if (!$stmt) {
        error_log("Erro ao preparar atualização de usuário: " . $conexao->error);
        header("Location: gerenciamento-usuarios.php?erro=atualizacao");
        exit;
    }

    $stmt->bind_param("ii", $novoStatus, $idUsuario);

    if (!$stmt->execute()) {
        error_log("Erro ao atualizar usuário: " . $stmt->error);
        $stmt->close();
        header("Location: gerenciamento-usuarios.php?erro=atualizacao");
        exit;
    }

    $stmt->close();

    header("Location: gerenciamento-usuarios.php?sucesso=" . $acao);
    exit;
}

$status = $_GET['status'] ?? '';
$busca = trim($_GET['busca'] ?? '');

if (!in_array($status, ['', 'ativo', 'inativo'], true)) {
    $status = '';
}

$sql = "SELECT id_usuario, login, papel, ativo, telefone, criado_em
        FROM usuarios
        WHERE 1 = 1";

$parametros = [];
$tipos = "";

if ($status === 'inativo') {
    $sql .= " AND ativo = 0";
} elseif ($status === 'ativo') {
    $sql .= " AND ativo = 1";
}

if ($busca !== '') {
    $sql .= " AND login LIKE ?";
    $parametros[] = "%" . $busca . "%";
    $tipos .= "s";
}

$sql .= " ORDER BY login ASC";

$stmt = $conexao->prepare($sql);

if (!$stmt) {
    error_log("Erro ao preparar consulta de usuários: " . $conexao->error);
    die("Erro ao consultar os usuários.");
}

if (!empty($parametros)) {
    $stmt->bind_param($tipos, ...$parametros);
}

if (!$stmt->execute()) {
    error_log("Erro ao consultar usuários: " . $stmt->error);
    $stmt->close();
    die("Erro ao consultar os usuários.");
}

$resultado = $stmt->get_result();
?>
<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Usuários — Ferrovias</title>
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
                            <a class="nav-link d-flex align-items-center gap-2 rounded px-3 text-body" href="painel-gestor.php">
                                <i class="bi bi-speedometer2"></i><span>Painel do Gestor</span>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link d-flex align-items-center gap-2 rounded px-3 text-body" href="painel-maquinista.php">
                                <i class="bi bi-person-badge"></i><span>Painel do Maquinista</span>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link d-flex align-items-center gap-2 rounded px-3 text-body" href="painel-cliente.php">
                                <i class="bi bi-person"></i><span>Painel do Cliente</span>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link d-flex align-items-center gap-2 rounded px-3 text-body" href="dashboard.php">
                                <i class="bi bi-grid-1x2"></i><span>Dashboard Geral</span>
                            </a>
                        </li>

                        <li class="nav-item mt-3 mb-1">
                            <span class="text-uppercase small fw-semibold text-secondary px-3">Operação</span>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link d-flex align-items-center gap-2 rounded px-3 text-body" href="gestao-rotas.php">
                                <i class="bi bi-signpost-split"></i><span>Gestão de Rotas</span>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link d-flex align-items-center gap-2 rounded px-3 text-body" href="adicionar-rota.php">
                                <i class="bi bi-plus-square"></i><span>Adicionar Rota</span>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link d-flex align-items-center gap-2 rounded px-3 text-body" href="monitoramento-cargas.php">
                                <i class="bi bi-box-seam"></i><span>Monitoramento de Cargas</span>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link d-flex align-items-center gap-2 rounded px-3 text-body" href="trens.php">
                                <i class="bi bi-train-front"></i><span>Trens</span>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link d-flex align-items-center gap-2 rounded px-3 text-body" href="trens-cadastrados.php">
                                <i class="bi bi-list-ul"></i><span>Trens Cadastrados</span>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link d-flex align-items-center gap-2 rounded px-3 text-body" href="alertas.php">
                                <i class="bi bi-bell"></i><span>Alertas e Notificações</span>
                            </a>
                        </li>

                        <li class="nav-item mt-3 mb-1">
                            <span class="text-uppercase small fw-semibold text-secondary px-3">Sensores</span>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link d-flex align-items-center gap-2 rounded px-3 text-body" href="sensores.php">
                                <i class="bi bi-cpu"></i><span>Gerenciar Sensores</span>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link d-flex align-items-center gap-2 rounded px-3 text-body" href="adicionar-sensor.php">
                                <i class="bi bi-plus-circle"></i><span>Adicionar Sensor</span>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link d-flex align-items-center gap-2 rounded px-3 text-body" href="editar-sensor.php">
                                <i class="bi bi-pencil"></i><span>Editar Sensor</span>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link d-flex align-items-center gap-2 rounded px-3 text-body" href="remover-sensor.php">
                                <i class="bi bi-dash-circle"></i><span>Remover Sensor</span>
                            </a>
                        </li>

                        <li class="nav-item mt-3 mb-1">
                            <span class="text-uppercase small fw-semibold text-secondary px-3">Relatórios</span>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link d-flex align-items-center gap-2 rounded px-3 text-body" href="relatorios.php">
                                <i class="bi bi-file-earmark-bar-graph"></i><span>Relatórios</span>
                            </a>
                        </li>

                        <li class="nav-item mt-3 mb-1">
                            <span class="text-uppercase small fw-semibold text-secondary px-3">Usuários</span>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link d-flex align-items-center gap-2 rounded px-3 text-body" href="usuarios.php">
                                <i class="bi bi-people"></i><span>Status de Usuários</span>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link d-flex align-items-center gap-2 rounded px-3 active bg-primary text-white" href="gerenciamento-usuarios.php" aria-current="page">
                                <i class="bi bi-person-gear"></i><span>Gerenciar Usuários</span>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link d-flex align-items-center gap-2 rounded px-3 text-body" href="adicionar-usuario.php">
                                <i class="bi bi-person-plus"></i><span>Adicionar Usuário</span>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link d-flex align-items-center gap-2 rounded px-3 text-body" href="perfil.php">
                                <i class="bi bi-person-circle"></i><span>Meu Perfil</span>
                            </a>
                        </li>

                        <li class="nav-item mt-3 mb-1">
                            <span class="text-uppercase small fw-semibold text-secondary px-3">Cliente</span>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link d-flex align-items-center gap-2 rounded px-3 text-body" href="pagamento.php">
                                <i class="bi bi-credit-card"></i><span>Pagamento</span>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link d-flex align-items-center gap-2 rounded px-3 text-body" href="passagens.php">
                                <i class="bi bi-ticket-perforated"></i><span>Passagens</span>
                            </a>
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
                    <ul class="nav nav-pills flex-column">
                        <li class="nav-item mt-3 mb-1"><span class="text-uppercase small fw-semibold text-secondary px-3">Painéis</span></li>
                        <li class="nav-item"><a class="nav-link text-body" href="painel-gestor.php"><i class="bi bi-speedometer2 me-2"></i>Painel do Gestor</a></li>
                        <li class="nav-item"><a class="nav-link text-body" href="painel-maquinista.php"><i class="bi bi-person-badge me-2"></i>Painel do Maquinista</a></li>
                        <li class="nav-item"><a class="nav-link text-body" href="painel-cliente.php"><i class="bi bi-person me-2"></i>Painel do Cliente</a></li>
                        <li class="nav-item"><a class="nav-link text-body" href="dashboard.php"><i class="bi bi-grid-1x2 me-2"></i>Dashboard Geral</a></li>

                        <li class="nav-item mt-3 mb-1"><span class="text-uppercase small fw-semibold text-secondary px-3">Operação</span></li>
                        <li class="nav-item"><a class="nav-link text-body" href="gestao-rotas.php"><i class="bi bi-signpost-split me-2"></i>Gestão de Rotas</a></li>
                        <li class="nav-item"><a class="nav-link text-body" href="adicionar-rota.php"><i class="bi bi-plus-square me-2"></i>Adicionar Rota</a></li>
                        <li class="nav-item"><a class="nav-link text-body" href="monitoramento-cargas.php"><i class="bi bi-box-seam me-2"></i>Monitoramento de Cargas</a></li>
                        <li class="nav-item"><a class="nav-link text-body" href="trens.php"><i class="bi bi-train-front me-2"></i>Trens</a></li>
                        <li class="nav-item"><a class="nav-link text-body" href="trens-cadastrados.php"><i class="bi bi-list-ul me-2"></i>Trens Cadastrados</a></li>
                        <li class="nav-item"><a class="nav-link text-body" href="alertas.php"><i class="bi bi-bell me-2"></i>Alertas e Notificações</a></li>

                        <li class="nav-item mt-3 mb-1"><span class="text-uppercase small fw-semibold text-secondary px-3">Sensores</span></li>
                        <li class="nav-item"><a class="nav-link text-body" href="sensores.php"><i class="bi bi-cpu me-2"></i>Gerenciar Sensores</a></li>
                        <li class="nav-item"><a class="nav-link text-body" href="adicionar-sensor.php"><i class="bi bi-plus-circle me-2"></i>Adicionar Sensor</a></li>
                        <li class="nav-item"><a class="nav-link text-body" href="editar-sensor.php"><i class="bi bi-pencil me-2"></i>Editar Sensor</a></li>
                        <li class="nav-item"><a class="nav-link text-body" href="remover-sensor.php"><i class="bi bi-dash-circle me-2"></i>Remover Sensor</a></li>

                        <li class="nav-item mt-3 mb-1"><span class="text-uppercase small fw-semibold text-secondary px-3">Relatórios</span></li>
                        <li class="nav-item"><a class="nav-link text-body" href="relatorios.php"><i class="bi bi-file-earmark-bar-graph me-2"></i>Relatórios</a></li>

                        <li class="nav-item mt-3 mb-1"><span class="text-uppercase small fw-semibold text-secondary px-3">Usuários</span></li>
                        <li class="nav-item"><a class="nav-link text-body" href="usuarios.php"><i class="bi bi-people me-2"></i>Status de Usuários</a></li>
                        <li class="nav-item"><a class="nav-link active bg-primary text-white" href="gerenciamento-usuarios.php"><i class="bi bi-person-gear me-2"></i>Gerenciar Usuários</a></li>
                        <li class="nav-item"><a class="nav-link text-body" href="adicionar-usuario.php"><i class="bi bi-person-plus me-2"></i>Adicionar Usuário</a></li>
                        <li class="nav-item"><a class="nav-link text-body" href="perfil.php"><i class="bi bi-person-circle me-2"></i>Meu Perfil</a></li>

                        <li class="nav-item mt-3 mb-1"><span class="text-uppercase small fw-semibold text-secondary px-3">Cliente</span></li>
                        <li class="nav-item"><a class="nav-link text-body" href="pagamento.php"><i class="bi bi-credit-card me-2"></i>Pagamento</a></li>
                        <li class="nav-item"><a class="nav-link text-body" href="passagens.php"><i class="bi bi-ticket-perforated me-2"></i>Passagens</a></li>
                    </ul>
                </div>
            </div>

            <main class="col-12 col-lg-9 col-xl-10 py-4 px-3 px-md-4">

                <div class="d-flex flex-wrap align-items-center justify-content-between gap-2 mb-4">
                    <div class="d-flex align-items-center gap-2">
                        <a class="btn btn-outline-secondary btn-sm" href="usuarios.php" aria-label="Voltar">
                            <i class="bi bi-arrow-left"></i>
                        </a>
                        <div>
                            <h1 class="h3 mb-0">Usuários</h1>
                            <p class="text-secondary mb-0 small">Gerenciamento de usuários</p>
                        </div>
                    </div>
                </div>

                <?php if (($_GET['sucesso'] ?? '') === 'desativar'): ?>
                    <div class="alert alert-success" role="alert">
                        Usuário desativado com sucesso.
                    </div>
                <?php elseif (($_GET['sucesso'] ?? '') === 'ativar'): ?>
                    <div class="alert alert-success" role="alert">
                        Usuário ativado com sucesso.
                    </div>
                <?php elseif (($_GET['erro'] ?? '') === 'propria_conta'): ?>
                    <div class="alert alert-warning" role="alert">
                        Você não pode desativar sua própria conta.
                    </div>
                <?php elseif (($_GET['erro'] ?? '') === 'solicitacao'): ?>
                    <div class="alert alert-danger" role="alert">
                        Solicitação inválida.
                    </div>
                <?php elseif (($_GET['erro'] ?? '') === 'atualizacao'): ?>
                    <div class="alert alert-danger" role="alert">
                        Não foi possível atualizar o usuário.
                    </div>
                <?php endif; ?>

                <div class="row g-3">

                    <div class="col-12">
                        <div class="card border-0 shadow-sm">
                            <div class="card-body">
                                <form method="GET" class="row g-2 align-items-end">

                                    <div class="col-12 col-md-5">
                                        <label for="buscarUsuario" class="form-label">Buscar usuário</label>
                                        <input
                                            type="search"
                                            class="form-control"
                                            id="buscarUsuario"
                                            name="busca"
                                            placeholder="Digite o login do usuário"
                                            value="<?= htmlspecialchars($busca, ENT_QUOTES, 'UTF-8') ?>"
                                        >
                                    </div>

                                    <div class="col-12 col-md-3">
                                        <label for="filtroStatus" class="form-label">Filtrar por status</label>
                                        <select class="form-select" id="filtroStatus" name="status">
                                            <option value="" <?= $status === '' ? 'selected' : '' ?>>Todos</option>
                                            <option value="ativo" <?= $status === 'ativo' ? 'selected' : '' ?>>Ativos</option>
                                            <option value="inativo" <?= $status === 'inativo' ? 'selected' : '' ?>>Inativos</option>
                                        </select>
                                    </div>

                                    <div class="col-6 col-md-2 d-grid">
                                        <button type="submit" class="btn btn-primary">
                                            <i class="bi bi-search me-1"></i>Buscar
                                        </button>
                                    </div>

                                    <div class="col-6 col-md-2 d-grid">
                                        <a href="gerenciamento-usuarios.php" class="btn btn-outline-secondary">
                                            Limpar
                                        </a>
                                    </div>

                                    <div class="col-12">
                                        <a href="adicionar-usuario.php" class="btn btn-outline-primary">
                                            <i class="bi bi-person-plus me-1"></i>Adicionar usuário
                                        </a>
                                    </div>

                                </form>
                            </div>
                        </div>
                    </div>

                    <div class="col-12">
                        <div class="card border-0 shadow-sm">
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table class="table table-hover align-middle mb-0">

                                        <thead class="table-light">
                                            <tr>
                                                <th scope="col">Usuário</th>
                                                <th scope="col">Cargo</th>
                                                <th scope="col">Status</th>
                                                <th scope="col">Telefone</th>
                                                <th scope="col">Cadastro</th>
                                                <th scope="col">Ações</th>
                                            </tr>
                                        </thead>

                                        <tbody>
                                            <?php if ($resultado->num_rows > 0): ?>

                                                <?php while ($usuario = $resultado->fetch_assoc()): ?>

                                                    <tr>
                                                        <td>
                                                            <div class="d-flex align-items-center gap-2">
                                                                <span class="d-inline-flex align-items-center justify-content-center bg-body-secondary text-secondary rounded-circle flex-shrink-0 p-2">
                                                                    <i class="bi bi-person"></i>
                                                                </span>
                                                                <div>
                                                                    <div class="fw-semibold">
                                                                        <?= htmlspecialchars($usuario['login'], ENT_QUOTES, 'UTF-8') ?>
                                                                    </div>
                                                                    <small class="text-secondary">
                                                                        ID: <?= (int) $usuario['id_usuario'] ?>
                                                                    </small>
                                                                </div>
                                                            </div>
                                                        </td>

                                                        <td>
                                                            <span class="badge text-bg-light border">
                                                                <?= htmlspecialchars(ucfirst($usuario['papel']), ENT_QUOTES, 'UTF-8') ?>
                                                            </span>
                                                        </td>

                                                        <td>
                                                            <?php if ((int) $usuario['ativo'] === 1): ?>
                                                                <span class="badge text-bg-success">Ativo</span>
                                                            <?php else: ?>
                                                                <span class="badge text-bg-secondary">Inativo</span>
                                                            <?php endif; ?>
                                                        </td>

                                                        <td>
                                                            <?= htmlspecialchars($usuario['telefone'] ?? 'Não informado', ENT_QUOTES, 'UTF-8') ?>
                                                        </td>

                                                        <td>
                                                            <?php
                                                            if (!empty($usuario['criado_em'])) {
                                                                echo htmlspecialchars(
                                                                    date('d/m/Y', strtotime($usuario['criado_em'])),
                                                                    ENT_QUOTES,
                                                                    'UTF-8'
                                                                );
                                                            } else {
                                                                echo 'Não informado';
                                                            }
                                                            ?>
                                                        </td>

                                                        <td>
                                                            <?php if ((int) $usuario['ativo'] === 1): ?>

                                                                <?php if ((int) $usuario['id_usuario'] === (int) ($_SESSION['id_usuario'] ?? 0)): ?>

                                                                    <button
                                                                        type="button"
                                                                        class="btn btn-sm btn-outline-secondary"
                                                                        disabled
                                                                        title="Você não pode desativar sua própria conta"
                                                                    >
                                                                        <i class="bi bi-shield-lock me-1"></i>Sua conta
                                                                    </button>

                                                                <?php else: ?>

                                                                    <form method="POST"
                                                                        onsubmit="return confirm('Tem certeza de que deseja desativar este usuário?');">

                                                                        <input type="hidden" name="csrf_token"
                                                                            value="<?= htmlspecialchars($_SESSION['csrf_usuarios'], ENT_QUOTES, 'UTF-8') ?>">

                                                                        <input type="hidden" name="id_usuario"
                                                                            value="<?= (int) $usuario['id_usuario'] ?>">

                                                                        <input type="hidden" name="acao" value="desativar">

                                                                        <button type="submit" class="btn btn-sm btn-outline-danger">
                                                                            <i class="bi bi-person-dash me-1"></i>Desativar
                                                                        </button>
                                                                    </form>

                                                                <?php endif; ?>

                                                            <?php else: ?>

                                                                <form method="POST"
                                                                    onsubmit="return confirm('Tem certeza de que deseja reativar este usuário?');">

                                                                    <input type="hidden" name="csrf_token"
                                                                        value="<?= htmlspecialchars($_SESSION['csrf_usuarios'], ENT_QUOTES, 'UTF-8') ?>">

                                                                    <input type="hidden" name="id_usuario"
                                                                        value="<?= (int) $usuario['id_usuario'] ?>">

                                                                    <input type="hidden" name="acao" value="ativar">

                                                                    <button type="submit" class="btn btn-sm btn-outline-success">
                                                                        <i class="bi bi-person-check me-1"></i>Ativar
                                                                    </button>
                                                                </form>

                                                            <?php endif; ?>
                                                        </td>
                                                    </tr>

                                                <?php endwhile; ?>

                                            <?php else: ?>

                                                <tr>
                                                    <td colspan="6" class="text-center text-secondary py-4">
                                                        Nenhum usuário encontrado.
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