-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Tempo de geração: 05/06/2026 às 15:28
-- Versão do servidor: 10.4.32-MariaDB
-- Versão do PHP: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Banco de dados: `mp_reparos`
--

-- --------------------------------------------------------

--
-- Estrutura para tabela `agendamentos`
--

CREATE TABLE `agendamentos` (
  `id` int(11) NOT NULL,
  `cliente_id` int(11) DEFAULT NULL,
  `veiculo_id` int(11) DEFAULT NULL,
  `data_agenda` date NOT NULL,
  `hora` varchar(8) DEFAULT NULL,
  `status` enum('aguardando','andamento','pronto','cancelado') DEFAULT 'aguardando',
  `observacao` text DEFAULT NULL,
  `valor_total` decimal(10,2) DEFAULT 0.00,
  `criado_em` timestamp NOT NULL DEFAULT current_timestamp(),
  `ano` int(11) DEFAULT NULL,
  `mes` int(11) DEFAULT NULL,
  `semana` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estrutura para tabela `agendamento_servicos`
--

CREATE TABLE `agendamento_servicos` (
  `id` int(11) NOT NULL,
  `agendamento_id` int(11) NOT NULL,
  `descricao` varchar(255) DEFAULT NULL,
  `valor` decimal(10,2) DEFAULT 0.00
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estrutura para tabela `clientes`
--

CREATE TABLE `clientes` (
  `id` int(11) NOT NULL,
  `nome` varchar(150) NOT NULL,
  `cpf_cnpj` varchar(25) DEFAULT NULL,
  `telefone` varchar(25) DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL,
  `endereco` varchar(255) DEFAULT NULL,
  `criado_em` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Despejando dados para a tabela `clientes`
--

INSERT INTO `clientes` (`id`, `nome`, `cpf_cnpj`, `telefone`, `email`, `endereco`, `criado_em`) VALUES
(4, 'Alx', NULL, NULL, NULL, NULL, '2026-06-02 13:23:41'),
(5, 'Pequeno', NULL, NULL, NULL, NULL, '2026-06-02 13:57:25'),
(8, 'Smotors', NULL, NULL, NULL, NULL, '2026-06-02 18:12:53'),
(12, 'paulo', NULL, NULL, NULL, NULL, '2026-06-02 18:21:58');

-- --------------------------------------------------------

--
-- Estrutura para tabela `custos_servico`
--

CREATE TABLE `custos_servico` (
  `id` int(11) NOT NULL,
  `orcamento_id` int(11) DEFAULT NULL,
  `ordem_id` int(11) DEFAULT NULL,
  `tipo` varchar(50) NOT NULL,
  `descricao` varchar(255) NOT NULL,
  `valor` decimal(10,2) DEFAULT 0.00,
  `data_custo` date DEFAULT curdate(),
  `criado_em` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estrutura para tabela `faturas`
--

CREATE TABLE `faturas` (
  `id` int(11) NOT NULL,
  `numero` varchar(30) DEFAULT NULL,
  `cliente_id` int(11) DEFAULT NULL,
  `data_emissao` date DEFAULT curdate(),
  `status` varchar(50) DEFAULT 'Aberta',
  `observacoes` text DEFAULT NULL,
  `total` decimal(10,2) DEFAULT 0.00,
  `criado_em` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Despejando dados para a tabela `faturas`
--

INSERT INTO `faturas` (`id`, `numero`, `cliente_id`, `data_emissao`, `status`, `observacoes`, `total`, `criado_em`) VALUES
(4, '0004', 5, '2026-06-04', 'Aberta', 'pagamento referente aos veiculos acima', 1000.00, '2026-06-04 14:26:19');

-- --------------------------------------------------------

--
-- Estrutura para tabela `fatura_itens`
--

CREATE TABLE `fatura_itens` (
  `id` int(11) NOT NULL,
  `fatura_id` int(11) NOT NULL,
  `orcamento_id` int(11) DEFAULT NULL,
  `descricao` varchar(255) NOT NULL,
  `veiculo` varchar(150) DEFAULT NULL,
  `placa` varchar(20) DEFAULT NULL,
  `valor` decimal(10,2) DEFAULT 0.00
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Despejando dados para a tabela `fatura_itens`
--

INSERT INTO `fatura_itens` (`id`, `fatura_id`, `orcamento_id`, `descricao`, `veiculo`, `placa`, `valor`) VALUES
(7, 4, 6, 'Serviço de funilaria e pintura', 'Corsa', 'ABC1234', 1000.00);

-- --------------------------------------------------------

--
-- Estrutura para tabela `orcamentos`
--

CREATE TABLE `orcamentos` (
  `id` int(11) NOT NULL,
  `numero` varchar(20) DEFAULT NULL,
  `cliente_id` int(11) DEFAULT NULL,
  `veiculo_id` int(11) DEFAULT NULL,
  `data_emissao` date DEFAULT NULL,
  `data_validade` date DEFAULT NULL,
  `status` varchar(60) DEFAULT 'Aguardando aprovação',
  `pagamento` varchar(100) DEFAULT NULL,
  `prazo` varchar(100) DEFAULT NULL,
  `garantia` varchar(100) DEFAULT NULL,
  `desconto` decimal(5,2) DEFAULT 0.00,
  `observacoes` text DEFAULT NULL,
  `danos` text DEFAULT NULL,
  `subtotal` decimal(10,2) DEFAULT 0.00,
  `total` decimal(10,2) DEFAULT 0.00,
  `criado_em` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Despejando dados para a tabela `orcamentos`
--

INSERT INTO `orcamentos` (`id`, `numero`, `cliente_id`, `veiculo_id`, `data_emissao`, `data_validade`, `status`, `pagamento`, `prazo`, `garantia`, `desconto`, `observacoes`, `danos`, `subtotal`, `total`, `criado_em`) VALUES
(6, 'pequeno', 5, 16, '2026-06-04', '2026-07-04', 'Aguardando aprovação', 'À vista — Pix', '7 dias uteis', '30 dias', 0.00, 'orçamento valido conforme as informações acima', '{\"cliente_nome\":\"Pequeno\",\"veiculo_desc\":\"Corsa \",\"placa\":\"abc1234\",\"cor\":\"branco\",\"tipo_tinta\":\"PU\",\"seguradora\":\"revenda\",\"areas\":[\"Tampa traseira\",\"Para-choque traseiro\"],\"diagnostico\":\"calha do teto lado esquerdo\"}', 1000.00, 1000.00, '2026-06-04 13:55:26'),
(7, '001', 5, 17, '2026-06-04', '2026-07-04', 'Em andamento', 'À vista — Dinheiro', '7 dias uteis', '30 dias', 0.00, 'Orçamento valido segundo os dados acima', '{\"cliente_nome\":\"pequeno\",\"veiculo_desc\":\"polo \",\"placa\":\"naruto\",\"cor\":\"preto\",\"tipo_tinta\":\"Poliéster\",\"seguradora\":\"revenda\",\"areas\":[\"Para-choque dianteiro\",\"Capô\",\"Paralama esquerdo\",\"Paralama direito\",\"Porta dianteira esquerda\",\"Porta dianteira direita\",\"Porta traseira esquerda\",\"Porta traseira direita\",\"Teto\",\"Tampa traseira\",\"Para-choque traseiro\",\"Caixa de ar\"],\"diagnostico\":\"\"}', 0.00, 0.00, '2026-06-04 14:53:38'),
(8, '1', 5, 18, '2026-06-04', '2026-07-04', 'Aprovado', 'À vista — Pix', '7 dias uteis', '30 dias', 0.00, 'orçamento valido conforme as informações acima', '{\"cliente_nome\":\"pequeno\",\"veiculo_desc\":\"astra\",\"placa\":\"mao1f22\",\"cor\":\"prata\",\"tipo_tinta\":\"Poliéster\",\"seguradora\":\"particular\",\"areas\":[],\"diagnostico\":\"\"}', 500.00, 500.00, '2026-06-04 18:28:56');

-- --------------------------------------------------------

--
-- Estrutura para tabela `orcamento_itens`
--

CREATE TABLE `orcamento_itens` (
  `id` int(11) NOT NULL,
  `orcamento_id` int(11) NOT NULL,
  `descricao` varchar(255) DEFAULT NULL,
  `quantidade` decimal(10,3) DEFAULT 1.000,
  `unidade` varchar(10) DEFAULT 'un',
  `valor_unit` decimal(10,2) DEFAULT 0.00,
  `valor_total` decimal(10,2) DEFAULT 0.00
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Despejando dados para a tabela `orcamento_itens`
--

INSERT INTO `orcamento_itens` (`id`, `orcamento_id`, `descricao`, `quantidade`, `unidade`, `valor_unit`, `valor_total`) VALUES
(14, 6, 'tampa traseira', 1.000, '0', 250.00, 250.00),
(15, 6, 'paarachoque traseiro', 1.000, '0', 250.00, 250.00),
(16, 6, 'calha do teto lado esquerdo', 1.000, '0', 250.00, 250.00),
(17, 6, 'porta dianteira lado esquerdo', 1.000, '0', 250.00, 250.00),
(19, 8, 'teto', 1.000, '0', 500.00, 500.00);

-- --------------------------------------------------------

--
-- Estrutura para tabela `ordens_servico`
--

CREATE TABLE `ordens_servico` (
  `id` int(11) NOT NULL,
  `orcamento_id` int(11) DEFAULT NULL,
  `cliente_id` int(11) DEFAULT NULL,
  `veiculo_id` int(11) DEFAULT NULL,
  `numero_os` varchar(30) DEFAULT NULL,
  `status` varchar(50) DEFAULT 'Aguardando',
  `prioridade` varchar(30) DEFAULT 'Normal',
  `tarefas` text DEFAULT NULL,
  `observacoes` text DEFAULT NULL,
  `data_criacao` datetime DEFAULT current_timestamp(),
  `data_entrega` date DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Despejando dados para a tabela `ordens_servico`
--

INSERT INTO `ordens_servico` (`id`, `orcamento_id`, `cliente_id`, `veiculo_id`, `numero_os`, `status`, `prioridade`, `tarefas`, `observacoes`, `data_criacao`, `data_entrega`) VALUES
(1, NULL, NULL, 8, 'OS-0004', 'Finalizado', 'Normal', 'Áreas danificadas:\n- Para-choque dianteiro\n- Capô\n- Paralama direito\n- Porta dianteira esquerda\n\nDiagnóstico técnico:\nsda\n\nTipo de tinta: Metálica\nSeguradora / Revenda: das', 'sdasd', '2026-06-02 11:27:29', NULL),
(2, NULL, 4, 15, 'OS-0005', 'Aguardando', 'Normal', 'Áreas danificadas:\n- Tampa traseira\n\nTipo de tinta: PU\nSeguradora / Revenda: revenda', '', '2026-06-02 17:15:50', NULL),
(3, 6, 5, 16, 'OS-0006', 'Aguardando', 'Normal', 'Áreas danificadas:\n- Tampa traseira\n- Para-choque traseiro\n\nDiagnóstico técnico:\ncalha do teto lado esquerdo\n\nTipo de tinta: PU\nSeguradora / Revenda: revenda', 'orçamento valido conforme as informações acima', '2026-06-04 10:55:26', NULL),
(4, 7, 5, 17, 'OS-0007', 'Aguardando', 'Normal', 'Áreas danificadas:\n- Para-choque dianteiro\n- Capô\n- Paralama esquerdo\n- Paralama direito\n- Porta dianteira esquerda\n- Porta dianteira direita\n- Porta traseira esquerda\n- Porta traseira direita\n- Teto\n- Tampa traseira\n- Para-choque traseiro\n- Caixa de ar\n\nTipo de tinta: Poliéster\nSeguradora / Revenda: revenda', 'Orçamento valido segundo os dados acima', '2026-06-04 11:53:38', NULL),
(5, 8, 5, 18, 'OS-0008', 'Aguardando', 'Normal', 'Tipo de tinta: Poliéster\nSeguradora / Revenda: particular', 'orçamento valido conforme as informações acima', '2026-06-04 15:28:56', NULL);

-- --------------------------------------------------------

--
-- Estrutura para tabela `veiculos`
--

CREATE TABLE `veiculos` (
  `id` int(11) NOT NULL,
  `cliente_id` int(11) DEFAULT NULL,
  `placa` varchar(12) NOT NULL,
  `modelo` varchar(100) DEFAULT NULL,
  `marca` varchar(60) DEFAULT NULL,
  `ano` varchar(12) DEFAULT NULL,
  `cor` varchar(50) DEFAULT NULL,
  `km` varchar(25) DEFAULT NULL,
  `seguradora` varchar(100) DEFAULT NULL,
  `chassi` varchar(60) DEFAULT NULL,
  `criado_em` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Despejando dados para a tabela `veiculos`
--

INSERT INTO `veiculos` (`id`, `cliente_id`, `placa`, `modelo`, `marca`, `ano`, `cor`, `km`, `seguradora`, `chassi`, `criado_em`) VALUES
(8, NULL, 'DAS', 'dsa', NULL, NULL, 'sda', NULL, NULL, NULL, '2026-06-02 14:22:51'),
(15, 4, 'ABC1234', 'picasso', NULL, NULL, 'preta', NULL, NULL, NULL, '2026-06-02 20:15:50'),
(16, 5, 'ABC1234', 'Corsa', NULL, NULL, 'branco', NULL, NULL, NULL, '2026-06-04 13:55:26'),
(17, 5, 'NARUTO', 'polo', NULL, NULL, 'preto', NULL, NULL, NULL, '2026-06-04 14:53:38'),
(18, 5, 'MAO1F22', 'astra', NULL, NULL, 'prata', NULL, NULL, NULL, '2026-06-04 18:28:56');

--
-- Índices para tabelas despejadas
--

--
-- Índices de tabela `agendamentos`
--
ALTER TABLE `agendamentos`
  ADD PRIMARY KEY (`id`),
  ADD KEY `cliente_id` (`cliente_id`),
  ADD KEY `veiculo_id` (`veiculo_id`);

--
-- Índices de tabela `agendamento_servicos`
--
ALTER TABLE `agendamento_servicos`
  ADD PRIMARY KEY (`id`),
  ADD KEY `agendamento_id` (`agendamento_id`);

--
-- Índices de tabela `clientes`
--
ALTER TABLE `clientes`
  ADD PRIMARY KEY (`id`);

--
-- Índices de tabela `custos_servico`
--
ALTER TABLE `custos_servico`
  ADD PRIMARY KEY (`id`);

--
-- Índices de tabela `faturas`
--
ALTER TABLE `faturas`
  ADD PRIMARY KEY (`id`),
  ADD KEY `cliente_id` (`cliente_id`);

--
-- Índices de tabela `fatura_itens`
--
ALTER TABLE `fatura_itens`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fatura_id` (`fatura_id`),
  ADD KEY `orcamento_id` (`orcamento_id`);

--
-- Índices de tabela `orcamentos`
--
ALTER TABLE `orcamentos`
  ADD PRIMARY KEY (`id`),
  ADD KEY `cliente_id` (`cliente_id`),
  ADD KEY `veiculo_id` (`veiculo_id`);

--
-- Índices de tabela `orcamento_itens`
--
ALTER TABLE `orcamento_itens`
  ADD PRIMARY KEY (`id`),
  ADD KEY `orcamento_id` (`orcamento_id`);

--
-- Índices de tabela `ordens_servico`
--
ALTER TABLE `ordens_servico`
  ADD PRIMARY KEY (`id`),
  ADD KEY `orcamento_id` (`orcamento_id`),
  ADD KEY `cliente_id` (`cliente_id`),
  ADD KEY `veiculo_id` (`veiculo_id`);

--
-- Índices de tabela `veiculos`
--
ALTER TABLE `veiculos`
  ADD PRIMARY KEY (`id`),
  ADD KEY `cliente_id` (`cliente_id`);

--
-- AUTO_INCREMENT para tabelas despejadas
--

--
-- AUTO_INCREMENT de tabela `agendamentos`
--
ALTER TABLE `agendamentos`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT de tabela `agendamento_servicos`
--
ALTER TABLE `agendamento_servicos`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT de tabela `clientes`
--
ALTER TABLE `clientes`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT de tabela `custos_servico`
--
ALTER TABLE `custos_servico`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT de tabela `faturas`
--
ALTER TABLE `faturas`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT de tabela `fatura_itens`
--
ALTER TABLE `fatura_itens`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT de tabela `orcamentos`
--
ALTER TABLE `orcamentos`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT de tabela `orcamento_itens`
--
ALTER TABLE `orcamento_itens`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=20;

--
-- AUTO_INCREMENT de tabela `ordens_servico`
--
ALTER TABLE `ordens_servico`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT de tabela `veiculos`
--
ALTER TABLE `veiculos`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=19;

--
-- Restrições para tabelas despejadas
--

--
-- Restrições para tabelas `agendamentos`
--
ALTER TABLE `agendamentos`
  ADD CONSTRAINT `agendamentos_ibfk_1` FOREIGN KEY (`cliente_id`) REFERENCES `clientes` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `agendamentos_ibfk_2` FOREIGN KEY (`veiculo_id`) REFERENCES `veiculos` (`id`) ON DELETE SET NULL;

--
-- Restrições para tabelas `agendamento_servicos`
--
ALTER TABLE `agendamento_servicos`
  ADD CONSTRAINT `agendamento_servicos_ibfk_1` FOREIGN KEY (`agendamento_id`) REFERENCES `agendamentos` (`id`) ON DELETE CASCADE;

--
-- Restrições para tabelas `faturas`
--
ALTER TABLE `faturas`
  ADD CONSTRAINT `faturas_ibfk_1` FOREIGN KEY (`cliente_id`) REFERENCES `clientes` (`id`) ON DELETE SET NULL;

--
-- Restrições para tabelas `fatura_itens`
--
ALTER TABLE `fatura_itens`
  ADD CONSTRAINT `fatura_itens_ibfk_1` FOREIGN KEY (`fatura_id`) REFERENCES `faturas` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fatura_itens_ibfk_2` FOREIGN KEY (`orcamento_id`) REFERENCES `orcamentos` (`id`) ON DELETE SET NULL;

--
-- Restrições para tabelas `orcamentos`
--
ALTER TABLE `orcamentos`
  ADD CONSTRAINT `orcamentos_ibfk_1` FOREIGN KEY (`cliente_id`) REFERENCES `clientes` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `orcamentos_ibfk_2` FOREIGN KEY (`veiculo_id`) REFERENCES `veiculos` (`id`) ON DELETE SET NULL;

--
-- Restrições para tabelas `orcamento_itens`
--
ALTER TABLE `orcamento_itens`
  ADD CONSTRAINT `orcamento_itens_ibfk_1` FOREIGN KEY (`orcamento_id`) REFERENCES `orcamentos` (`id`) ON DELETE CASCADE;

--
-- Restrições para tabelas `ordens_servico`
--
ALTER TABLE `ordens_servico`
  ADD CONSTRAINT `ordens_servico_ibfk_1` FOREIGN KEY (`orcamento_id`) REFERENCES `orcamentos` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `ordens_servico_ibfk_2` FOREIGN KEY (`cliente_id`) REFERENCES `clientes` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `ordens_servico_ibfk_3` FOREIGN KEY (`veiculo_id`) REFERENCES `veiculos` (`id`) ON DELETE SET NULL;

--
-- Restrições para tabelas `veiculos`
--
ALTER TABLE `veiculos`
  ADD CONSTRAINT `veiculos_ibfk_1` FOREIGN KEY (`cliente_id`) REFERENCES `clientes` (`id`) ON DELETE SET NULL;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
