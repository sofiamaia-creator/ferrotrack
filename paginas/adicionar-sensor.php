<?php

require_once "../assets/php/proteger.php";
require_once "../assets/php/permissao.php";
require_once "../assets/php/cabecalho.php";

if (!temPapel(['gestor'])) {
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
    <title>Adicionar Sensor — Ferrovias</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../paginas/cabecalho.css">
</head>

<body class="bg-body-tertiary">
<?php gerarCabecalho(); ?>
    
    <nav class="navbar navbar-expand-lg bg-white border-bottom sticky-top">
      <div class="container-fluid gap-2">

        <button class="btn btn-outline-secondary d-lg-none" type="button"
                data-bs-toggle="offcanvas" data-bs-target="#menuLateral"
                aria-controls="menuLateral" aria-label="Abrir menu">
          <i class="bi bi-list fs-4"></i>
        </button>

        <a class="navbar-brand d-flex align-items-center gap-2 me-auto" href="painel-gestor.php">
          <img src="../assets/img/logo.png" alt="Ferrovias" width="32" height="32" class="object-fit-contain">
          <span class="fw-semibold">Ferrovias</span>
        </a>

        <div class="d-flex align-items-center gap-1">
          <button class="btn btn-sm btn-outline-secondary border-0" type="button" title="Acessibilidade" aria-label="Acessibilidade">
            <i class="bi bi-universal-access fs-5"></i>
          </button>
          <button class="btn btn-sm btn-outline-secondary border-0" type="button" title="Configurações" aria-label="Configurações">
            <i class="bi bi-gear fs-5"></i>
          </button>
          <button class="btn btn-sm btn-outline-secondary border-0" type="button" title="Modo escuro" aria-label="Modo escuro">
            <i class="bi bi-moon-fill fs-5"></i>
          </button>
          <button class="btn btn-sm btn-outline-secondary border-0 position-relative" type="button" title="Notificações" aria-label="Notificações">
            <i class="bi bi-bell fs-5"></i>
            <span class="position-absolute top-0 start-100 translate-middle p-1 bg-danger border border-light rounded-circle"></span>
          </button>
          <div class="vr mx-2 d-none d-sm-block"></div>
          <a class="btn btn-sm btn-outline-primary d-none d-sm-inline-flex align-items-center gap-1" href="perfil.php">
            <i class="bi bi-person-circle"></i>
            <span class="d-none d-md-inline">Minha conta</span>
          </a>
        </div>

      </div>
    </nav>

    <div class="container-fluid">
      <div class="row">

        <!-- Menu lateral: fixo a partir de lg, gaveta no celular e no tablet -->
        <aside class="col-lg-3 col-xl-2 d-none d-lg-block bg-white border-end vh-100 position-sticky top-0 p-0">
          <nav class="p-3 overflow-auto h-100">
            <ul class="nav nav-pills flex-column">
          <li class="nav-item mt-3 mb-1">
            <span class="text-uppercase small fw-semibold text-secondary px-3">Painéis</span>
          </li>
          <li class="nav-item">
            <a class="nav-link d-flex align-items-center gap-2 rounded px-3 text-body" href="painel-gestor.php">
              <i class="bi bi-speedometer2"></i>
              <span>Painel do Gestor</span>
            </a>
          </li>
          <li class="nav-item">
            <a class="nav-link d-flex align-items-center gap-2 rounded px-3 text-body" href="painel-maquinista.php">
              <i class="bi bi-person-badge"></i>
              <span>Painel do Maquinista</span>
            </a>
          </li>
          <li class="nav-item">
            <a class="nav-link d-flex align-items-center gap-2 rounded px-3 text-body" href="painel-cliente.php">
              <i class="bi bi-person"></i>
              <span>Painel do Cliente</span>
            </a>
          </li>
          <li class="nav-item">
            <a class="nav-link d-flex align-items-center gap-2 rounded px-3 text-body" href="dashboard.php">
              <i class="bi bi-grid-1x2"></i>
              <span>Dashboard Geral</span>
            </a>
          </li>
          <li class="nav-item mt-3 mb-1">
            <span class="text-uppercase small fw-semibold text-secondary px-3">Operação</span>
          </li>
          <li class="nav-item">
            <a class="nav-link d-flex align-items-center gap-2 rounded px-3 text-body" href="gestao-rotas.php">
              <i class="bi bi-signpost-split"></i>
              <span>Gestão de Rotas</span>
            </a>
          </li>
          <li class="nav-item">
            <a class="nav-link d-flex align-items-center gap-2 rounded px-3 text-body" href="adicionar-rota.php">
              <i class="bi bi-plus-square"></i>
              <span>Adicionar Rota</span>
            </a>
          </li>
          <li class="nav-item">
            <a class="nav-link d-flex align-items-center gap-2 rounded px-3 text-body" href="monitoramento-cargas.php">
              <i class="bi bi-box-seam"></i>
              <span>Monitoramento de Cargas</span>
            </a>
          </li>
          <li class="nav-item">
            <a class="nav-link d-flex align-items-center gap-2 rounded px-3 text-body" href="trens.php">
              <i class="bi bi-train-front"></i>
              <span>Trens</span>
            </a>
          </li>
          <li class="nav-item">
            <a class="nav-link d-flex align-items-center gap-2 rounded px-3 text-body" href="trens-cadastrados.php">
              <i class="bi bi-list-ul"></i>
              <span>Trens Cadastrados</span>
            </a>
          </li>
          <li class="nav-item">
            <a class="nav-link d-flex align-items-center gap-2 rounded px-3 text-body" href="alertas.php">
              <i class="bi bi-bell"></i>
              <span>Alertas e Notificações</span>
            </a>
          </li>
          <li class="nav-item mt-3 mb-1">
            <span class="text-uppercase small fw-semibold text-secondary px-3">Sensores</span>
          </li>
          <li class="nav-item">
            <a class="nav-link d-flex align-items-center gap-2 rounded px-3 text-body" href="sensores.php">
              <i class="bi bi-cpu"></i>
              <span>Gerenciar Sensores</span>
            </a>
          </li>
          <?php if (temPapel(['gestor'])): ?>
          <li class="nav-item">
            <a class="nav-link d-flex align-items-center gap-2 rounded px-3 active bg-primary text-white" href="adicionar-sensor.php" aria-current="page">
              <i class="bi bi-plus-circle"></i>
              <span>Adicionar Sensor</span>
            </a>
          </li>
          <li class="nav-item">
            <a class="nav-link d-flex align-items-center gap-2 rounded px-3 text-body" href="editar-sensor.php">
              <i class="bi bi-pencil"></i>
              <span>Editar Sensor</span>
            </a>
          </li>
          <li class="nav-item">
            <a class="nav-link d-flex align-items-center gap-2 rounded px-3 text-body" href="remover-sensor.php">
              <i class="bi bi-dash-circle"></i>
              <span>Remover Sensor</span>
            </a>
          </li>
          <?php endif; ?>
          <li class="nav-item mt-3 mb-1">
            <span class="text-uppercase small fw-semibold text-secondary px-3">Relatórios</span>
          </li>
          <li class="nav-item">
            <a class="nav-link d-flex align-items-center gap-2 rounded px-3 text-body" href="relatorios.php">
              <i class="bi bi-file-earmark-bar-graph"></i>
              <span>Relatórios</span>
            </a>
          </li>
          <li class="nav-item mt-3 mb-1">
            <span class="text-uppercase small fw-semibold text-secondary px-3">Usuários</span>
          </li>
          <li class="nav-item">
            <a class="nav-link d-flex align-items-center gap-2 rounded px-3 text-body" href="usuarios.php">
              <i class="bi bi-people"></i>
              <span>Status de Usuários</span>
            </a>
          </li>
          <li class="nav-item">
            <a class="nav-link d-flex align-items-center gap-2 rounded px-3 text-body" href="gerenciamento-usuarios.php">
              <i class="bi bi-person-gear"></i>
              <span>Gerenciar Usuários</span>
            </a>
          </li>
          <li class="nav-item">
            <a class="nav-link d-flex align-items-center gap-2 rounded px-3 text-body" href="adicionar-usuario.php">
              <i class="bi bi-person-plus"></i>
              <span>Adicionar Usuário</span>
            </a>
          </li>
          <li class="nav-item">
            <a class="nav-link d-flex align-items-center gap-2 rounded px-3 text-body" href="perfil.php">
              <i class="bi bi-person-circle"></i>
              <span>Meu Perfil</span>
            </a>
          </li>
          <li class="nav-item mt-3 mb-1">
            <span class="text-uppercase small fw-semibold text-secondary px-3">Cliente</span>
          </li>
          <li class="nav-item">
            <a class="nav-link d-flex align-items-center gap-2 rounded px-3 text-body" href="pagamento.php">
              <i class="bi bi-credit-card"></i>
              <span>Pagamento</span>
            </a>
          </li>
          <li class="nav-item">
            <a class="nav-link d-flex align-items-center gap-2 rounded px-3 text-body" href="passagens.php">
              <i class="bi bi-ticket-perforated"></i>
              <span>Passagens</span>
            </a>
          </li>
            </ul>
          </nav>
        </aside>

        <!-- Mesma navegação, em gaveta, para telas menores -->
        <div class="offcanvas offcanvas-start d-lg-none" tabindex="-1" id="menuLateral" aria-labelledby="tituloMenu">
          <div class="offcanvas-header border-bottom">
            <h5 class="offcanvas-title" id="tituloMenu">Menu</h5>
            <button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="Fechar"></button>
          </div>
          <div class="offcanvas-body">
            <ul class="nav nav-pills flex-column">
          <li class="nav-item mt-3 mb-1">
            <span class="text-uppercase small fw-semibold text-secondary px-3">Painéis</span>
          </li>
          <li class="nav-item">
            <a class="nav-link d-flex align-items-center gap-2 rounded px-3 text-body" href="painel-gestor.php">
              <i class="bi bi-speedometer2"></i>
              <span>Painel do Gestor</span>
            </a>
          </li>
          <li class="nav-item">
            <a class="nav-link d-flex align-items-center gap-2 rounded px-3 text-body" href="painel-maquinista.php">
              <i class="bi bi-person-badge"></i>
              <span>Painel do Maquinista</span>
            </a>
          </li>
          <li class="nav-item">
            <a class="nav-link d-flex align-items-center gap-2 rounded px-3 text-body" href="painel-cliente.php">
              <i class="bi bi-person"></i>
              <span>Painel do Cliente</span>
            </a>
          </li>
          <li class="nav-item">
            <a class="nav-link d-flex align-items-center gap-2 rounded px-3 text-body" href="dashboard.php">
              <i class="bi bi-grid-1x2"></i>
              <span>Dashboard Geral</span>
            </a>
          </li>
          <li class="nav-item mt-3 mb-1">
            <span class="text-uppercase small fw-semibold text-secondary px-3">Operação</span>
          </li>
          <li class="nav-item">
            <a class="nav-link d-flex align-items-center gap-2 rounded px-3 text-body" href="gestao-rotas.php">
              <i class="bi bi-signpost-split"></i>
              <span>Gestão de Rotas</span>
            </a>
          </li>
          <li class="nav-item">
            <a class="nav-link d-flex align-items-center gap-2 rounded px-3 text-body" href="adicionar-rota.php">
              <i class="bi bi-plus-square"></i>
              <span>Adicionar Rota</span>
            </a>
          </li>
          <li class="nav-item">
            <a class="nav-link d-flex align-items-center gap-2 rounded px-3 text-body" href="monitoramento-cargas.php">
              <i class="bi bi-box-seam"></i>
              <span>Monitoramento de Cargas</span>
            </a>
          </li>
          <li class="nav-item">
            <a class="nav-link d-flex align-items-center gap-2 rounded px-3 text-body" href="trens.php">
              <i class="bi bi-train-front"></i>
              <span>Trens</span>
            </a>
          </li>
          <li class="nav-item">
            <a class="nav-link d-flex align-items-center gap-2 rounded px-3 text-body" href="trens-cadastrados.php">
              <i class="bi bi-list-ul"></i>
              <span>Trens Cadastrados</span>
            </a>
          </li>
          <li class="nav-item">
            <a class="nav-link d-flex align-items-center gap-2 rounded px-3 text-body" href="alertas.php">
              <i class="bi bi-bell"></i>
              <span>Alertas e Notificações</span>
            </a>
          </li>
          <li class="nav-item mt-3 mb-1">
            <span class="text-uppercase small fw-semibold text-secondary px-3">Sensores</span>
          </li>
          <li class="nav-item">
            <a class="nav-link d-flex align-items-center gap-2 rounded px-3 text-body" href="sensores.php">
              <i class="bi bi-cpu"></i>
              <span>Gerenciar Sensores</span>
            </a>
          </li>
          <?php if (temPapel(['gestor'])): ?>
          <li class="nav-item">
            <a class="nav-link d-flex align-items-center gap-2 rounded px-3 active bg-primary text-white" href="adicionar-sensor.php" aria-current="page">
              <i class="bi bi-plus-circle"></i>
              <span>Adicionar Sensor</span>
            </a>
          </li>
          <li class="nav-item">
            <a class="nav-link d-flex align-items-center gap-2 rounded px-3 text-body" href="editar-sensor.php">
              <i class="bi bi-pencil"></i>
              <span>Editar Sensor</span>
            </a>
          </li>
          <li class="nav-item">
            <a class="nav-link d-flex align-items-center gap-2 rounded px-3 text-body" href="remover-sensor.php">
              <i class="bi bi-dash-circle"></i>
              <span>Remover Sensor</span>
            </a>
          </li>
          <?php endif; ?>
          <li class="nav-item mt-3 mb-1">
            <span class="text-uppercase small fw-semibold text-secondary px-3">Relatórios</span>
          </li>
          <li class="nav-item">
            <a class="nav-link d-flex align-items-center gap-2 rounded px-3 text-body" href="relatorios.php">
              <i class="bi bi-file-earmark-bar-graph"></i>
              <span>Relatórios</span>
            </a>
          </li>
          <li class="nav-item mt-3 mb-1">
            <span class="text-uppercase small fw-semibold text-secondary px-3">Usuários</span>
          </li>
          <li class="nav-item">
            <a class="nav-link d-flex align-items-center gap-2 rounded px-3 text-body" href="usuarios.php">
              <i class="bi bi-people"></i>
              <span>Status de Usuários</span>
            </a>
          </li>
          <li class="nav-item">
            <a class="nav-link d-flex align-items-center gap-2 rounded px-3 text-body" href="gerenciamento-usuarios.php">
              <i class="bi bi-person-gear"></i>
              <span>Gerenciar Usuários</span>
            </a>
          </li>
          <li class="nav-item">
            <a class="nav-link d-flex align-items-center gap-2 rounded px-3 text-body" href="adicionar-usuario.php">
              <i class="bi bi-person-plus"></i>
              <span>Adicionar Usuário</span>
            </a>
          </li>
          <li class="nav-item">
            <a class="nav-link d-flex align-items-center gap-2 rounded px-3 text-body" href="perfil.php">
              <i class="bi bi-person-circle"></i>
              <span>Meu Perfil</span>
            </a>
          </li>
          <li class="nav-item mt-3 mb-1">
            <span class="text-uppercase small fw-semibold text-secondary px-3">Cliente</span>
          </li>
          <li class="nav-item">
            <a class="nav-link d-flex align-items-center gap-2 rounded px-3 text-body" href="pagamento.php">
              <i class="bi bi-credit-card"></i>
              <span>Pagamento</span>
            </a>
          </li>
          <li class="nav-item">
            <a class="nav-link d-flex align-items-center gap-2 rounded px-3 text-body" href="passagens.php">
              <i class="bi bi-ticket-perforated"></i>
              <span>Passagens</span>
            </a>
          </li>
            </ul>
          </div>
        </div>

        <!-- Conteúdo da página -->
        <main class="col-12 col-lg-9 col-xl-10 py-4 px-3 px-md-4">

          <div class="d-flex flex-wrap align-items-center justify-content-between gap-2 mb-4">
            <div class="d-flex align-items-center gap-2">
              <a class="btn btn-outline-secondary btn-sm" href="sensores.php" aria-label="Voltar">
                <i class="bi bi-arrow-left"></i>
              </a>
              <div>
                <h1 class="h3 mb-0">Adicionar Sensor</h1>
                
              </div>
            </div>
          </div>

          <div class="row justify-content-center">
            <div class="col-12 col-lg-8 col-xl-6">
              <div class="card border-0 shadow-sm">
                <div class="card-header bg-white border-0 pt-3">
                  <h2 class="h5 mb-0">Informações</h2>
                </div>
                <div class="card-body p-4">

                  <form>

                    <div class="mb-3">
                      <label for="nomeSensor" class="form-label">Selecione o tipo de sensor</label>
                      <select class="form-select" name="nomeSensor" id="nomeSensor">
                        <option value="sensor1">Sensor - KY-037</option>
                        <option value="sensor2">Sensor - KY-038</option>
                        <option value="sensor3">Sensor - KY-039</option>
                      </select>
                    </div>

                    <div class="mb-3">
                      <label for="local" class="form-label">Selecione o local do sensor</label>
                      <select class="form-select" name="local" id="local">
                        <option value="localsensor1">Trilho 1</option>
                        <option value="localsensor2">Trilho 2</option>
                        <option value="localsensor3">Trilho 3</option>
                      </select>
                    </div>

                    <div class="mb-3">
                      <label for="dataAdicao" class="form-label">Selecione a data de adição</label>
                      <input type="date" class="form-control" id="dataAdicao">
                    </div>

                    <div class="mb-4">
                      <label for="procedimento" class="form-label">Descreva o procedimento</label>
                      <textarea class="form-control" id="procedimento" name="procedimento" rows="4"></textarea>
                    </div>

                    <div class="d-flex flex-column flex-sm-row gap-2">
                      <button type="submit" class="btn btn-primary flex-fill">Adicionar sensor</button>
                      <a href="sensores.php" class="btn btn-outline-secondary flex-fill">Cancelar</a>
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