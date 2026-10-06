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
    $quantidade = (int) ($_POST['quantidade'] ?? 0);
    $fora_limite = isset($_POST['fora_limite']);

    if ($id_sensor <= 0 || $quantidade <= 0) {

        $erro = "Selecione um sensor e informe uma quantidade válida.";

    } elseif ($quantidade > 100) {

        $erro = "A quantidade máxima é 100 leituras.";

    } else {

        $sql_sensor = "SELECT tipo FROM sensores WHERE id_sensor = ?";
        $stmt_sensor = $conexao->prepare($sql_sensor);
        $stmt_sensor->bind_param("i", $id_sensor);
        $stmt_sensor->execute();

        $resultado_sensor = $stmt_sensor->get_result();
        $sensor = $resultado_sensor->fetch_assoc();

        $stmt_sensor->close();

        if (!$sensor) {

            $erro = "Sensor não encontrado.";

        } else {

            $tipo = strtolower($sensor['tipo']);

            if (strpos($tipo, 'temperatura') !== false) {

                $unidade = "°C";

                if ($fora_limite) {
                    $minimo = 110;
                    $maximo = 130;
                } else {
                    $minimo = 60;
                    $maximo = 90;
                }

            } elseif (strpos($tipo, 'velocidade') !== false) {

                $unidade = "km/h";

                if ($fora_limite) {
                    $minimo = 120;
                    $maximo = 160;
                } else {
                    $minimo = 40;
                    $maximo = 100;
                }

            } elseif (strpos($tipo, 'pressão') !== false || strpos($tipo, 'pressao') !== false) {

                $unidade = "bar";

                if ($fora_limite) {
                    $minimo = 8;
                    $maximo = 12;
                } else {
                    $minimo = 2;
                    $maximo = 6;
                }

            } else {

                $unidade = "un";

                if ($fora_limite) {
                    $minimo = 101;
                    $maximo = 150;
                } else {
                    $minimo = 20;
                    $maximo = 80;
                }
            }

            $stmt = $conexao->prepare(
                "INSERT INTO leituras
                (id_sensor, data_hora, valor, unidade)
                VALUES (?, ?, ?, ?)"
            );

            if (!$stmt) {

                $erro = "Erro ao preparar o cadastro: " . $conexao->error;

            } else {

                for ($i = 0; $i < $quantidade; $i++) {

                    $data_hora = date(
                        'Y-m-d H:i:s',
                        time() - ($i * 300)
                    );

                    $valor = mt_rand(
                        $minimo * 100,
                        $maximo * 100
                    ) / 100;

                    $stmt->bind_param(
                        "isds",
                        $id_sensor,
                        $data_hora,
                        $valor,
                        $unidade
                    );

                    $stmt->execute();
                }

                $stmt->close();

                $mensagem = "$quantidade leituras geradas com sucesso!";
            }
        }
    }
}

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

?>

<!DOCTYPE html>
<html lang="pt-BR">

<head>

    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1"
    >

    <title>Simulador de Leituras — Ferrovias</title>

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
            Simulador de Leituras
        </h1>

        <p class="text-secondary mb-0">
            Gere leituras simuladas para os sensores do sistema.
        </p>

    </div>

    <?php if ($mensagem !== ""): ?>

        <div class="alert alert-success">
            <i class="bi bi-check-circle me-1"></i>
            <?= htmlspecialchars($mensagem) ?>
        </div>

    <?php endif; ?>

    <?php if ($erro !== ""): ?>

        <div class="alert alert-danger">
            <i class="bi bi-exclamation-triangle me-1"></i>
            <?= htmlspecialchars($erro) ?>
        </div>

    <?php endif; ?>

    <div class="card border-0 shadow-sm">

        <div class="card-body">

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
                                —
                                <?= htmlspecialchars($sensor['prefixo']) ?>
                                —
                                <?= htmlspecialchars($sensor['tipo']) ?>

                            </option>

                        <?php endwhile; ?>

                    </select>

                </div>

                <div class="mb-3">

                    <label for="quantidade" class="form-label">
                        Quantidade de leituras
                    </label>

                    <input
                        type="number"
                        name="quantidade"
                        id="quantidade"
                        class="form-control"
                        min="1"
                        max="100"
                        value="10"
                        required
                    >

                    <div class="form-text">
                        Máximo de 100 leituras por vez.
                    </div>

                </div>

                <div class="form-check mb-4">

                    <input
                        class="form-check-input"
                        type="checkbox"
                        name="fora_limite"
                        id="fora_limite"
                    >

                    <label
                        class="form-check-label"
                        for="fora_limite"
                    >
                        Gerar valores fora do limite normal
                    </label>

                </div>

                <button
                    type="submit"
                    class="btn btn-primary"
                >

                    <i class="bi bi-play-circle me-1"></i>
                    Gerar leituras

                </button>

                <a
                    href="leituras.php"
                    class="btn btn-outline-secondary ms-2"
                >
                    Ver leituras
                </a>

            </form>

        </div>

    </div>

</div>

<script
    src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js">
</script>

</body>

</html>