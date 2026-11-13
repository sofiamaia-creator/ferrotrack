<?php

require_once "../assets/php/proteger.php";
require_once "../assets/php/permissao.php";
require_once "../assets/php/cabecalho.php";
require_once "../assets/php/menu.php";

if (!temPapel(['maquinista', 'gestor'])) {
    http_response_code(403);
    echo "Acesso negado.";
    exit;
}
?>
<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Relatórios — Ferrovias</title>

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

        .card-relatorio {
            transition: transform 0.2s ease, box-shadow 0.2s ease;
        }

        .card-relatorio:hover {
            transform: translateY(-4px);
            box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.12) !important;
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
                    <div>
                        <h1 class="h3 mb-0">Relatórios</h1>
                        <p class="text-secondary mb-0 small">
                            Escolha o relatório que deseja consultar
                        </p>
                    </div>
                </div>

                <div class="row g-3">

                    <div class="col-12 col-sm-6 col-xl-3">
                        <a class="card card-relatorio h-100 text-decoration-none border-0 shadow-sm"
                            href="visualiza-relatorio.php?tipo=operacional">
                            <div class="card-body text-center py-4">
                                <span class="d-inline-flex align-items-center justify-content-center bg-primary-subtle text-primary rounded-3 mb-3 p-3">
                                    <i class="bi bi-graph-up fs-3"></i>
                                </span>

                                <h2 class="h6 mb-0 text-body">
                                    Relatórios operacionais
                                </h2>
                            </div>
                        </a>
                    </div>

                    <div class="col-12 col-sm-6 col-xl-3">
                        <a class="card card-relatorio h-100 text-decoration-none border-0 shadow-sm"
                            href="visualiza-relatorio.php?tipo=passagens">
                            <div class="card-body text-center py-4">
                                <span class="d-inline-flex align-items-center justify-content-center bg-primary-subtle text-primary rounded-3 mb-3 p-3">
                                    <i class="bi bi-ticket-perforated fs-3"></i>
                                </span>

                                <h2 class="h6 mb-0 text-body">
                                    Relatórios de passagens
                                </h2>
                            </div>
                        </a>
                    </div>

                    <div class="col-12 col-sm-6 col-xl-3">
                        <a class="card card-relatorio h-100 text-decoration-none border-0 shadow-sm"
                            href="visualiza-relatorio.php?tipo=manutencao">
                            <div class="card-body text-center py-4">
                                <span class="d-inline-flex align-items-center justify-content-center bg-primary-subtle text-primary rounded-3 mb-3 p-3">
                                    <i class="bi bi-tools fs-3"></i>
                                </span>

                                <h2 class="h6 mb-0 text-body">
                                    Relatórios de manutenção
                                </h2>
                            </div>
                        </a>
                    </div>

                    <div class="col-12 col-sm-6 col-xl-3">
                        <a class="card card-relatorio h-100 text-decoration-none border-0 shadow-sm"
                            href="visualiza-relatorio.php?tipo=carga">
                            <div class="card-body text-center py-4">
                                <span class="d-inline-flex align-items-center justify-content-center bg-primary-subtle text-primary rounded-3 mb-3 p-3">
                                    <i class="bi bi-box-seam fs-3"></i>
                                </span>

                                <h2 class="h6 mb-0 text-body">
                                    Relatórios de carga
                                </h2>
                            </div>
                        </a>
                    </div>

                </div>

            </main>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"></script>

</body>
</html>