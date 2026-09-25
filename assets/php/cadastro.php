<?php

require_once "conexao.php";

$erro = "";
$sucesso = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $nome = trim($_POST['nome'] ?? '');
    $nascimento = $_POST['nascimento'] ?? '';
    $email = trim($_POST['email'] ?? '');
    $telefone = trim($_POST['telefone'] ?? '');
    $cpf = preg_replace('/\D/', '', $_POST['cpf'] ?? '');
    $login = trim($_POST['login'] ?? '');
    $senha = $_POST['senha'] ?? '';
    $perfil = $_POST['perfil'] ?? '';

    if (
        empty($nome) ||
        empty($email) ||
        empty($cpf) ||
        empty($login) ||
        empty($senha) ||
        empty($perfil)
    ) {

        $erro = "Preencha todos os campos obrigatórios.";

    } elseif ($perfil !== "cliente") {

        $erro = "Este cadastro é destinado apenas para clientes.";

    } elseif (strlen($cpf) !== 11) {

        $erro = "Digite um CPF válido.";

    } elseif (strlen($senha) < 6) {

        $erro = "A senha deve ter pelo menos 6 caracteres.";

    } else {

        /*
         * Verifica se o login já existe
         */
        $sql = "SELECT id_usuario FROM usuarios WHERE login = ?";

        $stmt = $conexao->prepare($sql);

        if ($stmt) {

            $stmt->bind_param("s", $login);
            $stmt->execute();

            $resultado = $stmt->get_result();

            if ($resultado->num_rows > 0) {

                $erro = "Este nome de usuário já está cadastrado.";

            }

            $stmt->close();

        } else {

            $erro = "Erro ao verificar o cadastro.";

        }


        /*
         * Se não encontrou erro, continua o cadastro
         */
        if (empty($erro)) {

            /*
             * Verifica se o CPF já existe
             */
            $sql = "SELECT cliente_id FROM cliente WHERE cpf_cliente = ?";

            $stmt = $conexao->prepare($sql);

            if ($stmt) {

                $stmt->bind_param("s", $cpf);
                $stmt->execute();

                $resultado = $stmt->get_result();

                if ($resultado->num_rows > 0) {

                    $erro = "Este CPF já está cadastrado.";

                }

                $stmt->close();

            } else {

                $erro = "Erro ao verificar o CPF.";

            }

        }


        /*
         * Cadastro
         */
        if (empty($erro)) {

            /*
             * Criptografa a senha
             */
            $senhaHash = password_hash($senha, PASSWORD_DEFAULT);

            /*
             * Inicia uma transação
             */
            $conexao->begin_transaction();

            try {

                /*
                 * 1 - Cria o usuário
                 */
                $sql = "INSERT INTO usuarios
                        (login, senha, papel, ativo, telefone)
                        VALUES (?, ?, 'cliente', TRUE, ?)";

                $stmt = $conexao->prepare($sql);

                if (!$stmt) {
                    throw new Exception("Erro ao preparar o cadastro do usuário.");
                }

                $stmt->bind_param(
                    "sss",
                    $login,
                    $senhaHash,
                    $telefone
                );

                if (!$stmt->execute()) {
                    throw new Exception("Erro ao cadastrar usuário.");
                }

                /*
                 * Pega o ID do usuário criado
                 */
                $id_usuario = $conexao->insert_id;

                $stmt->close();


                /*
                 * 2 - Cria o cliente
                 */
                $sql = "INSERT INTO cliente
                        (
                            id_usuario,
                            nome_cliente,
                            data_nascimento_cliente,
                            email_cliente,
                            cpf_cliente
                        )
                        VALUES (?, ?, ?, ?, ?)";

                $stmt = $conexao->prepare($sql);

                if (!$stmt) {
                    throw new Exception("Erro ao preparar o cadastro do cliente.");
                }

                $stmt->bind_param(
                    "issss",
                    $id_usuario,
                    $nome,
                    $nascimento,
                    $email,
                    $cpf
                );

                if (!$stmt->execute()) {
                    throw new Exception("Erro ao cadastrar cliente.");
                }

                $stmt->close();


                /*
                 * Confirma tudo
                 */
                $conexao->commit();

                $sucesso = "Cadastro realizado com sucesso! Você já pode fazer login.";

            } catch (Exception $e) {

                /*
                 * Desfaz o cadastro se alguma etapa falhar
                 */
                $conexao->rollback();

                $erro = "Não foi possível realizar o cadastro.";

            }

        }

    }
}

?>

<!DOCTYPE html>
<html lang="pt-BR">

<head>

    <meta charset="UTF-8">

    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>Cadastro — Ferrovias</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet">

    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">

</head>

<body class="bg-body-tertiary">

<main class="container min-vh-100 d-flex align-items-center justify-content-center py-5">

    <div class="row justify-content-center w-100">

        <div class="col-md-8 col-lg-6 col-xl-5">

            <div class="card shadow-sm border-0">

                <div class="card-body p-4 p-md-5">

                    <div class="text-center mb-4">

                        <img
                            src="assets/img/logo.png"
                            alt="Ferrovias"
                            width="64"
                            height="64"
                            class="object-fit-contain mb-3"
                        >

                        <h1 class="h3 mb-0">
                            Faça seu cadastro
                        </h1>

                    </div>


                    <?php if ($erro): ?>

                        <div class="alert alert-danger" role="alert">
                            <?= htmlspecialchars($erro) ?>
                        </div>

                    <?php endif; ?>


                    <?php if ($sucesso): ?>

                        <div class="alert alert-success" role="alert">

                            <?= htmlspecialchars($sucesso) ?>

                            <div class="mt-2">

                                <a href="login.php" class="btn btn-success btn-sm">
                                    Ir para o login
                                </a>

                            </div>

                        </div>

                    <?php endif; ?>


                    <?php if (!$sucesso): ?>

                    <form method="POST" action="" enctype="multipart/form-data">

                        <div class="text-center mb-4">

                            <div class="d-inline-flex align-items-center justify-content-center bg-body-secondary rounded-circle mb-2 p-4">

                                <i class="bi bi-person fs-1 text-secondary"></i>

                            </div>

                            <div>

                                <label for="fotoPerfil" class="form-label">
                                    Foto de perfil
                                </label>

                                <input
                                    type="file"
                                    class="form-control"
                                    id="fotoPerfil"
                                    name="foto_perfil"
                                    accept="image/*"
                                >

                            </div>

                        </div>


                        <div class="row g-3">

                            <div class="col-12 col-md-6">

                                <label for="nome" class="form-label">
                                    Nome
                                </label>

                                <input
                                    type="text"
                                    class="form-control"
                                    id="nome"
                                    name="nome"
                                    placeholder="Nome"
                                    required
                                >

                            </div>


                            <div class="col-12 col-md-6">

                                <label for="nascimento" class="form-label">
                                    Data de nascimento
                                </label>

                                <input
                                    type="date"
                                    class="form-control"
                                    id="nascimento"
                                    name="nascimento"
                                >

                            </div>


                            <div class="col-12 col-md-6">

                                <label for="email" class="form-label">
                                    E-mail
                                </label>

                                <input
                                    type="email"
                                    class="form-control"
                                    id="email"
                                    name="email"
                                    placeholder="E-mail"
                                    required
                                >

                            </div>


                            <div class="col-12 col-md-6">

                                <label for="telefone" class="form-label">
                                    Telefone
                                </label>

                                <input
                                    type="tel"
                                    class="form-control"
                                    id="telefone"
                                    name="telefone"
                                    placeholder="Telefone"
                                >

                            </div>


                            <div class="col-12 col-md-6">

                                <label for="cpf" class="form-label">
                                    CPF
                                </label>

                                <input
                                    type="text"
                                    class="form-control"
                                    id="cpf"
                                    name="cpf"
                                    placeholder="CPF"
                                    maxlength="14"
                                    required
                                >

                            </div>


                            <div class="col-12 col-md-6">

                                <label for="perfil" class="form-label">
                                    Perfil
                                </label>

                                <select
                                    class="form-select"
                                    id="perfil"
                                    name="perfil"
                                    required
                                >

                                    <option value="" selected disabled>
                                        Perfil
                                    </option>

                                    <option value="cliente">
                                        Cliente
                                    </option>

                                </select>

                            </div>


                            <div class="col-12">

                                <label for="login" class="form-label">
                                    Nome de usuário
                                </label>

                                <input
                                    type="text"
                                    class="form-control"
                                    id="login"
                                    name="login"
                                    placeholder="Nome de usuário"
                                    required
                                >

                            </div>


                            <div class="col-12">

                                <label for="senha" class="form-label">
                                    Senha
                                </label>

                                <input
                                    type="password"
                                    class="form-control"
                                    id="senha"
                                    name="senha"
                                    placeholder="Senha"
                                    minlength="6"
                                    required
                                >

                                <div class="form-text">
                                    A senha deve ter pelo menos 6 caracteres.
                                </div>

                            </div>

                        </div>


                        <div class="d-grid mt-4">

                            <button
                                type="submit"
                                class="btn btn-primary btn-lg"
                            >
                                Salvar
                            </button>

                        </div>

                    </form>

                    <?php endif; ?>


                    <hr class="my-4">


                    <p class="text-center mb-0 small">

                        Já tem conta?

                        <a href="login.php">
                            Voltar para o login
                        </a>

                    </p>

                </div>

            </div>

        </div>

    </div>

</main>


<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"></script>

</body>

</html>