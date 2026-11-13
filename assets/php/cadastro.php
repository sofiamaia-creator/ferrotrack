<?php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$_SESSION['pagina_atual'] = basename($_SERVER['PHP_SELF']);

require_once "conexao.php";

$erro = "";
$sucesso = "";

function cadastroValorExiste($conexao, $tabela, $campo, $valor) {
    $sql = "SELECT id_usuario FROM $tabela WHERE $campo = ? LIMIT 1";
    $stmt = $conexao->prepare($sql);

    if (!$stmt) {
        throw new Exception("Erro ao verificar os dados do cadastro.");
    }

    $stmt->bind_param("s", $valor);
    $stmt->execute();

    $resultado = $stmt->get_result();
    $existe = $resultado->num_rows > 0;

    $stmt->close();

    return $existe;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $nome = trim($_POST['nome'] ?? '');
    $nascimento = ($_POST['nascimento'] ?? '') ?: null;
    $email = trim($_POST['email'] ?? '');
    $telefone = trim($_POST['telefone'] ?? '');
    $cpf = preg_replace('/\D/', '', $_POST['cpf'] ?? '');
    $login = trim($_POST['login'] ?? '');
    $senha = $_POST['senha'] ?? '';
    $perfil = $_POST['perfil'] ?? '';

    $regiao = trim($_POST['regiao_' . $perfil] ?? '') ?: null;
    $empresa = trim($_POST['empresa_' . $perfil] ?? '');
    $turno = trim($_POST['turno_' . $perfil] ?? '') ?: null;
    $certificacao = trim($_POST['certificacao_' . $perfil] ?? '');
    $matricula = $_POST['matricula'] ?? '';

    if (
        empty($nome) ||
        empty($email) ||
        empty($cpf) ||
        empty($login) ||
        empty($senha) ||
        empty($perfil)
    ) {
        $erro = "Preencha todos os campos obrigatórios.";

    } elseif (!in_array($perfil, ['cliente', 'maquinista', 'gestor'], true)) {
        $erro = "Selecione um perfil válido.";

    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $erro = "Digite um e-mail válido.";

    } elseif (strlen($cpf) !== 11) {
        $erro = "Digite um CPF válido.";

    } elseif (strlen($senha) < 6) {
        $erro = "A senha deve ter pelo menos 6 caracteres.";

    } elseif (
        $perfil === 'maquinista' &&
        (empty($empresa) || empty($certificacao))
    ) {
        $erro = "Informe a empresa ferroviária e a certificação técnica.";

    } elseif (
        $perfil === 'gestor' &&
        (
            empty($empresa) ||
            empty($certificacao) ||
            !ctype_digit((string) $matricula) ||
            (int) $matricula <= 0
        )
    ) {
        $erro = "Informe a matrícula, a empresa ferroviária e a certificação técnica corretamente.";

    } else {

        $transacaoIniciada = false;

        try {

            if (cadastroValorExiste($conexao, 'usuarios', 'login', $login)) {
                throw new Exception("Este nome de usuário já está cadastrado.");
            }

            $tabelas = [
                ['cliente', 'email_cliente', 'cpf_cliente'],
                ['maquinista', 'email_maquinista', 'cpf_maquinista'],
                ['gestor', 'email_gestor', 'cpf_gestor']
            ];

            foreach ($tabelas as $tabela) {

                if (cadastroValorExiste($conexao, $tabela[0], $tabela[1], $email)) {
                    throw new Exception("Este e-mail já está cadastrado.");
                }

                if (cadastroValorExiste($conexao, $tabela[0], $tabela[2], $cpf)) {
                    throw new Exception("Este CPF já está cadastrado.");
                }
            }

            if ($perfil === 'gestor') {

                $matricula = (int) $matricula;

                $stmt = $conexao->prepare(
                    "SELECT gestor_id FROM gestor WHERE matricula_gestor = ? LIMIT 1"
                );

                if (!$stmt) {
                    throw new Exception("Erro ao verificar a matrícula.");
                }

                $stmt->bind_param("i", $matricula);
                $stmt->execute();

                if ($stmt->get_result()->num_rows > 0) {
                    throw new Exception("Esta matrícula já está cadastrada.");
                }

                $stmt->close();
            }

            $senhaHash = password_hash($senha, PASSWORD_DEFAULT);

            $statusAprovacao = $perfil === 'cliente' ? 'aprovado' : 'pendente';

            $conexao->begin_transaction();
            $transacaoIniciada = true;

            $sql = "INSERT INTO usuarios
                    (login, senha, papel, ativo, telefone, status_aprovacao)
                    VALUES (?, ?, ?, TRUE, ?, ?)";

            $stmt = $conexao->prepare($sql);

            if (!$stmt) {
                throw new Exception("Erro ao preparar o cadastro do usuário.");
            }

            $stmt->bind_param(
                "sssss",
                $login,
                $senhaHash,
                $perfil,
                $telefone,
                $statusAprovacao
            );

            if (!$stmt->execute()) {
                throw new Exception("Erro ao cadastrar usuário.");
            }

            $id_usuario = $conexao->insert_id;
            $stmt->close();

            if ($perfil === 'cliente') {

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

            } elseif ($perfil === 'maquinista') {

                $sql = "INSERT INTO maquinista
                        (
                            id_usuario,
                            nome_maquinista,
                            data_nascimento_maquinista,
                            email_maquinista,
                            cpf_maquinista,
                            regiao_atuacao_maquinista,
                            empresa_ferroviaria_maquinista,
                            turno_maquinista,
                            certificacao_tecnica_maquinista
                        )
                        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";

                $stmt = $conexao->prepare($sql);

                if (!$stmt) {
                    throw new Exception("Erro ao preparar o cadastro do maquinista.");
                }

                $stmt->bind_param(
                    "issssssss",
                    $id_usuario,
                    $nome,
                    $nascimento,
                    $email,
                    $cpf,
                    $regiao,
                    $empresa,
                    $turno,
                    $certificacao
                );

            } else {

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
                    throw new Exception("Erro ao preparar o cadastro do gestor.");
                }

                $stmt->bind_param(
                    "isssisssss",
                    $id_usuario,
                    $nome,
                    $nascimento,
                    $email,
                    $matricula,
                    $cpf,
                    $empresa,
                    $regiao,
                    $turno,
                    $certificacao
                );
            }

            if (!$stmt->execute()) {
                throw new Exception("Erro ao cadastrar os dados do perfil.");
            }

            $stmt->close();

            $conexao->commit();
            $transacaoIniciada = false;

            if ($perfil === 'cliente') {
                $sucesso = "Cadastro realizado com sucesso! Você já pode fazer login.";
            } else {
                $sucesso = "Solicitação enviada com sucesso! Aguarde a aprovação de um gestor para acessar o sistema.";
            }

        } catch (Exception $e) {

            if ($transacaoIniciada) {
                $conexao->rollback();
            }

            $erro = $e->getMessage();
        }
    }
}

?>

<!DOCTYPE html>
<html lang="pt-BR">

<head>

    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>Cadastro — FerroTrack</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">

</head>

<body class="bg-body-tertiary">

<main class="container min-vh-100 d-flex align-items-center justify-content-center py-5">

    <div class="row justify-content-center w-100">

        <div class="col-md-10 col-lg-8 col-xl-7">

            <div class="card shadow-sm border-0">

                <div class="card-body p-4 p-md-5">

                    <div class="text-center mb-4">

                        <img
                            src="../img/logo.png"
                            alt="FerroTrack"
                            width="64"
                            height="64"
                            class="object-fit-contain mb-3"
                        >

                        <h1 class="h3 mb-0">Faça seu cadastro</h1>

                    </div>

                    <?php if ($erro): ?>

                        <div class="alert alert-danger" role="alert">
                            <?= htmlspecialchars($erro) ?>
                        </div>

                    <?php endif; ?>

                    <?php if ($sucesso): ?>

                        <div class="alert alert-success" role="alert">
                            <?= htmlspecialchars($sucesso) ?>
                        </div>

                        <div class="d-grid">
                            <a href="../../index.php" class="btn btn-primary">
                                Voltar para o login
                            </a>
                        </div>

                    <?php else: ?>

                        <form method="POST" action="">

                            <div class="row g-3">

                                <div class="col-12">

                                    <label for="perfil" class="form-label">Perfil</label>

                                    <select class="form-select" id="perfil" name="perfil" required>

                                        <option value="" selected disabled>Selecione seu perfil</option>
                                        <option value="cliente">Cliente</option>
                                        <option value="maquinista">Maquinista</option>
                                        <option value="gestor">Gestor</option>

                                    </select>

                                </div>

                                <div class="col-md-6">

                                    <label for="nome" class="form-label">Nome completo</label>
                                    <input type="text" class="form-control" id="nome" name="nome" required>

                                </div>

                                <div class="col-md-6">

                                    <label for="nascimento" class="form-label">Data de nascimento</label>
                                    <input type="date" class="form-control" id="nascimento" name="nascimento">

                                </div>

                                <div class="col-md-6">

                                    <label for="email" class="form-label">E-mail</label>
                                    <input type="email" class="form-control" id="email" name="email" required>

                                </div>

                                <div class="col-md-6">

                                    <label for="telefone" class="form-label">Telefone</label>
                                    <input type="tel" class="form-control" id="telefone" name="telefone">

                                </div>

                                <div class="col-md-6">

                                    <label for="cpf" class="form-label">CPF</label>
                                    <input type="text" class="form-control" id="cpf" name="cpf" maxlength="14" required>

                                </div>

                                <div class="col-12 d-none" id="camposMaquinista">

                                    <h2 class="h5 mt-3">Informações do maquinista</h2>

                                    <div class="row g-3">

                                        <div class="col-md-6">

                                            <label for="empresaMaquinista" class="form-label">Empresa ferroviária</label>
                                            <input type="text" class="form-control" id="empresaMaquinista" name="empresa_maquinista" data-required>

                                        </div>

                                        <div class="col-md-6">

                                            <label for="certificacaoMaquinista" class="form-label">Certificação técnica</label>
                                            <input type="text" class="form-control" id="certificacaoMaquinista" name="certificacao_maquinista" data-required>

                                        </div>

                                        <div class="col-md-6">

                                            <label for="regiaoMaquinista" class="form-label">Região de atuação</label>
                                            <input type="text" class="form-control" id="regiaoMaquinista" name="regiao_maquinista">

                                        </div>

                                        <div class="col-md-6">

                                            <label for="turnoMaquinista" class="form-label">Turno</label>

                                            <select class="form-select" id="turnoMaquinista" name="turno_maquinista">
                                                <option value="">Selecione</option>
                                                <option value="Diurno">Diurno</option>
                                                <option value="Noturno">Noturno</option>
                                                <option value="Integral">Integral</option>
                                            </select>

                                        </div>

                                    </div>

                                </div>

                                <div class="col-12 d-none" id="camposGestor">

                                    <h2 class="h5 mt-3">Informações do gestor</h2>

                                    <div class="row g-3">

                                        <div class="col-md-6">

                                            <label for="matricula" class="form-label">Matrícula</label>
                                            <input type="number" class="form-control" id="matricula" name="matricula" min="1" data-required>

                                        </div>

                                        <div class="col-md-6">

                                            <label for="empresaGestor" class="form-label">Empresa ferroviária</label>
                                            <input type="text" class="form-control" id="empresaGestor" name="empresa_gestor" data-required>

                                        </div>

                                        <div class="col-md-6">

                                            <label for="certificacaoGestor" class="form-label">Certificação técnica</label>
                                            <input type="text" class="form-control" id="certificacaoGestor" name="certificacao_gestor" data-required>

                                        </div>

                                        <div class="col-md-6">

                                            <label for="regiaoGestor" class="form-label">Região de atuação</label>
                                            <input type="text" class="form-control" id="regiaoGestor" name="regiao_gestor">

                                        </div>

                                        <div class="col-md-6">

                                            <label for="turnoGestor" class="form-label">Turno</label>

                                            <select class="form-select" id="turnoGestor" name="turno_gestor">
                                                <option value="">Selecione</option>
                                                <option value="Diurno">Diurno</option>
                                                <option value="Noturno">Noturno</option>
                                                <option value="Integral">Integral</option>
                                            </select>

                                        </div>

                                    </div>

                                </div>

                                <div class="col-12">

                                    <label for="login" class="form-label">Nome de usuário</label>
                                    <input type="text" class="form-control" id="login" name="login" required>

                                </div>

                                <div class="col-12">

                                    <label for="senha" class="form-label">Senha</label>

                                    <input
                                        type="password"
                                        class="form-control"
                                        id="senha"
                                        name="senha"
                                        minlength="6"
                                        required
                                    >

                                    <div class="form-text">
                                        A senha deve ter pelo menos 6 caracteres.
                                    </div>

                                </div>

                            </div>

                            <div class="d-grid mt-4">

                                <button type="submit" class="btn btn-primary btn-lg">
                                    Enviar cadastro
                                </button>

                            </div>

                        </form>

                    <?php endif; ?>

                    <hr class="my-4">

                    <p class="text-center mb-0 small">
                        Já tem conta?
                        <a href="../../index.php">Voltar para o login</a>
                    </p>

                </div>

            </div>

        </div>

    </div>

</main>

<script>

const perfil = document.getElementById('perfil');

const camposMaquinista = document.getElementById('camposMaquinista');
const camposGestor = document.getElementById('camposGestor');

function atualizarCampos() {

    const valor = perfil.value;

    camposMaquinista.classList.toggle('d-none', valor !== 'maquinista');
    camposGestor.classList.toggle('d-none', valor !== 'gestor');

    document.querySelectorAll('[data-required]').forEach(campo => {

        const grupo = campo.closest('#camposMaquinista, #camposGestor');

        campo.required = grupo && !grupo.classList.contains('d-none');

    });

}

perfil.addEventListener('change', atualizarCampos);

atualizarCampos();

</script>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"></script>

</body>
</html>