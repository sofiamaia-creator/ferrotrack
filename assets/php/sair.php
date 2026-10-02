
<?php

// PASSO 1: Retomar a sessão
session_start();

// PASSO 2: Limpar as variáveis da sessão
session_unset();

// NÍVEL AVANÇADO: Expirar o cookie
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();

    setcookie(
        session_name(),
        '',
        time() - 42000,
        $params["path"],
        $params["domain"],
        $params["secure"],
        $params["httponly"]
    );
}

// PASSO 3: Destruir a sessão
session_destroy();

// PASSO 4: Redirecionar para o login
header("Location: ../../index.php");
exit();