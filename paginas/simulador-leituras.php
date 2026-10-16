
<?php

require_once "../assets/php/proteger.php";
require_once "../assets/php/permissao.php";
require_once "../assets/php/conexao.php";
require_once "../assets/php/cabecalho.php";
require_once "../assets/php/verificar-alerta.php";

if (!temPapel(['gestor', 'maquinista'])) {
    http_response_code(403);
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
        $stmt_sensor = $conexao->prepare(
            "SELECT tipo FROM sensores WHERE id_sensor = ?"
        );

        if (!$stmt_sensor) {
            $erro = "Erro ao consultar sensor: " . $conexao->error;
        } else {
            $stmt_sensor->bind_param("i", $id_sensor);

            if (!$stmt_sensor->execute()) {
                $erro = "Erro ao consultar sensor: " . $stmt_sensor->error;
            } else {
                $sensor = $stmt_sensor->get_result()->fetch_assoc();
            }

            $stmt_sensor->close();

            if ($erro === "" && empty($sensor)) {
                $erro = "Sensor não encontrado.";
            }

            if ($erro === "" && !empty($sensor)) {
                $tipo_sensor = $sensor['tipo'];

                try {
                    $limite_sensor = buscarLimite($tipo_sensor);

                    if ($limite_sensor === null) {
                        throw new Exception(
                            "Não existe limite cadastrado para esse sensor."
                        );
                    }

                    $unidade = $limite_sensor['unidade'];
                    $valor_limite = (float) $limite_sensor['valor_maximo'];

                    if ($fora_limite) {
                        $minimo = $valor_limite * 1.01;
                        $maximo = $valor_limite * 1.30;
                    } else {
                        $minimo = $valor_limite * 0.50;
                        $maximo = $valor_limite * 0.90;
                    }

                    $stmt = $conexao->prepare(
                        "INSERT INTO leituras
                         (id_sensor, data_hora, valor, unidade)
                         VALUES (?, ?, ?, ?)"
                    );

                    if (!$stmt) {
                        throw new Exception(
                            "Erro ao preparar a leitura: " . $conexao->error
                        );
                    }

                    $conexao->begin_transaction();

                    try {
                        for ($i = 0; $i < $quantidade; $i++) {
                            $data_hora = date(
                                'Y-m-d H:i:s',
                                time() - ($i * 300)
                            );

                            $valor = mt_rand(
                                (int) round($minimo * 100),
                                (int) round($maximo * 100)
                            ) / 100;

                            $stmt->bind_param(
                                "isds",
                                $id_sensor,
                                $data_hora,
                                $valor,
                                $unidade
                            );

                            if (!$stmt->execute()) {
                                throw new Exception(
                                    "Erro ao cadastrar leitura: " . $stmt->error
                                );
                            }

                            $id_leitura = $conexao->insert_id;

                            if (!$id_leitura) {
                                throw new Exception(
                                    "Não foi possível obter o ID da leitura."
                                );
                            }

                            verificarAlerta(
                                $conexao,
                                $id_leitura,
                                $tipo_sensor,
                                $valor
                            );
                        }

                        $conexao->commit();
                        $mensagem = "$quantidade leituras geradas com sucesso!";
                    } catch (Throwable $e) {
                        $conexao->rollback();
                        throw $e;
                    } finally {
                        $stmt->close();
                    }
                } catch (Throwable $e) {
                    $erro = "Erro ao gerar leituras: " . $e->getMessage();
                }
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

if (!$sensores) {
    die("Erro ao consultar sensores: " . $conexao->error);
}

?>

<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Simulador de Leituras — FerroTrack</title>

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
        <h1 class="h3 mb-1">Simulador de Leituras</h1>
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
                    <label for="id_sensor" class="form-label">Sensor</label>

                    <select
                        name="id_sensor"
                        id="id_sensor"
                        class="form-select"
                        required
                    >
                        <option value="">Selecione o sensor</option>

                        <?php while ($sensor = $sensores->fetch_assoc()): ?>
                            <option value="<?= (int) $sensor['id_sensor'] ?>">
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
                        Gerar valores acima do limite (atenção e crítico)
                    </label>
                </div>

                <button type="submit" class="btn btn-primary">
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

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"></script>

</body>
</html>
