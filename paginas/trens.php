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
  <title>Trens — FerroTrack</title>

  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
  <link rel="stylesheet" href="../paginas/cabecalho.css">

  <style>
    .layout-principal {
      display: flex;
      align-items: flex-start;
      min-height: calc(100vh - 64px);
    }

    .layout-principal>aside {
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
      .layout-principal>aside {
        flex-basis: 16.666667%;
        max-width: 16.666667%;
      }
    }

    @media (max-width: 991.98px) {
      .layout-principal>aside {
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
            <h1 class="h3 mb-0">Trens</h1>
            <p class="text-secondary mb-0 small">Escolha uma opção</p>
          </div>
        </div>

        <div class="row g-3">

          <div class="col-12 col-md-6 col-xl-4">
            <a class="card h-100 text-decoration-none border-0 shadow-sm"
              href="trens-cadastrados.php">

              <div class="card-body d-flex align-items-center gap-3">

                <span class="d-inline-flex align-items-center justify-content-center bg-primary-subtle text-primary rounded-3 flex-shrink-0 p-3">
                  <i class="bi bi-list-ul fs-4"></i>
                </span>

                <span class="flex-grow-1 fw-semibold text-body">
                  Lista de trens cadastrados
                </span>

                <i class="bi bi-chevron-right text-secondary"></i>

              </div>
            </a>
          </div>

          <div class="col-12 col-md-6 col-xl-4">
            <a class="card h-100 text-decoration-none border-0 shadow-sm"
              href="trens-cadastrados.php">

              <div class="card-body d-flex align-items-center gap-3">

                <span class="d-inline-flex align-items-center justify-content-center bg-primary-subtle text-primary rounded-3 flex-shrink-0 p-3">
                  <i class="bi bi-info-circle fs-4"></i>
                </span>

                <span class="flex-grow-1 fw-semibold text-body">
                  Lista de informações
                </span>

                <i class="bi bi-chevron-right text-secondary"></i>

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