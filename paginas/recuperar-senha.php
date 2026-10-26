
<?php
session_start();

require_once "../assets/php/conexao.php";

if (empty($_SESSION['csrf_recuperacao'])) {
    $_SESSION['csrf_recuperacao'] = bin2hex(random_bytes(32));
}

$mensagem = '';
$tipoMensagem = 'danger';
$codigoGerado = '';
$etapa = isset($_SESSION['recuperacao_login']) ? 'redefinir' : 'solicitar';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $token = $_POST['csrf_token'] ?? '';

    if (!hash_equals($_SESSION['csrf_recuperacao'], $token)) {
        $mensagem = 'Solicitação inválida. Atualize a página e tente novamente.';
    } else {
        $acao = $_POST['acao'] ?? '';

        if ($acao === 'cancelar') {
            unset(
                $_SESSION['recuperacao_login'],
                $_SESSION['recuperacao_codigo_hash'],
                $_SESSION['recuperacao_expira']
            );

            header("Location: recuperar-senha.php");
            exit;
        }

        if ($acao === 'solicitar') {
            $login = trim($_POST['login'] ?? '');

            if ($login === '') {
                $mensagem = 'Informe seu login.';
            } else {
                $stmt = $conexao->prepare(
                    "SELECT id_usuario FROM usuarios
                     WHERE login = ? AND ativo = 1"
                );
                $stmt->bind_param("s", $login);
                $stmt->execute();
                $resultado = $stmt->get_result();

                if ($resultado->num_rows === 1) {
                    $codigoGerado = strtoupper(bin2hex(random_bytes(4)));

                    $_SESSION['recuperacao_login'] = $login;
                    $_SESSION['recuperacao_codigo_hash'] =
                        hash('sha256', $codigoGerado);
                    $_SESSION['recuperacao_expira'] = time() + 900;

                    $etapa = 'redefinir';
                    $mensagem = 'Código gerado! Como esta é uma simulação, anote o código exibido abaixo.';
                    $tipoMensagem = 'success';
                } else {
                    $mensagem = 'Não foi possível iniciar a recuperação. Verifique o login e se a conta está ativa.';
                }

                $stmt->close();
            }
        } elseif ($acao === 'redefinir') {
            $codigo = strtoupper(trim($_POST['codigo'] ?? ''));
            $senha = $_POST['senha'] ?? '';
            $confirmacao = $_POST['confirmacao'] ?? '';

            if (
                empty($_SESSION['recuperacao_login']) ||
                empty($_SESSION['recuperacao_codigo_hash']) ||
                empty($_SESSION['recuperacao_expira'])
            ) {
                $mensagem = 'Solicite um novo código de recuperação.';
                $etapa = 'solicitar';
            } elseif (time() > $_SESSION['recuperacao_expira']) {
                unset(
                    $_SESSION['recuperacao_login'],
                    $_SESSION['recuperacao_codigo_hash'],
                    $_SESSION['recuperacao_expira']
                );

                $mensagem = 'O código expirou. Solicite um novo código.';
                $etapa = 'solicitar';
            } elseif (
                !hash_equals(
                    $_SESSION['recuperacao_codigo_hash'],
                    hash('sha256', $codigo)
                )
            ) {
                $mensagem = 'Código de recuperação incorreto.';
                $etapa = 'redefinir';
            } elseif (strlen($senha) < 8) {
                $mensagem = 'A senha deve ter pelo menos 8 caracteres.';
                $etapa = 'redefinir';
            } elseif ($senha !== $confirmacao) {
                $mensagem = 'As senhas não coincidem.';
                $etapa = 'redefinir';
            } else {
                $login = $_SESSION['recuperacao_login'];
                $senhaHash = password_hash($senha, PASSWORD_DEFAULT);

                $stmt = $conexao->prepare(
                    "UPDATE usuarios
                     SET senha = ?
                     WHERE login = ? AND ativo = 1"
                );
                $stmt->bind_param("ss", $senhaHash, $login);

                if ($stmt->execute() && $stmt->affected_rows === 1) {
                    unset(
                        $_SESSION['recuperacao_login'],
                        $_SESSION['recuperacao_codigo_hash'],
                        $_SESSION['recuperacao_expira']
                    );

                    $mensagem = 'Senha redefinida com sucesso! Você já pode entrar com a nova senha.';
                    $tipoMensagem = 'success';
                    $etapa = 'solicitar';
                } else {
                    $mensagem = 'Não foi possível redefinir a senha. Tente novamente.';
                    $etapa = 'redefinir';
                }

                $stmt->close();
            }
        }
    }
}

if (
    empty($_SESSION['recuperacao_login']) &&
    $_SERVER['REQUEST_METHOD'] !== 'POST'
) {
    $etapa = 'solicitar';
}
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Recuperar senha — FerroTrack</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../paginas/cabecalho.css">
</head>

<body class="bg-body-tertiary">

<main class="container min-vh-100 d-flex align-items-center justify-content-center py-5">
    <div class="row justify-content-center w-100">
        <div class="col-md-8 col-lg-6 col-xl-5">
            <div class="card shadow-sm border-0">
                <div class="card-body p-4 p-md-5">

                    <div class="text-center mb-4">
                        <img src="../assets/img/logo.png"
                             alt="FerroTrack"
                             width="64"
                             height="64"
                             class="object-fit-contain mb-3">

                        <h1 class="h3 mb-2">Recuperar senha</h1>

                        <p class="text-secondary mb-0">
                            Recupere o acesso à sua conta FerroTrack.
                        </p>
                    </div>

                    <?php if ($mensagem !== ''): ?>
                        <div class="alert alert-<?= htmlspecialchars($tipoMensagem) ?>" role="alert">
                            <?= htmlspecialchars($mensagem) ?>
                        </div>
                    <?php endif; ?>

                    <?php if ($codigoGerado !== ''): ?>
                        <div class="alert alert-info text-center">
                            <p class="mb-2">Seu código de recuperação é:</p>
                            <strong class="fs-3"><?= htmlspecialchars($codigoGerado) ?></strong>
                            <p class="small mb-0 mt-2">
                                Este código expira em 15 minutos.
                            </p>
                        </div>
                    <?php endif; ?>

                    <?php if ($etapa === 'solicitar'): ?>

                        <form method="POST" action="recuperar-senha.php">
                            <input type="hidden" name="csrf_token"
                                   value="<?= htmlspecialchars($_SESSION['csrf_recuperacao']) ?>">

                            <input type="hidden" name="acao" value="solicitar">

                            <div class="mb-4">
                                <label for="login" class="form-label">Login cadastrado</label>

                                <div class="input-group">
                                    <span class="input-group-text">
                                        <i class="bi bi-person"></i>
                                    </span>

                                    <input type="text"
                                           class="form-control"
                                           id="login"
                                           name="login"
                                           placeholder="Digite seu login"
                                           required>
                                </div>
                            </div>

                            <div class="d-grid">
                                <button type="submit" class="btn btn-primary btn-lg">
                                    Gerar código de recuperação
                                </button>
                            </div>
                        </form>

                    <?php else: ?>

                        <form method="POST" action="recuperar-senha.php">
                            <input type="hidden" name="csrf_token"
                                   value="<?= htmlspecialchars($_SESSION['csrf_recuperacao']) ?>">

                            <input type="hidden" name="acao" value="redefinir">

                            <div class="mb-3">
                                <label for="codigo" class="form-label">
                                    Código de recuperação
                                </label>

                                <input type="text"
                                       class="form-control"
                                       id="codigo"
                                       name="codigo"
                                       maxlength="8"
                                       placeholder="Digite o código recebido"
                                       required>
                            </div>

                            <div class="mb-3">
                                <label for="senha" class="form-label">Nova senha</label>

                                <input type="password"
                                       class="form-control"
                                       id="senha"
                                       name="senha"
                                       minlength="8"
                                       autocomplete="new-password"
                                       required>
                            </div>

                            <div class="mb-4">
                                <label for="confirmacao" class="form-label">
                                    Confirme a nova senha
                                </label>

                                <input type="password"
                                       class="form-control"
                                       id="confirmacao"
                                       name="confirmacao"
                                       minlength="8"
                                       autocomplete="new-password"
                                       required>
                            </div>

                            <div class="d-grid mb-3">
                                <button type="submit" class="btn btn-primary btn-lg">
                                    Redefinir senha
                                </button>
                            </div>
                        </form>

                        <form method="POST" action="recuperar-senha.php">
                            <input type="hidden" name="csrf_token"
                                   value="<?= htmlspecialchars($_SESSION['csrf_recuperacao']) ?>">

                            <input type="hidden" name="acao" value="cancelar">

                            <div class="d-grid">
                                <button type="submit" class="btn btn-outline-secondary">
                                    Solicitar outro código
                                </button>
                            </div>
                        </form>

                    <?php endif; ?>

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
