-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Servidor: 127.0.0.1
-- Tiempo de generación: 20-11-2023 a las 16:12:46
-- Versión del servidor: 10.4.28-MariaDB
-- Versión de PHP: 8.2.4

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Base de datos: `tp_comanda`
--

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `mesas`
--

CREATE TABLE `mesas` (
  `id` int(5) NOT NULL,
  `estado` varchar(50) NOT NULL,
  `disponible` tinyint(1) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `mesas`
--

INSERT INTO `mesas` (`id`, `estado`, `disponible`) VALUES
(10012, 'cerrado', 1),
(10013, 'cerrado', 1),
(10014, 'cerrado', 1),
(10015, 'cerrado', 1),
(10016, 'cerrado', 1),
(10017, 'cerrado', 1),
(10018, 'cerrado', 1),
(10019, 'cerrado', 1),
(10020, 'cerrado', 1),
(10021, 'cerrado', 1),
(10022, 'cerrado', 1);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `pedidos`
--

CREATE TABLE `pedidos` (
  `idPedido` varchar(5) NOT NULL,
  `idMesa` int(5) NOT NULL,
  `idMozo` int(11) NOT NULL,
  `cliente` varchar(50) NOT NULL,
  `estado` varchar(50) NOT NULL,
  `fechaCreacion` datetime NOT NULL,
  `tiempoDePreparacion` int(11) NOT NULL,
  `productos` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL CHECK (json_valid(`productos`)),
  `precio` decimal(10,2) NOT NULL,
  `disponible` tinyint(1) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `productos`
--

CREATE TABLE `productos` (
  `id` int(11) NOT NULL,
  `nombre` varchar(50) NOT NULL,
  `sector` varchar(50) NOT NULL,
  `tiempoDePreparacion` int(11) NOT NULL,
  `precio` decimal(10,2) NOT NULL,
  `disponible` tinyint(1) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `productos`
--

INSERT INTO `productos` (`id`, `nombre`, `sector`, `tiempoDePreparacion`, `precio`, `disponible`) VALUES
(6, 'Milanesa a caballo', 'cocina', 20, 350.00, 1),
(7, 'Cerveza negra', 'choperas', 3, 200.00, 1),
(8, 'Ron con cola', 'tragos', 3, 650.00, 1),
(9, 'Hot Cakes', 'candy bar', 10, 280.00, 0),
(11, 'Panqueques', 'candy bar', 10, 150.00, 1),
(24, 'Fernet', 'tragos', 5, 500.00, 1),
(25, 'Empanadas', 'cocina', 15, 300.00, 1),
(28, 'Pizza', 'cocina', 25, 700.00, 1);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `usuarios`
--

CREATE TABLE `usuarios` (
  `id` int(11) NOT NULL,
  `nombre` varchar(50) NOT NULL,
  `usuario` varchar(50) NOT NULL,
  `contrasenia` varchar(50) NOT NULL,
  `sector` varchar(50) NOT NULL,
  `pedidos_pendiente` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL,
  `estado` varchar(50) NOT NULL,
  `fechaDeBaja` date DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `usuarios`
--

INSERT INTO `usuarios` (`id`, `nombre`, `usuario`, `contrasenia`, `sector`, `pedidos_pendiente`, `estado`, `fechaDeBaja`) VALUES
(15, 'Lucas', 'Lucas-380', '123', 'socio', '[]', 'disponible', NULL),
(16, 'Ana', 'Anita1323', '123', 'mozo', '[]', 'disponible', NULL),
(17, 'Pedro', 'Pedro694', 'lala', 'cocina', '[]', 'disponible', NULL),
(18, 'Juan', 'juanse1', '789', 'choperas', '[]', 'disponible', NULL),
(19, 'Mateo', 'Mate13', '8995', 'tragos', '[]', 'disponible', NULL),
(20, 'Enzo7', 'enzo23', 'e21z', 'candy bar', '[]', 'disponible', NULL),
(21, 'Carla', 'Carlita23', 'jija', 'mozo', '[]', 'disponible', NULL),
(22, 'Gabriell', 'gabi321', '852', 'tragos', '[]', 'disponible', NULL);

--
-- Índices para tablas volcadas
--

--
-- Indices de la tabla `mesas`
--
ALTER TABLE `mesas`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `productos`
--
ALTER TABLE `productos`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `usuarios`
--
ALTER TABLE `usuarios`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT de las tablas volcadas
--

--
-- AUTO_INCREMENT de la tabla `mesas`
--
ALTER TABLE `mesas`
  MODIFY `id` int(5) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10023;

--
-- AUTO_INCREMENT de la tabla `productos`
--
ALTER TABLE `productos`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=29;

--
-- AUTO_INCREMENT de la tabla `usuarios`
--
ALTER TABLE `usuarios`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=23;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
