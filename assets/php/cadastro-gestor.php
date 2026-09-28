<?php

require_once "conexao.php";

$erro = "";
$sucesso = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $nome = trim($_POST['nome'] ?? '');
    $nascimento = $_POST['nascimento'] ?? '';
    $email = trim($_POST['email'] ?? '');
    $cpf = preg_replace('/\D/', '', $_POST['cpf'] ?? '');

    $matricula = trim($_POST['matricula'] ?? '');
    $empresa = trim($_POST['empresa'] ?? '');
    $regiao = trim($_POST['regiao'] ?? '');
    $turno = $_POST['turno'] ?? '';
    $certificacao = trim($_POST['certificacao'] ?? '');

    $login = trim($_POST['login'] ?? '');
    $senha = $_POST['senha'] ?? '';
    $confirmarSenha = $_POST['confirmarSenha'] ?? '';

    
    if (
        empty($nome) ||
        empty($email) ||
        empty($cpf) ||
        empty($matricula) ||
        empty($empresa) ||
        empty($certificacao) ||
        empty($login) ||
        empty($senha) ||
        empty($confirmarSenha)
    ) {

        $erro = "Preencha todos os campos obrigatórios.";

    } elseif (strlen($cpf) !== 11) {

        $erro = "Digite um CPF válido.";

    } elseif (!ctype_digit($matricula)) {

        $erro = "A matrícula deve conter apenas números.";

    } elseif (strlen($senha) < 6) {

        $erro = "A senha deve ter pelo menos 6 caracteres.";

    } elseif ($senha !== $confirmarSenha) {

        $erro = "As senhas não coincidem.";

    } else {

        
        $sql = "SELECT id_usuario
                FROM usuarios
                WHERE login = ?";

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

            $erro = "Erro ao verificar o usuário.";

        }

        if (empty($erro)) {

            $sql = "SELECT gestor_id
                    FROM gestor
                    WHERE cpf_gestor = ?";

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


        if (empty($erro)) {

            $matriculaNumero = (int) $matricula;

            $sql = "SELECT gestor_id
                    FROM gestor
                    WHERE matricula_gestor = ?";

            $stmt = $conexao->prepare($sql);

            if ($stmt) {

                $stmt->bind_param("i", $matriculaNumero);
                $stmt->execute();

                $resultado = $stmt->get_result();

                if ($resultado->num_rows > 0) {
                    $erro = "Esta matrícula já está cadastrada.";
                }

                $stmt->close();

            } else {

                $erro = "Erro ao verificar a matrícula.";

            }
        }


        if (empty($erro)) {

            $senhaHash = password_hash($senha, PASSWORD_DEFAULT);

            $conexao->begin_transaction();

            try {

                
                $sql = "INSERT INTO usuarios
                        (login, senha, papel, ativo)
                        VALUES (?, ?, 'gestor', TRUE)";

                $stmt = $conexao->prepare($sql);

                if (!$stmt) {
                    throw new Exception("Erro ao preparar usuário.");
                }

                $stmt->bind_param(
                    "ss",
                    $login,
                    $senhaHash
                );

                if (!$stmt->execute()) {
                    throw new Exception("Erro ao cadastrar usuário.");
                }

               
                $id_usuario = $conexao->insert_id;

                $stmt->close();


                $sql = "INSERT INTO gestor
                        (
                            id_usuario,
                            nome_gestor,
                            data_nascimento_gestor,
                            email_gestor,
                            matricula_gestor,
                            cpf_gestor,
                            empresa_ferroviaria_gestor,
                            regiao_atuacao_gestor,
                            turno_gestor,
                            certificacao_tecnica_gestor
                        )
                        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

                $stmt = $conexao->prepare($sql);

                if (!$stmt) {
                    throw new Exception("Erro ao preparar gestor.");
                }

                $stmt->bind_param(
                    "isssisssss",
                    $id_usuario,
                    $nome,
                    $nascimento,
                    $email,
                    $matriculaNumero,
                    $cpf,
                    $empresa,
                    $regiao,
                    $turno,
                    $certificacao
                );

                if (!$stmt->execute()) {
                    throw new Exception("Erro ao cadastrar gestor.");
                }

                $stmt->close();


                $conexao->commit();

                $sucesso = "Cadastro de gestor realizado com sucesso!";

            } catch (Exception $e) {

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

    <title>Cadastro de Gestor — Ferrovias</title>

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

                    <div class="text-center mb-4">

                        <img
                            src="assets/img/logo.png"
                            alt="Ferrovias"
                            width="64"
                            height="64"
                            class="object-fit-contain mb-3"
                        >

                        <h1 class="h3 mb-0">
                            Cadastro de Gestor
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

                                <a
                                    href="login.php"
                                    class="btn btn-success btn-sm"
                                >
                                    Ir para o login
                                </a>

                            </div>

                        </div>

                    <?php endif; ?>


                    <?php if (!$sucesso): ?>

                    <form method="POST" action="">

                        <div class="row g-3">

                           

                            <div class="col-12">

                                <label
                                    for="nome"
                                    class="form-label"
                                >
                                    Nome
                                </label>

                                <input
                                    type="text"
                                    class="form-control"
                                    id="nome"
                                    name="nome"
                                    placeholder="Nome completo"
                                    required
                                >

                            </div>


                            <div class="col-12 col-md-6">

                                <label
                                    for="nascimento"
                                    class="form-label"
                                >
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

                                <label
                                    for="cpf"
                                    class="form-label"
                                >
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


                            <div class="col-12">

                                <label
                                    for="email"
                                    class="form-label"
                                >
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

                                <label
                                    for="matricula"
                                    class="form-label"
                                >
                                    Matrícula profissional
                                </label>

                                <input
                                    type="text"
                                    class="form-control"
                                    id="matricula"
                                    name="matricula"
                                    placeholder="Matrícula profissional"
                                    required
                                >

                            </div>


                            <div class="col-12 col-md-6">

                                <label
                                    for="empresa"
                                    class="form-label"
                                >
                                    Empresa ferroviária
                                </label>

                                <input
                                    type="text"
                                    class="form-control"
                                    id="empresa"
                                    name="empresa"
                                    placeholder="Empresa ferroviária"
                                    required
                                >

                            </div>


                            <div class="col-12 col-md-6">

                                <label
                                    for="regiao"
                                    class="form-label"
                                >
                                    Região de atuação
                                </label>

                                <input
                                    type="text"
                                    class="form-control"
                                    id="regiao"
                                    name="regiao"
                                    placeholder="Região de atuação"
                                >

                            </div>


                            <div class="col-12 col-md-6">

                                <label
                                    for="turno"
                                    class="form-label"
                                >
                                    Turno
                                </label>

                                <select
                                    class="form-select"
                                    id="turno"
                                    name="turno"
                                >

                                    <option value="" selected>
                                        Turno
                                    </option>

                                    <option value="Matutino">
                                        Matutino
                                    </option>

                                    <option value="Vespertino">
                                        Vespertino
                                    </option>

                                    <option value="Noturno">
                                        Noturno
                                    </option>

                                </select>

                            </div>


                            <div class="col-12">

                                <label
                                    for="certificacao"
                                    class="form-label"
                                >
                                    Certificação técnica
                                </label>

                                <input
                                    type="text"
                                    class="form-control"
                                    id="certificacao"
                                    name="certificacao"
                                    placeholder="Certificação técnica"
                                    required
                                >

                            </div>


                            <div class="col-12">

                                <label
                                    for="login"
                                    class="form-label"
                                >
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


                            <div class="col-12 col-md-6">

                                <label
                                    for="senhaGestor"
                                    class="form-label"
                                >
                                    Senha
                                </label>

                                <input
                                    type="password"
                                    class="form-control"
                                    id="senhaGestor"
                                    name="senha"
                                    placeholder="Senha"
                                    minlength="6"
                                    required
                                >

                            </div>


                            <div class="col-12 col-md-6">

                                <label
                                    for="confirmarSenha"
                                    class="form-label"
                                >
                                    Confirmar senha
                                </label>

                                <input
                                    type="password"
                                    class="form-control"
                                    id="confirmarSenha"
                                    name="confirmarSenha"
                                    placeholder="Confirmar senha"
                                    minlength="6"
                                    required
                                >

                            </div>

                        </div>


                        <div class="d-grid mt-4">

                            <button
                                type="submit"
                                class="btn btn-primary btn-lg"
                            >
                                Cadastrar
                            </button>

                        </div>

                    </form>

                    <?php endif; ?>


                    <hr class="my-4">


                    <p class="text-center mb-0 small">

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