-- ============================================================
-- MP Reparos Automotivos — Banco de Dados
-- Importar no phpMyAdmin
-- ============================================================

CREATE DATABASE IF NOT EXISTS mp_reparos
  CHARACTER SET utf8mb4
  COLLATE utf8mb4_unicode_ci;

USE mp_reparos;

-- ── Clientes ──────────────────────────────────────────────
CREATE TABLE clientes (
  id         INT AUTO_INCREMENT PRIMARY KEY,
  nome       VARCHAR(150) NOT NULL,
  cpf_cnpj   VARCHAR(25),
  telefone   VARCHAR(25),
  email      VARCHAR(100),
  endereco   VARCHAR(255),
  criado_em  TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- ── Veículos ──────────────────────────────────────────────
CREATE TABLE veiculos (
  id          INT AUTO_INCREMENT PRIMARY KEY,
  cliente_id  INT,
  placa       VARCHAR(12) NOT NULL,
  modelo      VARCHAR(100),
  marca       VARCHAR(60),
  ano         VARCHAR(12),
  cor         VARCHAR(50),
  km          VARCHAR(25),
  seguradora  VARCHAR(100),
  chassi      VARCHAR(60),
  criado_em   TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (cliente_id) REFERENCES clientes(id) ON DELETE SET NULL
) ENGINE=InnoDB;

-- ── Agendamentos ──────────────────────────────────────────
CREATE TABLE agendamentos (
  id               INT AUTO_INCREMENT PRIMARY KEY,
  cliente_id       INT,
  veiculo_id       INT,
  data_agenda      DATE NOT NULL,
  hora             VARCHAR(8),
  status           ENUM('aguardando','andamento','pronto','cancelado') DEFAULT 'aguardando',
  observacao       TEXT,
  valor_total      DECIMAL(10,2) DEFAULT 0.00,
  criado_em        TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (cliente_id) REFERENCES clientes(id) ON DELETE SET NULL,
  FOREIGN KEY (veiculo_id) REFERENCES veiculos(id) ON DELETE SET NULL
) ENGINE=InnoDB;

-- ── Serviços do agendamento ────────────────────────────────
CREATE TABLE agendamento_servicos (
  id              INT AUTO_INCREMENT PRIMARY KEY,
  agendamento_id  INT NOT NULL,
  descricao       VARCHAR(255),
  valor           DECIMAL(10,2) DEFAULT 0.00,
  FOREIGN KEY (agendamento_id) REFERENCES agendamentos(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- ── Orçamentos ────────────────────────────────────────────
CREATE TABLE orcamentos (
  id             INT AUTO_INCREMENT PRIMARY KEY,
  numero         VARCHAR(20),
  cliente_id     INT,
  veiculo_id     INT,
  data_emissao   DATE,
  data_validade  DATE,
  status         VARCHAR(60) DEFAULT 'Aguardando aprovação',
  pagamento      VARCHAR(100),
  prazo          VARCHAR(100),
  garantia       VARCHAR(100),
  desconto       DECIMAL(5,2) DEFAULT 0.00,
  observacoes    TEXT,
  danos          TEXT,
  subtotal       DECIMAL(10,2) DEFAULT 0.00,
  total          DECIMAL(10,2) DEFAULT 0.00,
  criado_em      TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (cliente_id) REFERENCES clientes(id) ON DELETE SET NULL,
  FOREIGN KEY (veiculo_id) REFERENCES veiculos(id) ON DELETE SET NULL
) ENGINE=InnoDB;

-- ── Itens do orçamento ────────────────────────────────────
CREATE TABLE orcamento_itens (
  id            INT AUTO_INCREMENT PRIMARY KEY,
  orcamento_id  INT NOT NULL,
  descricao     VARCHAR(255),
  quantidade    DECIMAL(10,3) DEFAULT 1.000,
  unidade       VARCHAR(10) DEFAULT 'un',
  valor_unit    DECIMAL(10,2) DEFAULT 0.00,
  valor_total   DECIMAL(10,2) DEFAULT 0.00,
  FOREIGN KEY (orcamento_id) REFERENCES orcamentos(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- ── Dados de exemplo ──────────────────────────────────────
INSERT INTO clientes (nome, cpf_cnpj, telefone, email, endereco) VALUES
('João da Silva',   '123.456.789-00', '(51) 98888-1111', 'joao@email.com',   'Rua das Flores, 10 — Centro'),
('Maria Oliveira',  '987.654.321-00', '(51) 99777-2222', 'maria@email.com',  'Av. Brasil, 200 — Bairro Novo'),
('Carlos Souza',    '111.222.333-44', '(51) 97666-3333', 'carlos@email.com', 'Rua Ipiranga, 55 — Vila Olímpia');

INSERT INTO veiculos (cliente_id, placa, modelo, marca, ano, cor, km) VALUES
(1, 'ABC-1234', 'Strada Endurance', 'Fiat',       '2021/2022', 'Branco',   '45.000 km'),
(2, 'DEF-5678', 'HB20 Vision',      'Hyundai',    '2020/2021', 'Prata',    '32.000 km'),
(3, 'GHI-9012', 'Onix Plus',        'Chevrolet',  '2022/2023', 'Vermelho', '18.000 km');
