
<?php

require_once "permissao.php";

function gerarCabecalho()
{
    echo '<header>

        <span>
            Conectado como ' . htmlspecialchars($_SESSION["usuario_nome"]) . '
            (' . htmlspecialchars($_SESSION["usuario_papel"]) . ')
        </span>

        <nav>
            <a href="trens.php">Trens</a>
            <a href="sensores.php">Sensores</a>';

    if (temPapel(['gestor'])) {
        echo '<a href="usuarios.php">Usuários</a>';
    }

    echo '<a href="../assets/php/sair.php">Sair</a>

        </nav>

    </header>';
}
?>