# FerroTrack — Sistema de Monitoramento Ferroviário

Sistema de monitoramento ferroviário desenvolvido a partir da refatoração do projeto da SA. O FerroTrack organiza as funcionalidades de gestão de trens, sensores, leituras, alertas e usuários, com controle de acesso por perfil e interface responsiva utilizando Bootstrap 5.

## 1. Tecnologias utilizadas

- **PHP:** processamento no servidor e implementação das funcionalidades.
- **MySQL:** armazenamento dos dados do sistema.
- **MySQLi:** comunicação entre o PHP e o banco de dados.
- **HTML5:** estrutura das páginas.
- **Bootstrap 5:** componentes visuais e responsividade.
- **Bootstrap Icons:** ícones da interface.
- **JavaScript:** scripts de interação das páginas.
- **XAMPP:** ambiente local para execução do PHP e do MySQL.
- **phpMyAdmin:** gerenciamento e consulta do banco de dados.

O Bootstrap e os Bootstrap Icons são carregados por CDN, sendo necessária uma conexão com a internet para carregar esses recursos externos.

## 2. Requisitos para execução

Antes de executar o projeto, é necessário ter:

- XAMPP instalado.
- Apache e MySQL disponíveis no XAMPP.
- Banco de dados MySQL configurado.
- Navegador atualizado.
- Conexão com a internet para os recursos carregados por CDN.

## 3. Como executar o projeto

### 3.1. Preparar os arquivos

Coloque a pasta `ferrotrack` dentro do diretório `htdocs` do XAMPP.

O caminho padrão no Windows é:

`C:\xampp\htdocs\ferrotrack\`

### 3.2. Iniciar os serviços

Abra o painel de controle do XAMPP e inicie os serviços Apache e MySQL.

Caso o MySQL esteja configurado em uma porta diferente da padrão, utilize a porta correspondente na configuração da conexão.

### 3.3. Configurar o banco de dados

Crie ou importe o banco de dados utilizado pelo projeto. O nome configurado no ambiente de desenvolvimento é:

`sa_ferrorama`

A estrutura deve incluir as tabelas utilizadas pelas funcionalidades do sistema, como:

- `usuarios`
- `gestor`
- `maquinista`
- `cliente`
- `trens`
- `sensores`
- `leituras`
- `alertas`
- `limites`

Os relacionamentos e as colunas precisam corresponder às consultas executadas pelos arquivos PHP.

### 3.4. Configurar a conexão

Confira as configurações no arquivo responsável pela conexão, localizado em `assets/php/conexao.php`.

Verifique:

- Endereço do servidor MySQL.
- Porta do MySQL.
- Usuário do banco de dados.
- Senha do banco de dados.
- Nome do banco de dados.

No ambiente local, o banco utilizado é `sa_ferrorama`. A porta deve corresponder à configuração do MySQL no XAMPP.

### 3.5. Acessar o sistema

Abra o navegador e acesse:

`http://localhost/ferrotrack/`

O arquivo `index.php` é a página de entrada do sistema.

**Importante:** abrir um arquivo PHP diretamente pelo navegador não executa o PHP corretamente sem um servidor. Para testar login, banco de dados, permissões, recuperação de senha e demais funcionalidades de backend, utilize o endereço local do XAMPP.

## 4. Estrutura de pastas

A organização geral do projeto segue esta estrutura:

```text
ferrotrack/
├── index.php
├── paginas/
│   ├── cadastro.php
│   ├── cadastro-gestor.php
│   ├── recuperar-senha.php
│   ├── dashboard.php
│   ├── trens.php
│   ├── sensores.php
│   ├── alertas.php
│   └── ...
├── assets/
│   ├── img/
│   ├── js/
│   └── php/
├── includes/
│   └── limites.php
└── README.md
```

A estrutura representa a organização geral do projeto; os arquivos e subdiretórios podem variar conforme a versão implementada.

- `index.php`: página de entrada do sistema.
- `paginas/`: páginas da aplicação.
- `assets/img/`: imagens e logotipos.
- `assets/js/`: scripts JavaScript.
- `assets/php/`: arquivos PHP compartilhados, como conexão, autenticação e componentes comuns.
- `includes/`: funções auxiliares e regras reutilizáveis.
- `README.md`: documentação do projeto.

## 5. Refatoração da interface

### 5.1. Organização dos arquivos

Durante a refatoração, foram realizadas as seguintes alterações:

- Organização das páginas na pasta `paginas/`.
- Separação dos recursos estáticos e arquivos PHP compartilhados.
- Padronização dos nomes dos arquivos em letras minúsculas e com hífen.
- Remoção do arquivo `htmls.zip` que estava versionado dentro da pasta `html/`.
- Renomeação de imagens para facilitar a identificação.
- Revisão dos caminhos das imagens utilizados pelas páginas.

### 5.2. Remoção do CSS próprio

A refatoração priorizou a utilização de classes do Bootstrap para a apresentação visual.

As alterações documentadas incluem:

- Remoção dos arquivos CSS próprios que não estavam sendo utilizados pelas páginas.
- Remoção dos atributos `style` e dos blocos `<style>` nas páginas verificadas.
- Utilização de classes do Bootstrap para organizar os componentes visuais.

### 5.3. Padronização das telas

As telas internas seguem um padrão visual com:

- Barra superior com logotipo, ícones e acesso ao perfil.
- Menu lateral com os itens de navegação.
- Destaque para o item correspondente à página atual.
- Área de conteúdo com título e subtítulo.
- Botão de voltar quando necessário.
- Cartões, tabelas, linhas e colunas para organizar as informações.

As telas de login, cadastro e recuperação de senha utilizam uma estrutura centralizada, sem menu lateral, por serem páginas relacionadas ao acesso ao sistema.

### 5.4. Responsividade

O layout foi organizado para diferentes tamanhos de tela.

| Dispositivo | Comportamento previsto |
|---|---|
| Celular, abaixo de 768 px | Menu hambúrguer, cartões em uma coluna e tabelas com rolagem horizontal |
| Tablet, de 768 px a 991 px | Menu hambúrguer e cartões em duas colunas |
| Computador, a partir de 992 px | Menu lateral e cartões em três ou quatro colunas |

As verificações de responsividade documentadas utilizaram larguras de 390 px, 820 px e 1440 px.

## 6. Páginas do sistema

As páginas do projeto estão organizadas por finalidade.

### 6.1. Acesso e autenticação

- `index.php`
- `paginas/cadastro.php`
- `paginas/cadastro-gestor.php`
- `paginas/recuperar-senha.php`

### 6.2. Painéis

- `painel-gestor.php`
- `painel-maquinista.php`
- `painel-cliente.php`
- `dashboard.php`

### 6.3. Operação ferroviária

- `gestao-rotas.php`
- `adicionar-rota.php`
- `monitoramento-cargas.php`
- `trens.php`
- `trens-cadastrados.php`
- `alertas.php`

### 6.4. Sensores

- `sensores.php`
- `adicionar-sensor.php`
- `editar-sensor.php`
- `remover-sensor.php`

### 6.5. Relatórios

- `relatorios.php`
- `relatorios-operacionais.php`
- `relatorios-passagens.php`
- `relatorios-manutencao.php`
- `relatorios-carga.php`

### 6.6. Usuários

- `usuarios.php`
- `gerenciamento-usuarios.php`
- `adicionar-usuario.php`
- `perfil.php`

### 6.7. Cliente

- `pagamento.php`
- `passagens.php`

A lista representa as páginas documentadas no projeto. A localização exata de cada arquivo deve ser conferida na versão atual do repositório.

## 7. Padronização dos nomes dos arquivos

A tabela apresenta exemplos das renomeações realizadas durante a refatoração.

| Nome original | Nome padronizado |
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
| `removerSensor.php` | `remover-sensor.php` |
| `trens_cadastrados.php` | `trens-cadastrados.php` |

O arquivo `passagens.php` foi mantido para a funcionalidade de passagens do cliente, enquanto a finalidade de relatório foi separada em `relatorios-passagens.php`.

## 8. Scripts JavaScript

Os scripts JavaScript são mantidos em `assets/js/`.

| Script | Finalidade documentada |
|---|---|
| `gerenciamentoUsuario.js` | Interações da página de gerenciamento de usuários |
| `adicionarUsuario.js` | Interações do formulário de cadastro de usuários |
| `editarSensor.js` | Interações do formulário de edição de sensores |
| `sensores.js` | Script mantido no projeto, mas não utilizado pela página que passou a utilizar os componentes do Bootstrap |

Os scripts preservados devem ser conferidos com as páginas correspondentes, pois os identificadores HTML precisam permanecer compatíveis com o código JavaScript.

## 9. Perfis e controle de acesso

O sistema possui três perfis principais:

| Perfil | Responsabilidade |
|---|---|
| `gestor` | Gerenciamento do sistema e dos usuários, conforme as permissões definidas |
| `maquinista` | Acesso às funcionalidades operacionais permitidas |
| `cliente` | Acesso aos recursos destinados aos clientes |

A autorização é verificada no backend. Ocultar um link ou botão na interface não é suficiente para impedir o acesso direto a uma página.

### 9.1. Verificação de permissões

O arquivo `assets/php/permissao.php` contém a função `temPapel()`, utilizada para verificar os perfis autorizados.

As páginas protegidas devem verificar a sessão e a permissão do usuário antes de executar operações restritas.

### 9.2. Acesso por funcionalidade

As regras documentadas para as principais funcionalidades são:

| Funcionalidade | Perfis autorizados |
|---|---|
| Gerenciamento de usuários | Gestor |
| Consulta de trens | Gestor e cliente |
| Consulta de trens cadastrados | Gestor |
| Relatórios operacionais | Maquinista e gestor |
| Recuperação de senha | Disponível para usuários que precisam recuperar o acesso |

A autorização efetiva deve ser conferida no código de cada página e no processamento das operações realizadas pelo servidor.

## 10. Entrega 2 — Encerramento de sessão (Logout)

O encerramento da sessão é realizado pelo arquivo `assets/php/sair.php`.

O processo de logout utiliza as seguintes etapas:

1. `session_start()` para acessar a sessão ativa.
2. `session_unset()` para limpar as variáveis de sessão.
3. Expiração do cookie de sessão no navegador, quando aplicável.
4. `session_destroy()` para destruir a sessão no servidor.
5. `header()` para redirecionar o usuário à página de login.
6. `exit()` para interromper a execução após o redirecionamento.

A destruição da sessão no servidor e a expiração do cookie são operações distintas. A expiração do cookie ajuda a impedir que o navegador continue enviando o identificador de sessão antigo.

## 11. Entrega 3 — Gestão de trens

### 11.1. Modelagem da tabela

A tabela `trens` foi atualizada para armazenar informações de identificação e operação dos trens.

Os campos documentados incluem:

- `prefixo`
- `modelo`
- `ano`
- `status`
- `capacidade`
- `ultima_inspecao`

### 11.2. Decisões de modelagem

**Unicidade do prefixo**

A restrição `UNIQUE` impede o cadastro de dois trens com o mesmo prefixo.

**Status**

O campo `status` utiliza os valores `Ativo`, `Em manutenção` e `Inativo`, de acordo com a modelagem adotada.

**Capacidade**

O campo `capacidade` utiliza `DECIMAL(10,2)` para armazenar valores com duas casas decimais, representando a capacidade em toneladas.

**Última inspeção**

O campo `ultima_inspecao` utiliza o tipo `DATE` e aceita valores nulos para trens que ainda não possuem uma data de inspeção registrada.

### 11.3. Registros iniciais

Foram inseridos registros de exemplo para testar o cadastro e a consulta dos trens. Os registros devem possuir prefixos distintos e informações compatíveis com a estrutura da tabela.

## 12. Entrega 3 — Sensores e leituras

Os sensores estão relacionados aos trens e permitem registrar informações monitoradas durante a operação ferroviária.

A tabela `sensores` armazena os dados de identificação e o tipo de sensor. A tabela `leituras` registra os valores medidos e os relaciona ao sensor correspondente.

O relacionamento geral é:

`sensores.id_trem` → `trens.id_trem`

`leituras.id_sensor` → `sensores.id_sensor`

A consulta dos sensores pode utilizar relacionamentos com os trens para apresentar o prefixo do trem associado.

### 12.1. Última leitura

A consulta da última leitura deve utilizar a tabela `leituras` para identificar o registro mais recente de cada sensor.

A consulta deve preservar os sensores que ainda não possuem leituras. Para isso, pode utilizar uma subconsulta ou um `LEFT JOIN` com uma consulta agrupada.

Quando não existir uma leitura registrada, a interface deve apresentar uma indicação como `Sem leitura`, em vez de omitir o sensor.

## 13. Entrega 3 — Alertas e notificações

O sistema utiliza a tabela `alertas` para registrar condições identificadas a partir das leituras dos sensores.

Os alertas permitem identificar situações que ultrapassam os limites configurados para cada tipo de sensor.

O relacionamento entre os dados segue a estrutura:

`alertas` → `leituras` → `sensores` → `trens`

O alerta fica relacionado à leitura que originou sua criação por meio do campo `id_leitura`.

### 13.1. Limites dos sensores

Os limites são armazenados na tabela `limites`, permitindo consultar os valores máximos e as unidades correspondentes a cada tipo de sensor.

Os valores utilizados nos testes documentados foram:

| Tipo de sensor | Limite máximo | Unidade |
|---|---:|---|
| Temperatura | 100 | °C |
| Velocidade | 110 | km/h |
| Pressão | 7 | bar |

Esses valores são parâmetros do ambiente de testes e devem ser ajustados conforme as regras operacionais definidas para o sistema.

### 13.2. Classificação dos alertas

O arquivo `includes/limites.php` contém as funções `buscarLimite($tipo)` e `classificar($tipo, $valor)`.

A função `buscarLimite()` consulta o limite cadastrado para o tipo de sensor. A função `classificar()` compara o valor recebido com o limite e determina a classificação.

A margem de atenção utilizada é de 10% acima do limite máximo.

| Condição | Classificação | Comportamento |
|---|---|---|
| Valor menor ou igual ao limite | `normal` | Não gera alerta |
| Valor acima do limite e menor ou igual a 110% do limite | `atencao` | Gera alerta de atenção |
| Valor acima de 110% do limite | `critico` | Gera alerta crítico |

As classificações internas utilizam os identificadores `normal`, `atencao` e `critico`. Na interface, as severidades podem ser apresentadas como `atenção` e `crítico`.

### 13.3. Registro dos alertas

O simulador de leituras registra os valores na tabela `leituras` e utiliza a função `verificarAlerta()` para verificar se a leitura ultrapassa os limites configurados.

Quando uma leitura é classificada como `atencao` ou `critico`, o sistema registra um alerta relacionado à leitura correspondente.

As leituras classificadas como `normal` não geram novos alertas.

### 13.4. Transações no banco de dados

O simulador utiliza transações para processar lotes de leituras e seus respectivos alertas.

- `begin_transaction()`: inicia a transação.
- `commit()`: confirma as operações.
- `rollback()`: solicita a reversão das operações da transação quando ocorre uma falha tratada pelo código.

A transação ajuda a manter a consistência dos dados. A confirmação da reversão em caso de falha exige um teste específico no ambiente de execução.

## 14. Testes de alertas e notificações

### 14.1. Leituras dentro do limite

Foram geradas leituras com valores dentro dos limites configurados.

**Resultado esperado:** as leituras são registradas sem gerar novos alertas.

**Resultado observado:** o teste foi concluído com sucesso.

### 14.2. Leituras fora do limite

Foram geradas leituras com valores acima dos limites configurados.

**Resultado esperado:** o sistema classifica as leituras conforme a margem de atenção e registra alertas associados às leituras correspondentes.

**Resultado observado:** foram registrados alertas com severidades de atenção e crítica.

### 14.3. Verificação no banco de dados

Os registros foram consultados no phpMyAdmin para conferir informações como:

- Identificador do alerta.
- Identificador da leitura relacionada.
- Tipo de sensor.
- Valor medido.
- Limite configurado.
- Severidade do alerta.

Os resultados documentados confirmaram a criação dos alertas e a classificação dos valores acima dos limites.

### 14.4. Reversão da transação

O código possui tratamento de erros com `rollback()`.

A validação prática da reversão exige provocar uma falha durante o processamento e verificar se os dados da transação foram revertidos corretamente.

## 15. Entrega 3.2 — Gerenciamento e ativação de usuários

A página `gerenciamento-usuarios.php` é destinada ao gerenciamento de contas por usuários com perfil `gestor`.

A funcionalidade de gerenciamento contempla a consulta de usuários, pesquisa, filtro por status e ativação ou desativação de contas.

### 15.1. Ativação e desativação

O campo `ativo` da tabela `usuarios` determina se uma conta está ativa.

- Contas ativas podem realizar login, desde que as credenciais estejam corretas.
- Contas desativadas não podem realizar login.
- Um gestor pode ativar novamente uma conta desativada, conforme as permissões implementadas.

### 15.2. Proteções implementadas

O gerenciamento inclui as seguintes regras:

- Somente gestores podem acessar a funcionalidade.
- O usuário não pode desativar a própria conta.
- O sistema impede a desativação do último gestor ativo.
- As ações de alteração de status são processadas no backend.
- Um token CSRF é utilizado para validar as solicitações de alteração.

Essas verificações ajudam a impedir alterações indevidas e a preservar a existência de pelo menos um gestor ativo.

### 15.3. Testes realizados

Foram testadas a desativação e a reativação de usuários, além das proteções relacionadas à própria conta e ao último gestor ativo.

O banco de dados foi consultado para verificar os valores do campo `ativo` após as operações.

## 16. Entrega 3.3 — Recuperação de senha

Foi implementada uma funcionalidade de recuperação de senha na página `paginas/recuperar-senha.php`.

A implementação utiliza a geração de um código de recuperação exibido na tela para simular o envio de um código por e-mail, sem depender de um serviço externo de envio de mensagens.

### 16.1. Funcionamento

O processo de recuperação segue estas etapas:

1. O usuário informa o login cadastrado.
2. O sistema verifica se a conta existe e está ativa.
3. É gerado um código de recuperação aleatório.
4. O código é exibido na tela para fins de simulação.
5. O usuário informa o código e cadastra uma nova senha.
6. O sistema valida o código, sua validade, o tamanho da senha e a confirmação.
7. A nova senha é armazenada no banco de dados como hash.
8. O usuário pode voltar à página de login e entrar com a nova senha.

### 16.2. Regras de segurança

A implementação contempla:

- Geração de código aleatório.
- Armazenamento do hash do código na sessão.
- Validade de 15 minutos para o código.
- Validação do código informado.
- Exigência de senha com pelo menos 8 caracteres.
- Confirmação da nova senha.
- Utilização de `password_hash()` para armazenar a senha.
- Verificação de conta ativa antes de iniciar a recuperação.
- Token CSRF nos formulários de recuperação.

A senha não deve ser armazenada em texto puro. A utilização de `password_hash()` permite armazenar um hash que pode ser verificado posteriormente pelo processo de autenticação.

### 16.3. Limitações da simulação

O envio real de e-mails não foi implementado, pois a configuração de e-mail no XAMPP está fora do escopo da atividade.

Por esse motivo, o código é apresentado diretamente na tela. Essa abordagem serve para demonstrar o fluxo de recuperação no ambiente de desenvolvimento, mas não deve ser utilizada dessa forma em produção.

Em um ambiente real, o código seria enviado ao e-mail cadastrado, teria validade limitada e seria acompanhado de medidas adicionais para proteger a conta.

### 16.4. Testes realizados

Foram realizados os seguintes testes:

- Solicitação de recuperação utilizando o login cadastrado.
- Geração e exibição do código.
- Validação do código informado.
- Cadastro e confirmação de uma nova senha.
- Redefinição da senha.
- Login utilizando a nova senha.

**Resultado observado:** o fluxo de recuperação foi concluído com sucesso e a nova senha permitiu realizar o login.

## 17. Verificações gerais da refatoração

As verificações documentadas durante a refatoração incluem:

- Organização das páginas e dos recursos.
- Conferência dos caminhos das imagens.
- Verificação de atributos `style` e blocos `<style>` nas páginas verificadas.
- Verificação da renderização em larguras de 390 px, 820 px e 1440 px.
- Teste do menu hambúrguer e da navegação em tela de celular.
- Conferência dos componentes visuais padronizados.

A quantidade de páginas e os arquivos presentes devem ser conferidos na versão atual do repositório, pois podem mudar durante o desenvolvimento.

## 18. Pendências de validação

Os seguintes testes exigem confirmação específica caso ainda não tenham sido realizados:

- Teste de reversão da transação após uma falha real durante o processamento.
- Verificação individual de todas as permissões de acesso por perfil.
- Verificação de todas as páginas após a refatoração.
- Teste de entradas inválidas e de códigos de recuperação expirados ou incorretos.
- Teste do comportamento de todos os formulários e botões nas diferentes larguras de tela.

Essa seção permite distinguir os testes já documentados daqueles que ainda precisam ser executados.

## 19. Considerações finais

O FerroTrack reúne a refatoração da interface, a organização dos arquivos, o controle de acesso por perfil, a gestão de trens e usuários, o monitoramento por sensores, o registro de leituras e a geração de alertas.

As funcionalidades documentadas incluem a desativação de contas, a proteção contra a desativação do último gestor ativo e a recuperação de senha por código de uso temporário.

Os testes realizados confirmaram o funcionamento do simulador de leituras e alertas, assim como o fluxo de recuperação de senha. O sistema utiliza mecanismos de controle de acesso, hash de senhas e transações no banco de dados para contribuir com a segurança e a consistência das operações.

A configuração de envio real de e-mails e a validação das pendências listadas permanecem dependentes das necessidades e do escopo de implantação do projeto.
