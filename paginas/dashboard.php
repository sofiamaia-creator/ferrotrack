<?php
require_once "../assets/php/proteger.php";
require_once "../assets/php/permissao.php";
require_once "../assets/php/cabecalho.php";
require_once "../assets/php/menu.php";

if (!temPapel(['gestor', 'maquinista', 'cliente'])) {
  echo "Acesso negado.";
  exit;
}
?>

<!DOCTYPE html>

<html lang="pt-BR">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">

  <title>Dashboard Geral — Ferrovias</title>

  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
  <link rel="stylesheet" href="../paginas/cabecalho.css">

  <style>
    /* Estrutura principal */
    .layout-principal {
      display: flex;
      align-items: flex-start;
      min-height: calc(100vh - 64px);
    }

    /* Menu lateral */
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

    /* Conteúdo */
    .conteudo-principal {
      flex: 1 1 0;
      min-width: 0;
      padding: 24px;
    }

    .card {
      border-radius: 8px;
    }

    .imagem-dashboard {
      max-width: 100%;
      height: auto;
      object-fit: contain;
    }

    @media (min-width: 992px) {
      .layout-principal>aside {
        flex-basis: 25%;
        max-width: 25%;
      }
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
  ```

</head>

<body class="bg-body-tertiary">

  ```
  <?php gerarCabecalho(); ?>

  <nav class="navbar navbar-expand-lg bg-white border-bottom sticky-top">
    <div class="container-fluid gap-2">

      <button
        class="btn btn-outline-secondary d-lg-none"
        type="button"
        data-bs-toggle="offcanvas"
        data-bs-target="#menuLateral"
        aria-controls="menuLateral"
        aria-label="Abrir menu">
        <i class="bi bi-list fs-4"></i>
      </button>

      <a class="navbar-brand d-flex align-items-center gap-2 me-auto" href="dashboard.php">
        <img
          src="../assets/img/logo.png"
          alt="Ferrovias"
          width="32"
          height="32"
          class="object-fit-contain">
        <span class="fw-semibold">Ferrovias</span>
      </a>

      <div class="d-flex align-items-center gap-1">

        <button
          class="btn btn-sm btn-outline-secondary border-0 position-relative"
          type="button"
          title="Notificações"
          aria-label="Notificações"
          onclick="window.location.href='alertas.php'">
          <i class="bi bi-bell fs-5"></i>
          <span class="position-absolute top-0 start-100 translate-middle p-1 bg-danger border border-light rounded-circle"></span>
        </button>

        <div class="vr mx-2 d-none d-sm-block"></div>

        <a
          class="btn btn-sm btn-outline-primary d-none d-sm-inline-flex align-items-center gap-1"
          href="perfil.php">
          <i class="bi bi-person-circle"></i>
          <span class="d-none d-md-inline">Minha conta</span>
        </a>

      </div>
    </div>
  </nav>

  <div class="container-fluid px-0">
    <div class="layout-principal">

      <!-- Menu lateral para computador -->
      <?php renderizarMenu(); ?>

      <!-- Menu móvel -->
      <div
        class="offcanvas offcanvas-start d-lg-none"
        tabindex="-1"
        id="menuLateral"
        aria-labelledby="tituloMenu">

        <div class="offcanvas-header border-bottom">
          <h5 class="offcanvas-title" id="tituloMenu">Menu</h5>
          <button
            type="button"
            class="btn-close"
            data-bs-dismiss="offcanvas"
            aria-label="Fechar"></button>
        </div>

        <div class="offcanvas-body">
          <ul class="nav nav-pills flex-column">

            <?php
            $menusMobile = [
              'Painéis' => [
                ['painel-gestor.php', 'bi-speedometer2', 'Painel do Gestor', ['gestor']],
                ['painel-maquinista.php', 'bi-person-badge', 'Painel do Maquinista', ['gestor']],
                ['painel-cliente.php', 'bi-person', 'Painel do Cliente', ['gestor']],
                ['dashboard.php', 'bi-grid-1x2', 'Dashboard Geral', ['gestor', 'maquinista']]
              ],
              'Operação' => [
                ['gestao-rotas.php', 'bi-signpost-split', 'Gestão de Rotas', ['gestor', 'maquinista']],
                ['adicionar-rota.php', 'bi-plus-square', 'Adicionar Rota', ['gestor']],
                ['monitoramento-cargas.php', 'bi-box-seam', 'Monitoramento de Cargas', ['gestor', 'maquinista']],
                ['trens.php', 'bi-train-front', 'Trens', ['gestor', 'maquinista']],
                ['trens-cadastrados.php', 'bi-list-ul', 'Trens Cadastrados', ['gestor', 'maquinista']],
                ['alertas.php', 'bi-bell', 'Alertas e Notificações', ['gestor', 'maquinista', 'cliente']]
              ],
              'Sensores' => [
                ['sensores.php', 'bi-cpu', 'Gerenciar Sensores', ['gestor', 'maquinista']],
                ['adicionar-sensor.php', 'bi-plus-circle', 'Adicionar Sensor', ['gestor']],
                ['editar-sensor.php', 'bi-pencil', 'Editar Sensor', ['gestor']],
                ['remover-sensor.php', 'bi-dash-circle', 'Remover Sensor', ['gestor']]
              ],
              'Relatórios' => [
                ['relatorios.php', 'bi-file-earmark-bar-graph', 'Relatórios', ['gestor', 'maquinista']]
              ],
              'Usuários' => [
                ['usuarios.php', 'bi-people', 'Status de Usuários', ['gestor', 'maquinista']],
                ['gerenciamento-usuarios.php', 'bi-person-gear', 'Gerenciar Usuários', ['gestor']],
                ['adicionar-usuario.php', 'bi-person-plus', 'Adicionar Usuário', ['gestor']],
                ['perfil.php', 'bi-person-circle', 'Meu Perfil', ['gestor', 'maquinista', 'cliente']]
              ],
              'Cliente' => [
                ['pagamento.php', 'bi-credit-card', 'Pagamento', ['gestor', 'maquinista', 'cliente']],
                ['passagens.php', 'bi-ticket-perforated', 'Passagens', ['gestor', 'maquinista', 'cliente']]
              ]
            ];

            $paginaAtual = basename($_SERVER['PHP_SELF']);

            foreach ($menusMobile as $categoria => $itens) {
              $itensPermitidos = array_filter(
                $itens,
                fn($item) => temPapel($item[3])
              );

              if (empty($itensPermitidos)) {
                continue;
              }

              echo '<li class="nav-item mt-3 mb-1">';
              echo '<span class="text-uppercase small fw-semibold text-secondary px-3">';
              echo htmlspecialchars($categoria);
              echo '</span></li>';

              foreach ($itensPermitidos as $item) {
                [$pagina, $icone, $texto] = $item;
                $ativo = $paginaAtual === $pagina;

                $classe = $ativo
                  ? 'active bg-primary text-white'
                  : 'text-body';

                echo '<li class="nav-item">';
                echo '<a class="nav-link d-flex align-items-center gap-2 rounded px-3 ' . $classe . '" href="' . htmlspecialchars($pagina) . '"';

                if ($ativo) {
                  echo ' aria-current="page"';
                }

                echo '>';
                echo '<i class="bi ' . htmlspecialchars($icone) . '"></i>';
                echo '<span>' . htmlspecialchars($texto) . '</span>';
                echo '</a></li>';
              }
            }
            ?>

          </ul>
        </div>
      </div>

      <!-- Conteúdo do dashboard -->
      <main class="conteudo-principal">

        <div class="d-flex flex-wrap align-items-center justify-content-between gap-2 mb-4">
          <div>
            <h1 class="h3 mb-0">Dashboard Geral</h1>
            <p class="text-secondary mb-0 small">
              Visão consolidada da operação
            </p>
          </div>
        </div>

        <div class="row g-3">

          <div class="col-12 col-lg-6">
            <div class="card border-0 shadow-sm h-100">
              <div class="card-body d-flex align-items-center justify-content-between gap-3">
                <div>
                  <h2 class="h4 mb-1">17 °C — Joinville</h2>
                  <p class="text-secondary mb-0">
                    Terça, 09/06 | 0 mm | 20%
                  </p>
                </div>

                <img
                  src="../assets/img/clima.png"
                  alt="Condição do tempo"
                  width="96"
                  height="96"
                  class="imagem-dashboard">
              </div>
            </div>
          </div>

          <div class="col-6 col-lg-3">
            <a
              class="card border-0 shadow-sm h-100 text-decoration-none"
              href="perfil.php">
              <div class="card-body text-center">
                <img
                  src="../assets/img/perfil.png"
                  alt=""
                  width="64"
                  height="64"
                  class="imagem-dashboard mb-2">

                <h3 class="h6 mb-0 text-body">Meu Perfil</h3>
              </div>
            </a>
          </div>

          <div class="col-6 col-lg-3">
            <a
              class="card border-0 shadow-sm h-100 text-decoration-none"
              href="relatorios.php">
              <div class="card-body text-center">
                <img
                  src="../assets/img/documento.png"
                  alt=""
                  width="64"
                  height="64"
                  class="imagem-dashboard mb-2">

                <h3 class="h6 mb-0 text-body">Documentação</h3>
              </div>
            </a>
          </div>

          <div class="col-12">
            <div class="card border-0 shadow-sm">
              <div class="card-body">
                <h2 class="h5 mb-3">Mapa da malha</h2>

                <img
                  src="../assets/img/mapa.png"
                  alt="Mapa da malha ferroviária"
                  class="img-fluid rounded w-100">
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