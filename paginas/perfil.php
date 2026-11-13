<?php

require_once "../assets/php/proteger.php";
require_once "../assets/php/permissao.php";
require_once "../assets/php/cabecalho.php";
require_once "../assets/php/menu.php";

if (!temPapel(['gestor', 'maquinista', 'cliente'])) {
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
    <title>Meu Perfil — Ferrovias</title>
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
                        <h1 class="h3 mb-0">Meu Perfil</h1>
                        <p class="text-secondary mb-0 small">Dados da conta</p>
                    </div>
                </div>

                <div class="row g-3">

                    <div class="col-12 col-lg-4">
                        <div class="card border-0 shadow-sm h-100">
                            <div class="card-body text-center">
                                <span class="d-inline-flex align-items-center justify-content-center bg-body-secondary text-secondary rounded-circle mb-3 p-5">
                                    <i class="bi bi-person fs-1"></i>
                                </span>

                                <div class="mb-3 text-start">
                                    <label for="input-nome" class="form-label">Nome</label>
                                    <div class="input-group">
                                        <input type="text" class="form-control" id="input-nome" value="Nome" readonly>
                                        <button class="btn btn-outline-secondary" type="button" aria-label="Editar nome">
                                            <i class="bi bi-pencil"></i>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-12 col-lg-8">
                        <div class="card border-0 shadow-sm h-100">
                            <div class="card-header bg-white border-0 pt-3">
                                <h2 class="h5 mb-0">Dados de contato</h2>
                            </div>

                            <div class="card-body">
                                <form>

                                    <div class="mb-3">
                                        <label for="emailPerfil" class="form-label">E-mail</label>
                                        <input type="email" class="form-control" id="emailPerfil" name="email" placeholder="seu@email.com">
                                    </div>

                                    <div class="mb-3">
                                        <label for="telefonePerfil" class="form-label">Telefone</label>
                                        <input type="tel" class="form-control" id="telefonePerfil" name="telefone" placeholder="(00) 00000-0000">
                                    </div>

                                    <div class="mb-4">
                                        <label for="enderecoPerfil" class="form-label">Endereço</label>
                                        <input type="text" class="form-control" id="enderecoPerfil" name="endereco" placeholder="Rua, número, bairro, cidade">
                                    </div>

                                    <div class="d-flex flex-column flex-sm-row gap-2">
                                        <button type="submit" class="btn btn-primary flex-fill">Salvar alterações</button>
                                        <a href="../index.php" class="btn btn-outline-danger flex-fill">Sair</a>
                                    </div>

                                </form>
                            </div>
                        </div>
                    </div>

                </div>

            </main>

        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"></script>

</body>
</html>