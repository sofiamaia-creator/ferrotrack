<?php

require_once "../assets/php/proteger.php";
require_once "../assets/php/permissao.php";
require_once "../assets/php/cabecalho.php";
require_once "../assets/php/menu.php";

if (!temPapel(['gestor'])) {
    http_response_code(403);
    exit("Acesso negado.");
}

?>

<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Monitoramento de Cargas — FerroTrack</title>

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

                    <div>
                        <h1 class="h3 mb-0">Monitoramento de Cargas</h1>
                        <p class="text-secondary mb-0 small">
                            Acompanhamento de pontos de partida e destino
                        </p>
                    </div>

                    <a href="dashboard.php" class="btn btn-outline-secondary">
                        <i class="bi bi-arrow-left me-1"></i>
                        Voltar ao Dashboard Geral
                    </a>

                </div>

                <div class="row g-3">

                    <div class="col-12">
                        <div class="card border-0 shadow-sm">
                            <div class="card-body">

                                <form class="row g-2 align-items-end">

                                    <div class="col-12 col-md-8 col-lg-9">
                                        <label for="buscaCarga" class="form-label">
                                            Buscar carga
                                        </label>

                                        <input
                                            type="search"
                                            class="form-control"
                                            id="buscaCarga"
                                            placeholder="Digite o nome da carga ou do ponto"
                                        >
                                    </div>

                                    <div class="col-12 col-md-4 col-lg-3 d-grid">
                                        <button type="submit" class="btn btn-primary">
                                            <i class="bi bi-search me-1"></i>
                                            Pesquisar
                                        </button>
                                    </div>

                                </form>

                            </div>
                        </div>
                    </div>

                    <div class="col-12">
                        <div class="card border-0 shadow-sm">
                            <div class="card-body">

                                <div class="row align-items-center g-3 text-center text-md-start">

                                    <div class="col-12 col-md-5">
                                        <p class="text-secondary small mb-1">
                                            Ponto de partida
                                        </p>

                                        <p class="h5 mb-0">Joinville</p>
                                    </div>

                                    <div class="col-12 col-md-2 text-center">
                                        <i class="bi bi-arrow-right fs-3 text-secondary d-none d-md-inline"></i>
                                        <i class="bi bi-arrow-down fs-3 text-secondary d-md-none"></i>
                                    </div>

                                    <div class="col-12 col-md-5">
                                        <p class="text-secondary small mb-1">
                                            Ponto de destino / carga
                                        </p>

                                        <p class="h5 mb-0">Curitiba</p>
                                    </div>

                                </div>

                            </div>
                        </div>
                    </div>

                    <div class="col-12">
                        <a href="adicionar-rota.php" class="btn btn-primary">
                            <i class="bi bi-plus-lg me-1"></i>
                            Adicionar rota
                        </a>
                    </div>

                </div>

            </main>

        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"></script>

</body>
</html>
