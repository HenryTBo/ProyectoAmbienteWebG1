-- ============================================================
-- proyectoambienteweb_full.sql
-- Base completa + módulos de pedidos/carrito (clientes y admin)
-- Compatible con MySQL 8.x (XAMPP)
-- ============================================================

DROP DATABASE IF EXISTS proyectoambienteweb;
CREATE DATABASE proyectoambienteweb CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;
USE proyectoambienteweb;

SET FOREIGN_KEY_CHECKS = 0;

-- =======================
-- TABLA: tberror
-- =======================
DROP TABLE IF EXISTS tberror;
CREATE TABLE tberror (
  ConsecutivoError INT NOT NULL AUTO_INCREMENT,
  Mensaje VARCHAR(8000) NOT NULL,
  FechaHora DATETIME NOT NULL,
  PRIMARY KEY (ConsecutivoError)
) ENGINE=InnoDB;

-- =======================
-- TABLA: tbperfil
-- =======================
DROP TABLE IF EXISTS tbperfil;
CREATE TABLE tbperfil (
  ConsecutivoPerfil INT NOT NULL AUTO_INCREMENT,
  Nombre VARCHAR(50) NOT NULL,
  PRIMARY KEY (ConsecutivoPerfil)
) ENGINE=InnoDB;

INSERT INTO tbperfil (ConsecutivoPerfil, Nombre) VALUES
(1,'Usuario Administrador'),
(2,'Usuario Regular');

-- =======================
-- TABLA: tbusuario
-- =======================
DROP TABLE IF EXISTS tbusuario;
CREATE TABLE tbusuario (
  ConsecutivoUsuario INT NOT NULL AUTO_INCREMENT,
  Identificacion VARCHAR(15) NOT NULL,
  Nombre VARCHAR(255) NOT NULL,
  Contrasenna VARCHAR(60) NOT NULL,
  Estado BIT(1) NOT NULL,
  ConsecutivoPerfil INT NOT NULL,
  CorreoElectronico VARCHAR(100) NOT NULL,
  PRIMARY KEY (ConsecutivoUsuario),
  KEY idx_usuario_perfil (ConsecutivoPerfil),
  UNIQUE KEY uq_usuario_correo (CorreoElectronico),
  UNIQUE KEY uq_usuario_ident (Identificacion),
  CONSTRAINT fk_usuario_perfil FOREIGN KEY (ConsecutivoPerfil) REFERENCES tbperfil (ConsecutivoPerfil)
) ENGINE=InnoDB;

-- Admin de ejemplo (ajusta contraseña si deseas)
INSERT INTO tbusuario (Identificacion, Nombre, Contrasenna, Estado, ConsecutivoPerfil, CorreoElectronico) VALUES
('117600318','TREJOS BONILLA HENRY GABRIEL','1234',b'1',1,'htrejos00318@ufide.ac.cr');

-- =======================
-- TABLA: productos
-- =======================
DROP TABLE IF EXISTS productos;
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
) ENGINE=InnoDB;

-- Productos de ejemplo
INSERT INTO productos (nombre, descripcion, categoria, precio, stock, unidad, proveedor, imagen, es_equipo, activo) VALUES
('Cerveza Heineken Original Lata 6 pack - 2130 ml','6 unidades de lata — Importada — 5% Alcohol', 'Licorera', 5680, 35, 'pack', 'Heineken', 'https://walmartcr.vtexassets.com/arquivos/ids/901239-800-600?v=638796936110470000&width=800&height=600&aspect=true', 0, 1),
('6 Pack Lata - 350ml','6 unidades — Marca Nacional — Estilo Lager','Licorera', 5200, 50, 'pack', 'Cervecería Costa Rica', 'https://walmartcr.vtexassets.com/arquivos/ids/902606-800-600?v=638796984170130000&width=800&height=600&aspect=true', 0, 1),
('Cerveza Natural Light 6 pack - 355 ml','Ligera — Baja en calorías — 6 unidades','Licorera', 2900, 38, 'pack', 'Cervecería Costa Rica', 'https://walmartcr.vtexassets.com/arquivos/ids/717991-800-600?v=638624752312870000&width=800&height=600&aspect=true', 0, 1),
('15 Pack Cerveza Pilsen Lata - 350ml','Pack ahorro — 15 unidades — Lager clásica','Licorera', 9800, 22, 'pack', 'Cervecería Costa Rica', 'https://walmartcr.vtexassets.com/arquivos/ids/902423-800-600?v=638796978172730000&width=800&height=600&aspect=true', 0, 1),
('Cerveza Cezka Lager 4% Alcohol 24 Pack - 7920ml','24 botellas — 4% Alcohol — Ahorro','Licorera', 8000, 20, 'pack', 'Cezka', 'https://walmartcr.vtexassets.com/arquivos/ids/436187-800-600?v=638284557739270000&width=800&height=600&aspect=true', 0, 1),
('Cerveza Sol Vidrio - 330 ml','100% Mexicana — Botella de vidrio','Licorera', 1050, 60, 'unidad', 'Sol', 'https://walmartcr.vtexassets.com/arquivos/ids/975976-800-600?v=638899242375770000&width=800&height=600&aspect=true', 0, 1),
('Cerveza Imperial Original Lata 4Pack - 473 ml c/u','Imperial regular — Pack de 4 — Lager','Licorera', 4140, 55, 'pack', 'Cervecería Costa Rica', 'https://walmartcr.vtexassets.com/arquivos/ids/901932-800-600?v=638796957318270000&width=800&height=600&aspect=true', 0, 1),
('Cerveza Bohemia en lata 6 Pack - 2100 ml','Pack de 6 — Cerveza Nacional','Licorera', 3100, 32, 'pack', 'Cervecería Costa Rica', 'https://walmartcr.vtexassets.com/arquivos/ids/901262-800-600?v=638796936214200000&width=800&height=600&aspect=true', 0, 1),
('Cerveza Corona Extra Botella - 355ml','Mexicana Premium — Botella individual','Licorera', 1200, 45, 'unidad', 'Corona', 'https://walmartcr.vtexassets.com/arquivos/ids/1020886-800-600?v=638985672026930000&width=800&height=600&aspect=true', 0, 1),
('Cerveza Imperial Michelada Mango Verde Limón y Sal - 350 ml','Michelada lista para tomar — Sabor Mango Verde','Licorera', 920, 70, 'unidad', 'Cervecería Costa Rica', 'https://walmartcr.vtexassets.com/arquivos/ids/891775-800-600?v=638816249217100000&width=800&height=600&aspect=true', 0, 1),
('Cerveza Imperial Michelada Verde Limón y Sal - 350 ml','Cerveza con sabor limón — Tipo michelada','Licorera', 920, 65, 'unidad', 'Cervecería Costa Rica', 'https://walmartcr.vtexassets.com/arquivos/ids/891833-800-600?v=638816245902900000&width=800&height=600&aspect=true', 0, 1);

-- =======================
-- TABLA: empleados
-- =======================
DROP TABLE IF EXISTS empleados;
CREATE TABLE empleados (
  id INT NOT NULL AUTO_INCREMENT,
  nombre VARCHAR(255) NOT NULL,
  puesto VARCHAR(100) NOT NULL,
  salario DECIMAL(12,2) NOT NULL,
  activo TINYINT(1) NOT NULL DEFAULT 1,
  PRIMARY KEY (id)
) ENGINE=InnoDB;

INSERT INTO empleados (nombre, puesto, salario, activo) VALUES
('José Sánchez', 'Cajero', 350000.00, 1),
('María Hernández', 'Bodeguera', 320000.00, 1),
('Luis Rodríguez', 'Alistador', 300000.00, 1),
('Andrés Morales', 'Chofer', 450000.00, 1),
('Ana López', 'Contadora', 500000.00, 1),
('Carlos Martínez', 'TI', 550000.00, 1);

-- =======================
-- TABLAS: pedidos / pedido_detalle
-- =======================
DROP TABLE IF EXISTS pedido_detalle;
DROP TABLE IF EXISTS pedidos;

CREATE TABLE pedidos (
  id INT NOT NULL AUTO_INCREMENT,
  ConsecutivoUsuario INT NOT NULL,
  fecha DATETIME NOT NULL,
  estado VARCHAR(50) NOT NULL DEFAULT 'Pendiente',
  total DECIMAL(12,2) NOT NULL,
  entrega_tipo VARCHAR(20) NOT NULL DEFAULT 'Tienda',   -- 'Tienda' o 'Domicilio'
  direccion VARCHAR(255) NULL,
  id_conductor INT NULL,
  updated_at TIMESTAMP NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  KEY idx_pedidos_usuario (ConsecutivoUsuario),
  KEY idx_pedidos_estado (estado),
  KEY idx_pedidos_conductor (id_conductor),
  CONSTRAINT fk_pedidos_usuario FOREIGN KEY (ConsecutivoUsuario) REFERENCES tbusuario (ConsecutivoUsuario)
    ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT fk_pedidos_conductor FOREIGN KEY (id_conductor) REFERENCES empleados (id)
    ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB;

CREATE TABLE pedido_detalle (
  id INT NOT NULL AUTO_INCREMENT,
  id_pedido INT NOT NULL,
  id_producto INT NOT NULL,
  cantidad INT NOT NULL,
  precio DECIMAL(12,2) NOT NULL,
  PRIMARY KEY (id),
  KEY idx_det_pedido (id_pedido),
  KEY idx_det_producto (id_producto),
  CONSTRAINT fk_detalle_pedido FOREIGN KEY (id_pedido) REFERENCES pedidos (id)
    ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT fk_detalle_producto FOREIGN KEY (id_producto) REFERENCES productos (id)
    ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE=InnoDB;

SET FOREIGN_KEY_CHECKS = 1;

-- ============================================================
-- PROCEDIMIENTOS ALMACENADOS
-- ============================================================
DELIMITER $$

-- Log de errores
DROP PROCEDURE IF EXISTS RegistrarError $$
CREATE PROCEDURE RegistrarError(pMensaje VARCHAR(8000))
BEGIN
  INSERT INTO tberror (Mensaje, FechaHora) VALUES (pMensaje, NOW());
END $$

-- USUARIOS
DROP PROCEDURE IF EXISTS ValidarCuenta $$
CREATE PROCEDURE ValidarCuenta(pCorreoElectronico VARCHAR(100), pContrasenna VARCHAR(60))
BEGIN
  SELECT U.*, P.Nombre AS NombrePerfil
  FROM tbusuario U
  INNER JOIN tbperfil P ON U.ConsecutivoPerfil = P.ConsecutivoPerfil
  WHERE U.CorreoElectronico = pCorreoElectronico
    AND U.Contrasenna = pContrasenna
    AND U.Estado = b'1';
END $$

DROP PROCEDURE IF EXISTS CrearCuenta $$
CREATE PROCEDURE CrearCuenta(pIdentificacion VARCHAR(15), pNombre VARCHAR(255), pCorreoElectronico VARCHAR(100), pContrasenna VARCHAR(60))
BEGIN
  DECLARE vCuentaExistente INT DEFAULT 0;

  SELECT COUNT(*) INTO vCuentaExistente
  FROM tbusuario
  WHERE Identificacion = pIdentificacion OR CorreoElectronico = pCorreoElectronico;

  IF vCuentaExistente > 0 THEN
    SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Ya existe un usuario con esa identificación o correo electrónico.';
  ELSE
    INSERT INTO tbusuario (Identificacion, Nombre, CorreoElectronico, Contrasenna, Estado, ConsecutivoPerfil)
    VALUES (pIdentificacion, pNombre, pCorreoElectronico, pContrasenna, b'1', 2);
  END IF;
END $$

DROP PROCEDURE IF EXISTS ConsultarUsuario $$
CREATE PROCEDURE ConsultarUsuario(pConsecutivoUsuario INT)
BEGIN
  SELECT U.*, P.Nombre AS NombrePerfil
  FROM tbusuario U
  INNER JOIN tbperfil P ON U.ConsecutivoPerfil = P.ConsecutivoPerfil
  WHERE U.ConsecutivoUsuario = pConsecutivoUsuario;
END $$

DROP PROCEDURE IF EXISTS ActualizarPerfil $$
CREATE PROCEDURE ActualizarPerfil(pConsecutivoUsuario INT, pIdentificacion VARCHAR(15), pNombre VARCHAR(255), pCorreoElectronico VARCHAR(100))
BEGIN
  UPDATE tbusuario
  SET Identificacion = pIdentificacion, Nombre = pNombre, CorreoElectronico = pCorreoElectronico
  WHERE ConsecutivoUsuario = pConsecutivoUsuario;
END $$

DROP PROCEDURE IF EXISTS ActualizarContrasenna $$
CREATE PROCEDURE ActualizarContrasenna(pConsecutivoUsuario INT, pContrasennaGenerada VARCHAR(60))
BEGIN
  UPDATE tbusuario SET Contrasenna = pContrasennaGenerada WHERE ConsecutivoUsuario = pConsecutivoUsuario;
END $$

DROP PROCEDURE IF EXISTS sp_Usuarios_Listar $$
CREATE PROCEDURE sp_Usuarios_Listar()
BEGIN
  SELECT U.ConsecutivoUsuario, U.Identificacion, U.Nombre, U.CorreoElectronico, U.ConsecutivoPerfil, P.Nombre AS NombrePerfil
  FROM tbusuario U
  INNER JOIN tbperfil P ON U.ConsecutivoPerfil = P.ConsecutivoPerfil
  ORDER BY U.Nombre;
END $$

DROP PROCEDURE IF EXISTS sp_Usuario_CambiarPerfil $$
CREATE PROCEDURE sp_Usuario_CambiarPerfil(pConsecutivoUsuario INT, pConsecutivoPerfil INT)
BEGIN
  UPDATE tbusuario SET ConsecutivoPerfil = pConsecutivoPerfil WHERE ConsecutivoUsuario = pConsecutivoUsuario;
END $$

-- PRODUCTOS
DROP PROCEDURE IF EXISTS sp_Productos_ListarActivos $$
CREATE PROCEDURE sp_Productos_ListarActivos()
BEGIN
  SELECT id, nombre, descripcion, categoria, precio, stock, unidad, proveedor, imagen, es_equipo, activo
  FROM productos
  WHERE activo = 1
  ORDER BY categoria, nombre;
END $$

DROP PROCEDURE IF EXISTS sp_Producto_ObtenerPorId $$
CREATE PROCEDURE sp_Producto_ObtenerPorId(pId INT)
BEGIN
  SELECT id, nombre, descripcion, categoria, precio, stock, unidad, proveedor, imagen, es_equipo, activo
  FROM productos
  WHERE id = pId
  LIMIT 1;
END $$

DROP PROCEDURE IF EXISTS sp_Productos_PorCategoria $$
CREATE PROCEDURE sp_Productos_PorCategoria(pCategoria VARCHAR(100))
BEGIN
  SELECT id, nombre, descripcion, categoria, precio, stock, unidad, proveedor, imagen, es_equipo, activo
  FROM productos
  WHERE activo = 1 AND categoria = pCategoria
  ORDER BY nombre;
END $$

-- EMPLEADOS
DROP PROCEDURE IF EXISTS sp_Empleados_ListarActivos $$
CREATE PROCEDURE sp_Empleados_ListarActivos()
BEGIN
  SELECT id, nombre, puesto, salario, activo
  FROM empleados
  WHERE activo = 1
  ORDER BY nombre;
END $$

DROP PROCEDURE IF EXISTS sp_Conductores_Listar $$
CREATE PROCEDURE sp_Conductores_Listar()
BEGIN
  SELECT id, nombre
  FROM empleados
  WHERE activo = 1 AND (puesto LIKE 'Chofer%' OR puesto LIKE 'Conductor%')
  ORDER BY nombre;
END $$

-- PEDIDOS
-- Mantiene firma usada por tu PHP actual: (userId, estado, total)
DROP PROCEDURE IF EXISTS sp_Pedidos_Crear $$
CREATE PROCEDURE sp_Pedidos_Crear(pConsecutivoUsuario INT, pEstado VARCHAR(50), pTotal DECIMAL(12,2))
BEGIN
  INSERT INTO pedidos (ConsecutivoUsuario, fecha, estado, total)
  VALUES (pConsecutivoUsuario, NOW(), pEstado, pTotal);
  SELECT LAST_INSERT_ID() AS id;
END $$

-- Permite actualizar entrega: esto soluciona tu error:
-- "PROCEDURE proyectoambienteweb.sp_Pedido_ActualizarEntrega does not exist"
DROP PROCEDURE IF EXISTS sp_Pedido_ActualizarEntrega $$
CREATE PROCEDURE sp_Pedido_ActualizarEntrega(pIdPedido INT, pEntregaTipo VARCHAR(20), pDireccion VARCHAR(255))
BEGIN
  UPDATE pedidos
  SET entrega_tipo = IFNULL(pEntregaTipo,'Tienda'),
      direccion    = CASE
                       WHEN IFNULL(pEntregaTipo,'Tienda') = 'Domicilio' THEN pDireccion
                       ELSE NULL
                     END
  WHERE id = pIdPedido;
END $$

DROP PROCEDURE IF EXISTS sp_Pedido_AsignarConductor $$
CREATE PROCEDURE sp_Pedido_AsignarConductor(pIdPedido INT, pIdConductor INT)
BEGIN
  UPDATE pedidos
  SET id_conductor = pIdConductor
  WHERE id = pIdPedido;
END $$

DROP PROCEDURE IF EXISTS sp_PedidoDetalle_Agregar $$
CREATE PROCEDURE sp_PedidoDetalle_Agregar(pIdPedido INT, pIdProducto INT, pCantidad INT, pPrecio DECIMAL(12,2))
BEGIN
  INSERT INTO pedido_detalle (id_pedido, id_producto, cantidad, precio)
  VALUES (pIdPedido, pIdProducto, pCantidad, pPrecio);
END $$

DROP PROCEDURE IF EXISTS sp_Pedidos_Listar $$
CREATE PROCEDURE sp_Pedidos_Listar()
BEGIN
  SELECT p.id,
         p.ConsecutivoUsuario,
         u.Nombre AS nombreUsuario,
         p.fecha,
         p.estado,
         p.total,
         p.entrega_tipo,
         p.direccion,
         p.id_conductor,
         e.nombre AS nombreConductor
  FROM pedidos p
  INNER JOIN tbusuario u ON p.ConsecutivoUsuario = u.ConsecutivoUsuario
  LEFT JOIN empleados e  ON p.id_conductor = e.id
  ORDER BY p.fecha DESC;
END $$

DROP PROCEDURE IF EXISTS sp_Pedidos_Usuario_Listar $$
CREATE PROCEDURE sp_Pedidos_Usuario_Listar(pConsecutivoUsuario INT)
BEGIN
  SELECT p.id, p.fecha, p.estado, p.total, p.entrega_tipo, p.direccion, p.id_conductor
  FROM pedidos p
  WHERE p.ConsecutivoUsuario = pConsecutivoUsuario
  ORDER BY p.fecha DESC;
END $$

DROP PROCEDURE IF EXISTS sp_Pedido_ObtenerPorId $$
CREATE PROCEDURE sp_Pedido_ObtenerPorId(pIdPedido INT)
BEGIN
  SELECT p.id,
         p.ConsecutivoUsuario,
         u.Nombre AS nombreUsuario,
         p.fecha,
         p.estado,
         p.total,
         p.entrega_tipo,
         p.direccion,
         p.id_conductor,
         e.nombre AS nombreConductor
  FROM pedidos p
  INNER JOIN tbusuario u ON p.ConsecutivoUsuario = u.ConsecutivoUsuario
  LEFT JOIN empleados e  ON p.id_conductor = e.id
  WHERE p.id = pIdPedido
  LIMIT 1;
END $$

DROP PROCEDURE IF EXISTS sp_Pedido_Detalle $$
CREATE PROCEDURE sp_Pedido_Detalle(pIdPedido INT)
BEGIN
  SELECT d.id_producto AS idProducto,
         pr.nombre AS nombreProducto,
         d.cantidad,
         d.precio
  FROM pedido_detalle d
  INNER JOIN productos pr ON d.id_producto = pr.id
  WHERE d.id_pedido = pIdPedido;
END $$

DROP PROCEDURE IF EXISTS sp_Pedido_ActualizarEstado $$
CREATE PROCEDURE sp_Pedido_ActualizarEstado(pIdPedido INT, pEstado VARCHAR(50))
BEGIN
  UPDATE pedidos SET estado = pEstado WHERE id = pIdPedido;
END $$

DELIMITER ;

-- ============================================================
-- SCRIPT DE CORRECCIÓN (EJECUTAR DESPUÉS DE TU SCRIPT ACTUAL)
-- ============================================================

USE proyectoambienteweb;

DELIMITER $$

/* =========================
   RECUPERACIÓN (FALTANTE)
=========================*/
DROP PROCEDURE IF EXISTS ValidarCorreo $$
CREATE PROCEDURE ValidarCorreo(pCorreoElectronico VARCHAR(100))
BEGIN
  SELECT U.ConsecutivoUsuario,
         U.Identificacion,
         U.Nombre,
         U.CorreoElectronico,
         U.ConsecutivoPerfil,
         P.Nombre AS NombrePerfil,
         U.Estado
  FROM tbusuario U
  INNER JOIN tbperfil P ON U.ConsecutivoPerfil = P.ConsecutivoPerfil
  WHERE U.CorreoElectronico = pCorreoElectronico
  LIMIT 1;
END $$

/* =========================
   PRODUCTOS (CRUD FALTANTE)
=========================*/
DROP PROCEDURE IF EXISTS sp_Producto_Crear $$
CREATE PROCEDURE sp_Producto_Crear(
  pNombre      VARCHAR(200),
  pDescripcion TEXT,
  pCategoria   VARCHAR(80),
  pPrecio      DECIMAL(12,2),
  pStock       INT,
  pUnidad      VARCHAR(50),
  pProveedor   VARCHAR(120),
  pImagen      VARCHAR(255),
  pEsEquipo    TINYINT(1),
  pActivo      TINYINT(1)
)
BEGIN
  INSERT INTO productos(nombre, descripcion, categoria, precio, stock, unidad, proveedor, imagen, es_equipo, activo)
  VALUES (pNombre, pDescripcion, pCategoria, IFNULL(pPrecio,0), IFNULL(pStock,0), pUnidad, pProveedor, pImagen, IFNULL(pEsEquipo,0), IFNULL(pActivo,1));

  SELECT LAST_INSERT_ID() AS id;
END $$

DROP PROCEDURE IF EXISTS sp_Producto_Actualizar $$
CREATE PROCEDURE sp_Producto_Actualizar(
  pId          INT,
  pNombre      VARCHAR(200),
  pDescripcion TEXT,
  pCategoria   VARCHAR(80),
  pPrecio      DECIMAL(12,2),
  pStock       INT,
  pUnidad      VARCHAR(50),
  pProveedor   VARCHAR(120),
  pImagen      VARCHAR(255),
  pEsEquipo    TINYINT(1),
  pActivo      TINYINT(1)
)
BEGIN
  UPDATE productos
  SET nombre      = pNombre,
      descripcion = pDescripcion,
      categoria   = pCategoria,
      precio      = IFNULL(pPrecio,0),
      stock       = IFNULL(pStock,0),
      unidad      = pUnidad,
      proveedor   = pProveedor,
      imagen      = pImagen,
      es_equipo   = IFNULL(pEsEquipo,0),
      activo      = IFNULL(pActivo,1)
  WHERE id = pId;
END $$

DROP PROCEDURE IF EXISTS sp_Producto_Eliminar $$
CREATE PROCEDURE sp_Producto_Eliminar(pId INT)
BEGIN
  UPDATE productos SET activo = 0 WHERE id = pId;
END $$

DROP PROCEDURE IF EXISTS sp_Productos_Buscar $$
CREATE PROCEDURE sp_Productos_Buscar(pTerm VARCHAR(255))
BEGIN
  SELECT id, nombre, descripcion, categoria, precio, stock, unidad, proveedor, imagen, es_equipo, activo
  FROM productos
  WHERE activo = 1
    AND (
      nombre      LIKE pTerm OR
      descripcion LIKE pTerm OR
      categoria   LIKE pTerm OR
      proveedor   LIKE pTerm
    )
  ORDER BY categoria, nombre;
END $$

DROP PROCEDURE IF EXISTS sp_Productos_Equipos $$
CREATE PROCEDURE sp_Productos_Equipos()
BEGIN
  SELECT id, nombre, descripcion, categoria, precio, stock, unidad, proveedor, imagen, es_equipo, activo
  FROM productos
  WHERE activo = 1 AND es_equipo = 1
  ORDER BY nombre;
END $$

/* =========================
   EMPLEADOS (CRUD FALTANTE)
=========================*/
DROP PROCEDURE IF EXISTS sp_Empleado_Crear $$
CREATE PROCEDURE sp_Empleado_Crear(
  pNombre   VARCHAR(255),
  pPuesto   VARCHAR(100),
  pSalario  DECIMAL(12,2),
  pActivo   TINYINT(1)
)
BEGIN
  INSERT INTO empleados(nombre, puesto, salario, activo)
  VALUES (pNombre, pPuesto, pSalario, IFNULL(pActivo,1));

  SELECT LAST_INSERT_ID() AS id;
END $$

DROP PROCEDURE IF EXISTS sp_Empleado_Actualizar $$
CREATE PROCEDURE sp_Empleado_Actualizar(
  pId       INT,
  pNombre   VARCHAR(255),
  pPuesto   VARCHAR(100),
  pSalario  DECIMAL(12,2),
  pActivo   TINYINT(1)
)
BEGIN
  UPDATE empleados
  SET nombre = pNombre,
      puesto = pPuesto,
      salario = pSalario,
      activo = IFNULL(pActivo,1)
  WHERE id = pId;
END $$

DROP PROCEDURE IF EXISTS sp_Empleado_Eliminar $$
CREATE PROCEDURE sp_Empleado_Eliminar(pId INT)
BEGIN
  UPDATE empleados SET activo = 0 WHERE id = pId;
END $$

DROP PROCEDURE IF EXISTS sp_Empleado_ObtenerPorId $$
CREATE PROCEDURE sp_Empleado_ObtenerPorId(pId INT)
BEGIN
  SELECT id, nombre, puesto, salario, activo
  FROM empleados
  WHERE id = pId
  LIMIT 1;
END $$

DELIMITER ;

