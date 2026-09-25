# Ferrovias — projeto refatorado

Refatoração do projeto da SA: organização de pastas, remoção de todo o CSS próprio, padronização das telas e aplicação de Bootstrap 5 com responsividade para celular, tablet e computador.

---

## Como abrir

Coloque a pasta dentro de `htdocs` do XAMPP e acesse:

```
http://localhost/ferrovias-refatorado
```

Também funciona abrindo o `index.html` direto no navegador, porque não há PHP nesta etapa. O Bootstrap vem por CDN, então é necessário estar com internet.

---

## Estrutura de pastas

```
ferrovias-refatorado/
├── index.html              login, porta de entrada do sistema
├── paginas/                todas as demais telas
├── assets/
│   ├── img/                imagens do projeto
│   └── js/                 scripts que já existiam
└── README.md
```

Só o `index.html` fica na raiz, porque é a página de entrada. Tudo o mais fica em `paginas/`. Imagens e scripts ficam em `assets/`, separados por tipo.

---

## O que foi feito

### 1. Organização

| Antes | Depois |
|---|---|
| `html/`, `css/`, `js/`, `img/` na raiz, com imagens soltas junto | `index.html` na raiz, `paginas/` e `assets/` |
| Nomes misturando maiúscula e minúscula: `DashbooardGeral.html`, `MonitoramentoCargas.html` | Tudo em minúsculo, com hífen: `dashboard.html`, `monitoramento-cargas.html` |
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
`index.html` login · `paginas/cadastro.html` · `paginas/cadastro-gestor.html` · `paginas/recuperar-senha.html`

### Painéis
`painel-gestor.html` · `painel-maquinista.html` · `painel-cliente.html` · `dashboard.html`

### Operação
`gestao-rotas.html` · `adicionar-rota.html` · `monitoramento-cargas.html` · `trens.html` · `trens-cadastrados.html` · `alertas.html`

### Sensores
`sensores.html` · `adicionar-sensor.html` · `editar-sensor.html` · `remover-sensor.html`

### Relatórios
`relatorios.html` · `relatorios-operacionais.html` · `relatorios-passagens.html` · `relatorios-manutencao.html` · `relatorios-carga.html`

### Usuários
`usuarios.html` · `gerenciamento-usuarios.html` · `adicionar-usuario.html` · `perfil.html`

### Cliente
`pagamento.html` · `passagens.html`

---

## O que mudou de nome

| Arquivo original | Arquivo novo |
|---|---|
| `AdicionarRotas.html` | `adicionar-rota.html` |
| `AlertaNotificacoes.html` | `alertas.html` |
| `Cliente.html` | `painel-cliente.html` |
| `DashbooardGeral.html` | `dashboard.html` |
| `GestaoRotas.html` | `gestao-rotas.html` |
| `Gestor.html` | `painel-gestor.html` |
| `Maquinista.html` | `painel-maquinista.html` |
| `MonitoramentoCargas.html` | `monitoramento-cargas.html` |
| `Perfil.html` | `perfil.html` |
| `adicionarSensor.html` | `adicionar-sensor.html` |
| `adicionarUsuario.html` | `adicionar-usuario.html` |
| `cadastro_gestor.html` | `cadastro-gestor.html` |
| `carga.html` | `relatorios-carga.html` |
| `editarSensores.html` | `editar-sensor.html` |
| `gerenciamentoUsuario.html` | `gerenciamento-usuarios.html` |
| `manutencao.html` | `relatorios-manutencao.html` |
| `operacionais.html` | `relatorios-operacionais.html` |
| `passagens.html` | `relatorios-passagens.html` e `passagens.html` |
| `removerSensor.html` | `remover-sensor.html` |
| `trens_cadastrados.html` | `trens-cadastrados.html` |
| `usuarios.html` | `usuarios.html` |

---

## Observações sobre o conteúdo

Os elementos das telas foram preservados: todos os campos, botões, tabelas, listas, ícones e textos do projeto original continuam presentes. O que mudou foi a organização e a apresentação.

Três pontos exigiram decisão:

1. **`recuperar-senha.html` estava vazio**, com zero byte. Foi criada uma tela de recuperação por e-mail, seguindo o padrão das demais telas de acesso.
2. **`passagens.html` aparecia em dois papéis** no projeto original: como relatório, chamado a partir de `relatorios.html`, e como ticket de viagem do cliente. Foram separados em dois arquivos com o mesmo conteúdo, cada um no seu lugar do menu.
3. **O arquivo usado como logotipo** no projeto original era uma imagem de seta para a esquerda. Foi substituído por `trem.png`, que já existia no projeto. As setas de voltar passaram a usar ícone do Bootstrap.

---

## Scripts

Os quatro arquivos JavaScript do projeto original foram mantidos em `assets/js/` sem alteração. Os identificadores de que eles dependem foram preservados nas páginas correspondentes:

| Script | Página | Identificadores preservados |
|---|---|---|
| `gerenciamentoUsuario.js` | `gerenciamento-usuarios.html` | `buscarUsuario`, `btnBuscar`, `nomeBloquear`, `btnBloquear`, `tabelaUsuarios` |
| `adicionarUsuario.js` | `adicionar-usuario.html` | `nome`, `email`, `telefone`, `usuario`, `senha`, `cargo`, `status`, `btnSalvar` |
| `editarSensor.js` | `editar-sensor.html` | `sensor`, `tipo`, `local`, `novoSensor`, `novoTipo`, `novoLocal`, `data`, `descricao`, `btnSalvar` |
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
