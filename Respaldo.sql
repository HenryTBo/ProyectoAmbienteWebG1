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

CREATE PROCEDURE `ConsultarUsuario`(
	pConsecutivoUsuario int(11)
)
BEGIN
	SELECT U.*, P.Nombre AS NombrePerfil
	FROM tbusuario U
	INNER JOIN tbperfil P ON U.ConsecutivoPerfil = P.ConsecutivoPerfil
	WHERE ConsecutivoUsuario = pConsecutivoUsuario;
END ;;

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

CREATE PROCEDURE `RegistrarError`(
	pMensaje varchar(8000)
)
BEGIN
	INSERT INTO tberror (Mensaje, FechaHora)
	VALUES (pMensaje, NOW());
END ;;

CREATE PROCEDURE `ValidarCorreo`(
	pCorreoElectronico varchar(100)
)
BEGIN
	SELECT *
	FROM tbusuario
	WHERE CorreoElectronico = pCorreoElectronico
	  AND Estado = 1;
END ;;

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

-- --------------------------------------
-- TABLE productos (tu tabla nueva)
-- --------------------------------------

CREATE TABLE `productos` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `nombre` VARCHAR(200) NOT NULL,
  `descripcion` TEXT,
  `categoria` VARCHAR(80),
  `precio` DECIMAL(12,2) DEFAULT 0,
  `stock` INT DEFAULT 0,
  `unidad` VARCHAR(50),
  `proveedor` VARCHAR(120),
  `imagen` VARCHAR(255),
  `es_equipo` TINYINT(1) DEFAULT 0,
  `activo` TINYINT(1) DEFAULT 1,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO productos 
(nombre, descripcion, categoria, precio, stock, unidad, proveedor, imagen, es_equipo)
VALUES
('Cerveza Imperial 24x355ml',
 'Pack de 15 unidades - ideal para bares.',
 'Licorera',
 45000,
 120,
 'pack',
 'Cervecería Costa Rica',
 'imagenes/15packimperial.webp',
 0),

('Gaseosa Coca-Cola 2L',
 'Botella de 2 litros para supermercados y ventas al detalle.',
 'Supermercado',
 800,
 500,
 'unidad',
 'Coca-Cola',
 'imagenes/CocaCola.webp',
 0),

('Congelador Exhibidor 200L',
 'Congelador vertical para negocios — disponible para venta o alquiler.',
 'Mayoreo',
 650000,
 5,
 'unidad',
 'Equipamientos JJ',
 'imagenes/camara1.jpg',
 1);

 UPDATE productos 
SET imagen = 'https://walmartcr.vtexassets.com/arquivos/ids/901535-1200-900?v=638796945249100000&width=1200&height=900&aspect=true' 
WHERE id = 1;

UPDATE productos 
SET imagen = 'https://walmartcr.vtexassets.com/arquivos/ids/954737-1200-900?v=638866434018730000&width=1200&height=900&aspect=true' 
WHERE id = 2;

UPDATE productos 
SET imagen = 'https://refrisander.com/wp-content/uploads/2024/06/200-lt-02-1-768x768.jpg' 
WHERE id = 3;
