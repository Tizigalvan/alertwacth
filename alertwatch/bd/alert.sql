-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Servidor: 127.0.0.1:3307
-- Tiempo de generación: 23-09-2025 a las 20:05:28
-- Versión del servidor: 10.4.32-MariaDB
-- Versión de PHP: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Base de datos: `alert`
--

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `configuracion`
--

CREATE TABLE `configuracion` (
  `id` int(11) NOT NULL,
  `min_bpm` int(11) DEFAULT NULL,
  `max_bpm` int(11) DEFAULT NULL,
  `contacto` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `configuracion`
--

INSERT INTO `configuracion` (`id`, `min_bpm`, `max_bpm`, `contacto`) VALUES
(1, 60, 100, '1133018812');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `frecuencia`
--

CREATE TABLE `frecuencia` (
  `id_fre` int(11) NOT NULL,
  `nombre` varchar(20) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_spanish2_ci;

--
-- Volcado de datos para la tabla `frecuencia`
--

INSERT INTO `frecuencia` (`id_fre`, `nombre`) VALUES
(5, 'cada 12 horas'),
(2, 'cada 2 horas'),
(6, 'cada 24 horas'),
(3, 'cada 4 horas'),
(4, 'cada 8 horas'),
(1, 'seleccionar horario');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `historial_diario`
--

CREATE TABLE `historial_diario` (
  `id` int(11) NOT NULL,
  `fecha` date NOT NULL,
  `min_bpm` int(11) NOT NULL,
  `max_bpm` int(11) NOT NULL,
  `promedio_bpm` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `historial_diario`
--

INSERT INTO `historial_diario` (`id`, `fecha`, `min_bpm`, `max_bpm`, `promedio_bpm`) VALUES
(1, '2025-09-18', 70, 99, 87);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `medicamentos`
--

CREATE TABLE `medicamentos` (
  `id_med` int(11) NOT NULL,
  `medicamen` text NOT NULL,
  `contenido` text NOT NULL,
  `gramos` varchar(14) NOT NULL,
  `horario` time NOT NULL,
  `frecuencia_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_spanish2_ci;

--
-- Volcado de datos para la tabla `medicamentos`
--

INSERT INTO `medicamentos` (`id_med`, `medicamen`, `contenido`, `gramos`, `horario`, `frecuencia_id`) VALUES
(1, 'aspirina', 'alivio del dolor', '500g', '22:47:00', 3),
(5, 'ibuprofeno', 'en tableta', '1g', '19:45:00', 2),
(11, 'valsartán', 'tableta', '80mg', '10:00:00', 6),
(13, 'metformina', 'tableta ', '500g', '08:00:00', 5),
(34, 'Tizi celu', 'Unico', '500g', '09:00:00', 4),
(35, 'Prueba celu', 'Unico', '8mg', '14:53:00', 3),
(36, 'H', 'Unico', '500g', '14:59:00', 3),
(37, 'Luz', 'Unico', '1mg', '15:02:00', 2),
(38, 'Rosana', 'Unico', '2mg', '15:05:00', 3);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `password_hash` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `users`
--

INSERT INTO `users` (`id`, `username`, `password_hash`) VALUES
(1, 'galvan', '$2y$10$QfRnPHYKdvKPkUojhn9VeuHVcwfxAV/5hSsDkWZ1.ZHPgJa5a24g.'),
(6, 'rosana', '$2y$10$tAn5Q2AodHk8EPckIz7VM.PsgEMJ48ZSYB1hEzi2Pv0hFS4hcLBpa'),
(7, 'Pepito', '$2y$10$mAItFNSdMWR7k6QEePF1auRkCtf86eu.ejc5EyNSmN86a57jXdvr.'),
(8, 'yo', '$2y$10$wYeiUBSRXzzIOqz2hQzHi.fWx/dQ6IWu/ztCwXRPRN79fnsizYMAe'),
(9, 'G', '$2y$10$BIwGPnBUf0vl/MAB1QKwzeBTPt1SCixwAggX1PQzUivVfbI1jxoGC'),
(10, 'Luz', '$2y$10$s5Ifir.wEEddrQOA5cIuNO80DR8sQRdrgiAWezIVoXDIXAvJST4ra'),
(11, 'Rosanaa', '$2y$10$xaTRJhDAKZsExkJ3hUQtveDErWTOk9bidmlus2V0feESLcKo.W7u6');

--
-- Índices para tablas volcadas
--

--
-- Indices de la tabla `configuracion`
--
ALTER TABLE `configuracion`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `frecuencia`
--
ALTER TABLE `frecuencia`
  ADD PRIMARY KEY (`id_fre`),
  ADD UNIQUE KEY `nombre` (`nombre`);

--
-- Indices de la tabla `historial_diario`
--
ALTER TABLE `historial_diario`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `fecha` (`fecha`);

--
-- Indices de la tabla `medicamentos`
--
ALTER TABLE `medicamentos`
  ADD PRIMARY KEY (`id_med`),
  ADD KEY `frecuencia_id` (`frecuencia_id`);

--
-- Indices de la tabla `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`);

--
-- AUTO_INCREMENT de las tablas volcadas
--

--
-- AUTO_INCREMENT de la tabla `configuracion`
--
ALTER TABLE `configuracion`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT de la tabla `frecuencia`
--
ALTER TABLE `frecuencia`
  MODIFY `id_fre` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT de la tabla `historial_diario`
--
ALTER TABLE `historial_diario`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT de la tabla `medicamentos`
--
ALTER TABLE `medicamentos`
  MODIFY `id_med` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=39;

--
-- AUTO_INCREMENT de la tabla `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- Restricciones para tablas volcadas
--

--
-- Filtros para la tabla `medicamentos`
--
ALTER TABLE `medicamentos`
  ADD CONSTRAINT `medicamentos_ibfk_2` FOREIGN KEY (`frecuencia_id`) REFERENCES `frecuencia` (`id_fre`) ON DELETE CASCADE ON UPDATE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
