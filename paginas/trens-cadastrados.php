<?php

require_once "../assets/php/proteger.php";
require_once "../assets/php/permissao.php";
require_once "../assets/php/cabecalho.php";
require_once "../assets/php/menu.php";
require_once "../assets/php/conexao.php";

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

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['excluir_id'])) {
    $excluir_id = (int)$_POST['excluir_id'];

    if ($excluir_id > 0) {
        $stmt = $conexao->prepare('DELETE FROM trens WHERE id_trem = ?');
        $stmt->bind_param('i', $excluir_id);

        if ($stmt->execute()) {
            header('Location: trens-cadastrados.php?sucesso=excluido');
            exit;
        } else {
            $erros[] = 'Não foi possível excluir o trem. Verifique se existem registros relacionados a ele.';
        }
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && !isset($_POST['excluir_id'])) {
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
    die('Erro ao preparar consulta: ' . $conexao->error);
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

        .tabela-desktop {
            display: block;
        }

        .lista-mobile {
            display: none;
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

<div class="container-fluid px-0">

    <div class="layout-principal">

        <?php renderizarMenu(); ?>

        <main class="conteudo-principal">

            <?php if ($sucesso === 'cadastrado'): ?>
                <div class="alert alert-success">
                    Trem cadastrado com sucesso.
                </div>
            <?php elseif ($sucesso === 'editado'): ?>
                <div class="alert alert-success">
                    Trem editado com sucesso.
                </div>
            <?php elseif ($sucesso === 'excluido'): ?>
                <div class="alert alert-success">
                    Trem excluído com sucesso.
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

                        <input type="hidden" name="id_trem" value="<?= htmlspecialchars((string)$id_trem) ?>">

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
                                value="<?= htmlspecialchars((string)$ano) ?>"
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
                                value="<?= htmlspecialchars((string)$capacidade) ?>"
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

                    <a class="btn btn-outline-secondary btn-sm" href="trens.php" aria-label="Voltar">
                        <i class="bi bi-arrow-left"></i>
                    </a>

                    <div>
                        <h1 class="h3 mb-0">Trens Cadastrados</h1>
                        <p class="text-secondary mb-0 small">Frota registrada no sistema</p>
                    </div>

                </div>

            </div>

            <div class="card border-0 shadow-sm mb-3">

                <div class="card-body">

                    <form method="GET" class="row g-3">

                        <div class="col-12 col-md-6">
                            <label for="busca" class="form-label">Buscar trem</label>
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
                            <label for="status" class="form-label">Status</label>
                            <select class="form-select" id="status" name="status">
                                <option value="">Todos os status</option>
                                <option value="Ativo" <?= $status === 'Ativo' ? 'selected' : '' ?>>Ativo</option>
                                <option value="Em manutenção" <?= $status === 'Em manutenção' ? 'selected' : '' ?>>Em manutenção</option>
                                <option value="Inativo" <?= $status === 'Inativo' ? 'selected' : '' ?>>Inativo</option>
                            </select>
                        </div>

                        <div class="col-12 col-md-2 d-flex align-items-end">
                            <button type="submit" class="btn btn-primary w-100">
                                <i class="bi bi-search me-1"></i>
                                Buscar
                            </button>
                        </div>

                    </form>

                </div>

            </div>

            <div class="card border-0 shadow-sm">

                <div class="card-header bg-white border-0 pt-3">
                    <h2 class="h5 mb-0">Frota registrada</h2>
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
                                    </tr>
                                </thead>

                                <tbody>

                                    <?php if (count($trens) > 0): ?>

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
                                                        <span class="text-secondary">Não realizada</span>
                                                    <?php endif; ?>
                                                </td>

                                                <td>
                                                    <div class="d-flex gap-2">

                                                        <a
                                                            href="trens-cadastrados.php?id_trem=<?= (int)$trem['id_trem'] ?>"
                                                            class="btn btn-sm btn-outline-primary"
                                                        >
                                                            <i class="bi bi-pencil"></i>
                                                            Editar
                                                        </a>

                                                        <form
                                                            method="POST"
                                                            class="d-inline"
                                                            onsubmit="return confirm('Confirma a exclusão?');"
                                                        >
                                                            <input
                                                                type="hidden"
                                                                name="excluir_id"
                                                                value="<?= (int)$trem['id_trem'] ?>"
                                                            >

                                                            <button type="submit" class="btn btn-sm btn-outline-danger">
                                                                <i class="bi bi-trash"></i>
                                                                Excluir
                                                            </button>
                                                        </form>

                                                    </div>
                                                </td>

                                            </tr>

                                        <?php endforeach; ?>

                                    <?php else: ?>

                                        <tr>
                                            <td colspan="7" class="text-center text-secondary py-4">
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
                                                    <span class="text-secondary d-block">Ano</span>
                                                    <strong><?= htmlspecialchars($trem['ano']) ?></strong>
                                                </div>

                                                <div class="col-6">
                                                    <span class="text-secondary d-block">Capacidade</span>
                                                    <strong><?= htmlspecialchars($trem['capacidade']) ?> t</strong>
                                                </div>

                                                <div class="col-12">
                                                    <span class="text-secondary d-block">Última inspeção</span>
                                                    <strong>
                                                        <?= !empty($trem['ultima_inspecao'])
                                                            ? htmlspecialchars($trem['ultima_inspecao'])
                                                            : 'Não realizada' ?>
                                                    </strong>
                                                </div>

                                            </div>

                                            <div class="mt-3 d-flex gap-2">

                                                <a
                                                    href="trens-cadastrados.php?id_trem=<?= (int)$trem['id_trem'] ?>"
                                                    class="btn btn-sm btn-outline-primary"
                                                >
                                                    <i class="bi bi-pencil"></i>
                                                    Editar
                                                </a>

                                                <form
                                                    method="POST"
                                                    onsubmit="return confirm('Confirma a exclusão?');"
                                                >
                                                    <input
                                                        type="hidden"
                                                        name="excluir_id"
                                                        value="<?= (int)$trem['id_trem'] ?>"
                                                    >

                                                    <button type="submit" class="btn btn-sm btn-outline-danger">
                                                        <i class="bi bi-trash"></i>
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

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"></script>

</body>
</html>
