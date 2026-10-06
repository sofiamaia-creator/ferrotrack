<?php

require_once "../assets/php/proteger.php";
require_once "../assets/php/permissao.php";
require_once "../assets/php/conexao.php";
require_once "../assets/php/cabecalho.php";

if (!temPapel(['gestor', 'maquinista'])) {
    echo "Acesso negado.";
    exit;
}

$erro = "";
$mensagem = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $id_sensor = (int) ($_POST['id_sensor'] ?? 0);
    $data_hora = $_POST['data_hora'] ?? '';
    $valor = $_POST['valor'] ?? '';
    $unidade = trim($_POST['unidade'] ?? '');

    if (
        $id_sensor <= 0 ||
        $data_hora === '' ||
        $valor === '' ||
        $unidade === ''
    ) {
        $erro = "Preencha todos os campos.";
    } else {

        $data_hora = str_replace('T', ' ', $data_hora);

        if (strlen($data_hora) === 16) {
            $data_hora .= ':00';
        }

        $sql = "INSERT INTO leituras
                (id_sensor, data_hora, valor, unidade)
                VALUES (?, ?, ?, ?)";

        $stmt = $conexao->prepare($sql);

        if ($stmt) {

            $stmt->bind_param(
                "isds",
                $id_sensor,
                $data_hora,
                $valor,
                $unidade
            );

            if ($stmt->execute()) {
                $mensagem = "Leitura cadastrada com sucesso!";
            } else {
                $erro = "Erro ao cadastrar a leitura: " . $stmt->error;
            }

            $stmt->close();

        } else {
            $erro = "Erro ao preparar o cadastro: " . $conexao->error;
        }
    }
}

$sensores = $conexao->query(
    "SELECT sensores.id_sensor,
            sensores.codigo,
            sensores.tipo,
            trens.prefixo,
            trens.modelo
     FROM sensores
     INNER JOIN trens ON trens.id_trem = sensores.id_trem
     ORDER BY trens.prefixo, sensores.codigo"
);

?>

<!DOCTYPE html>
<html lang="pt-BR">

<head>

    <meta charset="UTF-8">

    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>Adicionar Leitura — Ferrovias</title>

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

    <div class="mb-4">

        <h1 class="h3 mb-1">
            Adicionar Leitura
        </h1>

        <p class="text-secondary mb-0">
            Registre uma nova leitura de sensor
        </p>

    </div>

    <?php if ($mensagem !== ""): ?>

        <div class="alert alert-success">
            <?= htmlspecialchars($mensagem) ?>
        </div>

    <?php endif; ?>

    <?php if ($erro !== ""): ?>

        <div class="alert alert-danger">
            <?= htmlspecialchars($erro) ?>
        </div>

    <?php endif; ?>

    <div class="card border-0 shadow-sm">

        <div class="card-body">

            <h5 class="card-title mb-4">
                Informações da leitura
            </h5>

            <form method="POST">

                <div class="mb-3">

                    <label for="id_sensor" class="form-label">
                        Sensor
                    </label>

                    <select
                        name="id_sensor"
                        id="id_sensor"
                        class="form-select"
                        required
                    >

                        <option value="">
                            Selecione o sensor
                        </option>

                        <?php while ($sensor = $sensores->fetch_assoc()): ?>

                            <option value="<?= $sensor['id_sensor'] ?>">

                                <?= htmlspecialchars($sensor['codigo']) ?>
                                -
                                <?= htmlspecialchars($sensor['tipo']) ?>
                                -
                                <?= htmlspecialchars($sensor['prefixo']) ?>

                            </option>

                        <?php endwhile; ?>

                    </select>

                </div>

                <div class="mb-3">

                    <label for="data_hora" class="form-label">
                        Data e hora
                    </label>

                    <input
                        type="datetime-local"
                        name="data_hora"
                        id="data_hora"
                        class="form-control"
                        required
                    >

                </div>

                <div class="mb-3">

                    <label for="valor" class="form-label">
                        Valor
                    </label>

                    <input
                        type="number"
                        name="valor"
                        id="valor"
                        class="form-control"
                        step="0.01"
                        required
                    >

                </div>

                <div class="mb-4">

                    <label for="unidade" class="form-label">
                        Unidade
                    </label>

                    <input
                        type="text"
                        name="unidade"
                        id="unidade"
                        class="form-control"
                        placeholder="Ex: °C, km/h, bar"
                        maxlength="10"
                        required
                    >

                </div>

                <div class="d-flex gap-2">

                    <button
                        type="submit"
                        class="btn btn-primary"
                    >
                        <i class="bi bi-check-circle me-1"></i>
                        Cadastrar leitura
                    </button>

                    <a
                        href="sensores.php"
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
    src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js">
</script>

</body>

</html>