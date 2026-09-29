<?php
?>

<header>
    <span>
        Conectado como
        <?= htmlspecialchars($_SESSION['usuario_nome']) ?>
        (<?= htmlspecialchars($_SESSION['usuario_papel']) ?>)
    </span>

    <nav>
        <a href="trens.php">Trens</a>
        <a href="sensores.php">Sensores</a>
        <a href="usuarios.php">Usuários</a>
        <a href="sair.php">Sair</a>
    </nav>
</header>
