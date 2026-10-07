DROP DATABASE IF EXISTS syspan_locacao_estagio_v2;

CREATE DATABASE IF NOT EXISTS syspan_locacao_estagio_v2
 DEFAULT CHARACTER SET utf8mb4
 DEFAULT COLLATE utf8mb4_general_ci;
USE syspan_locacao_estagio_v2;

-- Clientes
CREATE TABLE IF NOT EXISTS clientes (
 id INT AUTO_INCREMENT PRIMARY KEY,
 tipo_pessoa ENUM('F','J') NOT NULL DEFAULT 'F',
 nome VARCHAR(120) NOT NULL,
 cpf_cnpj VARCHAR(18) NOT NULL UNIQUE,
 email VARCHAR(120) NOT NULL,
 telefone VARCHAR(20) NULL,
 cep VARCHAR(9) NULL,
 endereco VARCHAR(150) NULL,
 numero VARCHAR(10) NULL,
 complemento VARCHAR(100) NULL,
 bairro VARCHAR(80) NULL,
 cidade VARCHAR(80) NULL,
 estado CHAR(2) NULL,
 observacao VARCHAR(255) NULL,
 created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
 updated_at DATETIME NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);
CREATE UNIQUE INDEX uk_clientes_email ON clientes(email);

-- Equipamentos
CREATE TABLE IF NOT EXISTS equipamentos (
 id INT AUTO_INCREMENT PRIMARY KEY,
 codigo VARCHAR(30) NOT NULL UNIQUE,
 descricao VARCHAR(120) NOT NULL,
 categoria VARCHAR(80) NULL,
 marca VARCHAR(80) NULL,
 modelo VARCHAR(80) NULL,
 numero_serie VARCHAR(80) NULL,
 ativo TINYINT(1) NOT NULL DEFAULT 1,
 observacao VARCHAR(255) NULL,
 created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
 updated_at DATETIME NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Preços dos Equipamentos
CREATE TABLE IF NOT EXISTS precos (
 id INT AUTO_INCREMENT PRIMARY KEY,
 nome VARCHAR(100) NOT NULL,
 descricao VARCHAR(255) NULL,
 ativo TINYINT(1) NOT NULL DEFAULT 1,
 created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
 updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Preços dos itens dos equipamentos (muitos preços por equipamento)
CREATE TABLE IF NOT EXISTS preco_itens (
 id INT AUTO_INCREMENT PRIMARY KEY,
 id_preco INT NOT NULL,
 id_equipamento INT NOT NULL,
 valor_diaria DECIMAL(10,2) NOT NULL,
 CONSTRAINT fk_preco
 FOREIGN KEY (id_preco) REFERENCES precos(id),
 CONSTRAINT fk_preco_equipamento
 FOREIGN KEY (id_equipamento) REFERENCES equipamentos(id)
);
CREATE UNIQUE INDEX uk_preco_equipamento ON preco_itens(id_preco, id_equipamento);

-- Contratos de Locação (simplificado)
CREATE TABLE IF NOT EXISTS contratos (
 id INT AUTO_INCREMENT PRIMARY KEY,
 id_cliente INT NOT NULL,
 id_preco INT NOT NULL,
 data_inicio DATE NOT NULL,
 data_fim DATE NOT NULL,
 status VARCHAR(15) NOT NULL DEFAULT 'AGENDADO',
 observacao VARCHAR(255) NULL,
 created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
 updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
 CONSTRAINT fk_contratos_cliente
 FOREIGN KEY (id_cliente) REFERENCES clientes(id),
 CONSTRAINT fk_contrato_preco
 FOREIGN KEY (id_preco) REFERENCES precos(id)
);

-- Itens do contrato (muitos equipamentos por contrato)
CREATE TABLE IF NOT EXISTS contrato_itens (
 id INT AUTO_INCREMENT PRIMARY KEY,
 id_contrato INT NOT NULL,
 id_equipamento INT NOT NULL,
 diaria DECIMAL(10,2) NOT NULL,
 qtd INT NOT NULL DEFAULT 1,
 CONSTRAINT fk_itens_contrato
 FOREIGN KEY (id_contrato) REFERENCES contratos(id),
 CONSTRAINT fk_itens_equip
 FOREIGN KEY (id_equipamento) REFERENCES equipamentos(id)
);

-- Inserção de Clientes
INSERT INTO clientes (tipo_pessoa, nome, cpf_cnpj, email, telefone, cep, endereco, numero, complemento, bairro, cidade, estado, observacao) VALUES 
('J', 'Rent Obras', '63.784.367/0001-63', 'rentobras@gmail.com', '(14) 99153-2789', '17601-290', 'Rua Cezário Nogueira Cabral', '412', 'Empresa', 'Vila Abarca', 'Tupã', 'SP', 'Comunicação somente até 15 horas.'),
('F', 'Marcelo Dias', '845.423.948-08', 'marcelodias@gmail.com', '(14) 98610-2385', '17625-050', 'Rua da Consolação', '75', 'Casa', 'Centro(Varpa)', 'Tupã', 'SP', 'Residência em obra.'),
('J', 'Geraldo e Manoel Ferragens ME', '71.312.773/0001-51', 'suporte@gmferragensme.com.br', '(11) 99949-6888', '06539-265', 'Alameda das Azaléias', '84', '', 'Alphaville', 'Santana de Parnaíba', 'SP', ''),
('F', 'Ana Luiza Hernandes', '123.356.554-00', 'analuizahernandes@gmail.com', '(14) 99856-2104', '17400-100', 'Rua Armando Sales de Oliveira', '810', 'Apartamento', 'Ferraropolis', 'Garça', 'SP', 'Precisa de contato por e-mail.'),
('J', 'Luna Soluções', '19.049.262/0001-79', 'lunasolucoes@gmail.com', '(14) 97562-4578', '17606-100', 'Rua Boaventura Avelar Campos', '59', 'Sala Comercial', 'Vila Nova II', 'Tupã', 'SP', ''),
('F', 'Leandro Figueiredo', '757.357.958-64', 'leandrofigueiredo@gmail.com', '(14) 99858-9989', '17606-210', 'Rua das Rosas', '866', 'Casa', 'Vila Alvorada', 'Tupã', 'SP', '');

-- Inserção de Equipamentos
INSERT INTO equipamentos (codigo, descricao, categoria, marca, modelo, numero_serie, ativo, observacao) VALUES 
('EQ0001', 'Gerador 10 kVA', 'Energia', 'Nagano', 'ND8GFLDE', 'NG10KVA-45896321', 1, 'Baixo consumo e alta durabilidade.'),
('EQ0002', 'Furadeira de Impacto 1/2"', 'Ferramentas Elétricas', 'Bosch', 'GSB 450 RE', '3603B00500', 1, 'Incluso chave de mandril.'),
('EQ0003', 'Martelo Demolidor 11kg', 'Construção Civil', 'DeWalt', 'D25911K', 'DW25911-889652', 1, 'Acompanha maleta e empunhadura auxiliar.'),
('EQ0004', 'Plataforma Elevatória Articulada', 'Acesso e Elevação', 'Haulotte', 'HA12 CJ', 'HA12CJ-998241', 1, 'Elétrica, indicada para uso interno e externo.'),
('EQ0005', 'Betoneira 400L', 'Construção Civil', 'Menegotti', 'Prime 400L', 'MN400L-332145', 1, 'Motor monofásico 2CV 220V.');

-- Inserção de Preços
INSERT INTO precos (nome, descricao) VALUES 
('Tabela Padrão', 'Tabela de preços inicial.'),
('Tabela Clientes VIP', 'Tabela para clientes fidelizados.'),
('Tabela Promocional', 'Tabela de preços promocionais.'),
('Tabela Construção Civil', 'Tabela de preços de máquinas pesadas.'),
('Tabela Eventos', 'Tabela de preços para eventos.');

-- Inserção de Itens do Preços
INSERT INTO preco_itens (id_preco, id_equipamento, valor_diaria) VALUES
(1, 1, 180.00),
(1, 2, 45.00),
(1, 3, 230.00),
(2, 1, 170.00),
(3, 4, 150.00);

-- Inserção de Contratos
INSERT INTO contratos (id_cliente, id_preco, data_inicio, data_fim, status, observacao) VALUES 
(1, 1, '2026-10-04', '2026-10-11', 'AGENDADO', 'Contrato futuro'),
(3, 2, '2026-08-03', '2026-08-14', 'ATIVO', 'Contrato em andamento'),
(2, 5, '2026-07-20', '2026-07-24', 'ENCERRADO', 'Contrato encerrado'),
(4, 3, '2027-04-05', '2027-05-05', 'AGENDADO', 'Contrato para obra futura'),
(5, 1, '2026-07-27', '2026-10-27', 'ATIVO', 'Contrato em andamento');

-- Inserção de Itens do Contratos
INSERT INTO contrato_itens (id_contrato, id_equipamento, diaria, qtd) VALUES 
(1, 1, 180.00, 3),
(1, 2, 45.00, 2),
(2, 3, 230.00, 1),
(2, 4, 150.00, 3);

-- Select Contratos + Clientes
SELECT contratos.id, clientes.nome as cliente, contratos.data_inicio, contratos.data_fim, contratos.status, contratos.observacao
FROM contratos INNER JOIN clientes ON contratos.id_cliente = clientes.id;

-- Select Itens + Equipamento com subtotal
SELECT contrato_itens.id_contrato, equipamentos.descricao as equipamento, contrato_itens.diaria, contrato_itens.qtd, (contrato_itens.diaria * contrato_itens.qtd) as subtotal
FROM contrato_itens INNER JOIN equipamentos ON contrato_itens.id_equipamento = equipamentos.id;