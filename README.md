# **Mini Locação Syspan (PHP + MySQL)**

O **Mini Sistema de Locação Syspan** é um projeto desenvolvido para prática de estudos e desenvolvimento web, utilizando PHP, MySQL, HTML, CSS e JavaScript, inspirado nos sistemas da Syspan. 
O sistema permite gerenciar clientes, equipamentos, tabelas de preços e contratos de locação (incluindo os itens associados aos contratos), calculando o valor de cada item e o total do contrato.

## Sumário

1. [Requisitos](#requisitos)
2. [Funcionalidades](#funcionalidades)
3. [Tecnologias utilizadas](#tecnologias-utilizadas)
4. [Estrutura do projeto](#estrutura-do-projeto)
5. [Como instalar e executar o projeto](#como-instalar-e-executar-o-projeto)
6. [Dados de teste](#dados-de-teste)
7. [Checklist de funcionamento](#checklist-de-funcionamento)
8. [Problemas conhecidos](#problemas-conhecidos)
9. [Autora](#autora)

## **Requisitos**

|Item|Versão|
|---|---|
|PHP|**8.1 ou superior**|
|MySQL ou MariaDB|MySQL 5.7+ ou MariaDB 10.4+|
|Extensão PHP|`pdo_mysql` habilitada no XAMPP|
|Servidor web|Apache (XAMPP, Laragon ou similar)|
|Navegador|Qualquer navegador atual|
|Git|Necessário apenas para clonar o repositório|
|Internet|Necessária para a busca por CEP (API ViaCEP)|

O projeto foi desenvolvido e testado em um ambiente local com PHP 8.2.12, MariaDB 10.4.32 e phpMyAdmin 5.2.1 (XAMPP no Windows).

## **Funcionalidades**

### Módulo de Clientes
- Cadastro, consulta, edição e exclusão de clientes.
- Validação nativa de CPF e CNPJ, e-mail, telefone, CEP e estado.
- Busca automática de endereço por CEP integrando a API externa do ViaCEP.
- Máscaras de entrada para CPF/CNPJ, telefone e CEP.
- Não permite excluir cliente que tenha contratos.

### Módulo de Equipamentos
- Cadastro, consulta, edição e exclusão de equipamentos.
- Código único por equipamento e indicação de ativo/inativo.
- Verificação de vínculos antes da exclusão.
- Não permite excluir equipamento usado em contratos ou tabelas de preços.

### Módulo de Tabela de Preços
- Cadastro, consulta, edição e exclusão de tabelas de preços.
- Suporte a vários preços para o mesmo equipamento.
- Controle flexível de preços por diária.
- CRUD completo de itens da tabela de preços.
- Verificação de vínculos antes da exclusão.

### Módulo de Contratos
- Cadastro, consulta, edição e exclusão de contratos.
- Associação direta entre cliente e tabela de preços.
- CRUD completo de itens de contrato.
- Cálculo de subtotal e valor total do contrato.
- Contratos encerrados não aceitam alteração de itens.

### Segurança e Organização do Código
- Proteção nas saídas HTML via função helper `e()` usando `htmlspecialchars`.
- Consultas com PDO e Prepared Statements.
- Proteção CSRF nos formulários de gravação ou exclusão de dados.
- Exclusão somente por `POST`, com tela de confirmação e verificação de vínculos.
- Mensagens de sucesso e erro, sem expor SQL nem detalhes do banco.
- Configuração do banco fora do código principal (`config.local.php`, ignorado pelo Git).
- Validações robustas em PHP centralizadas em `helpers.php`.
- Registro de operações em `logger.log`.
- Textos em UTF-8 e páginas com `lang="pt-BR"`.

## **Tecnologias utilizadas**
- **PHP com PDO** para acesso ao banco de dados.
- **MySQL ou MariaDB** para armazenamento dos dados.
- **HTML e CSS** para estrutura e apresentação das páginas.
- **JavaScript** para interações na interface.
- **API ViaCEP** para consulta de endereços por CEP.
- **XAMPP ou similar** para execução do ambiente local.

## **Estrutura do projeto**
```text
syspan-estagio-locacao-v2/
├── projeto-locacao/
│    ├── assets/
│    ├── clientes/
│    ├── contratos/
│    ├── equipamentos/
│    ├── layout/
│    ├── precos/
│    ├── .gitignore
│    ├── conexao.php
│    ├── config.exemplo.php
│    ├── config.local.php       # não vai para o Git, criado pelo usuário
│    ├── helpers.php
│    ├── index.php
│    └── logger.php
├── sql/
│    └── locacao.sql
├── CHECKLIST_TESTES.md
└── README.md
```

## **Como instalar e executar o projeto**

### **1. Baixe o projeto**
Clone o repositório para dentro da pasta `htdocs` do XAMPP:
```bash
cd C:\xampp\htdocs
git clone https://github.com/thamiresvianna/syspan-estagio-locacao-v2.git
```
> **Importante:** a pasta do projeto deve ser nomeada exatamente `syspan-estagio-locacao-v2` e ficar diretamente em `htdocs`.
>
> Resultado esperado: `C:\xampp\htdocs\syspan-estagio-locacao-v2\projeto-locacao\index.php`.

### **2. Configure o ambiente**
1. Instale o XAMPP (ou outro servidor com PHP + MySQL).
2. Abra o painel de controle do XAMPP.
3. Inicie os serviços **Apache** e **MySQL**.

### **3. Crie o banco de dados**
O arquivo `sql/locacao.sql` cria o banco `syspan_locacao_estagio_v2`, as tabelas e os dados de exemplo. Não é necessário criar o banco antes.

1. Acesse o phpMyAdmin em `http://localhost/phpmyadmin/`.
2. Sem selecionar nenhum banco, abra a aba **Importar**.
3. Clique em **Escolher arquivo**, selecione `sql/locacao.sql` na pasta `sql` do projeto e clique em **Importar**.
4. Confirme que o banco `syspan_locacao_estagio_v2` apareceu na lista da esquerda, com as tabelas `clientes`, `equipamentos`, `precos`, `preco_itens`, `contratos` e `contrato_itens`.

> **Atenção:** o script inicia com `DROP DATABASE IF EXISTS syspan_locacao_estagio_v2`. Se esse banco já existir, ele será apagado e recriado. Faça um backup antes, se for o caso.

### **4. Configure a conexão**
A configuração do banco fica em um arquivo local, que não é enviado ao Git. 

Na pasta `projeto-locacao`, copie `config.exemplo.php`, renomeie a cópia para `config.local.php` e preencha os dados do seu ambiente.

Exemplo de configuração local:
```php
<?php
    return [
        'host' => '127.0.0.1',
        'porta' => '3306',
        'banco' => 'syspan_locacao_estagio_v2',
        'usuario' => 'root',
        'senha' => '',
    ];
```

Os valores de usuário e senha devem corresponder à instalação local do MySQL ou MariaDB. O exemplo acima considera um ambiente local no qual o usuário `root` não possui senha.

> **Importante:** se o seu MySQL tiver senha, coloque-a **somente** no `config.local.php`, que não é enviado ao Git. Nunca coloque senhas reais no `config.exemplo.php`, no `conexao.php` nem neste README.

### **5. Acesse o sistema**
Com o Apache e o MySQL iniciados e a conexão configurada, abra:
```bash
http://localhost/syspan-estagio-locacao-v2/projeto-locacao/index.php
```

## **Dados de teste**

O sistema utiliza o banco de dados configurado em `config.local.php` e há dados de exemplo inseridos no `sql/locacao.sql`.

O sistema **não possui login:** não existem usuários nem senhas, e qualquer pessoa que acesse a URL pode utilizar todas as telas.

O status de contrato (Agendado, Ativo ou Encerrado) é calculado pela data de hoje em relação ao período do contrato.

## **Checklist de funcionamento**

Após a instalação, verifique os seguintes itens:

**Instalação**
- [ ] Apache e MySQL iniciam normalmente no XAMPP.
- [ ] O banco de dados foi criado e as tabelas foram importadas.
- [ ] O sistema abre pelo navegador sem erro de conexão.
- [ ] A página inicial abre com os textos em português e os quatro cartões.
- [ ] A lista de Clientes mostra os dados de teste.

**Clientes**
- [ ] Cadastrar, editar e visualizar um cliente.
- [ ] Tentar cadastrar com CPF inválido: aparece a mensagem de erro.
- [ ] Tentar excluir cliente com contrato: o sistema bloqueia.

**Equipamentos e preços**
- [ ] Cadastrar, editar e visualizar um equipamento.
- [ ] Cadastrar, editar e visualizar uma tabela de preços.
- [ ] Adicionar equipamento a uma tabela de preços.
- [ ] Editar o valor da diária de um item.

**Contratos**
- [ ] Criar um contrato e adicionar um equipamento.
- [ ] Editar e excluir um item do contrato.
- [ ] Conferir o subtotal e o total do contrato.

**Segurança**
- [ ] As mensagens de sucesso e erro são exibidas.
- [ ] Visitar um link `excluir.php?id=...` mostra apenas a pergunta e não exclui nada.
- [ ] Enviar um formulário sem o campo `csrf_token` (apagado pelo F12) não altera o banco.
- [ ] Os formulários protegidos rejeitam tokens CSRF inválidos.
- [ ] A consulta de CEP funciona quando a API ViaCEP está disponível.

## **Problemas conhecidos**

**Erro de conexão com o banco**

Confira se o MySQL está iniciado no XAMPP e se o host, a porta, o nome do banco, o usuário e a senha estão corretos no arquivo `config.local.php`.

**Página não encontrada**

Confira se o projeto está dentro de `C:\xampp\htdocs\` e se o endereço acessado corresponde à pasta instalada.

**Sem autenticação**

O sistema não possui login, usuários nem permissões.

**Arquivo de log na pasta pública**

O `logger.log` fica em `projeto-locacao/` e pode ser aberto pelo navegador. Ele é ignorado pelo Git, mas não deve ser usado desta forma em produção.

**Caminho fixo no menu e no CSS**

O `layout/header.php` usa o caminho `syspan-estagio-locacao-v2/projeto-locacao/`. Caso a pasta tenha outro nome ou esteja em outro local, o menu e o CSS deixam de funcionar. Altere os caminhos no `layout/header.php` para acompanhar a pasta.

**Exclusão de item da tabela de preços**

O sistema bloqueia a remoção se o equipamento aparece em **qualquer** contrato e não só nos contratos que usam aquela tabela.

**Sem controle de disponibilidade**

O mesmo equipamento pode ser incluído em contratos com datas sobrepostas.

**Status do contrato**

O status dos contratos é salvo no banco de dados e também é recalculado nas telas, fazendo com que os dois possam divergir com o passar dos dias.

**Consulta de CEP**

A busca de endereço via API ViaCEP depende de internet e do serviço externo. Caso não tenha acesso à internet ou o serviço esteja indisponível, o endereço precisa ser digitado manualmente.

## Autora

**Thamires Vianna**

GitHub: https://github.com/thamiresvianna