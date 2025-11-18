-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Nov 18, 2025 at 12:36 PM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `sistema-estagio`
--

-- --------------------------------------------------------

--
-- Table structure for table `atv_estagio_fin`
--

CREATE TABLE `atv_estagio_fin` (
  `atvf_id` int(11) NOT NULL,
  `atvf_atividade` varchar(1023) NOT NULL,
  `atvf_resumo` varchar(1023) NOT NULL,
  `atvf_disciplina_relacionada` varchar(1023) NOT NULL,
  `atvf_id_relatorio_fin` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `atv_estagio_fin`
--

INSERT INTO `atv_estagio_fin` (`atvf_id`, `atvf_atividade`, `atvf_resumo`, `atvf_disciplina_relacionada`, `atvf_id_relatorio_fin`) VALUES
(17, 'sad', 's', 'ad', 13);

-- --------------------------------------------------------

--
-- Table structure for table `atv_estagio_ini`
--

CREATE TABLE `atv_estagio_ini` (
  `atvi_id` int(11) NOT NULL,
  `atvi_atividade` varchar(1023) NOT NULL,
  `atvi_comentario` varchar(1023) DEFAULT NULL,
  `atvi_id_relatorio_ini` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `atv_estagio_ini`
--

INSERT INTO `atv_estagio_ini` (`atvi_id`, `atvi_atividade`, `atvi_comentario`, `atvi_id_relatorio_ini`) VALUES
(68, 'atv 1', 'comentário foda', 20),
(81, 'Atividade 1', 'Comentário 1', 26);

-- --------------------------------------------------------

--
-- Table structure for table `ausencias`
--

CREATE TABLE `ausencias` (
  `ausn_id` int(11) NOT NULL,
  `ausn_data` date NOT NULL,
  `ausn_id_contrato` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `contratos`
--

CREATE TABLE `contratos` (
  `cntr_id` int(11) NOT NULL,
  `cntr_data_inicio` date NOT NULL,
  `cntr_data_fim` date NOT NULL,
  `cntr_hora_inicio` time NOT NULL,
  `cntr_hora_final` time NOT NULL,
  `cntr_termo_contrato` varchar(255) NOT NULL,
  `cntr_anexo_extra` varchar(255) DEFAULT NULL,
  `cntr_tipo_estagio` varchar(63) NOT NULL,
  `cntr_ativo` tinyint(1) NOT NULL,
  `cntr_id_relatorio_inicial` int(11) DEFAULT NULL,
  `cntr_id_relatorio_final` int(11) DEFAULT NULL,
  `cntr_id_empresa` int(11) NOT NULL,
  `cntr_id_usuario` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `contratos`
--

INSERT INTO `contratos` (`cntr_id`, `cntr_data_inicio`, `cntr_data_fim`, `cntr_hora_inicio`, `cntr_hora_final`, `cntr_termo_contrato`, `cntr_anexo_extra`, `cntr_tipo_estagio`, `cntr_ativo`, `cntr_id_relatorio_inicial`, `cntr_id_relatorio_final`, `cntr_id_empresa`, `cntr_id_usuario`) VALUES
(1, '2025-03-01', '2025-07-18', '12:00:00', '18:00:00', 'link contrato', 'link anexo', '1', 1, 20, 13, 1, 1),
(2, '2025-05-19', '2025-10-30', '12:00:00', '18:00:00', 'backend/uploads/contratos/contrato_termo_4_1748574857_6018.pdf', NULL, '1', 1, 26, NULL, 2, 4);

-- --------------------------------------------------------

--
-- Table structure for table `cursos`
--

CREATE TABLE `cursos` (
  `curs_id` int(11) NOT NULL,
  `curs_nome` varchar(127) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `cursos`
--

INSERT INTO `cursos` (`curs_id`, `curs_nome`) VALUES
(1, 'GESTÃO DE TI'),
(2, 'ADS'),
(3, 'Marketing');

-- --------------------------------------------------------

--
-- Table structure for table `empresas`
--

CREATE TABLE `empresas` (
  `empr_id` int(11) NOT NULL,
  `empr_nome` varchar(255) NOT NULL,
  `empr_cnpj` int(14) NOT NULL,
  `empr_tipo` varchar(31) NOT NULL,
  `empr_contato_1` varchar(127) NOT NULL,
  `empr_contato_2` varchar(127) DEFAULT NULL,
  `empr_cidade` varchar(127) NOT NULL,
  `empr_endereco` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `empresas`
--

INSERT INTO `empresas` (`empr_id`, `empr_nome`, `empr_cnpj`, `empr_tipo`, `empr_contato_1`, `empr_contato_2`, `empr_cidade`, `empr_endereco`) VALUES
(1, 'Jesmine Cook\'s', 0, '', '(12) 9999-9999', NULL, 'Caraguatatuba - SP', 'Rua dos João, nº 88'),
(2, 'Jerry Uncook\'s', 0, '', '12032-23039', 'jerry@gmail.com', 'São Sebastião', 'Rua Joaninha, nº 98');

-- --------------------------------------------------------

--
-- Table structure for table `relatorio_final`
--

CREATE TABLE `relatorio_final` (
  `rfin_id` int(11) NOT NULL,
  `rfin_sintese_empresa` varchar(1023) NOT NULL,
  `rfin_anexo_1` varchar(255) DEFAULT NULL,
  `rfin_anexo_2` varchar(255) DEFAULT NULL,
  `rfin_assinatura` varchar(255) DEFAULT NULL,
  `rfin_aprovado` tinyint(1) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `relatorio_final`
--

INSERT INTO `relatorio_final` (`rfin_id`, `rfin_sintese_empresa`, `rfin_anexo_1`, `rfin_anexo_2`, `rfin_assinatura`, `rfin_aprovado`) VALUES
(13, 'asd', NULL, NULL, 'backend/uploads/relatorio-final/relatorio_assinado_13_1_1748571620_8328.pdf', 1);

-- --------------------------------------------------------

--
-- Table structure for table `relatorio_inicial`
--

CREATE TABLE `relatorio_inicial` (
  `rini_id` int(11) NOT NULL,
  `rini_como_ocorreu` varchar(1023) NOT NULL,
  `rini_dev_cronograma` varchar(1023) NOT NULL,
  `rini_preparacao_inicio` varchar(1023) NOT NULL,
  `rini_dificul_encontradas` varchar(1023) NOT NULL,
  `rini_aplic_conhecimento` varchar(1023) NOT NULL,
  `rini_novas_ferramentas` varchar(1023) NOT NULL,
  `rini_comentarios` varchar(1023) DEFAULT NULL,
  `rini_anexo_1` varchar(255) DEFAULT NULL,
  `rini_anexo_2` varchar(255) DEFAULT NULL,
  `rini_assinatura` varchar(255) DEFAULT NULL,
  `rini_aprovado` tinyint(1) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `relatorio_inicial`
--

INSERT INTO `relatorio_inicial` (`rini_id`, `rini_como_ocorreu`, `rini_dev_cronograma`, `rini_preparacao_inicio`, `rini_dificul_encontradas`, `rini_aplic_conhecimento`, `rini_novas_ferramentas`, `rini_comentarios`, `rini_anexo_1`, `rini_anexo_2`, `rini_assinatura`, `rini_aprovado`) VALUES
(20, 'Discorra sobre a forma como ocorreu a sua contratação:', 'Comente sobre o desenvolvimento de seu cronograma de estágio:', 'Discorra sobre como foi sua preparação para o início do estágio:', 'Discorra sobre as dificuldades encontradas no desenvolvimento e como foram solucionadas:', 'Discorra sobre as aplicações de conhecimentos desenvolvidos pelas disciplinas do curso, relacionando a atividade na qual ocorreu, as disciplinas envolvidas com elas e as contribuições que cada disciplina propiciou:', 'Houve contato com novas ferramentas, técnicas e/ou métodos, diferentes dos aprendidos durante o curso? Em caso positivo, cite-os e comente-os:', 'Outros comentários desejáveis:', '', '', 'backend/uploads/relatorio-inicial/relatorio_assinado_20_user_1_1748347602_5376.pdf', 1),
(26, 'Discorra sobre a forma como ocorreu a sua contratação:oi', 'Comente sobre o desenvolvimento de seu cronograma de estágio:', 'Discorra sobre como foi sua preparação para o início do estágio:', 'Discorra sobre as dificuldades encontradas no desenvolvimento e como foram solucionadas:', 'Discorra sobre as aplicações de conhecimentos desenvolvidos pelas disciplinas do curso, relacionando a atividade na qual ocorreu, as disciplinas envolvidas com elas e as contribuições que cada disciplina propiciou:Discorra sobre as aplicações de conhecimentos desenvolvidos pelas disciplinas do curso, relacionando a atividade na qual ocorreu, as disciplinas envolvidas com elas e as contribuições que cada disciplina propiciou:Discorra sobre as aplicações de conhecimentos desenvolvidos pelas disciplinas do curso, relacionando a atividade na qual ocorreu, as disciplinas envolvidas com elas e as contribuições que cada disciplina propiciou:Discorra sobre as aplicações de conhecimentos desenvolvidos pelas disciplinas do curso, relacionando a atividade na qual ocorreu, as disciplinas envolvidas com ela', 'Houve contato com novas ferramentas, técnicas e/ou métodos, diferentes dos aprendidos durante o curso? Em caso positivo, cite-os e comente-os:', 'Outros comentários desejáveis:', 'backend/uploads/relatorio-anexos\\relatorio_anexo__1_2_1762723171_9738.pdf', NULL, NULL, 0);

-- --------------------------------------------------------

--
-- Table structure for table `usuarios`
--

CREATE TABLE `usuarios` (
  `user_id` int(11) NOT NULL,
  `user_ra` int(11) DEFAULT NULL,
  `user_login` varchar(255) NOT NULL,
  `user_senha` varchar(255) NOT NULL,
  `user_nome` varchar(255) NOT NULL,
  `user_contato` varchar(127) DEFAULT NULL,
  `user_acesso` varchar(15) NOT NULL,
  `user_id_curs` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `usuarios`
--

INSERT INTO `usuarios` (`user_id`, `user_ra`, `user_login`, `user_senha`, `user_nome`, `user_contato`, `user_acesso`, `user_id_curs`) VALUES
(1, 123, 'user', '$2y$10$HP9cYLqpBikz6OoTFb46Cu7F9JYirfJ8aSU7T1QMXSsv4V1ezb8Xm', 'Usuarinio da Silva', '(12) 99730-0000', 'aluno', 1),
(3, NULL, 'admin', '$2y$10$HKX2BBMEMC0Lk99n/WTbG.Ra7VUuDNMBhUDmTqnfv1/LSYlOHlhAO', 'Adiminio da Silva', '(12) admin-admin', 'admin', NULL),
(4, 12322222, 'user2', '$2y$10$L2a2R2k4gs4NVu8qLdDquuXUan0VKv8XVE0bMe/pCiLHn1zpHuAJC', 'Usuario dois', '222-2222', 'aluno', 2);

--
-- Indexes for dumped tables
--

--
-- Indexes for table `atv_estagio_fin`
--
ALTER TABLE `atv_estagio_fin`
  ADD PRIMARY KEY (`atvf_id`),
  ADD KEY `fk_atvf_rfin` (`atvf_id_relatorio_fin`);

--
-- Indexes for table `atv_estagio_ini`
--
ALTER TABLE `atv_estagio_ini`
  ADD PRIMARY KEY (`atvi_id`),
  ADD KEY `fk_atvi_rini` (`atvi_id_relatorio_ini`);

--
-- Indexes for table `ausencias`
--
ALTER TABLE `ausencias`
  ADD PRIMARY KEY (`ausn_id`),
  ADD KEY `fk_ausn_cntr` (`ausn_id_contrato`);

--
-- Indexes for table `contratos`
--
ALTER TABLE `contratos`
  ADD PRIMARY KEY (`cntr_id`),
  ADD KEY `fk_cntr_empr` (`cntr_id_empresa`),
  ADD KEY `fk_cntr_user` (`cntr_id_usuario`),
  ADD KEY `fk_cntr_rini` (`cntr_id_relatorio_inicial`),
  ADD KEY `fk_cntr_rfin` (`cntr_id_relatorio_final`);

--
-- Indexes for table `cursos`
--
ALTER TABLE `cursos`
  ADD PRIMARY KEY (`curs_id`);

--
-- Indexes for table `empresas`
--
ALTER TABLE `empresas`
  ADD PRIMARY KEY (`empr_id`);

--
-- Indexes for table `relatorio_final`
--
ALTER TABLE `relatorio_final`
  ADD PRIMARY KEY (`rfin_id`);

--
-- Indexes for table `relatorio_inicial`
--
ALTER TABLE `relatorio_inicial`
  ADD PRIMARY KEY (`rini_id`);

--
-- Indexes for table `usuarios`
--
ALTER TABLE `usuarios`
  ADD PRIMARY KEY (`user_id`),
  ADD KEY `fk_user_curs` (`user_id_curs`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `atv_estagio_fin`
--
ALTER TABLE `atv_estagio_fin`
  MODIFY `atvf_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=24;

--
-- AUTO_INCREMENT for table `atv_estagio_ini`
--
ALTER TABLE `atv_estagio_ini`
  MODIFY `atvi_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=82;

--
-- AUTO_INCREMENT for table `ausencias`
--
ALTER TABLE `ausencias`
  MODIFY `ausn_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `contratos`
--
ALTER TABLE `contratos`
  MODIFY `cntr_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `cursos`
--
ALTER TABLE `cursos`
  MODIFY `curs_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `empresas`
--
ALTER TABLE `empresas`
  MODIFY `empr_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `relatorio_final`
--
ALTER TABLE `relatorio_final`
  MODIFY `rfin_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=17;

--
-- AUTO_INCREMENT for table `relatorio_inicial`
--
ALTER TABLE `relatorio_inicial`
  MODIFY `rini_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=27;

--
-- AUTO_INCREMENT for table `usuarios`
--
ALTER TABLE `usuarios`
  MODIFY `user_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `atv_estagio_fin`
--
ALTER TABLE `atv_estagio_fin`
  ADD CONSTRAINT `fk_atvf_rfin` FOREIGN KEY (`atvf_id_relatorio_fin`) REFERENCES `relatorio_final` (`rfin_id`);

--
-- Constraints for table `atv_estagio_ini`
--
ALTER TABLE `atv_estagio_ini`
  ADD CONSTRAINT `fk_atvi_rini` FOREIGN KEY (`atvi_id_relatorio_ini`) REFERENCES `relatorio_inicial` (`rini_id`);

--
-- Constraints for table `ausencias`
--
ALTER TABLE `ausencias`
  ADD CONSTRAINT `fk_ausn_cntr` FOREIGN KEY (`ausn_id_contrato`) REFERENCES `contratos` (`cntr_id`);

--
-- Constraints for table `contratos`
--
ALTER TABLE `contratos`
  ADD CONSTRAINT `fk_cntr_empr` FOREIGN KEY (`cntr_id_empresa`) REFERENCES `empresas` (`empr_id`),
  ADD CONSTRAINT `fk_cntr_rfin` FOREIGN KEY (`cntr_id_relatorio_final`) REFERENCES `relatorio_final` (`rfin_id`),
  ADD CONSTRAINT `fk_cntr_rini` FOREIGN KEY (`cntr_id_relatorio_inicial`) REFERENCES `relatorio_inicial` (`rini_id`),
  ADD CONSTRAINT `fk_cntr_user` FOREIGN KEY (`cntr_id_usuario`) REFERENCES `usuarios` (`user_id`);

--
-- Constraints for table `usuarios`
--
ALTER TABLE `usuarios`
  ADD CONSTRAINT `fk_user_curs` FOREIGN KEY (`user_id_curs`) REFERENCES `cursos` (`curs_id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
