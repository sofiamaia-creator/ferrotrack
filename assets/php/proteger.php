<?php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (isset($_SESSION['pagina_atual'])) {
    $paginaAtual = $_SESSION['pagina_atual'];
}

if ((!isset($_SESSION['id_usuario'])) && ($paginaAtual != 'cadastro.php')) {
    header('Location: ../../index.php');
    exit;
}