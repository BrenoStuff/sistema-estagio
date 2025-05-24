-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: May 16, 2025 at 02:45 AM
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
-- Table structure for table `admins`
--

CREATE TABLE `admins` (
  `admin_id` int(11) NOT NULL,
  `admin_login` varchar(255) NOT NULL,
  `admin_senha` varchar(255) NOT NULL,
  `admin_nome` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

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
  `cntr_escala_horario` varchar(127) NOT NULL,
  `cntr_termo_contrato` varchar(255) NOT NULL,
  `cntr_anexo_extra` varchar(255) DEFAULT NULL,
  `cntr_remunerado` tinyint(1) NOT NULL,
  `cntr_ativo` tinyint(1) NOT NULL,
  `cntr_id_relatorio_inicial` int(11) DEFAULT NULL,
  `cntr_id_relatorio_final` int(11) DEFAULT NULL,
  `cntr_id_empresa` int(11) NOT NULL,
  `cntr_id_usuario` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `contratos`
--

INSERT INTO `contratos` (`cntr_id`, `cntr_data_inicio`, `cntr_data_fim`, `cntr_escala_horario`, `cntr_termo_contrato`, `cntr_anexo_extra`, `cntr_remunerado`, `cntr_ativo`, `cntr_id_relatorio_inicial`, `cntr_id_relatorio_final`, `cntr_id_empresa`, `cntr_id_usuario`) VALUES
(1, '2025-03-01', '2025-07-18', '12h às 18h', 'link contrato', 'link anexo', 1, 1, NULL, NULL, 1, 1);

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
(1, 'GESTÃO DE TI');

-- --------------------------------------------------------

--
-- Table structure for table `empresas`
--

CREATE TABLE `empresas` (
  `empr_id` int(11) NOT NULL,
  `empr_nome` varchar(255) NOT NULL,
  `empr_contato_1` varchar(127) NOT NULL,
  `empr_contato_2` varchar(127) DEFAULT NULL,
  `empr_cidade` varchar(127) NOT NULL,
  `empr_endereco` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `empresas`
--

INSERT INTO `empresas` (`empr_id`, `empr_nome`, `empr_contato_1`, `empr_contato_2`, `empr_cidade`, `empr_endereco`) VALUES
(1, 'Jesmine Cook\'s', '(12) 9999-9999', NULL, 'Caraguatatuba - SP', 'Rua dos João, nº 88');

-- --------------------------------------------------------

--
-- Table structure for table `relatorio_final`
--

CREATE TABLE `relatorio_final` (
  `rfin_id` int(11) NOT NULL,
  `rfin_sintese_empresa` varchar(1023) NOT NULL,
  `rfin_assinatura` varchar(255) DEFAULT NULL,
  `rfin_aprovado` tinyint(1) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

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
  `rini_assinatura` varchar(255) NOT NULL,
  `rini_aprovado` tinyint(1) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `usuarios`
--

CREATE TABLE `usuarios` (
  `user_id` int(11) NOT NULL,
  `user_ra` int(11) NOT NULL,
  `user_login` varchar(255) NOT NULL,
  `user_senha` varchar(255) NOT NULL,
  `user_nome` varchar(255) NOT NULL,
  `user_contato` varchar(127) DEFAULT NULL,
  `user_id_curs` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `usuarios`
--

INSERT INTO `usuarios` (`user_id`, `user_ra`, `user_login`, `user_senha`, `user_nome`, `user_contato`, `user_id_curs`) VALUES
(1, 123, 'user', 'user', 'Usuarinio da Silva', '(12) 99730-0000', 1);

--
-- Indexes for dumped tables
--

--
-- Indexes for table `admins`
--
ALTER TABLE `admins`
  ADD PRIMARY KEY (`admin_id`);

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
-- AUTO_INCREMENT for table `admins`
--
ALTER TABLE `admins`
  MODIFY `admin_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `atv_estagio_fin`
--
ALTER TABLE `atv_estagio_fin`
  MODIFY `atvf_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `atv_estagio_ini`
--
ALTER TABLE `atv_estagio_ini`
  MODIFY `atvi_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `ausencias`
--
ALTER TABLE `ausencias`
  MODIFY `ausn_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `contratos`
--
ALTER TABLE `contratos`
  MODIFY `cntr_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `cursos`
--
ALTER TABLE `cursos`
  MODIFY `curs_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `empresas`
--
ALTER TABLE `empresas`
  MODIFY `empr_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `relatorio_final`
--
ALTER TABLE `relatorio_final`
  MODIFY `rfin_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `relatorio_inicial`
--
ALTER TABLE `relatorio_inicial`
  MODIFY `rini_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `usuarios`
--
ALTER TABLE `usuarios`
  MODIFY `user_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

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
