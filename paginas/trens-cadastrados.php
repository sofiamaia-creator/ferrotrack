<?php

require_once "../assets/php/proteger.php";
require_once "../assets/php/permissao.php";
require_once "../assets/php/cabecalho.php";
require_once "../conexao.php";

if (!temPapel(['gestor'])) {
    echo "Acesso negado.";
    exit;
}

$id_trem = (int)($_POST['id_trem'] ?? $_GET['id_trem'] ?? 0);
$prefixo = trim($_POST['prefixo'] ?? '');
$modelo = trim($_POST['modelo'] ?? '');
$ano = $_POST['ano'] ?? '';
$status_trem = $_POST['status'] ?? '';
$capacidade = $_POST['capacidade'] ?? '';
$ultima_inspecao = $_POST['ultima_inspecao'] ?? '';

$erros = [];
$mensagem = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['excluir_id'])) {
    $excluir_id = (int) $_POST['excluir_id'];

    if ($excluir_id > 0) {
        $stmt = $conexao->prepare('DELETE FROM trens WHERE id_trem = ?');
        $stmt->bind_param('i', $excluir_id);
        $stmt->execute();
        $mensagem = 'Trem excluído com sucesso.';
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $ano = (int)$ano;
    $capacidade = (float)$capacidade;

    if ($prefixo === '') {
        $erros[] = 'O prefixo é obrigatório.';
    }

    if ($modelo === '') {
        $erros[] = 'O modelo é obrigatório.';
    }

    if ($ano < 1900 || $ano > 2100) {
        $erros[] = 'O ano deve estar entre 1900 e 2100.';
    }

    if ($capacidade <= 0) {
        $erros[] = 'A capacidade deve ser maior que zero.';
    }

    $status_validos = ['Ativo', 'Em manutenção', 'Inativo'];

    if (!in_array($status_trem, $status_validos, true)) {
        $erros[] = 'Status inválido.';
    }

    if ($ultima_inspecao !== '') {
        $data_valida = DateTime::createFromFormat('Y-m-d', $ultima_inspecao);

        if (!$data_valida || $data_valida->format('Y-m-d') !== $ultima_inspecao) {
            $erros[] = 'A data da última inspeção é inválida.';
        }
    } else {
        $ultima_inspecao = null;
    }

    $sql = 'SELECT id_trem FROM trens WHERE prefixo = ?';

    if ($id_trem > 0) {
        $sql .= ' AND id_trem != ?';
    }

    $stmt = $conexao->prepare($sql);

    if ($id_trem > 0) {
        $stmt->bind_param('si', $prefixo, $id_trem);
    } else {
        $stmt->bind_param('s', $prefixo);
    }

    $stmt->execute();

    $resultado_prefixo = $stmt->get_result();

    if ($resultado_prefixo->num_rows > 0) {
        $erros[] = 'Este prefixo já está cadastrado.';
    }

    if (count($erros) === 0) {
        if ($id_trem > 0) {
            $sql = 'UPDATE trens
                    SET prefixo = ?, modelo = ?, ano = ?, status = ?,
                        capacidade = ?, ultima_inspecao = ?
                    WHERE id_trem = ?';

            $stmt = $conexao->prepare($sql);

            $stmt->bind_param(
                'ssisdsi',
                $prefixo,
                $modelo,
                $ano,
                $status_trem,
                $capacidade,
                $ultima_inspecao,
                $id_trem
            );

            if ($stmt->execute()) {
                header('Location: trens-cadastrados.php?sucesso=editado');
                exit;
            }

            $erros[] = 'Não foi possível editar o trem.';
        } else {
            $sql = 'INSERT INTO trens
                    (prefixo, modelo, ano, status, capacidade, ultima_inspecao)
                    VALUES (?, ?, ?, ?, ?, ?)';

            $stmt = $conexao->prepare($sql);

            $stmt->bind_param(
                'ssisds',
                $prefixo,
                $modelo,
                $ano,
                $status_trem,
                $capacidade,
                $ultima_inspecao
            );

            if ($stmt->execute()) {
                $id_trem = $conexao->insert_id;
                header('Location: trens-cadastrados.php?sucesso=cadastrado');
                exit;
            }

            $erros[] = 'Não foi possível cadastrar o trem.';
        }
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'GET' && $id_trem > 0) {
    $stmt = $conexao->prepare('SELECT * FROM trens WHERE id_trem = ?');
    $stmt->bind_param('i', $id_trem);
    $stmt->execute();

    $registro = $stmt->get_result()->fetch_assoc();

    if ($registro) {
        $prefixo = $registro['prefixo'];
        $modelo = $registro['modelo'];
        $ano = $registro['ano'];
        $status_trem = $registro['status'];
        $capacidade = $registro['capacidade'];
        $ultima_inspecao = $registro['ultima_inspecao'];
    } else {
        $id_trem = 0;
        $erros[] = 'Trem não encontrado.';
    }
}

$busca = $_GET['busca'] ?? '';
$status = $_GET['status'] ?? '';
$sucesso = $_GET['sucesso'] ?? '';

$condicoes = [];
$valores = [];
$tipos = '';

if ($busca !== '') {
    $condicoes[] = '(prefixo LIKE ? OR modelo LIKE ?)';
    $termo = '%' . $busca . '%';
    $valores[] = $termo;
    $valores[] = $termo;
    $tipos .= 'ss';
}

if ($status !== '') {
    $condicoes[] = 'status = ?';
    $valores[] = $status;
    $tipos .= 's';
}

$sql = 'SELECT * FROM trens';

if (count($condicoes) > 0) {
    $sql .= ' WHERE ' . implode(' AND ', $condicoes);
}

$sql .= ' ORDER BY prefixo';

$stmt = $conexao->prepare($sql);

if (!$stmt) {
    die("Erro ao preparar consulta: " . $conexao->error);
}

if (!empty($valores)) {
    $stmt->bind_param($tipos, ...$valores);
}

$stmt->execute();

$resultado = $stmt->get_result();

$trens = [];

while ($trem = $resultado->fetch_assoc()) {
    $trens[] = $trem;
}

?>

<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>Trens Cadastrados — Ferrovias</title>

    <link
        href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css"
        rel="stylesheet"
    >

    <link
        href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css"
        rel="stylesheet"
    >

    <link
        rel="stylesheet"
        href="../paginas/cabecalho.css"
    >

    <style>
        .tabela-desktop {
            display: block;
        }

        .lista-mobile {
            display: none;
        }

        @media (max-width: 767.98px) {
            .tabela-desktop {
                display: none;
            }

            .lista-mobile {
                display: block;
            }
        }
    </style>
</head>

<body class="bg-body-tertiary">

<?php gerarCabecalho(); ?>

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
            href="painel-gestor.php"
        >
            <img
                src="../assets/img/logo.png"
                alt="Ferrovias"
                width="32"
                height="32"
                class="object-fit-contain"
            >

            <span class="fw-semibold">
                Ferrovias
            </span>
        </a>

        <div class="d-flex align-items-center gap-1">

            <button
                class="btn btn-sm btn-outline-secondary border-0"
                type="button"
                title="Acessibilidade"
                aria-label="Acessibilidade"
            >
                <i class="bi bi-universal-access fs-5"></i>
            </button>

            <button
                class="btn btn-sm btn-outline-secondary border-0"
                type="button"
                title="Configurações"
                aria-label="Configurações"
            >
                <i class="bi bi-gear fs-5"></i>
            </button>

            <button
                class="btn btn-sm btn-outline-secondary border-0"
                type="button"
                title="Modo escuro"
                aria-label="Modo escuro"
            >
                <i class="bi bi-moon-fill fs-5"></i>
            </button>

            <button
                class="btn btn-sm btn-outline-secondary border-0 position-relative"
                type="button"
                title="Notificações"
                aria-label="Notificações"
            >
                <i class="bi bi-bell fs-5"></i>

                <span
                    class="position-absolute top-0 start-100 translate-middle p-1 bg-danger border border-light rounded-circle"
                ></span>
            </button>

            <div class="vr mx-2 d-none d-sm-block"></div>

            <a
                class="btn btn-sm btn-outline-primary d-none d-sm-inline-flex align-items-center gap-1"
                href="perfil.php"
            >
                <i class="bi bi-person-circle"></i>

                <span class="d-none d-md-inline">
                    Minha conta
                </span>
            </a>

        </div>

    </div>

</nav>

<div class="container-fluid">

    <div class="row">

        <aside
            class="col-lg-3 col-xl-2 d-none d-lg-block bg-white border-end vh-100 position-sticky top-0 p-0"
        >

            <nav class="p-3 overflow-auto h-100">

                <ul class="nav nav-pills flex-column">

                    <li class="nav-item mt-3 mb-1">
                        <span class="text-uppercase small fw-semibold text-secondary px-3">
                            Painéis
                        </span>
                    </li>

                    <li class="nav-item">
                        <a
                            class="nav-link d-flex align-items-center gap-2 rounded px-3 text-body"
                            href="painel-gestor.php"
                        >
                            <i class="bi bi-speedometer2"></i>
                            <span>Painel do Gestor</span>
                        </a>
                    </li>

                    <li class="nav-item">
                        <a
                            class="nav-link d-flex align-items-center gap-2 rounded px-3 text-body"
                            href="painel-maquinista.php"
                        >
                            <i class="bi bi-person-badge"></i>
                            <span>Painel do Maquinista</span>
                        </a>
                    </li>

                    <li class="nav-item">
                        <a
                            class="nav-link d-flex align-items-center gap-2 rounded px-3 text-body"
                            href="painel-cliente.php"
                        >
                            <i class="bi bi-person"></i>
                            <span>Painel do Cliente</span>
                        </a>
                    </li>

                    <li class="nav-item">
                        <a
                            class="nav-link d-flex align-items-center gap-2 rounded px-3 text-body"
                            href="dashboard.php"
                        >
                            <i class="bi bi-grid-1x2"></i>
                            <span>Dashboard Geral</span>
                        </a>
                    </li>

                    <li class="nav-item mt-3 mb-1">
                        <span class="text-uppercase small fw-semibold text-secondary px-3">
                            Operação
                        </span>
                    </li>

                    <li class="nav-item">
                        <a
                            class="nav-link d-flex align-items-center gap-2 rounded px-3 text-body"
                            href="gestao-rotas.php"
                        >
                            <i class="bi bi-signpost-split"></i>
                            <span>Gestão de Rotas</span>
                        </a>
                    </li>

                    <li class="nav-item">
                        <a
                            class="nav-link d-flex align-items-center gap-2 rounded px-3 text-body"
                            href="adicionar-rota.php"
                        >
                            <i class="bi bi-plus-square"></i>
                            <span>Adicionar Rota</span>
                        </a>
                    </li>

                    <li class="nav-item">
                        <a
                            class="nav-link d-flex align-items-center gap-2 rounded px-3 text-body"
                            href="monitoramento-cargas.php"
                        >
                            <i class="bi bi-box-seam"></i>
                            <span>Monitoramento de Cargas</span>
                        </a>
                    </li>

                    <li class="nav-item">
                        <a
                            class="nav-link d-flex align-items-center gap-2 rounded px-3 text-body"
                            href="trens.php"
                        >
                            <i class="bi bi-train-front"></i>
                            <span>Trens</span>
                        </a>
                    </li>

                    <li class="nav-item">
                        <a
                            class="nav-link d-flex align-items-center gap-2 rounded px-3 active bg-primary text-white"
                            href="trens-cadastrados.php"
                            aria-current="page"
                        >
                            <i class="bi bi-list-ul"></i>
                            <span>Trens Cadastrados</span>
                        </a>
                    </li>

                    <li class="nav-item">
                        <a
                            class="nav-link d-flex align-items-center gap-2 rounded px-3 text-body"
                            href="alertas.php"
                        >
                            <i class="bi bi-bell"></i>
                            <span>Alertas e Notificações</span>
                        </a>
                    </li>

                    <li class="nav-item mt-3 mb-1">
                        <span class="text-uppercase small fw-semibold text-secondary px-3">
                            Sensores
                        </span>
                    </li>

                    <li class="nav-item">
                        <a
                            class="nav-link d-flex align-items-center gap-2 rounded px-3 text-body"
                            href="sensores.php"
                        >
                            <i class="bi bi-cpu"></i>
                            <span>Gerenciar Sensores</span>
                        </a>
                    </li>

                    <li class="nav-item">
                        <a
                            class="nav-link d-flex align-items-center gap-2 rounded px-3 text-body"
                            href="adicionar-sensor.php"
                        >
                            <i class="bi bi-plus-circle"></i>
                            <span>Adicionar Sensor</span>
                        </a>
                    </li>

                    <li class="nav-item">
                        <a
                            class="nav-link d-flex align-items-center gap-2 rounded px-3 text-body"
                            href="editar-sensor.php"
                        >
                            <i class="bi bi-pencil"></i>
                            <span>Editar Sensor</span>
                        </a>
                    </li>

                    <li class="nav-item">
                        <a
                            class="nav-link d-flex align-items-center gap-2 rounded px-3 text-body"
                            href="remover-sensor.php"
                        >
                            <i class="bi bi-dash-circle"></i>
                            <span>Remover Sensor</span>
                        </a>
                    </li>

                    <li class="nav-item mt-3 mb-1">
                        <span class="text-uppercase small fw-semibold text-secondary px-3">
                            Relatórios
                        </span>
                    </li>

                    <li class="nav-item">
                        <a
                            class="nav-link d-flex align-items-center gap-2 rounded px-3 text-body"
                            href="relatorios.php"
                        >
                            <i class="bi bi-file-earmark-bar-graph"></i>
                            <span>Relatórios</span>
                        </a>
                    </li>

                    <li class="nav-item mt-3 mb-1">
                        <span class="text-uppercase small fw-semibold text-secondary px-3">
                            Usuários
                        </span>
                    </li>

                    <li class="nav-item">
                        <a
                            class="nav-link d-flex align-items-center gap-2 rounded px-3 text-body"
                            href="usuarios.php"
                        >
                            <i class="bi bi-people"></i>
                            <span>Status de Usuários</span>
                        </a>
                    </li>

                    <li class="nav-item">
                        <a
                            class="nav-link d-flex align-items-center gap-2 rounded px-3 text-body"
                            href="gerenciamento-usuarios.php"
                        >
                            <i class="bi bi-person-gear"></i>
                            <span>Gerenciar Usuários</span>
                        </a>
                    </li>

                    <li class="nav-item">
                        <a
                            class="nav-link d-flex align-items-center gap-2 rounded px-3 text-body"
                            href="adicionar-usuario.php"
                        >
                            <i class="bi bi-person-plus"></i>
                            <span>Adicionar Usuário</span>
                        </a>
                    </li>

                    <li class="nav-item">
                        <a
                            class="nav-link d-flex align-items-center gap-2 rounded px-3 text-body"
                            href="perfil.php"
                        >
                            <i class="bi bi-person-circle"></i>
                            <span>Meu Perfil</span>
                        </a>
                    </li>

                    <li class="nav-item mt-3 mb-1">
                        <span class="text-uppercase small fw-semibold text-secondary px-3">
                            Cliente
                        </span>
                    </li>

                    <li class="nav-item">
                        <a
                            class="nav-link d-flex align-items-center gap-2 rounded px-3 text-body"
                            href="pagamento.php"
                        >
                            <i class="bi bi-credit-card"></i>
                            <span>Pagamento</span>
                        </a>
                    </li>

                    <li class="nav-item">
                        <a
                            class="nav-link d-flex align-items-center gap-2 rounded px-3 text-body"
                            href="passagens.php"
                        >
                            <i class="bi bi-ticket-perforated"></i>
                            <span>Passagens</span>
                        </a>
                    </li>

                </ul>

            </nav>

        </aside>

        <div
            class="offcanvas offcanvas-start d-lg-none"
            tabindex="-1"
            id="menuLateral"
            aria-labelledby="tituloMenu"
        >

            <div class="offcanvas-header border-bottom">

                <h5
                    class="offcanvas-title"
                    id="tituloMenu"
                >
                    Menu
                </h5>

                <button
                    type="button"
                    class="btn-close"
                    data-bs-dismiss="offcanvas"
                    aria-label="Fechar"
                ></button>

            </div>

            <div class="offcanvas-body">

                <ul class="nav nav-pills flex-column">

                    <li class="nav-item mt-3 mb-1">
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
                        <a class="nav-link text-body" href="adicionar-rota.php">
                            <i class="bi bi-plus-square me-2"></i>
                            Adicionar Rota
                        </a>
                    </li>

                    <li class="nav-item">
                        <a class="nav-link text-body" href="monitoramento-cargas.php">
                            <i class="bi bi-box-seam me-2"></i>
                            Monitoramento de Cargas
                        </a>
                    </li>

                    <li class="nav-item">
                        <a class="nav-link text-body" href="trens.php">
                            <i class="bi bi-train-front me-2"></i>
                            Trens
                        </a>
                    </li>

                    <li class="nav-item">
                        <a class="nav-link active bg-primary text-white" href="trens-cadastrados.php">
                            <i class="bi bi-list-ul me-2"></i>
                            Trens Cadastrados
                        </a>
                    </li>

                    <li class="nav-item">
                        <a class="nav-link text-body" href="alertas.php">
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
                        <a class="nav-link text-body" href="adicionar-sensor.php">
                            <i class="bi bi-plus-circle me-2"></i>
                            Adicionar Sensor
                        </a>
                    </li>

                    <li class="nav-item">
                        <a class="nav-link text-body" href="editar-sensor.php">
                            <i class="bi bi-pencil me-2"></i>
                            Editar Sensor
                        </a>
                    </li>

                    <li class="nav-item">
                        <a class="nav-link text-body" href="remover-sensor.php">
                            <i class="bi bi-dash-circle me-2"></i>
                            Remover Sensor
                        </a>
                    </li>

                    <li class="nav-item mt-3 mb-1">
                        <span class="text-uppercase small fw-semibold text-secondary px-3">
                            Relatórios
                        </span>
                    </li>

                    <li class="nav-item">
                        <a class="nav-link text-body" href="relatorios.php">
                            <i class="bi bi-file-earmark-bar-graph me-2"></i>
                            Relatórios
                        </a>
                    </li>

                    <li class="nav-item mt-3 mb-1">
                        <span class="text-uppercase small fw-semibold text-secondary px-3">
                            Usuários
                        </span>
                    </li>

                    <li class="nav-item">
                        <a class="nav-link text-body" href="usuarios.php">
                            <i class="bi bi-people me-2"></i>
                            Status de Usuários
                        </a>
                    </li>

                    <li class="nav-item">
                        <a class="nav-link text-body" href="gerenciamento-usuarios.php">
                            <i class="bi bi-person-gear me-2"></i>
                            Gerenciar Usuários
                        </a>
                    </li>

                    <li class="nav-item">
                        <a class="nav-link text-body" href="adicionar-usuario.php">
                            <i class="bi bi-person-plus me-2"></i>
                            Adicionar Usuário
                        </a>
                    </li>

                    <li class="nav-item">
                        <a class="nav-link text-body" href="perfil.php">
                            <i class="bi bi-person-circle me-2"></i>
                            Meu Perfil
                        </a>
                    </li>

                    <li class="nav-item mt-3 mb-1">
                        <span class="text-uppercase small fw-semibold text-secondary px-3">
                            Cliente
                        </span>
                    </li>

                    <li class="nav-item">
                        <a class="nav-link text-body" href="pagamento.php">
                            <i class="bi bi-credit-card me-2"></i>
                            Pagamento
                        </a>
                    </li>

                    <li class="nav-item">
                        <a class="nav-link text-body" href="passagens.php">
                            <i class="bi bi-ticket-perforated me-2"></i>
                            Passagens
                        </a>
                    </li>

                </ul>

            </div>

        </div>

        <main class="col-12 col-lg-9 col-xl-10 py-4 px-3 px-md-4">

            <?php if ($sucesso === 'cadastrado'): ?>
                <div class="alert alert-success">
                    Trem cadastrado com sucesso.
                </div>
            <?php elseif ($sucesso === 'editado'): ?>
                <div class="alert alert-success">
                    Trem editado com sucesso.
                </div>
            <?php endif; ?>

            <?php if (count($erros) > 0): ?>
                <div class="alert alert-danger">
                    <ul class="mb-0">
                        <?php foreach ($erros as $erro): ?>
                            <li><?= htmlspecialchars($erro) ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            <?php endif; ?>

            <div class="card border-0 shadow-sm mb-3">
                <div class="card-header bg-white border-0 pt-3">
                    <h2 class="h5 mb-0">
                        <?= $id_trem > 0 ? 'Editar trem' : 'Cadastrar trem' ?>
                    </h2>
                </div>

                <div class="card-body">
                    <form method="POST" class="row g-3">
                        <input type="hidden" name="id_trem" value="<?= htmlspecialchars($id_trem) ?>">

                        <div class="col-12 col-md-4">
                            <label for="prefixo" class="form-label">Prefixo</label>
                            <input
                                type="text"
                                class="form-control"
                                id="prefixo"
                                name="prefixo"
                                maxlength="10"
                                placeholder="TR-204"
                                value="<?= htmlspecialchars($prefixo) ?>"
                                required
                            >
                        </div>

                        <div class="col-12 col-md-8">
                            <label for="modelo" class="form-label">Modelo</label>
                            <input
                                type="text"
                                class="form-control"
                                id="modelo"
                                name="modelo"
                                maxlength="100"
                                value="<?= htmlspecialchars($modelo) ?>"
                                required
                            >
                        </div>

                        <div class="col-12 col-md-4">
                            <label for="ano" class="form-label">Ano</label>
                            <input
                                type="number"
                                class="form-control"
                                id="ano"
                                name="ano"
                                min="1900"
                                max="2100"
                                value="<?= htmlspecialchars($ano) ?>"
                                required
                            >
                        </div>

                        <div class="col-12 col-md-4">
                            <label for="status_trem" class="form-label">Status</label>
                            <select class="form-select" id="status_trem" name="status" required>
                                <option value="">Selecione</option>
                                <option value="Ativo" <?= $status_trem === 'Ativo' ? 'selected' : '' ?>>Ativo</option>
                                <option value="Em manutenção" <?= $status_trem === 'Em manutenção' ? 'selected' : '' ?>>Em manutenção</option>
                                <option value="Inativo" <?= $status_trem === 'Inativo' ? 'selected' : '' ?>>Inativo</option>
                            </select>
                        </div>

                        <div class="col-12 col-md-4">
                            <label for="capacidade" class="form-label">Capacidade (toneladas)</label>
                            <input
                                type="number"
                                class="form-control"
                                id="capacidade"
                                name="capacidade"
                                min="0.01"
                                step="0.01"
                                value="<?= htmlspecialchars($capacidade) ?>"
                                required
                            >
                        </div>

                        <div class="col-12 col-md-4">
                            <label for="ultima_inspecao" class="form-label">Última inspeção</label>
                            <input
                                type="date"
                                class="form-control"
                                id="ultima_inspecao"
                                name="ultima_inspecao"
                                value="<?= htmlspecialchars($ultima_inspecao ?? '') ?>"
                            >
                        </div>

                        <div class="col-12">
                            <button type="submit" class="btn btn-primary">
                                <?= $id_trem > 0 ? 'Salvar alterações' : 'Cadastrar trem' ?>
                            </button>

                            <?php if ($id_trem > 0): ?>
                                <a href="trens-cadastrados.php" class="btn btn-outline-secondary">
                                    Cancelar
                                </a>
                            <?php endif; ?>
                        </div>
                    </form>
                </div>
            </div>

            <div class="d-flex flex-wrap align-items-center justify-content-between gap-2 mb-4">

                <div class="d-flex align-items-center gap-2">

                    <a
                        class="btn btn-outline-secondary btn-sm"
                        href="trens.php"
                        aria-label="Voltar"
                    >
                        <i class="bi bi-arrow-left"></i>
                    </a>

                    <div>

                        <h1 class="h3 mb-0">
                            Trens Cadastrados
                        </h1>

                        <p class="text-secondary mb-0 small">
                            Frota registrada no sistema
                        </p>

                    </div>

                </div>

            </div>

            <div class="card border-0 shadow-sm mb-3">

                <div class="card-body">

                    <form
                        method="GET"
                        class="row g-3"
                    >

                        <div class="col-12 col-md-6">

                            <label
                                for="busca"
                                class="form-label"
                            >
                                Buscar trem
                            </label>

                            <input
                                type="text"
                                class="form-control"
                                id="busca"
                                name="busca"
                                placeholder="Digite o prefixo ou modelo"
                                value="<?= htmlspecialchars($busca) ?>"
                            >

                        </div>

                        <div class="col-12 col-md-4">

                            <label
                                for="status"
                                class="form-label"
                            >
                                Status
                            </label>

                            <select
                                class="form-select"
                                id="status"
                                name="status"
                            >

                                <option value="">
                                    Todos os status
                                </option>

                                <option
                                    value="Ativo"
                                    <?= $status === 'Ativo' ? 'selected' : '' ?>
                                >
                                    Ativo
                                </option>

                                <option
                                    value="Em manutenção"
                                    <?= $status === 'Em manutenção' ? 'selected' : '' ?>
                                >
                                    Em manutenção
                                </option>

                                <option
                                    value="Inativo"
                                    <?= $status === 'Inativo' ? 'selected' : '' ?>
                                >
                                    Inativo
                                </option>

                            </select>

                        </div>

                        <div class="col-12 col-md-2 d-flex align-items-end">

                            <button
                                type="submit"
                                class="btn btn-primary w-100"
                            >

                                <i class="bi bi-search me-1"></i>

                                Buscar

                            </button>

                        </div>

                    </form>

                </div>

            </div>

            <div class="card border-0 shadow-sm">

                <div class="card-header bg-white border-0 pt-3">

                    <h2 class="h5 mb-0">
                        Frota registrada
                    </h2>

                </div>

                <div class="card-body">

                    <div class="tabela-desktop">

                        <div class="table-responsive">

                            <table class="table table-hover align-middle mb-0">

                                <thead class="table-light">

                                    <tr>

                                        <th>Prefixo</th>
                                        <th>Modelo</th>
                                        <th>Ano</th>
                                        <th>Status</th>
                                        <th>Capacidade</th>
                                        <th>Última inspeção</th>
                                        <th>Ações</th>
                                        <th>Ações</th>

                                    </tr>

                                </thead>

                                <tbody>

                                    <?php if (count($trens) > 0): ?>

                                        <?php foreach ($trens as $trem): ?>

                                            <tr>

                                                <td class="fw-semibold">
                                                    <?= htmlspecialchars($trem['prefixo']) ?>
                                                </td>

                                                <td>
                                                    <?= htmlspecialchars($trem['modelo']) ?>
                                                </td>

                                                <td>
                                                    <?= htmlspecialchars($trem['ano']) ?>
                                                </td>

                                                <td>

                                                    <?php

                                                    if ($trem['status'] === 'Ativo') {
                                                        $classeStatus = 'text-bg-success';
                                                    } elseif ($trem['status'] === 'Em manutenção') {
                                                        $classeStatus = 'text-bg-warning';
                                                    } else {
                                                        $classeStatus = 'text-bg-danger';
                                                    }

                                                    ?>

                                                    <span class="badge <?= $classeStatus ?>">
                                                        <?= htmlspecialchars($trem['status']) ?>
                                                    </span>

                                                </td>

                                                <td>
                                                    <?= htmlspecialchars($trem['capacidade']) ?> t
                                                </td>

                                                <td>

                                                    <?php if (!empty($trem['ultima_inspecao'])): ?>

                                                        <?= htmlspecialchars($trem['ultima_inspecao']) ?>

                                                    <?php else: ?>

                                                        <span class="text-secondary">
                                                            Não realizada
                                                        </span>

                                                    <?php endif; ?>

                                                </td>

                                                <td>
                                                    <a
                                                        href="trens-cadastrados.php?id_trem=<?= (int)$trem['id_trem'] ?>"
                                                        class="btn btn-sm btn-outline-primary"
                                                    >
                                                        <i class="bi bi-pencil"></i>
                                                        Editar
                                                    </a>
                                                </td>

                                                <td>
                                                    <a href="trens-cadastrados.php?id_trem=<?= $trem['id_trem'] ?>" class="btn btn-sm btn-outline-primary">
                                                        Editar
                                                    </a>

                                                    <form method="POST" class="d-inline" onsubmit="return confirm('Confirma a exclusão?');">
                                                        <input type="hidden" name="excluir_id" value="<?= $trem['id_trem'] ?>">
                                                        <button type="submit" class="btn btn-sm btn-outline-danger">
                                                            Excluir
                                                        </button>
                                                    </form>
                                                </td>

                                            </tr>

                                        <?php endforeach; ?>

                                    <?php else: ?>

                                        <tr>

                                            <td
                                                colspan="7"
                                                class="text-center text-secondary py-4"
                                            >
                                                Nenhum trem encontrado.
                                            </td>

                                        </tr>

                                    <?php endif; ?>

                                </tbody>

                            </table>

                        </div>

                    </div>

                    <div class="lista-mobile">

                        <?php if (count($trens) > 0): ?>

                            <div class="d-flex flex-column gap-3">

                                <?php foreach ($trens as $trem): ?>

                                    <?php

                                    if ($trem['status'] === 'Ativo') {
                                        $classeStatus = 'text-bg-success';
                                    } elseif ($trem['status'] === 'Em manutenção') {
                                        $classeStatus = 'text-bg-warning';
                                    } else {
                                        $classeStatus = 'text-bg-danger';
                                    }

                                    ?>

                                    <div class="card border">

                                        <div class="card-body">

                                            <div class="d-flex justify-content-between align-items-start gap-2 mb-3">

                                                <div>

                                                    <h3 class="h5 mb-1">
                                                        <?= htmlspecialchars($trem['prefixo']) ?>
                                                    </h3>

                                                    <p class="text-secondary mb-0">
                                                        <?= htmlspecialchars($trem['modelo']) ?>
                                                    </p>

                                                </div>

                                                <span class="badge <?= $classeStatus ?>">
                                                    <?= htmlspecialchars($trem['status']) ?>
                                                </span>

                                            </div>

                                            <div class="row g-2 small">

                                                <div class="col-6">

                                                    <span class="text-secondary d-block">
                                                        Ano
                                                    </span>

                                                    <strong>
                                                        <?= htmlspecialchars($trem['ano']) ?>
                                                    </strong>

                                                </div>

                                                <div class="col-6">

                                                    <span class="text-secondary d-block">
                                                        Capacidade
                                                    </span>

                                                    <strong>
                                                        <?= htmlspecialchars($trem['capacidade']) ?> t
                                                    </strong>

                                                </div>

                                                <div class="col-12">

                                                    <span class="text-secondary d-block">
                                                        Última inspeção
                                                    </span>

                                                    <strong>

                                                        <?php if (!empty($trem['ultima_inspecao'])): ?>

                                                            <?= htmlspecialchars($trem['ultima_inspecao']) ?>

                                                        <?php else: ?>

                                                            Não realizada

                                                        <?php endif; ?>

                                                    </strong>

                                                </div>

                                                <div class="mt-3">
                                                    <a
                                                        href="trens-cadastrados.php?id_trem=<?= (int)$trem['id_trem'] ?>"
                                                        class="btn btn-sm btn-outline-primary"
                                                    >
                                                        <i class="bi bi-pencil"></i>
                                                        Editar
                                                    </a>
                                                </div>

                                            </div>

                                            <div class="mt-3 d-flex gap-2">
                                                <a href="trens-cadastrados.php?id_trem=<?= $trem['id_trem'] ?>" class="btn btn-sm btn-outline-primary">
                                                    Editar
                                                </a>

                                                <form method="POST" onsubmit="return confirm('Confirma a exclusão?');">
                                                    <input type="hidden" name="excluir_id" value="<?= $trem['id_trem'] ?>">
                                                    <button type="submit" class="btn btn-sm btn-outline-danger">
                                                        Excluir
                                                    </button>
                                                </form>
                                            </div>

                                        </div>

                                    </div>

                                <?php endforeach; ?>

                            </div>

                        <?php else: ?>

                            <div class="text-center text-secondary py-4">
                                Nenhum trem encontrado.
                            </div>

                        <?php endif; ?>

                    </div>

                </div>

            </div>

        </main>

    </div>

</div>

<script
    src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"
></script>

</body>

</html>

