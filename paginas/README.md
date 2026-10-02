# Ferrovias — projeto refatorado

Refatoração do projeto da SA: organização de pastas, remoção de todo o CSS próprio, padronização das telas e aplicação de Bootstrap 5 com responsividade para celular, tablet e computador.

---

## Como abrir

Coloque a pasta dentro de `htdocs` do XAMPP e acesse:

```
http://localhost/ferrovias-refatorado
```

Também funciona abrindo o `index.php` direto no navegador, porque não há PHP nesta etapa. O Bootstrap vem por CDN, então é necessário estar com internet.

---

## Estrutura de pastas

```
ferrovias-refatorado/
├── index.php              login, porta de entrada do sistema
├── paginas/                todas as demais telas
├── assets/
│   ├── img/                imagens do projeto
│   └── js/                 scripts que já existiam
└── README.md
```

Só o `index.php` fica na raiz, porque é a página de entrada. Tudo o mais fica em `paginas/`. Imagens e scripts ficam em `assets/`, separados por tipo.

---

## O que foi feito

### 1. Organização

| Antes | Depois |
|---|---|
| `html/`, `css/`, `js/`, `img/` na raiz, com imagens soltas junto | `index.php` na raiz, `paginas/` e `assets/` |
| Nomes misturando maiúscula e minúscula: `DashbooardGeral.php`, `MonitoramentoCargas.php` | Tudo em minúsculo, com hífen: `dashboard.php`, `monitoramento-cargas.php` |
| `htmls.zip` versionado dentro de `html/` | Removido |
| Imagens com nome do gerador: `Gemini_Generated_Image_7l35b87...png` | Nome que diz o que é: `mapa.png`, `clima.png`, `logo.png` |
| Caminhos de imagem quebrados: `src="3135823.png"` numa página dentro de `html/` | Todos os caminhos conferidos e funcionando |

### 2. Remoção do CSS

- Os 22 arquivos da pasta `css/` foram removidos. Eles já não estavam sendo carregados por nenhuma página.
- Nenhum atributo `style` sobrou em nenhuma página.
- Nenhum bloco `<style>` sobrou em nenhuma página.
- Todo o visual vem de classes do Bootstrap. Não há folha de estilo própria no projeto.

### 3. Padronização das telas

Todas as telas internas usam a mesma casca:

- **Barra superior fixa**, com logotipo à esquerda, ícones de acessibilidade, configurações, modo escuro e notificações à direita, e o acesso ao perfil.
- **Menu lateral à esquerda**, com os itens agrupados por assunto: Painéis, Operação, Sensores, Relatórios, Usuários e Cliente. O item da página aberta aparece destacado.
- **Área de conteúdo** com título, subtítulo e, quando faz sentido, botão de voltar.
- Conteúdo sempre dentro de cartões, organizado em linhas e colunas.

As telas de login, cadastro e recuperação de senha usam uma casca diferente, centralizada e sem menu, porque o usuário ainda não entrou no sistema.

### 4. Responsividade

| Tamanho | Comportamento |
|---|---|
| Celular, abaixo de 768 px | Menu vira hambúrguer, que abre uma gaveta lateral. Cartões em coluna única. Tabelas com rolagem horizontal. Botões ocupam a largura toda |
| Tablet, de 768 px a 991 px | Ainda usa o hambúrguer. Cartões em duas colunas. Formulários em duas colunas |
| Computador, 992 px ou mais | Menu lateral fixo e sempre visível. Cartões em três ou quatro colunas |

O menu de hambúrguer e o menu lateral têm exatamente os mesmos itens: a navegação não muda conforme o tamanho da tela, só a forma de exibir.

---

## Páginas

### Acesso
`index.php` login · `paginas/cadastro.php` · `paginas/cadastro-gestor.php` · `paginas/recuperar-senha.php`

### Painéis
`painel-gestor.php` · `painel-maquinista.php` · `painel-cliente.php` · `dashboard.php`

### Operação
`gestao-rotas.php` · `adicionar-rota.php` · `monitoramento-cargas.php` · `trens.php` · `trens-cadastrados.php` · `alertas.php`

### Sensores
`sensores.php` · `adicionar-sensor.php` · `editar-sensor.php` · `remover-sensor.php`

### Relatórios
`relatorios.php` · `relatorios-operacionais.php` · `relatorios-passagens.php` · `relatorios-manutencao.php` · `relatorios-carga.php`

### Usuários
`usuarios.php` · `gerenciamento-usuarios.php` · `adicionar-usuario.php` · `perfil.php`

### Cliente
`pagamento.php` · `passagens.php`

---

## O que mudou de nome

| Arquivo original | Arquivo novo |
|---|---|
| `AdicionarRotas.php` | `adicionar-rota.php` |
| `AlertaNotificacoes.php` | `alertas.php` |
| `Cliente.php` | `painel-cliente.php` |
| `DashbooardGeral.php` | `dashboard.php` |
| `GestaoRotas.php` | `gestao-rotas.php` |
| `Gestor.php` | `painel-gestor.php` |
| `Maquinista.php` | `painel-maquinista.php` |
| `MonitoramentoCargas.php` | `monitoramento-cargas.php` |
| `Perfil.php` | `perfil.php` |
| `adicionarSensor.php` | `adicionar-sensor.php` |
| `adicionarUsuario.php` | `adicionar-usuario.php` |
| `cadastro_gestor.php` | `cadastro-gestor.php` |
| `carga.php` | `relatorios-carga.php` |
| `editarSensores.php` | `editar-sensor.php` |
| `gerenciamentoUsuario.php` | `gerenciamento-usuarios.php` |
| `manutencao.php` | `relatorios-manutencao.php` |
| `operacionais.php` | `relatorios-operacionais.php` |
| `passagens.php` | `relatorios-passagens.php` e `passagens.php` |
| `removerSensor.php` | `remover-sensor.php` |
| `trens_cadastrados.php` | `trens-cadastrados.php` |
| `usuarios.php` | `usuarios.php` |

---

## Observações sobre o conteúdo

Os elementos das telas foram preservados: todos os campos, botões, tabelas, listas, ícones e textos do projeto original continuam presentes. O que mudou foi a organização e a apresentação.

Três pontos exigiram decisão:

1. **`recuperar-senha.php` estava vazio**, com zero byte. Foi criada uma tela de recuperação por e-mail, seguindo o padrão das demais telas de acesso.
2. **`passagens.php` aparecia em dois papéis** no projeto original: como relatório, chamado a partir de `relatorios.php`, e como ticket de viagem do cliente. Foram separados em dois arquivos com o mesmo conteúdo, cada um no seu lugar do menu.
3. **O arquivo usado como logotipo** no projeto original era uma imagem de seta para a esquerda. Foi substituído por `trem.png`, que já existia no projeto. As setas de voltar passaram a usar ícone do Bootstrap.

---

## Scripts

Os quatro arquivos JavaScript do projeto original foram mantidos em `assets/js/` sem alteração. Os identificadores de que eles dependem foram preservados nas páginas correspondentes:

| Script | Página | Identificadores preservados |
|---|---|---|
| `gerenciamentoUsuario.js` | `gerenciamento-usuarios.php` | `buscarUsuario`, `btnBuscar`, `nomeBloquear`, `btnBloquear`, `tabelaUsuarios` |
| `adicionarUsuario.js` | `adicionar-usuario.php` | `nome`, `email`, `telefone`, `usuario`, `senha`, `cargo`, `status`, `btnSalvar` |
| `editarSensor.js` | `editar-sensor.php` | `sensor`, `tipo`, `local`, `novoSensor`, `novoTipo`, `novoLocal`, `data`, `descricao`, `btnSalvar` |
| `sensores.js` | — | Controlava um menu que passou a ser feito pelo Bootstrap. Mantido no projeto, mas não é mais chamado |

---

## Verificações feitas

- 29 páginas geradas
- Nenhum arquivo `.css` no projeto
- Nenhum atributo `style` nas páginas
- Nenhum bloco `<style>` nas páginas
- Nenhum link ou caminho de imagem quebrado, conferido arquivo por arquivo
- Renderização verificada em navegador real nas larguras de 390 px, 820 px e 1440 px
- Menu de hambúrguer testado: abre a gaveta com a navegação completa no celular

## Perfis do sistema

O sistema possui três perfis de acesso:

| Perfil no sistema | Perfil definido no guia | Acesso |
|---|---|---|
| `gestor` | Administrador/Gerente | Acesso completo ao sistema |
| `maquinista` | Maquinista | Acesso às informações e sensores do trem que opera |
| `cliente` | Usuário comum | Consulta informações públicas e recursos destinados ao cliente |

## Entrega 2 — Encerramento de Sessão (Logout)

Para destruir a sessão com segurança, o arquivo `assets/php/sair.php` executa os seguintes passos:
1. `session_start()` para retomar a sessão ativa.
2. `session_unset()` para limpar as variáveis guardadas.
3. Remoção do cookie de sessão (`setcookie`) para forçar o navegador a descartar o identificador.
4. `session_destroy()` para apagar o registro no servidor.
5. Redirecionamento para o login com `header()` e encerramento com `exit()`.

### Por que expirar o cookie de sessão? (Nível Avançado)
A função `session_destroy()` exclui apenas o arquivo da sessão salvo no servidor. O navegador, porém, continua guardando o cookie (`PHPSESSID`) contendo a chave do usuário. Expirar esse cookie com `setcookie(..., time() - 42000)` apaga essa chave do computador do cliente, impedindo que o ID de sessão descartado continue em trânsito ou seja reutilizado.