<?php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['id_usuario'])) {
    header('Location: ../../index.php');
    exit;
}

require_once __DIR__ . '/conexao.php';

$sql = "SELECT ativo, status_aprovacao FROM usuarios WHERE id_usuario = ?";
$stmt = $conexao->prepare($sql);
$stmt->bind_param("i", $_SESSION['id_usuario']);
$stmt->execute();

$resultado = $stmt->get_result();
$usuario = $resultado->fetch_assoc();

$stmt->close();

if (!$usuario || $usuario['status_aprovacao'] !== 'aprovado' || !$usuario['ativo']) {
    session_unset();
    session_destroy();

    header('Location: ../../index.php');
    exit;
}