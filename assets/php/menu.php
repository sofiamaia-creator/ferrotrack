<?php

require_once "../assets/php/permissao.php";

function renderizarMenu()
{
    $papel = $_SESSION['usuario_papel'] ?? '';
    $paginaAtual = basename($_SERVER['PHP_SELF']);

    $categorias = [
        'Painéis' => [
            ['painel-gestor.php', 'bi-speedometer2', 'Painel do Gestor', ['gestor']],
            ['painel-maquinista.php', 'bi-person-badge', 'Painel do Maquinista', ['gestor']],
            ['painel-cliente.php', 'bi-person', 'Painel do Cliente', ['gestor']],
            ['dashboard.php', 'bi-grid-1x2', 'Dashboard Geral', ['gestor', 'maquinista']]
        ],

        'Operação' => [
            ['gestao-rotas.php', 'bi-signpost-split', 'Gestão de Rotas', ['gestor', 'maquinista']],
            ['adicionar-rota.php', 'bi-plus-square', 'Adicionar Rota', ['gestor']],
            ['monitoramento-cargas.php', 'bi-box-seam', 'Monitoramento de Cargas', ['gestor', 'maquinista']],
            ['trens.php', 'bi-train-front', 'Trens', ['gestor', 'maquinista']],
            ['trens-cadastrados.php', 'bi-list-ul', 'Trens Cadastrados', ['gestor', 'maquinista']],
            ['alertas.php', 'bi-bell', 'Alertas e Notificações', ['gestor', 'maquinista', 'cliente']]
        ],

        'Sensores' => [
            ['sensores.php', 'bi-cpu', 'Gerenciar Sensores', ['gestor', 'maquinista']],
            ['adicionar-sensor.php', 'bi-plus-circle', 'Adicionar Sensor', ['gestor']],
            ['editar-sensor.php', 'bi-pencil', 'Editar Sensor', ['gestor']],
            ['remover-sensor.php', 'bi-dash-circle', 'Remover Sensor', ['gestor']]
        ],

        'Relatórios' => [
            ['relatorios.php', 'bi-file-earmark-bar-graph', 'Relatórios', ['gestor', 'maquinista']]
        ],

        'Usuários' => [
            ['usuarios.php', 'bi-people', 'Status de Usuários', ['gestor', 'maquinista']],
            ['gerenciamento-usuarios.php', 'bi-person-gear', 'Gerenciar Usuários', ['gestor']],
            ['adicionar-usuario.php', 'bi-person-plus', 'Adicionar Usuário', ['gestor']],
            ['perfil.php', 'bi-person-circle', 'Meu Perfil', ['gestor', 'maquinista', 'cliente']]
        ],

        'Cliente' => [
            ['pagamento.php', 'bi-credit-card', 'Pagamento', ['gestor', 'maquinista', 'cliente']],
            ['passagens.php', 'bi-ticket-perforated', 'Passagens', ['gestor', 'maquinista', 'cliente']]
        ]
    ];

?>

    ```
    <aside class="col-lg-3 col-xl-2 d-none d-lg-block bg-white border-end vh-100 position-sticky top-0 p-0">
        <nav class="p-3 overflow-auto h-100">
            <ul class="nav nav-pills flex-column">

                <?php foreach ($categorias as $categoria => $itens): ?>

                    <?php
                    $itensPermitidos = array_filter(
                        $itens,
                        fn($item) => temPapel($item[3])
                    );

                    if (empty($itensPermitidos)) {
                        continue;
                    }
                    ?>

                    <li class="nav-item mt-3 mb-1">
                        <span class="text-uppercase small fw-semibold text-secondary px-3">
                            <?= htmlspecialchars($categoria) ?>
                        </span>
                    </li>

                    <?php foreach ($itensPermitidos as $item): ?>

                        <?php
                        [$pagina, $icone, $texto, $papeisPermitidos] = $item;
                        $ativo = ($paginaAtual === $pagina);
                        ?>

                        <li class="nav-item">
                            <a
                                class="nav-link d-flex align-items-center gap-2 rounded px-3 <?= $ativo ? 'active bg-primary text-white' : 'text-body' ?>"
                                href="<?= htmlspecialchars($pagina) ?>"
                                <?= $ativo ? 'aria-current="page"' : '' ?>>
                                <i class="bi <?= htmlspecialchars($icone) ?>"></i>
                                <span><?= htmlspecialchars($texto) ?></span>
                            </a>
                        </li>

                    <?php endforeach; ?>

                <?php endforeach; ?>

            </ul>
        </nav>
    </aside>

<?php

}
?>