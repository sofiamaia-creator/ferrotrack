<?php
require_once "../assets/php/proteger.php";
require_once "../assets/php/permissao.php";
require_once "../assets/php/conexao.php";
require_once "../assets/php/cabecalho.php";

if (!temPapel(['gestor', 'cliente'])) {
    http_response_code(403);
    echo "Acesso negado.";
    exit;
}

$mensagem = "";
$tipoMensagem = "danger";
$precoUnitario = 5.25;
$rotas = [];
$clienteId = null;

$resultadoRotas = $conexao->query(
    "SELECT id_rota, origem, destino, data_viagem, hora_partida, hora_chegada
     FROM rotas
     ORDER BY data_viagem, hora_partida"
);

if ($resultadoRotas) {
    while ($rota = $resultadoRotas->fetch_assoc()) {
        $rotas[] = $rota;
    }
}

$idUsuario = $_SESSION['usuario_id'] ?? $_SESSION['id_usuario'] ?? null;

if ($idUsuario !== null) {
    $stmtCliente = $conexao->prepare(
        "SELECT cliente_id FROM cliente WHERE id_usuario = ? LIMIT 1"
    );
    if ($stmtCliente) {
        $stmtCliente->bind_param("i", $idUsuario);
        $stmtCliente->execute();
        $resultadoCliente = $stmtCliente->get_result();
        if ($linhaCliente = $resultadoCliente->fetch_assoc()) {
            $clienteId = (int)$linhaCliente['cliente_id'];
        }
        $stmtCliente->close();
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['registrar_passagem'])) {
    $idRota = filter_input(INPUT_POST, 'id_rota', FILTER_VALIDATE_INT);
    $quantidade = filter_input(INPUT_POST, 'quantidade_passageiros', FILTER_VALIDATE_INT);
    $tipoTicket = trim($_POST['tipo_ticket'] ?? '');
    $formaPagamento = trim($_POST['forma_pagamento'] ?? '');

    $formasPermitidas = ['Cartão de Crédito', 'Pix', 'Boleto', 'Carteira Digital'];
    $tiposPermitidos = ['Inteira', 'Meia', 'Idoso'];

    if (!$clienteId) {
        $mensagem = "Não foi possível identificar seu cadastro de cliente. Entre com uma conta vinculada a um cliente.";
    } elseif (!$idRota || !$quantidade || $quantidade < 1 || $quantidade > 10) {
        $mensagem = "Selecione uma rota e informe entre 1 e 10 passageiros.";
    } elseif (!in_array($tipoTicket, $tiposPermitidos, true)) {
        $mensagem = "Selecione um tipo de bilhete válido.";
    } elseif (!in_array($formaPagamento, $formasPermitidas, true)) {
        $mensagem = "Selecione uma forma de pagamento válida.";
    } else {
        $stmtRota = $conexao->prepare(
            "SELECT origem, destino, data_viagem FROM rotas WHERE id_rota = ? LIMIT 1"
        );
        $stmtRota->bind_param("i", $idRota);
        $stmtRota->execute();
        $resultadoRota = $stmtRota->get_result();
        $dadosRota = $resultadoRota->fetch_assoc();
        $stmtRota->close();

        if (!$dadosRota) {
            $mensagem = "A rota selecionada não foi encontrada.";
        } else {
            $valorUnitario = $precoUnitario;
            if ($tipoTicket === 'Meia') {
                $valorUnitario = $precoUnitario / 2;
            } elseif ($tipoTicket === 'Idoso') {
                $valorUnitario = 0.0;
            }

            $dataHoraTicket = date('Y-m-d H:i:s');
            $lugaresMaisComprados = $dadosRota['destino'];

            $stmtTicket = $conexao->prepare(
                "INSERT INTO ticket
                (local_destino, local_partida, data_hora_ticket, quantidade_passageiros,
                 valor_ticket, quantidade_tickets, forma_pagamento, lugares_mais_comprados,
                 tipo_ticket, cliente_id, id_rota)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)"
            );

            if (!$stmtTicket) {
                $mensagem = "Não foi possível preparar o registro da passagem.";
            } else {
                $stmtTicket->bind_param(
                    "sssidisssii",
                    $dadosRota['destino'],
                    $dadosRota['origem'],
                    $dataHoraTicket,
                    $quantidade,
                    $valorUnitario,
                    $quantidade,
                    $formaPagamento,
                    $lugaresMaisComprados,
                    $tipoTicket,
                    $clienteId,
                    $idRota
                );

                if ($stmtTicket->execute()) {
                    $tipoMensagem = "success";
                    $total = $valorUnitario * $quantidade;
                    $mensagem = "Registro simulado concluído! {$quantidade} passagem(ns) registrada(s). Total: R$ " . number_format($total, 2, ',', '.') . ". Nenhuma cobrança real foi realizada.";
                } else {
                    $mensagem = "Não foi possível registrar a passagem: " . $stmtTicket->error;
                }
                $stmtTicket->close();
            }
        }
    }
}

function h($valor) {
    return htmlspecialchars((string)($valor ?? ''), ENT_QUOTES, 'UTF-8');
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Pagamento — Ferrovias</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../paginas/cabecalho.css">
</head>
<body class="bg-body-tertiary">
<?php gerarCabecalho(); ?>
<nav class="navbar navbar-expand-lg bg-white border-bottom sticky-top">
    <div class="container-fluid">
        <a class="navbar-brand d-flex align-items-center gap-2" href="painel-cliente.php">
            <img src="../assets/img/logo.png" alt="Ferrovias" width="32" height="32">
            <span class="fw-semibold">Ferrovias</span>
        </a>
        <a class="btn btn-outline-primary btn-sm" href="passagens.php"><i class="bi bi-ticket-perforated me-1"></i>Passagens</a>
    </div>
</nav>

<main class="container py-4">
    <div class="d-flex align-items-center gap-2 mb-4">
        <a class="btn btn-outline-secondary btn-sm" href="painel-cliente.php" aria-label="Voltar"><i class="bi bi-arrow-left"></i></a>
        <div>
            <h1 class="h3 mb-0">Comprar passagem</h1>
            <p class="text-secondary mb-0">Selecione a viagem e registre uma compra simulada.</p>
        </div>
    </div>

    <?php if ($mensagem !== ''): ?>
        <div class="alert alert-<?= h($tipoMensagem) ?>" role="alert"><?= h($mensagem) ?></div>
    <?php endif; ?>

    <?php if (!$clienteId): ?>
        <div class="alert alert-warning">A conta conectada não foi identificada como cliente. Para registrar passagens, entre com uma conta vinculada à tabela <code>cliente</code>.</div>
    <?php endif; ?>

    <div class="row justify-content-center">
        <div class="col-12 col-lg-8">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white border-0 pt-3">
                    <h2 class="h5 mb-0">Detalhes da passagem</h2>
                </div>
                <div class="card-body">
                    <form method="POST" action="">
                        <div class="mb-3">
                            <label for="id_rota" class="form-label">Rota</label>
                            <select class="form-select" id="id_rota" name="id_rota" required>
                                <option value="">Selecione uma rota</option>
                                <?php foreach ($rotas as $rota): ?>
                                    <option value="<?= (int)$rota['id_rota'] ?>" <?= (string)($_POST['id_rota'] ?? '') === (string)$rota['id_rota'] ? 'selected' : '' ?>>
                                        <?= h($rota['origem']) ?> → <?= h($rota['destino']) ?> | <?= h($rota['data_viagem']) ?> | <?= h(substr($rota['hora_partida'], 0, 5)) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                            <?php if (!$rotas): ?><small class="text-danger">Nenhuma rota cadastrada.</small><?php endif; ?>
                        </div>

                        <div class="row g-3">
                            <div class="col-md-6">
                                <label for="quantidade_passageiros" class="form-label">Quantidade de passageiros</label>
                                <input class="form-control" type="number" id="quantidade_passageiros" name="quantidade_passageiros" min="1" max="10" value="<?= h($_POST['quantidade_passageiros'] ?? '1') ?>" required>
                            </div>
                            <div class="col-md-6">
                                <label for="tipo_ticket" class="form-label">Tipo de bilhete</label>
                                <select class="form-select" id="tipo_ticket" name="tipo_ticket" required>
                                    <option value="Inteira">Inteira — R$ 5,25</option>
                                    <option value="Meia" <?= ($_POST['tipo_ticket'] ?? '') === 'Meia' ? 'selected' : '' ?>>Meia — R$ 2,63</option>
                                    <option value="Idoso" <?= ($_POST['tipo_ticket'] ?? '') === 'Idoso' ? 'selected' : '' ?>>Idoso — R$ 0,00</option>
                                </select>
                            </div>
                        </div>

                        <div class="mt-3 mb-3">
                            <label for="forma_pagamento" class="form-label">Forma de pagamento</label>
                            <select class="form-select" id="forma_pagamento" name="forma_pagamento" required>
                                <option value="">Selecione</option>
                                <?php foreach (['Cartão de Crédito', 'Pix', 'Boleto', 'Carteira Digital'] as $forma): ?>
                                    <option value="<?= h($forma) ?>" <?= ($_POST['forma_pagamento'] ?? '') === $forma ? 'selected' : '' ?>><?= h($forma) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="alert alert-info">
                            <i class="bi bi-info-circle me-1"></i>
                            Este é um pagamento simulado para registrar dados de teste no sistema. Nenhuma cobrança real será feita.
                        </div>

                        <button class="btn btn-primary btn-lg w-100" type="submit" name="registrar_passagem" value="1" <?= (!$rotas || !$clienteId) ? 'disabled' : '' ?>>
                            <i class="bi bi-ticket-perforated me-1"></i>Registrar passagem
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</main>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
