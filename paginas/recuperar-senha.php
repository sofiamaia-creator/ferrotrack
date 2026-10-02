<?php

require_once "../assets/php/proteger.php";
require_once "../assets/php/permissao.php";
require_once "../assets/php/cabecalho.php";

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
    <title>Recuperar senha — Ferrovias</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../paginas/cabecalho.css">
</head>

<body class="bg-body-tertiary">
<?php gerarCabecalho(); ?>
    <main class="container min-vh-100 d-flex align-items-center justify-content-center py-5">
      <div class="row justify-content-center w-100">
        <div class="col-md-8 col-lg-6 col-xl-5">

          <div class="card shadow-sm border-0">
            <div class="card-body p-4 p-md-5">

              <div class="text-center mb-4">
                <img src="../assets/img/logo.png" alt="Ferrovias" width="64" height="64" class="object-fit-contain mb-3">
                <h1 class="h3 mb-2">Recuperar senha</h1>
                <p class="text-secondary mb-0">
                  Informe o e-mail cadastrado e enviaremos as instruções de recuperação.
                </p>
              </div>

              <form>

                <div class="mb-4">
                  <label for="emailRecuperacao" class="form-label">E-mail</label>
                  <div class="input-group">
                    <span class="input-group-text"><i class="bi bi-envelope"></i></span>
                    <input type="email" class="form-control" id="emailRecuperacao" placeholder="seu@email.com">
                  </div>
                </div>

                <div class="d-grid">
                  <button type="submit" class="btn btn-primary btn-lg">Enviar instruções</button>
                </div>

              </form>

              <hr class="my-4">

              <p class="text-center mb-0 small">
                <a href="../index.php">Voltar para o login</a>
              </p>

            </div>
          </div>

        </div>
      </div>
    </main>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>
