<?php

require_once "../assets/php/proteger.php";
require_once "../assets/php/permissao.php";
require_once "../assets/php/conexao.php";
require_once "../assets/php/cabecalho.php";

if (!temPapel(['gestor', 'maquinista'])) {
    echo "Acesso negado.";
    exit;
}

$id_sensor = (int) ($_GET['id_sensor'] ?? 0);
$id_trem = (int) ($_GET['id_trem'] ?? 0);
$data_inicio = $_GET['data_inicio'] ?? '';
$data_fim = $_GET['data_fim'] ?? '';

$condicoes = [];
$valores = [];
$tipos = '';

if ($id_sensor > 0) {
    $condicoes[] = "leituras.id_sensor = ?";
    $valores[] = $id_sensor;
    $tipos .= "i";
}

if ($id_trem > 0) {
    $condicoes[] = "trens.id_trem = ?";
    $valores[] = $id_trem;
    $tipos .= "i";
}

if ($data_inicio !== '') {
    $condicoes[] = "leituras.data_hora >= ?";
    $valores[] = $data_inicio . " 00:00:00";
    $tipos .= "s";
}

if ($data_fim !== '') {
    $condicoes[] = "leituras.data_hora <= ?";
    $valores[] = $data_fim . " 23:59:59";
    $tipos .= "s";
}

$sql = "SELECT
            leituras.id_leitura,
            leituras.data_hora,
            leituras.valor,
            leituras.unidade,
            sensores.codigo,
            sensores.tipo,
            trens.prefixo,
            trens.modelo
        FROM leituras
        INNER JOIN sensores
            ON sensores.id_sensor = leituras.id_sensor
        INNER JOIN trens
            ON trens.id_trem = sensores.id_trem";

if (!empty($condicoes)) {
    $sql .= " WHERE " . implode(" AND ", $condicoes);
}

$sql .= " ORDER BY leituras.data_hora DESC LIMIT 100";

$stmt = $conexao->prepare($sql);

if (!empty($valores)) {
    $stmt->bind_param($tipos, ...$valores);
}

$stmt->execute();

$resultado = $stmt->get_result();

$sensores = $conexao->query(
    "SELECT
        sensores.id_sensor,
        sensores.codigo,
        sensores.tipo,
        trens.prefixo,
        trens.modelo
     FROM sensores
     INNER JOIN trens
        ON trens.id_trem = sensores.id_trem
     ORDER BY trens.prefixo, sensores.codigo"
);

$trens = $conexao->query(
    "SELECT
        id_trem,
        prefixo,
        modelo
     FROM trens
     ORDER BY prefixo"
);

?>

<!DOCTYPE html>
<html lang="pt-BR">

<head>

    <meta charset="UTF-8">

    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>Leituras dos Sensores — Ferrovias</title>

    <link
        href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css"
        rel="stylesheet"
    >

    <link
        href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css"
        rel="stylesheet"
    >

</head>

<body class="bg-body-tertiary">

<?php gerarCabecalho(); ?>

<div class="container py-4">

    <div class="d-flex flex-wrap align-items-center justify-content-between gap-2 mb-4">

        <div>

            <h1 class="h3 mb-1">
                Leituras dos Sensores
            </h1>

            <p class="text-secondary mb-0">
                Histórico de leituras registradas
            </p>

        </div>

        <a href="adicionar-leitura.php" class="btn btn-primary">
            <i class="bi bi-plus-circle me-1"></i>
            Adicionar leitura
        </a>

    </div>

    <div class="card border-0 shadow-sm mb-4">

        <div class="card-body">

            <h5 class="mb-3">
                <i class="bi bi-funnel me-1"></i>
                Filtrar leituras
            </h5>

            <form method="GET">

                <div class="row g-3">

                    <div class="col-12 col-md-3">

                        <label for="id_sensor" class="form-label">
                            Sensor
                        </label>

                        <select
                            name="id_sensor"
                            id="id_sensor"
                            class="form-select"
                        >

                            <option value="0">
                                Todos os sensores
                            </option>

                            <?php while ($sensor = $sensores->fetch_assoc()): ?>

                                <option
                                    value="<?= $sensor['id_sensor'] ?>"
                                    <?= $id_sensor == $sensor['id_sensor'] ? 'selected' : '' ?>
                                >

                                    <?= htmlspecialchars($sensor['codigo']) ?>
                                    —
                                    <?= htmlspecialchars($sensor['prefixo']) ?>
                                    —
                                    <?= htmlspecialchars($sensor['tipo']) ?>

                                </option>

                            <?php endwhile; ?>

                        </select>

                    </div>

                    <div class="col-12 col-md-3">

                        <label for="id_trem" class="form-label">
                            Trem
                        </label>

                        <select
                            name="id_trem"
                            id="id_trem"
                            class="form-select"
                        >

                            <option value="0">
                                Todos os trens
                            </option>

                            <?php while ($trem = $trens->fetch_assoc()): ?>

                                <option
                                    value="<?= $trem['id_trem'] ?>"
                                    <?= $id_trem == $trem['id_trem'] ? 'selected' : '' ?>
                                >

                                    <?= htmlspecialchars($trem['prefixo']) ?>
                                    —
                                    <?= htmlspecialchars($trem['modelo']) ?>

                                </option>

                            <?php endwhile; ?>

                        </select>

                    </div>

                    <div class="col-12 col-md-2">

                        <label for="data_inicio" class="form-label">
                            Data inicial
                        </label>

                        <input
                            type="date"
                            name="data_inicio"
                            id="data_inicio"
                            class="form-control"
                            value="<?= htmlspecialchars($data_inicio) ?>"
                        >

                    </div>

                    <div class="col-12 col-md-2">

                        <label for="data_fim" class="form-label">
                            Data final
                        </label>

                        <input
                            type="date"
                            name="data_fim"
                            id="data_fim"
                            class="form-control"
                            value="<?= htmlspecialchars($data_fim) ?>"
                        >

                    </div>

                    <div class="col-12 col-md-2 d-flex align-items-end gap-2">

                        <button
                            type="submit"
                            class="btn btn-primary"
                        >
                            <i class="bi bi-search me-1"></i>
                            Filtrar
                        </button>

                        <a
                            href="leituras.php"
                            class="btn btn-outline-secondary"
                        >
                            Limpar
                        </a>

                    </div>

                </div>

            </form>

        </div>

    </div>

    <div class="card border-0 shadow-sm">

        <div class="card-body">

            <div class="table-responsive">

                <table class="table table-hover align-middle mb-0">

                    <thead class="table-light">

                        <tr>

                            <th>Sensor</th>
                            <th>Trem</th>
                            <th>Tipo</th>
                            <th>Data e hora</th>
                            <th>Valor</th>
                            <th>Unidade</th>

                        </tr>

                    </thead>

                    <tbody>

                        <?php if ($resultado && $resultado->num_rows > 0): ?>

                            <?php while ($leitura = $resultado->fetch_assoc()): ?>

                                <tr>

                                    <td class="fw-semibold">
                                        <?= htmlspecialchars($leitura['codigo']) ?>
                                    </td>

                                    <td>
                                        <?= htmlspecialchars($leitura['prefixo']) ?>
                                        —
                                        <?= htmlspecialchars($leitura['modelo']) ?>
                                    </td>

                                    <td>
                                        <?= htmlspecialchars($leitura['tipo']) ?>
                                    </td>

                                    <td>
                                        <?= date(
                                            'd/m/Y H:i:s',
                                            strtotime($leitura['data_hora'])
                                        ) ?>
                                    </td>

                                    <td>
                                        <?= htmlspecialchars($leitura['valor']) ?>
                                    </td>

                                    <td>
                                        <?= htmlspecialchars($leitura['unidade']) ?>
                                    </td>

                                </tr>

                            <?php endwhile; ?>

                        <?php else: ?>

                            <tr>

                                <td
                                    colspan="6"
                                    class="text-center text-secondary py-4"
                                >
                                    Nenhuma leitura encontrada.
                                </td>

                            </tr>

                        <?php endif; ?>

                    </tbody>

                </table>

            </div>

        </div>

    </div>

</div>

<script
    src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js">
</script>

</body>

</html>