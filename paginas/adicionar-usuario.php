<?php

require_once "../assets/php/proteger.php";
require_once "../assets/php/permissao.php";
require_once "../assets/php/cabecalho.php";

if (!temPapel(['gestor'])) {
    echo "Acesso negado.";
    exit;
}
?>
<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Adicionar Usuário — Ferrovias</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../paginas/cabecalho.css">
</head>

<body class="bg-body-tertiary">

    <!-- Barra superior: fixa em todas as telas -->
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
          <li class="nav-item">
            <a class="nav-link d-flex align-items-center gap-2 rounded px-3 text-body" href="adicionar-sensor.php">
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
            <a class="nav-link d-flex align-items-center gap-2 rounded px-3 active bg-primary text-white" href="adicionar-usuario.php" aria-current="page">
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
          <li class="nav-item">
            <a class="nav-link d-flex align-items-center gap-2 rounded px-3 text-body" href="adicionar-sensor.php">
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
            <a class="nav-link d-flex align-items-center gap-2 rounded px-3 active bg-primary text-white" href="adicionar-usuario.php" aria-current="page">
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
              <a class="btn btn-outline-secondary btn-sm" href="gerenciamento-usuarios.php" aria-label="Voltar">
                <i class="bi bi-arrow-left"></i>
              </a>
              <div>
                <h1 class="h3 mb-0">Adicionar Usuário</h1>
                
              </div>
            </div>
          </div>

          <div class="row g-3">

            <div class="col-12 col-xl-7">
              <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-white border-0 pt-3 d-flex align-items-center gap-2">
                  <i class="bi bi-person-fill text-primary"></i>
                  <h2 class="h5 mb-0">Informações pessoais</h2>
                </div>
                <div class="card-body">
                  <div class="row g-3">

                    <div class="col-12">
                      <label for="nome" class="form-label">Nome completo</label>
                      <input type="text" class="form-control" id="nome" placeholder="Nome completo">
                    </div>

                    <div class="col-12 col-md-7">
                      <label for="email" class="form-label">E-mail</label>
                      <input type="email" class="form-control" id="email" placeholder="E-mail">
                    </div>

                    <div class="col-12 col-md-5">
                      <label for="telefone" class="form-label">Telefone</label>
                      <input type="tel" class="form-control" id="telefone" placeholder="Telefone">
                    </div>

                    <div class="col-12 col-md-6">
                      <label for="usuario" class="form-label">Nome de usuário</label>
                      <input type="text" class="form-control" id="usuario" placeholder="Digite o nome de usuário">
                    </div>

                    <div class="col-12 col-md-6">
                      <label for="senha" class="form-label">Senha temporária</label>
                      <div class="input-group">
                        <input type="password" class="form-control" id="senha" placeholder="Digite uma senha">
                        <span class="input-group-text"><i class="bi bi-eye"></i></span>
                      </div>
                    </div>

                    <div class="col-12 col-md-6">
                      <label for="cargo" class="form-label">Cargo</label>
                      <select class="form-select" id="cargo">
                        <option selected disabled>Selecione o cargo</option>
                        <option>Cliente</option>
                        <option>Gestor</option>
                        <option>Maquinista</option>
                      </select>
                    </div>

                    <div class="col-12 col-md-6">
                      <label for="status" class="form-label">Status da conta</label>
                      <select class="form-select" id="status">
                        <option>Ativo</option>
                        <option>Inativo</option>
                        <option>Bloqueado</option>
                      </select>
                    </div>

                  </div>
                </div>
              </div>
            </div>

            <div class="col-12 col-xl-5">
              <div class="card border-0 shadow-sm mb-3">
                <div class="card-header bg-white border-0 pt-3">
                  <h2 class="h5 mb-0">Foto do usuário</h2>
                </div>
                <div class="card-body text-center">
                  <label for="foto" class="d-block border border-2 border-dashed rounded-3 p-4 w-100" role="button">
                    <i class="bi bi-camera-fill fs-1 text-secondary d-block mb-2"></i>
                    <span class="text-secondary small">Clique para adicionar ou arraste a imagem</span>
                  </label>
                  <input type="file" class="form-control mt-2" id="foto" accept="image/*">
                </div>
              </div>

              <div class="card border-0 shadow-sm">
                <div class="card-header bg-white border-0 pt-3">
                  <h2 class="h5 mb-0">Permissões de acesso</h2>
                </div>
                <div class="card-body">
                  <div class="form-check">
                    <input class="form-check-input" type="checkbox" id="permissao1">
                    <label class="form-check-label" for="permissao1">Acesso ao Painel de Monitoramento</label>
                  </div>
                  <div class="form-check">
                    <input class="form-check-input" type="checkbox" id="permissao2">
                    <label class="form-check-label" for="permissao2">Visualizar relatórios</label>
                  </div>
                  <div class="form-check">
                    <input class="form-check-input" type="checkbox" id="permissao3">
                    <label class="form-check-label" for="permissao3">Gerenciar estações</label>
                  </div>
                  <div class="form-check">
                    <input class="form-check-input" type="checkbox" id="permissao4">
                    <label class="form-check-label" for="permissao4">Manutenção e alertas</label>
                  </div>
                  <div class="form-check">
                    <input class="form-check-input" type="checkbox" id="permissao5">
                    <label class="form-check-label" for="permissao5">Cadastrar trens</label>
                  </div>
                  <div class="form-check">
                    <input class="form-check-input" type="checkbox" id="permissao6">
                    <label class="form-check-label" for="permissao6">Gerenciar usuários</label>
                  </div>
                  <div class="form-check">
                    <input class="form-check-input" type="checkbox" id="permissao7">
                    <label class="form-check-label" for="permissao7">Fazer edições no sistema</label>
                  </div>
                </div>
              </div>
            </div>

            <div class="col-12">
              <div class="d-flex flex-column flex-sm-row gap-2 justify-content-end">
                <a href="gerenciamento-usuarios.php" class="btn btn-outline-secondary">Cancelar</a>
                <button type="button" class="btn btn-primary" id="btnSalvar">Salvar</button>
              </div>
            </div>

          </div>

        </main>

      </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"></script>
    <script src="../assets/js/adicionarUsuario.js"></script>
</body>

</html>
