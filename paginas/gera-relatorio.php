
<?php

require_once "../assets/php/proteger.php";
require_once "../assets/php/permissao.php";
require_once "../assets/php/conexao.php";

if (!temPapel(['maquinista', 'gestor'])) {
    http_response_code(403);
    exit("Acesso negado.");
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $tipo = trim($_POST['tipo'] ?? '');
    $dataInicio = trim($_POST['data_inicio'] ?? '');
    $dataFim = trim($_POST['data_fim'] ?? '');
    $idTrem = trim($_POST['id_trem'] ?? '');

    $tiposPermitidos = [
        'passagens',
        'manutencao',
        'carga',
        'operacional'
    ];

    if (!in_array($tipo, $tiposPermitidos, true)) {
        http_response_code(400);
        exit("Tipo de relatório inválido.");
    }

    $validarData = function ($data) {
        if ($data === '') {
            return true;
        }

        $dataObjeto = DateTime::createFromFormat('!Y-m-d', $data);

        return $dataObjeto !== false
            && $dataObjeto->format('Y-m-d') === $data;
    };

    if (!$validarData($dataInicio) || !$validarData($dataFim)) {
        http_response_code(400);
        exit("Data inválida.");
    }

    if (
        $dataInicio !== '' &&
        $dataFim !== '' &&
        $dataInicio > $dataFim
    ) {
        http_response_code(400);
        exit("A data inicial não pode ser posterior à data final.");
    }

    if ($idTrem !== '' && !ctype_digit($idTrem)) {
        http_response_code(400);
        exit("Trem inválido.");
    }

    if ($idTrem !== '' && (int) $idTrem <= 0) {
        http_response_code(400);
        exit("Trem inválido.");
    }

    $parametros = http_build_query([
        'tipo' => $tipo,
        'data_inicio' => $dataInicio,
        'data_fim' => $dataFim,
        'id_trem' => $idTrem
    ]);

    header("Location: visualiza-relatorio.php?$parametros");
    exit;
}

require_once "../assets/php/cabecalho.php";

$sqlTrens = "SELECT id_trem, prefixo FROM trens ORDER BY prefixo";
$resultadoTrens = $conexao->query($sqlTrens);

?>
<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Gerar Relatório — FerroTrack</title>

    <link
        href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css"
        rel="stylesheet">

    <link
        href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css"
        rel="stylesheet">

    <link rel="stylesheet" href="../paginas/cabecalho.css">
</head>

<body class="bg-body-tertiary">

    <?php gerarCabecalho(); ?>

    <div class="container py-4">

        <div class="d-flex align-items-center gap-3 mb-4">
            <a href="relatorios.php" class="btn btn-outline-secondary">
                <i class="bi bi-arrow-left"></i>
            </a>

            <div>
                <h1 class="h3 mb-1">Gerar relatório</h1>
                <p class="text-secondary mb-0">
                    Selecione o tipo, o período e os filtros desejados.
                </p>
            </div>
        </div>

        <div class="card border-0 shadow-sm">
            <div class="card-body p-4">

                <form method="POST">

                    <div class="mb-4">
                        <label for="tipo" class="form-label fw-semibold">
                            Tipo de relatório
                        </label>

                        <select
                            class="form-select"
                            name="tipo"
                            id="tipo"
                            required>

                            <option value="">Selecione o tipo</option>
                            <option value="passagens">Passagens</option>
                            <option value="manutencao">Manutenção</option>
                            <option value="carga">Carga</option>
                            <option value="operacional">Operacional</option>

                        </select>
                    </div>

                    <div class="row g-3 mb-4">

                        <div class="col-md-6">
                            <label
                                for="data_inicio"
                                class="form-label fw-semibold">
                                Data inicial
                            </label>

                            <input
                                type="date"
                                class="form-control"
                                name="data_inicio"
                                id="data_inicio">
                        </div>

                        <div class="col-md-6">
                            <label
                                for="data_fim"
                                class="form-label fw-semibold">
                                Data final
                            </label>

                            <input
                                type="date"
                                class="form-control"
                                name="data_fim"
                                id="data_fim">
                        </div>

                    </div>

                    <div class="mb-4">
                        <label
                            for="id_trem"
                            class="form-label fw-semibold">
                            Trem
                        </label>

                        <select
                            class="form-select"
                            name="id_trem"
                            id="id_trem">

                            <option value="">Todos os trens</option>

                            <?php if ($resultadoTrens): ?>
                                <?php while (
                                    $trem = $resultadoTrens->fetch_assoc()
                                ): ?>
                                    <option
                                        value="<?= (int) $trem['id_trem'] ?>">
                                        <?= htmlspecialchars(
                                            $trem['prefixo'],
                                            ENT_QUOTES,
                                            'UTF-8'
                                        ) ?>
                                    </option>
                                <?php endwhile; ?>
                            <?php endif; ?>

                        </select>
                    </div>

                    <div class="d-flex flex-wrap gap-2">

                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-file-earmark-bar-graph me-1"></i>
                            Gerar relatório
                        </button>

                        <a
                            href="relatorios.php"
                            class="btn btn-outline-secondary">
                            Cancelar
                        </a>

                    </div>

                </form>

            </div>
        </div>

    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"></script>

</body>

</html>
