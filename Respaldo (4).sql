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

-- ================================================================
--  PROCEDIMIENTOS ALMACENADOS PARA GESTIÓN DE PRODUCTOS
--  Estas rutinas encapsulan la lógica CRUD de la tabla productos
--  para que ninguna interacción con la base de datos se realice
--  mediante sentencias directas desde la aplicación.
-- ================================================================

DELIMITER ;;

CREATE PROCEDURE `sp_Productos_ListarActivos`()
BEGIN
    SELECT id, nombre, descripcion, categoria, precio, stock,
           unidad, proveedor, imagen, es_equipo, activo
    FROM productos
    WHERE activo = 1
    ORDER BY categoria, nombre;
END;;

CREATE PROCEDURE `sp_Producto_ObtenerPorId`(
    IN pId INT
)
BEGIN
    SELECT id, nombre, descripcion, categoria, precio, stock,
           unidad, proveedor, imagen, es_equipo, activo
    FROM productos
    WHERE id = pId
    LIMIT 1;
END;;

CREATE PROCEDURE `sp_Producto_Crear`(
    IN pNombre VARCHAR(255),
    IN pDescripcion TEXT,
    IN pCategoria VARCHAR(100),
    IN pPrecio DECIMAL(12,2),
    IN pStock INT,
    IN pUnidad VARCHAR(50),
    IN pProveedor VARCHAR(150),
    IN pImagen VARCHAR(500),
    IN pEsEquipo TINYINT(1),
    IN pActivo TINYINT(1)
)
BEGIN
    INSERT INTO productos (nombre, descripcion, categoria, precio, stock,
                           unidad, proveedor, imagen, es_equipo, activo)
    VALUES (pNombre, pDescripcion, pCategoria, pPrecio, pStock,
            pUnidad, pProveedor, pImagen, pEsEquipo, pActivo);
    -- devolver el ID generado
    SELECT LAST_INSERT_ID() AS id;
END;;

CREATE PROCEDURE `sp_Producto_Actualizar`(
    IN pId INT,
    IN pNombre VARCHAR(255),
    IN pDescripcion TEXT,
    IN pCategoria VARCHAR(100),
    IN pPrecio DECIMAL(12,2),
    IN pStock INT,
    IN pUnidad VARCHAR(50),
    IN pProveedor VARCHAR(150),
    IN pImagen VARCHAR(500),
    IN pEsEquipo TINYINT(1),
    IN pActivo TINYINT(1)
)
BEGIN
    UPDATE productos
    SET nombre      = pNombre,
        descripcion = pDescripcion,
        categoria   = pCategoria,
        precio      = pPrecio,
        stock       = pStock,
        unidad      = pUnidad,
        proveedor   = pProveedor,
        imagen      = pImagen,
        es_equipo   = pEsEquipo,
        activo      = pActivo
    WHERE id = pId;
END;;

CREATE PROCEDURE `sp_Producto_Eliminar`(
    IN pId INT
)
BEGIN
    -- baja lógica, mantiene el registro pero lo marca como inactivo
    UPDATE productos
    SET activo = 0
    WHERE id = pId;
END;;

CREATE PROCEDURE `sp_Productos_Buscar`(
    IN pTerm VARCHAR(255)
)
BEGIN
    SELECT id, nombre, descripcion, categoria, precio, stock,
           unidad, proveedor, imagen, es_equipo, activo
    FROM productos
    WHERE activo = 1
      AND (nombre LIKE pTerm OR descripcion LIKE pTerm
           OR proveedor LIKE pTerm OR categoria LIKE pTerm)
    ORDER BY categoria, nombre;
END;;

CREATE PROCEDURE `sp_Productos_PorCategoria`(
    IN pCategoria VARCHAR(100)
)
BEGIN
    SELECT id, nombre, descripcion, categoria, precio, stock,
           unidad, proveedor, imagen, es_equipo, activo
    FROM productos
    WHERE activo = 1 AND categoria = pCategoria
    ORDER BY nombre;
END;;

CREATE PROCEDURE `sp_Productos_Equipos`()
BEGIN
    SELECT id, nombre, descripcion, categoria, precio, stock,
           unidad, proveedor, imagen, es_equipo, activo
    FROM productos
    WHERE activo = 1 AND es_equipo = 1
    ORDER BY nombre;
END;;

DELIMITER ;

-- ================================================================
--  NUEVA TABLA Y PROCEDIMIENTOS PARA EMPLEADOS
--  Permite gestionar la planilla de trabajadores (crear, listar,
--  actualizar y eliminar empleados) mediante procedimientos
--  almacenados para cumplir con la política de no usar consultas
--  directas en la aplicación.
-- ================================================================

DROP TABLE IF EXISTS `empleados`;
CREATE TABLE `empleados` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `nombre` VARCHAR(255) NOT NULL,
  `puesto` VARCHAR(100) NOT NULL,
  `salario` DECIMAL(12,2) NOT NULL,
  `activo` TINYINT(1) NOT NULL DEFAULT 1,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Empleados de ejemplo
INSERT INTO empleados (nombre, puesto, salario, activo) VALUES
('José Sánchez', 'Cajero', 350000.00, 1),
('María Hernández', 'Bodeguera', 320000.00, 1),
('Luis Rodríguez', 'Alistador', 300000.00, 1),
('Andrés Morales', 'Chofer', 450000.00, 1),
('Ana López', 'Contadora', 500000.00, 1),
('Carlos Martínez', 'TI', 550000.00, 1);

DELIMITER ;;

CREATE PROCEDURE `sp_Empleados_ListarActivos`()
BEGIN
    SELECT id, nombre, puesto, salario, activo
    FROM empleados
    WHERE activo = 1
    ORDER BY nombre;
END;;

CREATE PROCEDURE `sp_Empleado_ObtenerPorId`(
    IN pId INT
)
BEGIN
    SELECT id, nombre, puesto, salario, activo
    FROM empleados
    WHERE id = pId
    LIMIT 1;
END;;

CREATE PROCEDURE `sp_Empleado_Crear`(
    IN pNombre VARCHAR(255),
    IN pPuesto VARCHAR(100),
    IN pSalario DECIMAL(12,2),
    IN pActivo TINYINT(1)
)
BEGIN
    INSERT INTO empleados (nombre, puesto, salario, activo)
    VALUES (pNombre, pPuesto, pSalario, pActivo);
    SELECT LAST_INSERT_ID() AS id;
END;;

CREATE PROCEDURE `sp_Empleado_Actualizar`(
    IN pId INT,
    IN pNombre VARCHAR(255),
    IN pPuesto VARCHAR(100),
    IN pSalario DECIMAL(12,2),
    IN pActivo TINYINT(1)
)
BEGIN
    UPDATE empleados
    SET nombre  = pNombre,
        puesto  = pPuesto,
        salario = pSalario,
        activo  = pActivo
    WHERE id = pId;
END;;

CREATE PROCEDURE `sp_Empleado_Eliminar`(
    IN pId INT
)
BEGIN
    UPDATE empleados
    SET activo = 0
    WHERE id = pId;
END;;

-- =============================================
-- PROCEDIMIENTOS PARA ADMINISTRACIÓN DE USUARIOS
-- Permite listar todas las cuentas y cambiar su perfil (rol)
-- =============================================

CREATE PROCEDURE `sp_Usuarios_Listar`()
BEGIN
    SELECT U.ConsecutivoUsuario, U.Identificacion, U.Nombre, U.CorreoElectronico,
           U.ConsecutivoPerfil, P.Nombre AS NombrePerfil
    FROM tbusuario U
    INNER JOIN tbperfil P ON U.ConsecutivoPerfil = P.ConsecutivoPerfil
    ORDER BY U.Nombre;
END;;

CREATE PROCEDURE `sp_Usuario_CambiarPerfil`(
    IN pConsecutivoUsuario INT,
    IN pConsecutivoPerfil INT
)
BEGIN
    UPDATE tbusuario
    SET ConsecutivoPerfil = pConsecutivoPerfil
    WHERE ConsecutivoUsuario = pConsecutivoUsuario;
END;;

-- =============================================
--  TABLAS Y PROCEDIMIENTOS PARA PEDIDOS
--  Permite crear pedidos y detalles, listar pedidos por usuario o todos,
--  obtener pedidos con detalle y actualizar el estado del pedido.
-- =============================================

-- Elimina las tablas si existen previamente
DROP TABLE IF EXISTS `pedido_detalle`;
DROP TABLE IF EXISTS `pedidos`;

-- Tabla de pedidos (cabecera)
CREATE TABLE `pedidos` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `ConsecutivoUsuario` INT NOT NULL,
  `fecha` DATETIME NOT NULL,
  `estado` VARCHAR(50) NOT NULL DEFAULT 'Pendiente',
  `total` DECIMAL(12,2) NOT NULL,
  PRIMARY KEY (`id`),
  CONSTRAINT `fk_pedidos_usuario`
    FOREIGN KEY (`ConsecutivoUsuario`) REFERENCES `tbusuario` (`ConsecutivoUsuario`)
    ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Tabla de detalles de pedido
CREATE TABLE `pedido_detalle` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `id_pedido` INT NOT NULL,
  `id_producto` INT NOT NULL,
  `cantidad` INT NOT NULL,
  `precio` DECIMAL(12,2) NOT NULL,
  PRIMARY KEY (`id`),
  CONSTRAINT `fk_detalle_pedido`
    FOREIGN KEY (`id_pedido`) REFERENCES `pedidos` (`id`)
    ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_detalle_producto`
    FOREIGN KEY (`id_producto`) REFERENCES `productos` (`id`)
    ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

DELIMITER ;;

-- Crea un pedido y devuelve el ID generado
CREATE PROCEDURE `sp_Pedidos_Crear`(
    IN pConsecutivoUsuario INT,
    IN pEstado VARCHAR(50),
    IN pTotal DECIMAL(12,2)
)
BEGIN
    INSERT INTO pedidos (ConsecutivoUsuario, fecha, estado, total)
    VALUES (pConsecutivoUsuario, NOW(), pEstado, pTotal);
    SELECT LAST_INSERT_ID() AS id;
END;;

-- Agrega una fila a la tabla de detalles para un pedido específico
CREATE PROCEDURE `sp_PedidoDetalle_Agregar`(
    IN pIdPedido INT,
    IN pIdProducto INT,
    IN pCantidad INT,
    IN pPrecio DECIMAL(12,2)
)
BEGIN
    INSERT INTO pedido_detalle (id_pedido, id_producto, cantidad, precio)
    VALUES (pIdPedido, pIdProducto, pCantidad, pPrecio);
END;;

-- Lista todos los pedidos con datos del usuario
CREATE PROCEDURE `sp_Pedidos_Listar`()
BEGIN
    SELECT p.id, p.ConsecutivoUsuario, u.Nombre AS nombreUsuario, p.fecha, p.estado, p.total
    FROM pedidos p
    INNER JOIN tbusuario u ON p.ConsecutivoUsuario = u.ConsecutivoUsuario
    ORDER BY p.fecha DESC;
END;;

-- Lista los pedidos de un usuario específico
CREATE PROCEDURE `sp_Pedidos_Usuario_Listar`(
    IN pConsecutivoUsuario INT
)
BEGIN
    SELECT id, fecha, estado, total
    FROM pedidos
    WHERE ConsecutivoUsuario = pConsecutivoUsuario
    ORDER BY fecha DESC;
END;;

-- Obtiene un pedido específico junto con el nombre del usuario
CREATE PROCEDURE `sp_Pedido_ObtenerPorId`(
    IN pIdPedido INT
)
BEGIN
    SELECT p.id, p.ConsecutivoUsuario, u.Nombre AS nombreUsuario, p.fecha, p.estado, p.total
    FROM pedidos p
    INNER JOIN tbusuario u ON p.ConsecutivoUsuario = u.ConsecutivoUsuario
    WHERE p.id = pIdPedido
    LIMIT 1;
END;;

-- Obtiene el detalle de un pedido (producto, cantidad, precio)
CREATE PROCEDURE `sp_Pedido_Detalle`(
    IN pIdPedido INT
)
BEGIN
    SELECT d.id_producto AS idProducto, pr.nombre AS nombreProducto, d.cantidad, d.precio
    FROM pedido_detalle d
    INNER JOIN productos pr ON d.id_producto = pr.id
    WHERE d.id_pedido = pIdPedido;
END;;

-- Actualiza el estado de un pedido
CREATE PROCEDURE `sp_Pedido_ActualizarEstado`(
    IN pIdPedido INT,
    IN pEstado VARCHAR(50)
)
BEGIN
    UPDATE pedidos
    SET estado = pEstado
    WHERE id = pIdPedido;
END;;

DELIMITER ;

DELIMITER ;

