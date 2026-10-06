<?php

require_once "../assets/php/proteger.php";
require_once "../assets/php/permissao.php";
require_once "../assets/php/cabecalho.php";
require_once "../assets/php/conexao.php";

if (!temPapel(['gestor'])) {
    echo "Acesso negado.";
    exit;
}

$id_rota = (int) ($_GET['id'] ?? 0);

if ($id_rota <= 0) {
    echo "Rota inválida.";
    exit;
}

$erro = "";
$mensagem = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $id_trem = (int) ($_POST['id_trem'] ?? 0);
    $origem = trim($_POST['origem'] ?? '');
    $destino = trim($_POST['destino'] ?? '');
    $data_viagem = $_POST['data_viagem'] ?? '';
    $hora_partida = $_POST['hora_partida'] ?? '';
    $hora_chegada = $_POST['hora_chegada'] ?? '';
    $status = $_POST['status'] ?? '';

    if (
        $id_trem <= 0 ||
        $origem === '' ||
        $destino === '' ||
        $data_viagem === '' ||
        $hora_partida === '' ||
        $status === ''
    ) {
        $erro = "Preencha todos os campos obrigatórios.";
    } else {

        $hora_chegada_db = $hora_chegada !== '' ? $hora_chegada : null;

        $sql = "UPDATE rotas
                SET id_trem = ?,
                    origem = ?,
                    destino = ?,
                    data_viagem = ?,
                    hora_partida = ?,
                    hora_chegada = ?,
                    status = ?
                WHERE id_rota = ?";

        $stmt = $conexao->prepare($sql);

        if ($stmt) {

            $stmt->bind_param(
                "issssssi",
                $id_trem,
                $origem,
                $destino,
                $data_viagem,
                $hora_partida,
                $hora_chegada_db,
                $status,
                $id_rota
            );

            if ($stmt->execute()) {
                header("Location: gestao-rotas.php");
                exit;
            } else {
                $erro = "Erro ao atualizar a rota: " . $stmt->error;
            }

            $stmt->close();

        } else {
            $erro = "Erro ao preparar a atualização: " . $conexao->error;
        }
    }
}

$sql = "SELECT *
        FROM rotas
        WHERE id_rota = ?";

$stmt = $conexao->prepare($sql);
$stmt->bind_param("i", $id_rota);
$stmt->execute();

$resultado = $stmt->get_result();
$rota = $resultado->fetch_assoc();

$stmt->close();

if (!$rota) {
    echo "Rota não encontrada.";
    exit;
}

$trens = $conexao->query(
    "SELECT id_trem, prefixo, modelo
     FROM trens
     ORDER BY prefixo"
);
?>

<!DOCTYPE html>
<html lang="pt-BR">

<head>

    <meta charset="UTF-8">

    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Editar Rota — Ferrovias</title>

    <link
        href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
        rel="stylesheet"
    >

</head>

<body>

<div class="container py-4">

    <div class="mb-4">

        <h1 class="fw-bold">
            Editar Rota
        </h1>

        <p class="text-muted">
            Altere as informações da rota
        </p>

    </div>

    <?php if ($erro !== ""): ?>

        <div class="alert alert-danger">
            <?= htmlspecialchars($erro) ?>
        </div>

    <?php endif; ?>

    <div class="card shadow-sm">

        <div class="card-body">

            <h5 class="card-title mb-4">
                Informações da rota
            </h5>

            <form method="POST">

                <div class="mb-3">

                    <label for="id_trem" class="form-label">
                        Trem
                    </label>

                    <select
                        name="id_trem"
                        id="id_trem"
                        class="form-select"
                        required
                    >

                        <option value="">
                            Selecione o trem
                        </option>

                        <?php while ($trem = $trens->fetch_assoc()): ?>

                            <option
                                value="<?= $trem['id_trem'] ?>"
                                <?= $rota['id_trem'] == $trem['id_trem'] ? 'selected' : '' ?>
                            >

                                <?= htmlspecialchars($trem['prefixo']) ?> -
                                <?= htmlspecialchars($trem['modelo']) ?>

                            </option>

                        <?php endwhile; ?>

                    </select>

                </div>

                <div class="mb-3">

                    <label for="origem" class="form-label">
                        Ponto de partida
                    </label>

                    <input
                        type="text"
                        name="origem"
                        id="origem"
                        class="form-control"
                        placeholder="Ex: Joinville"
                        value="<?= htmlspecialchars($rota['origem']) ?>"
                        required
                    >

                </div>

                <div class="mb-3">

                    <label for="destino" class="form-label">
                        Destino
                    </label>

                    <input
                        type="text"
                        name="destino"
                        id="destino"
                        class="form-control"
                        placeholder="Ex: Curitiba"
                        value="<?= htmlspecialchars($rota['destino']) ?>"
                        required
                    >

                </div>

                <div class="mb-3">

                    <label for="data_viagem" class="form-label">
                        Data da viagem
                    </label>

                    <input
                        type="date"
                        name="data_viagem"
                        id="data_viagem"
                        class="form-control"
                        value="<?= htmlspecialchars($rota['data_viagem']) ?>"
                        required
                    >

                </div>

                <div class="mb-3">

                    <label for="hora_partida" class="form-label">
                        Hora de partida
                    </label>

                    <input
                        type="time"
                        name="hora_partida"
                        id="hora_partida"
                        class="form-control"
                        value="<?= htmlspecialchars($rota['hora_partida']) ?>"
                        required
                    >

                </div>

                <div class="mb-3">

                    <label for="hora_chegada" class="form-label">
                        Hora de chegada
                    </label>

                    <input
                        type="time"
                        name="hora_chegada"
                        id="hora_chegada"
                        class="form-control"
                        value="<?= htmlspecialchars($rota['hora_chegada'] ?? '') ?>"
                    >

                </div>

                <div class="mb-4">

                    <label for="status" class="form-label">
                        Status
                    </label>

                    <select
                        name="status"
                        id="status"
                        class="form-select"
                        required
                    >

                        <option value="">
                            Selecione o status
                        </option>

                        <option
                            value="programada"
                            <?= $rota['status'] === 'programada' ? 'selected' : '' ?>
                        >
                            Programada
                        </option>

                        <option
                            value="em andamento"
                            <?= $rota['status'] === 'em andamento' ? 'selected' : '' ?>
                        >
                            Em andamento
                        </option>

                        <option
                            value="concluída"
                            <?= $rota['status'] === 'concluída' ? 'selected' : '' ?>
                        >
                            Concluída
                        </option>

                        <option
                            value="cancelada"
                            <?= $rota['status'] === 'cancelada' ? 'selected' : '' ?>
                        >
                            Cancelada
                        </option>

                    </select>

                </div>

                <div class="d-flex gap-2">

                    <button
                        type="submit"
                        class="btn btn-primary"
                    >
                        Salvar alterações
                    </button>

                    <a
                        href="gestao-rotas.php"
                        class="btn btn-secondary"
                    >
                        Cancelar
                    </a>

                </div>

            </form>

        </div>

    </div>

</div>

<script
    src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js">
</script>

</body>

</html>