<?php
// PASSO 1: Retomar a sessão
// É necessário iniciar para o PHP saber qual sessão deve ser limpa e destruída.
session_start();

// PASSO 2: Limpar as variáveis da sessão
// Remove todos os dados guardados (ID, nome, papel, etc.).
session_unset();

// NÍVEL AVANÇADO: Expirar o cookie da sessão no navegador
// Força o navegador a deletar o cookie (PHPSESSID) no cliente.
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

// PASSO 3: Destruir a sessão no servidor
// Apaga o arquivo de sessão salvo no servidor.
session_destroy();

// PASSO 4: Redirecionar e encerrar a execução
// O ../ faz voltar da pasta 'php' para a raiz onde está a tela de login.
// Se a sua tela de login for 'index.php', mude 'login.php' para 'index.php'.
header("Location: ../index.php");
exit(); // O exit garante que o restante do script não continue rodando.