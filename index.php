<?php

session_start();

require_once "assets/php/conexao.php";

$erro = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $login = trim($_POST['login'] ?? '');
    $senha = $_POST['senha'] ?? '';

    $sql = "SELECT id_usuario, login, senha, papel
            FROM usuarios
            WHERE login = ?
            AND ativo = TRUE";

    $stmt = $conexao->prepare($sql);

    if ($stmt) {

        $stmt->bind_param("s", $login);
        $stmt->execute();

        $resultado = $stmt->get_result();
        $usuario = $resultado->fetch_assoc();

        if ($usuario && password_verify($senha, $usuario['senha'])) {

            session_regenerate_id(true);

            $_SESSION['id_usuario'] = $usuario['id_usuario'];
            $_SESSION['login'] = $usuario['login'];
            $_SESSION['papel'] = $usuario['papel'];

            header("Location: paginas/dashboard.php");
            exit;

        } else {

            $erro = "Login ou senha incorretos.";
        }

        $stmt->close();

    } else {

        $erro = "Não foi possível realizar o login.";
    }
}

?>

<!DOCTYPE html>
<html lang="pt-BR">

<head>

    <meta charset="UTF-8">

    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>Login — Ferrovias</title>

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

    <main class="container min-vh-100 d-flex align-items-center justify-content-center py-5">

        <div class="row justify-content-center w-100">

            <div class="col-md-8 col-lg-6 col-xl-5">

                <div class="card shadow-sm border-0">

                    <div class="card-body p-4 p-md-5">

                        <!-- LOGO E TÍTULO -->

                        <div class="text-center mb-4">

                            <img
                                src="assets/img/logo.png"
                                alt="Ferrovias"
                                width="72"
                                height="72"
                                class="object-fit-contain mb-3"
                            >

                            <h1 class="h3 mb-2">
                                Seja bem-vindo!
                            </h1>

                            <p class="text-secondary mb-0">
                                Insira seu <strong>nome</strong> e
                                <strong>senha</strong> para prosseguir
                            </p>

                        </div>


                        <!-- FORMULÁRIO DE LOGIN -->

                        <form method="POST" action="">

                            <?php if ($erro): ?>

                                <div class="alert alert-danger" role="alert">
                                    <?= htmlspecialchars($erro) ?>
                                </div>

                            <?php endif; ?>


                            <!-- USUÁRIO -->

                            <div class="mb-3">

                                <label for="usuario" class="form-label">
                                    Nome de usuário
                                </label>

                                <div class="input-group">

                                    <span class="input-group-text">
                                        <i class="bi bi-person"></i>
                                    </span>

                                    <input
                                        type="text"
                                        class="form-control"
                                        id="usuario"
                                        name="login"
                                        placeholder="Nome de usuário"
                                        required
                                    >

                                </div>

                            </div>


                            <!-- SENHA -->

                            <div class="mb-2">

                                <label for="senha" class="form-label">
                                    Senha
                                </label>

                                <div class="input-group">

                                    <span class="input-group-text">
                                        <i class="bi bi-lock"></i>
                                    </span>

                                    <input
                                        type="password"
                                        class="form-control"
                                        id="senha"
                                        name="senha"
                                        placeholder="Senha"
                                        required
                                    >

                                </div>

                            </div>


                            <!-- RECUPERAR SENHA -->

                            <div class="mb-4 text-end">

                                <a
                                    href="paginas/recuperar-senha.html"
                                    class="link-secondary small"
                                >
                                    Esqueceu sua senha?
                                </a>

                            </div>


                            <!-- BOTÕES -->

                            <div class="d-grid gap-2">

                                <button
                                    type="submit"
                                    class="btn btn-primary btn-lg"
                                >
                                    Entrar
                                </button>

                                <button
                                    type="button"
                                    class="btn btn-outline-secondary d-flex align-items-center justify-content-center gap-2"
                                >
                                    <i class="bi bi-google"></i>
                                    Continuar com Google
                                </button>

                            </div>

                        </form>


                        <!-- CADASTRO -->

                        <hr class="my-4">

                        <p class="text-center mb-0 small">

                            Ainda não tem conta?

                            <a href="assets/php/cadastro.php">
                                Faça seu cadastro
                            </a>

                        </p>

                    </div>

                </div>

            </div>

        </div>

    </main>


    <script
        src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js">
    </script>

</body>

</html>