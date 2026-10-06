<?php

require_once "../assets/php/proteger.php";
require_once "../assets/php/permissao.php";
require_once "../assets/php/conexao.php";

if (!temPapel(['gestor'])) {
    echo "Acesso negado.";
    exit;
}

$id_rota = (int) ($_GET['id'] ?? 0);

if ($id_rota <= 0) {
    echo "Rota inválida.";
    exit;
}

$sql = "DELETE FROM rotas WHERE id_rota = ?";

$stmt = $conexao->prepare($sql);

if (!$stmt) {
    echo "Erro ao preparar a exclusão: " . $conexao->error;
    exit;
}

$stmt->bind_param("i", $id_rota);

if ($stmt->execute()) {
    header("Location: gestao-rotas.php");
    exit;
}

echo "Não foi possível excluir a rota.";
echo "<br><br>";
echo "Ela pode estar sendo utilizada por outro registro do sistema.";
echo "<br><br>";
echo '<a href="gestao-rotas.php">Voltar para Gestão de Rotas</a>';

$stmt->close();