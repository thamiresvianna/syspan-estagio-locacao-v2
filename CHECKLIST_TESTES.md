# Checklist de testes manuais - Mini Locação Syspan

## 1. Ambiente

- Versão do PHP no terminal: PHP 8.2.12
- Versão do PHP no servidor web: PHP 8.2.12
- Versão do servidor do banco de dados: 10.4.32-MariaDB
- Versão do phpMyAdmin: 5.2.1

## 2. Testes de Funcionalidade - Clientes

- [✔️] Cadastrar cliente;
- [✔️] Editar cliente;
- [✔️] Visualizar cliente;
- [✔️] Tentar excluir cliente com contrato, verificando se a exclusão é impedida;

## 3. Testes de Funcionalidade - Equipamentos

- [✔️] Cadastrar equipamento;
- [✔️] Editar equipamento;
- [✔️] Visualizar equipamento;
- [✔️] Excluir equipamento;

## 4. Testes de Funcionalidade - Preços

- [✔️] Cadastrar tabela de preços;
- [✔️] Adicionar equipamento à tabela;
- [✔️] Editar tabela;
- [✔️] Excluir equipamento da tabela;

## 5. Testes de Funcionalidade - Contratos

- [✔️] Criar contrato;
- [✔️] Adicionar equipamento ao contrato;
- [✔️] Editar contrato;
- [✔️] Editar item do contrato;
- [✔️] Excluir item do contrato;

## 6. Conclusão da Tarefa 1

- [✔️] Testar todos os menus;
- [✔️] Realizar backup do banco de dados;
- [✔️] Verificar as versões do PHP;
- [✔️] Identificar possíveis erros durante os testes.

**Obs.:** nenhum erro crítico identificado.

---

## 7. Testes pós-melhorias

Segunda rodada de testes, executada depois das alterações de encoding, mensagens, CSRF, exclusão e configuração.

### 7.1 Encoding

- [✔️] Cadastrar cliente com nome acentuado (Ex: João Mendonça) e acentos em endereço e bairro;
- [✔️] Conferir acentos na listagem de clientes;
- [✔️] Conferir acentos na visualização de cliente;
- [✔️] Conferir acentos no phpMyAdmin;
- [✔️] Editar cliente e conferir se acentos continuam corretos.

### 7.2 Mensagens

- [✔️] Cadastrar, editar, excluir um registro e visualizar a mensagem de sucesso;
- [✔️] Recarregar a página e confirmar que a mensagem não reaparece;
- [✔️] Cadastrar cliente com CPF inválido e visualizar a mensagem de erro;
- [✔️] Cadastrar cliente com e-mail já existente e visualizar a mensagem de erro;
- [✔️] Confirmar que nenhuma mensagem apresenta SQL ou detalhes internos do banco.

### 7.3 CSRF e exclusões

- [✔️] O link `excluir.php?id=...` não exclui nada e só apresenta a pergunta;
- [✔️] Botão **Cancelar** na exclusão volta para a listagem sem excluir;
- [✔️] Apagar o campo `csrf_token` no F12 em um formulário de cadastro e enviar apresenta mensagem de formulário inválido;
- [✔️] Apagar o campo `csrf_token` no F12 em um formulário de exclusão e enviar apresenta mensagem de formulário inválido;
- [✔️] Alterar o valor do `csrf_token` no F12 e enviar apresenta mensagem de formulário inválido;
- [✔️] Excluir cliente com contrato tem a exclusão bloqueada;
- [✔️] Excluir um equipamento usado em contrato ou em tabela de preços tem a exclusão bloqueada;
- [✔️] Excluir uma tabela de preços com itens ou contratos tem a exclusão bloqueada;
- [✔️] Excluir um contrato com itens tem a exclusão bloqueada;
- [✔️] Excluir uma tabela de preços sem itens, mas usada por contrato, tem a exclusão bloqueada;
- [✔️] Excluir item de contrato não encerrado funciona e volta para a página do contrato;
- [✔️] Excluir item de contrato encerrado é bloqueado e volta para a página do contrato;
- [✔️] Excluir item de tabela de preços cujo equipamento está em contrato é bloqueado;
- [✔️] Excluir item de tabela de preços livre funciona e volta para a página da tabela;
- [✔️] Excluir cliente, equipamento, tabela de preços e contrato sem vínculos (registros de teste) funciona e mostra mensagem de sucesso.

### 7.4 Configuração e layout

- [✔️] Renomear `config.local.php` temporariamente: aparece mensagem de configuração não encontrada;
- [✔️] Remover a chave `'porta'` do `config.local.php`: aparece mensagem de configuração incompleta;
- [✔️] `config.local.php` sem `return`: aparece mensagem de configuração inválida;
- [✔️] Nome de banco inexistente em `config.local.php`: aparece apenas a mensagem genérica de conexão, sem texto do MySQL;
- [✔️] `git check-ignore -v projeto-locacao/config.local.php` aponta para o `.gitignore`;
- [✔️] Tela inicial em modo responsivo fica com os cartões em uma coluna;
- [✔️] Os quatro cartões da página inicial levam aos módulos corretos.

### 7.5 README

- [✔️] Clone limpo em pasta temporária: o `config.local.php` não aparece;
- [✔️] Banco apagado e reimportado seguindo o README;
- [✔️] `config.local.php` criado conforme o README, o sistema abre sem erro;
- [✔️] Nenhum passo do README travou ou ficou confuso;
- [✔️] Conferir se o `logger.log` abre pelo navegador.

## 8. Conclusão

- [✔️] Todos os itens da seção 7 foram executados;
- [✔️] Problemas encontrados foram corrigidos e testados novamente;
- [✔️] Prints das telas principais foram salvos.

**Obs.:** nenhum erro encontrado nesta rodada.