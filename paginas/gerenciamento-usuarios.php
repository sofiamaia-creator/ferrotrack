<?php

require_once "../assets/php/proteger.php";
require_once "../assets/php/permissao.php";
require_once "../assets/php/conexao.php";
require_once "../assets/php/cabecalho.php";
require_once "../assets/php/menu.php";

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

    if (!$idUsuario) {
        header("Location: gerenciamento-usuarios.php?erro=solicitacao");
        exit;
    }

    if (in_array($acao, ['aprovar', 'recusar'], true)) {
        $novoStatus = ($acao === 'aprovar') ? 'aprovado' : 'recusado';

        $stmt = $conexao->prepare(
            "UPDATE usuarios
             SET status_aprovacao = ?
             WHERE id_usuario = ?
             AND papel IN ('maquinista', 'gestor')
             AND status_aprovacao = 'pendente'"
        );

        if (!$stmt) {
            error_log("Erro ao preparar aprovação: " . $conexao->error);
            header("Location: gerenciamento-usuarios.php?erro=atualizacao");
            exit;
        }

        $stmt->bind_param("si", $novoStatus, $idUsuario);

        if (!$stmt->execute()) {
            error_log("Erro ao atualizar solicitação: " . $stmt->error);
            $stmt->close();

            header("Location: gerenciamento-usuarios.php?erro=atualizacao");
            exit;
        }

        $alterados = $stmt->affected_rows;
        $stmt->close();

        if ($alterados > 0) {
            header("Location: gerenciamento-usuarios.php?sucesso=" . $acao);
        } else {
            header("Location: gerenciamento-usuarios.php?erro=solicitacao");
        }

        exit;
    }

    if (!in_array($acao, ['ativar', 'desativar'], true)) {
        header("Location: gerenciamento-usuarios.php?erro=solicitacao");
        exit;
    }

    if ($idUsuario === $idLogado && $acao === 'desativar') {
        header("Location: gerenciamento-usuarios.php?erro=propria_conta");
        exit;
    }

    $novoStatus = ($acao === 'ativar') ? 1 : 0;

    $stmt = $conexao->prepare(
        "UPDATE usuarios
         SET ativo = ?
         WHERE id_usuario = ?
         AND status_aprovacao = 'aprovado'"
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

$sqlSolicitacoes = "
    SELECT id_usuario, login, papel, telefone, criado_em
    FROM usuarios
    WHERE status_aprovacao = 'pendente'
    AND papel IN ('maquinista', 'gestor')
    ORDER BY criado_em ASC
";

$resultadoSolicitacoes = $conexao->query($sqlSolicitacoes);

if (!$resultadoSolicitacoes) {
    error_log("Erro ao consultar solicitações: " . $conexao->error);
    die("Erro ao consultar as solicitações.");
}

$sql = "
    SELECT id_usuario, login, papel, ativo, telefone, criado_em
    FROM usuarios
    WHERE status_aprovacao = 'aprovado'
";

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
                    <div class="d-flex align-items-center gap-2">
                        <a class="btn btn-outline-secondary btn-sm" href="usuarios.php" aria-label="Voltar">
                            <i class="bi bi-arrow-left"></i>
                        </a>

                        <div>
                            <h1 class="h3 mb-0">Usuários</h1>
                            <p class="text-secondary mb-0 small">
                                Gerenciamento de usuários
                            </p>
                        </div>
                    </div>
                </div>

                <?php if (($_GET['sucesso'] ?? '') === 'aprovar'): ?>

                    <div class="alert alert-success" role="alert">
                        Cadastro aprovado com sucesso. O usuário já pode acessar o sistema.
                    </div>

                <?php elseif (($_GET['sucesso'] ?? '') === 'recusar'): ?>

                    <div class="alert alert-success" role="alert">
                        Solicitação recusada com sucesso.
                    </div>

                <?php elseif (($_GET['sucesso'] ?? '') === 'desativar'): ?>

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
                        Solicitação inválida ou já processada.
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

                                <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
                                    <div>
                                        <h2 class="h5 mb-1">Solicitações de cadastro</h2>
                                        <p class="text-secondary small mb-0">
                                            Aprove ou recuse novos cadastros de maquinistas e gestores.
                                        </p>
                                    </div>

                                    <span class="badge text-bg-warning">
                                        <?= $resultadoSolicitacoes->num_rows ?> pendente(s)
                                    </span>
                                </div>

                                <div class="table-responsive">
                                    <table class="table table-hover align-middle mb-0">

                                        <thead class="table-light">
                                            <tr>
                                                <th>Usuário</th>
                                                <th>Cargo solicitado</th>
                                                <th>Telefone</th>
                                                <th>Data da solicitação</th>
                                                <th>Ações</th>
                                            </tr>
                                        </thead>

                                        <tbody>

                                            <?php if ($resultadoSolicitacoes->num_rows > 0): ?>

                                                <?php while ($solicitacao = $resultadoSolicitacoes->fetch_assoc()): ?>

                                                    <tr>
                                                        <td>
                                                            <div class="fw-semibold">
                                                                <?= htmlspecialchars($solicitacao['login'], ENT_QUOTES, 'UTF-8') ?>
                                                            </div>

                                                            <small class="text-secondary">
                                                                ID: <?= (int) $solicitacao['id_usuario'] ?>
                                                            </small>
                                                        </td>

                                                        <td>
                                                            <span class="badge text-bg-light border">
                                                                <?= htmlspecialchars(ucfirst($solicitacao['papel']), ENT_QUOTES, 'UTF-8') ?>
                                                            </span>
                                                        </td>

                                                        <td>
                                                            <?= htmlspecialchars($solicitacao['telefone'] ?? 'Não informado', ENT_QUOTES, 'UTF-8') ?>
                                                        </td>

                                                        <td>
                                                            <?php
                                                            if (!empty($solicitacao['criado_em'])) {
                                                                echo htmlspecialchars(
                                                                    date('d/m/Y', strtotime($solicitacao['criado_em'])),
                                                                    ENT_QUOTES,
                                                                    'UTF-8'
                                                                );
                                                            } else {
                                                                echo 'Não informado';
                                                            }
                                                            ?>
                                                        </td>

                                                        <td>
                                                            <div class="d-flex flex-wrap gap-2">

                                                                <form method="POST"
                                                                    onsubmit="return confirm('Deseja aprovar este cadastro?');">

                                                                    <input type="hidden" name="csrf_token"
                                                                        value="<?= htmlspecialchars($_SESSION['csrf_usuarios'], ENT_QUOTES, 'UTF-8') ?>">

                                                                    <input type="hidden" name="id_usuario"
                                                                        value="<?= (int) $solicitacao['id_usuario'] ?>">

                                                                    <input type="hidden" name="acao" value="aprovar">

                                                                    <button type="submit" class="btn btn-sm btn-success">
                                                                        <i class="bi bi-check-circle me-1"></i>
                                                                        Aprovar
                                                                    </button>

                                                                </form>

                                                                <form method="POST"
                                                                    onsubmit="return confirm('Tem certeza de que deseja recusar este cadastro?');">

                                                                    <input type="hidden" name="csrf_token"
                                                                        value="<?= htmlspecialchars($_SESSION['csrf_usuarios'], ENT_QUOTES, 'UTF-8') ?>">

                                                                    <input type="hidden" name="id_usuario"
                                                                        value="<?= (int) $solicitacao['id_usuario'] ?>">

                                                                    <input type="hidden" name="acao" value="recusar">

                                                                    <button type="submit" class="btn btn-sm btn-outline-danger">
                                                                        <i class="bi bi-x-circle me-1"></i>
                                                                        Recusar
                                                                    </button>

                                                                </form>

                                                            </div>
                                                        </td>
                                                    </tr>

                                                <?php endwhile; ?>

                                            <?php else: ?>

                                                <tr>
                                                    <td colspan="5" class="text-center text-secondary py-4">
                                                        Não existem solicitações pendentes.
                                                    </td>
                                                </tr>

                                            <?php endif; ?>

                                        </tbody>

                                    </table>
                                </div>

                            </div>
                        </div>
                    </div>

                    <div class="col-12">
                        <div class="card border-0 shadow-sm">
                            <div class="card-body">

                                <h2 class="h5 mb-3">Usuários cadastrados</h2>

                                <form method="GET" class="row g-2 align-items-end">

                                    <div class="col-12 col-md-5">
                                        <label for="buscarUsuario" class="form-label">
                                            Buscar usuário
                                        </label>

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
                                        <label for="filtroStatus" class="form-label">
                                            Filtrar por status
                                        </label>

                                        <select class="form-select" id="filtroStatus" name="status">
                                            <option value="" <?= $status === '' ? 'selected' : '' ?>>
                                                Todos
                                            </option>

                                            <option value="ativo" <?= $status === 'ativo' ? 'selected' : '' ?>>
                                                Ativos
                                            </option>

                                            <option value="inativo" <?= $status === 'inativo' ? 'selected' : '' ?>>
                                                Inativos
                                            </option>
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
                                            <i class="bi bi-person-plus me-1"></i>
                                            Adicionar usuário
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
                                                <th>Usuário</th>
                                                <th>Cargo</th>
                                                <th>Status</th>
                                                <th>Telefone</th>
                                                <th>Cadastro</th>
                                                <th>Ações</th>
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
                                                                        <i class="bi bi-shield-lock me-1"></i>
                                                                        Sua conta
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
                                                                            <i class="bi bi-person-dash me-1"></i>
                                                                            Desativar
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
                                                                        <i class="bi bi-person-check me-1"></i>
                                                                        Ativar
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

    <?php $stmt->close(); ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"></script>

</body>
</html>