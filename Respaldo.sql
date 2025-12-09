CREATE DATABASE IF NOT EXISTS `proyectoambienteweb` /*!40100 DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci */;
USE `proyectoambienteweb`;

-- MySQL dump 10.13  Distrib 8.0.43, for Win64 (x86_64)
-- Host: 127.0.0.1    Database: proyectoambienteweb
-- ------------------------------------------------------


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!50503 SET NAMES utf8 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

-- --------------------------------------
-- TABLE tberror
-- --------------------------------------

DROP TABLE IF EXISTS `tberror`;
CREATE TABLE `tberror` (
  `ConsecutivoError` int(11) NOT NULL AUTO_INCREMENT,
  `Mensaje` varchar(8000) NOT NULL,
  `FechaHora` datetime NOT NULL,
  PRIMARY KEY (`ConsecutivoError`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO `tberror` VALUES
(1,'Unknown column ''CorreoElectronico'' in ''where clause''','2025-11-15 10:21:39'),
(2,'Cannot add or update a child row: a foreign key constraint fails','2025-11-15 10:26:36'),
(3,'Cannot add or update a child row: a foreign key constraint fails','2025-11-15 10:26:45');

-- --------------------------------------
-- TABLE tbperfil
-- --------------------------------------

DROP TABLE IF EXISTS `tbperfil`;
CREATE TABLE `tbperfil` (
  `ConsecutivoPerfil` int(11) NOT NULL AUTO_INCREMENT,
  `Nombre` varchar(50) NOT NULL,
  PRIMARY KEY (`ConsecutivoPerfil`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO `tbperfil` VALUES
(1,'Usuario Administrador'),
(2,'Usuario Regular');

-- --------------------------------------
-- TABLE tbusuario
-- --------------------------------------

DROP TABLE IF EXISTS `tbusuario`;
CREATE TABLE `tbusuario` (
  `ConsecutivoUsuario` int(11) NOT NULL AUTO_INCREMENT,
  `Identificacion` varchar(15) NOT NULL,
  `Nombre` varchar(255) NOT NULL,
  `Contrasenna` varchar(10) NOT NULL,
  `Estado` bit(1) NOT NULL,
  `ConsecutivoPerfil` int(11) NOT NULL,
  `CorreoElectronico` varchar(100) NOT NULL,
  PRIMARY KEY (`ConsecutivoUsuario`),
  KEY `ConsecutivoPerfil` (`ConsecutivoPerfil`),
  CONSTRAINT `tbusuario_ibfk_1` FOREIGN KEY (`ConsecutivoPerfil`)
    REFERENCES `tbperfil` (`ConsecutivoPerfil`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO `tbusuario` VALUES
(3,'117600318','TREJOS BONILLA HENRY GABRIEL','1234',_binary '',1,'htrejos00318@ufide.ac.cr');

-- --------------------------------------
-- PROCEDURES
-- --------------------------------------

DELIMITER ;;

CREATE PROCEDURE `ActualizarContrasenna`(
	pConsecutivoUsuario int(11), 
	pContrasennaGenerada varchar(10)
)
BEGIN
	UPDATE tbusuario
	SET Contrasenna = pContrasennaGenerada
	WHERE ConsecutivoUsuario = pConsecutivoUsuario;
END ;;

DELIMITER ;;
CREATE PROCEDURE `ActualizarPerfil`(
	pConsecutivoUsuario int(11), 
	pIdentificacion varchar(15),
	pNombre varchar(255),
	pCorreoElectronico varchar(100)
)
BEGIN
	UPDATE tbusuario
	SET Identificacion = pIdentificacion,
		Nombre = pNombre,
		CorreoElectronico = pCorreoElectronico
	WHERE ConsecutivoUsuario = pConsecutivoUsuario;
END ;;

DELIMITER ;;
CREATE PROCEDURE `ConsultarUsuario`(
	pConsecutivoUsuario int(11)
)
BEGIN
	SELECT U.*, P.Nombre AS NombrePerfil
	FROM tbusuario U
	INNER JOIN tbperfil P ON U.ConsecutivoPerfil = P.ConsecutivoPerfil
	WHERE ConsecutivoUsuario = pConsecutivoUsuario;
END ;;

DELIMITER ;;
CREATE PROCEDURE `CrearCuenta`(
	pIdentificacion varchar(15), 
	pNombre varchar(255),
	pCorreoElectronico varchar(100), 
	pContrasenna varchar(10)
)
BEGIN
	DECLARE vCuentaExistente INT;

	SELECT COUNT(*) INTO vCuentaExistente
	FROM tbusuario
	WHERE Identificacion = pIdentificacion
	OR CorreoElectronico = pCorreoElectronico;

	IF vCuentaExistente > 0 THEN
		SIGNAL SQLSTATE '45000'
			SET MESSAGE_TEXT = 'Ya existe un usuario con esa identificación o correo electrónico.';
	ELSE
		INSERT INTO tbusuario (Identificacion, Nombre, CorreoElectronico, Contrasenna, Estado, ConsecutivoPerfil)
		VALUES (pIdentificacion, pNombre, pCorreoElectronico, pContrasenna, 1, 2);
	END IF;
END ;;

DELIMITER ;;
CREATE PROCEDURE `RegistrarError`(
	pMensaje varchar(8000)
)
BEGIN
	INSERT INTO tberror (Mensaje, FechaHora)
	VALUES (pMensaje, NOW());
END ;;

DELIMITER ;;
CREATE PROCEDURE `ValidarCorreo`(
	pCorreoElectronico varchar(100)
)
BEGIN
	SELECT *
	FROM tbusuario
	WHERE CorreoElectronico = pCorreoElectronico
	  AND Estado = 1;
END ;;

DELIMITER ;;
CREATE PROCEDURE `ValidarCuenta`(
	pCorreoElectronico varchar(100),
	pContrasenna varchar(10)
)
BEGIN
	SELECT U.*, P.Nombre AS NombrePerfil
	FROM tbusuario U
	INNER JOIN tbperfil P ON U.ConsecutivoPerfil = P.ConsecutivoPerfil
	WHERE CorreoElectronico = pCorreoElectronico
	  AND Contrasenna = pContrasenna
	  AND Estado = 1;
END ;;

DELIMITER ;

-- ======================================
-- TABLE productos
-- ======================================
DROP TABLE IF EXISTS `productos`;
CREATE TABLE productos (
  id INT NOT NULL AUTO_INCREMENT,
  nombre VARCHAR(200) NOT NULL,
  descripcion TEXT,
  categoria VARCHAR(80),
  precio DECIMAL(12,2) DEFAULT 0,
  stock INT DEFAULT 0,
  unidad VARCHAR(50),
  proveedor VARCHAR(120),
  imagen VARCHAR(255),
  es_equipo TINYINT(1) DEFAULT 0,
  activo TINYINT(1) DEFAULT 1,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (id)
);


-- ======================================
-- INSERTAR PRODUCTOS NUEVOS
-- ======================================
INSERT INTO productos (nombre, descripcion, categoria, precio, stock, unidad, proveedor, imagen, es_equipo, activo) VALUES
('Cerveza Heineken Original Lata 6 pack - 2130 ml','6 unidades de lata — Importada — 5% Alcohol', 'Licorera', 5680, 35, 'pack', 'Heineken', 'https://walmartcr.vtexassets.com/arquivos/ids/901239-800-600?v=638796936110470000&width=800&height=600&aspect=true', 0, 1),

(', 6 Pack Lata - 350ml',
 '6 unidades — Marca Nacional — Estilo Lager',
 'Licorera', 5200, 50, 'pack', 'Cervecería Costa Rica', 'https://walmartcr.vtexassets.com/arquivos/ids/902606-800-600?v=638796984170130000&width=800&height=600&aspect=true', 0, 1),

('Cerveza Natural Light 6 pack - 355 ml',
 'Ligera — Baja en calorías — 6 unidades',
 'Licorera', 2900, 38, 'pack', 'Cervecería Costa Rica', 'https://walmartcr.vtexassets.com/arquivos/ids/717991-800-600?v=638624752312870000&width=800&height=600&aspect=true', 0, 1),

('15 Pack Cerveza Pilsen Lata - 350ml',
 'Pack ahorro — 15 unidades — Lager clásica',
 'Licorera', 9800, 22, 'pack', 'Cervecería Costa Rica', 'https://walmartcr.vtexassets.com/arquivos/ids/902423-800-600?v=638796978172730000&width=800&height=600&aspect=true', 0, 1),

('Cerveza Cezka Lager 4% Alcohol 24 Pack - 7920ml',
 '24 botellas — 4% Alcohol — Ahorro',
 'Licorera', 8000, 20, 'pack', 'Cezka', 'https://walmartcr.vtexassets.com/arquivos/ids/436187-800-600?v=638284557739270000&width=800&height=600&aspect=true', 0, 1),

('Cerveza Sol Vidrio - 330 ml',
 '100% Mexicana — Botella de vidrio',
 'Licorera', 1050, 60, 'unidad', 'Sol', 'https://walmartcr.vtexassets.com/arquivos/ids/975976-800-600?v=638899242375770000&width=800&height=600&aspect=true', 0, 1),

('Cerveza Imperial Original Lata 4Pack - 473 ml c/u',
 'Imperial regular — Pack de 4 — Lager',
 'Licorera', 4140, 55, 'pack', 'Cervecería Costa Rica', 'https://walmartcr.vtexassets.com/arquivos/ids/901932-800-600?v=638796957318270000&width=800&height=600&aspect=true', 0, 1),

('Cerveza Bohemia en lata 6 Pack - 2100 ml',
 'Pack de 6 — Cerveza Nacional',
 'Licorera', 3100, 32, 'pack', 'Cervecería Costa Rica', 'https://walmartcr.vtexassets.com/arquivos/ids/901262-800-600?v=638796936214200000&width=800&height=600&aspect=true', 0, 1),

('Cerveza Corona Extra Botella - 355ml',
 'Mexicana Premium — Botella individual',
 'Licorera', 1200, 45, 'unidad', 'Corona', 'https://walmartcr.vtexassets.com/arquivos/ids/1020886-800-600?v=638985672026930000&width=800&height=600&aspect=true', 0, 1),

('Cerveza Imperial Michelada Mango Verde Limón y Sal - 350 ml',
 'Michelada lista para tomar — Sabor Mango Verde',
 'Licorera', 920, 70, 'unidad', 'Cervecería Costa Rica', 'https://walmartcr.vtexassets.com/arquivos/ids/891775-800-600?v=638816249217100000&width=800&height=600&aspect=true', 0, 1),

('Cerveza Imperial Michelada Verde Limón y Sal - 350 ml',
 'Cerveza con sabor limón — Tipo michelada',
 'Licorera', 920, 65, 'unidad', 'Cervecería Costa Rica', 'https://walmartcr.vtexassets.com/arquivos/ids/891833-800-600?v=638816245902900000&width=800&height=600&aspect=true', 0, 1);
