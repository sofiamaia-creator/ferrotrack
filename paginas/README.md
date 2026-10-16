# FerroTrack — Sistema de Monitoramento Ferroviário

Refatoração do projeto da SA, com reorganização de pastas, padronização das telas, remoção do CSS próprio e aplicação do Bootstrap 5. O projeto possui interface responsiva para celulares, tablets e computadores, além de funcionalidades de gestão de trens, sensores, leituras e alertas.



## 1. Como abrir o projeto

Coloque a pasta do projeto dentro do diretório `htdocs` do XAMPP e acesse:

http://localhost/ferrotrack/

Também é possível abrir o arquivo `index.php` diretamente no navegador para visualizar a interface desta etapa. Para executar funcionalidades que dependem de PHP e banco de dados, é necessário utilizar um servidor local com PHP e configurar a conexão com o banco.

**Requisito:** conexão com a internet para carregar o Bootstrap por CDN.



## 2. Estrutura de pastas

ferrotrack/
├── index.php
├── paginas/
│   ├── cadastro.php
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

O `index.php` fica na raiz como página de entrada do sistema. As demais telas ficam organizadas em `paginas/`, enquanto imagens, scripts, arquivos PHP compartilhados e funções auxiliares são separados em suas respectivas pastas.



## 3. O que foi feito na refatoração

### 3.1. Organização dos arquivos

- Organização das páginas na pasta `paginas/`.
- Separação de imagens, scripts e arquivos PHP compartilhados.
- Padronização dos nomes dos arquivos em letras minúsculas e com hífen.
- Remoção do arquivo `htmls.zip` que estava versionado dentro da pasta `html/`.
- Renomeação de imagens para facilitar a identificação.
- Conferência dos caminhos das imagens utilizados pelas páginas.

### 3.2. Remoção do CSS próprio

- Remoção dos 22 arquivos da pasta `css/`, que não estavam sendo carregados pelas páginas.
- Remoção dos atributos `style` das páginas.
- Remoção dos blocos `<style>`.
- Utilização de classes do Bootstrap para a apresentação visual.

### 3.3. Padronização das telas

As telas internas seguem um padrão visual composto por:

- Barra superior com logotipo, ícones e acesso ao perfil.
- Menu lateral com os itens agrupados por assunto.
- Destaque para o item correspondente à página aberta.
- Área de conteúdo com título, subtítulo e botão de voltar quando necessário.
- Cartões, linhas e colunas para organizar as informações.

As telas de login, cadastro e recuperação de senha utilizam uma estrutura própria, centralizada e sem menu lateral, pois são páginas de acesso ao sistema.

### 3.4. Responsividade

| Dispositivo | Comportamento |
|---|---|
| Celular, abaixo de 768 px | Menu hambúrguer, cartões em uma coluna e tabelas com rolagem horizontal |
| Tablet, de 768 px a 991 px | Menu hambúrguer e cartões em duas colunas |
| Computador, a partir de 992 px | Menu lateral fixo e cartões em três ou quatro colunas |

O menu hambúrguer mantém os mesmos itens de navegação do menu lateral. A diferença está apenas na forma de exibição conforme o tamanho da tela.



## 4. Páginas do sistema

### 4.1. Acesso

- `index.php`
- `paginas/cadastro.php`
- `paginas/cadastro-gestor.php`
- `paginas/recuperar-senha.php`

### 4.2. Painéis

- `painel-gestor.php`
- `painel-maquinista.php`
- `painel-cliente.php`
- `dashboard.php`

### 4.3. Operação

- `gestao-rotas.php`
- `adicionar-rota.php`
- `monitoramento-cargas.php`
- `trens.php`
- `trens-cadastrados.php`
- `alertas.php`

### 4.4. Sensores

- `sensores.php`
- `adicionar-sensor.php`
- `editar-sensor.php`
- `remover-sensor.php`

### 4.5. Relatórios

- `relatorios.php`
- `relatorios-operacionais.php`
- `relatorios-passagens.php`
- `relatorios-manutencao.php`
- `relatorios-carga.php`

### 4.6. Usuários

- `usuarios.php`
- `gerenciamento-usuarios.php`
- `adicionar-usuario.php`
- `perfil.php`

### 4.7. Cliente

- `pagamento.php`
- `passagens.php`

---

## 5. Renomeação dos arquivos

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

## 6. Observações sobre o conteúdo

Os elementos das telas foram preservados durante a refatoração, incluindo campos, botões, tabelas, listas, ícones e textos. As principais mudanças foram na organização dos arquivos e na apresentação visual.

Três pontos exigiram decisões específicas:

1. **Recuperação de senha:** o arquivo `recuperar-senha.php` estava vazio. Foi criada uma tela de recuperação por e-mail seguindo o padrão visual das demais telas de acesso. O envio efetivo de e-mails depende de uma implementação de backend.
2. **Passagens:** o arquivo `passagens.php` aparecia com duas finalidades no projeto original: relatório e passagem de viagem do cliente. Os arquivos foram separados para manter cada finalidade em seu local de navegação.
3. **Logotipo:** a imagem utilizada originalmente como logotipo era uma seta para a esquerda. Ela foi substituída por `trem.png`, e os botões de voltar passaram a utilizar ícones do Bootstrap.



## 7. Scripts JavaScript

Os quatro arquivos JavaScript originais foram mantidos em `assets/js/`, sem alteração.

| Script | Página | Identificadores preservados |
|---|---|---|
| `gerenciamentoUsuario.js` | `gerenciamento-usuarios.php` | `buscarUsuario`, `btnBuscar`, `nomeBloquear`, `btnBloquear`, `tabelaUsuarios` |
| `adicionarUsuario.js` | `adicionar-usuario.php` | `nome`, `email`, `telefone`, `usuario`, `senha`, `cargo`, `status`, `btnSalvar` |
| `editarSensor.js` | `editar-sensor.php` | `sensor`, `tipo`, `local`, `novoSensor`, `novoTipo`, `novoLocal`, `data`, `descricao`, `btnSalvar` |
| `sensores.js` | — | Mantido no projeto, mas não é mais chamado pela página, pois o menu que controlava passou a utilizar o Bootstrap |

---

## 8. Verificações da refatoração

As seguintes verificações foram documentadas durante a refatoração:

- 29 páginas geradas.
- Ausência de arquivos `.css` próprios no projeto refatorado.
- Ausência de atributos `style` nas páginas verificadas.
- Ausência de blocos `<style>` nas páginas verificadas.
- Conferência dos caminhos das imagens.
- Verificação da renderização em larguras de 390 px, 820 px e 1440 px.
- Teste do menu hambúrguer e da navegação em tela de celular.



## 9. Perfis do sistema

O sistema possui três perfis de acesso.

| Perfil | Equivalência no guia | Acesso previsto |
|---|---|---|
| `gestor` | Administrador/Gerente | Acesso às funcionalidades de gestão |
| `maquinista` | Maquinista | Acesso às informações e aos sensores do trem que opera |
| `cliente` | Usuário comum | Consulta de informações públicas e recursos destinados ao cliente |

As permissões devem ser verificadas no backend, além da apresentação dos itens de navegação na interface.



## 10. Entrega 2 — Encerramento de sessão (Logout)

O encerramento da sessão é realizado pelo arquivo `assets/php/sair.php`.

O processo de logout utiliza as seguintes etapas:

1. `session_start()` para retomar a sessão ativa.
2. `session_unset()` para limpar as variáveis da sessão.
3. `setcookie()` para expirar o cookie de sessão no navegador.
4. `session_destroy()` para destruir a sessão no servidor.
5. `header()` para redirecionar o usuário à página de login.
6. `exit()` para interromper a execução do script após o redirecionamento.

### 10.1. Por que expirar o cookie?

A função `session_destroy()` destrói a sessão armazenada no servidor, mas não remove automaticamente o cookie que está no navegador.

Por isso, o cookie de sessão é expirado utilizando `setcookie()`, `session_name()` e `session_get_cookie_params()`. Essa medida ajuda a impedir que o navegador continue enviando um identificador de sessão antigo.

O redirecionamento para o login finaliza o fluxo de logout.



## 11. Entrega 3 — Gestão de trens

### 11.1. Modelagem da tabela

A tabela `trens` foi atualizada para atender aos requisitos da entrega, mantendo os campos anteriores necessários aos relacionamentos com outras partes do sistema.

Foram adicionados os campos:

- `prefixo`
- `modelo`
- `ano`
- `status`
- `capacidade`
- `ultima_inspecao`

Esses campos permitem armazenar informações de identificação, fabricação, situação operacional, capacidade de carga e inspeção dos trens.

### 11.2. Decisões de modelagem

**Unicidade do prefixo**

Foi utilizada a restrição `UNIQUE` para impedir que dois trens tenham o mesmo prefixo, garantindo a integridade dos dados no banco.

**Valores de status**

O campo `status` utiliza `ENUM` com os valores `Ativo`, `Em manutenção` e `Inativo`, evitando variações inconsistentes na escrita dos estados.

**Capacidade**

O campo `capacidade` utiliza `DECIMAL(10,2)`, permitindo armazenar valores com duas casas decimais para representar a capacidade em toneladas.

**Última inspeção**

O campo `ultima_inspecao` utiliza o tipo `DATE` e aceita valores nulos, permitindo cadastrar trens que ainda não passaram por uma inspeção.

### 11.3. Registros iniciais

Foram inseridos três registros de exemplo com prefixos distintos, modelos, anos, capacidades e datas de inspeção, conforme os requisitos definidos para a entrega.



## 12. Entrega 3 — Alertas e notificações

O sistema utiliza a tabela `alertas` para armazenar os alertas identificados a partir das leituras dos sensores.

O alerta representa uma condição detectada pelo sistema. A tela de notificações apresenta os alertas registrados, incluindo informações sobre sua origem e severidade.

O relacionamento entre as tabelas permite identificar o sensor e o trem associados ao alerta:

`alertas.id_leitura` → `leituras` → `sensores` → `trens`

A tabela `alertas` possui campos utilizados para registrar a leitura relacionada, o tipo de sensor, o valor medido, o limite correspondente, a mensagem, a severidade e o status do alerta.

### 12.1. Classificação dos alertas

A função `classificar($tipo, $valor)`, localizada em `includes/limites.php`, consulta o limite cadastrado para o tipo de sensor e compara esse valor com a leitura recebida.

A classificação utiliza uma margem de atenção de 10% acima do limite máximo.

| Condição | Classificação | Comportamento |
|---|---|---|
| Valor menor ou igual ao limite | `normal` | Não gera alerta |
| Valor acima do limite e menor ou igual a 110% do limite | `atencao` | Gera alerta de atenção |
| Valor acima de 110% do limite | `critico` | Gera alerta crítico |

A função retorna os identificadores `normal`, `atencao` e `critico`. Na apresentação dos alertas, as severidades são exibidas como `atenção` e `crítico`.

### 12.2. Limites dos sensores

Os limites são armazenados na tabela `limites`, permitindo consultar os valores máximos e as unidades correspondentes a cada tipo de sensor.

Os limites utilizados nos testes foram:

| Tipo de sensor | Limite máximo | Unidade |
|---|---:|---|
| Temperatura | 100 | °C |
| Velocidade | 110 | km/h |
| Pressão | 7 | bar |

A função `buscarLimite($tipo)` consulta o limite correspondente no banco de dados. A função `classificar($tipo, $valor)` utiliza esse limite para determinar a severidade.

### 12.3. Registro dos alertas

O simulador de leituras registra cada leitura na tabela `leituras`. Em seguida, utiliza a função `verificarAlerta()` para comparar o valor registrado com o limite configurado.

Quando a leitura é classificada como `atencao` ou `critico`, o sistema registra um alerta relacionado à leitura correspondente por meio do campo `id_leitura`.

Leituras classificadas como `normal` não geram novos alertas.

### 12.4. Transações no banco de dados

O simulador utiliza transações para processar os lotes de leituras e os respectivos alertas.

- `begin_transaction()`: inicia a transação.
- `commit()`: confirma as operações quando o processamento termina corretamente.
- `rollback()`: solicita a reversão das operações da transação quando ocorre um erro.

Esse mecanismo busca manter a consistência dos dados, evitando que um lote seja confirmado parcialmente quando uma falha interrompe seu processamento.



## 13. Testes realizados — Alertas e notificações

Foram realizados testes no simulador de leituras para verificar a classificação e o registro dos alertas.

### 13.1. Leituras dentro do limite

Foram geradas leituras com a opção de valores fora do limite desmarcada.

**Resultado esperado:** as leituras são registradas na tabela `leituras`, sem gerar novos alertas quando os valores permanecem dentro dos limites configurados.

**Resultado observado:** o teste foi concluído com sucesso.

### 13.2. Leituras fora do limite

Foram geradas leituras com a opção de valores fora do limite marcada.

**Resultado esperado:** as leituras acima dos limites são classificadas conforme a margem de atenção e registram alertas relacionados às respectivas leituras.

**Resultado observado:** foram registrados alertas com severidades `atenção` e `crítico`, conforme os valores medidos.

### 13.3. Verificação no banco de dados

A tabela `alertas` foi consultada no phpMyAdmin para conferir os registros gerados pelo simulador.

Foram verificados:

- O identificador do alerta (`id_alerta`).
- O identificador da leitura relacionada (`id_leitura`).
- O tipo de sensor (`tipo_sensor`).
- O valor medido (`valor_lido`).
- O limite máximo (`valor_limite`).
- A severidade do alerta (`severidade`).

Os resultados confirmaram o registro dos alertas e a classificação dos valores acima do limite.

### 13.4. Teste de reversão da transação

O código do simulador possui tratamento de erros com `rollback()` para solicitar a reversão da transação quando ocorre uma exceção.

A confirmação prática desse comportamento exige um teste específico que provoque uma falha durante o processamento e verifique se as operações da transação foram revertidas.



## 14. Considerações finais

A refatoração reorganizou os arquivos e padronizou a apresentação visual do projeto, mantendo a separação entre páginas, recursos estáticos e arquivos auxiliares.

As funcionalidades documentadas incluem os perfis de acesso, o encerramento de sessão, a gestão de trens e o monitoramento de leituras por meio de limites e alertas.

Os testes do simulador confirmaram o registro das leituras e a geração de alertas de atenção e críticos quando os valores ultrapassam os limites configurados. O sistema também utiliza transações para buscar consistência no processamento dos lotes de dados.