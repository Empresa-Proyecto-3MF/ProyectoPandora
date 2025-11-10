CREATE DATABASE IF NOT EXISTS pandoraDB DEFAULT CHARACTER SET utf8mb4;
USE pandoraDB;
SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;
DROP TRIGGER IF EXISTS trigger_insertar_usuario_por_rol;
DROP TRIGGER IF EXISTS trigger_actualizar_usuario_por_rol;
DROP TABLE IF EXISTS pagos;
DROP TABLE IF EXISTS mail_queue;
DROP TABLE IF EXISTS ticket_ratings;
DROP TABLE IF EXISTS tecnico_stats;
DROP TABLE IF EXISTS ticket_estado_historial;
DROP TABLE IF EXISTS item_ticket;
DROP TABLE IF EXISTS inventarios;
DROP TABLE IF EXISTS dispositivos;
DROP TABLE IF EXISTS tickets;
DROP TABLE IF EXISTS administradores;
DROP TABLE IF EXISTS supervisores;
DROP TABLE IF EXISTS tecnicos;
DROP TABLE IF EXISTS clientes;
DROP TABLE IF EXISTS historial;
DROP TABLE IF EXISTS estados_tickets;
DROP TABLE IF EXISTS categorias_inventario;
DROP TABLE IF EXISTS categorias;
DROP TABLE IF EXISTS notification_user;
DROP TABLE IF EXISTS notifications;
DROP TABLE IF EXISTS users;
SET FOREIGN_KEY_CHECKS = 1;
CREATE TABLE users (
    id INT NOT NULL AUTO_INCREMENT,
    name VARCHAR(50) NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    img_perfil VARCHAR(255) DEFAULT NULL,
    telefono VARCHAR(20),
    direccion VARCHAR(100),
    remember_selector VARCHAR(100) DEFAULT NULL,
    remember_token VARCHAR(255) DEFAULT NULL,
    remember_expires_at DATETIME DEFAULT NULL,
    -- Usamos 255 para almacenar password_hash del código (hash ~60 chars)
    reset_code VARCHAR(255) DEFAULT NULL,
    reset_expires_at DATETIME DEFAULT NULL,
    reset_attempts INT DEFAULT 0,
    reset_locked_until DATETIME DEFAULT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    role ENUM(
        'Cliente',
        'Tecnico',
        'Supervisor',
        'Administrador'
    ) NOT NULL DEFAULT 'Cliente',
    PRIMARY KEY (id),
    UNIQUE KEY uq_users_remember_selector (remember_selector)
) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4;

INSERT INTO users (name, email, password, role)
VALUES ('Admin', 'admin@admin.com', '$2y$10$0J4vZrY0oQvEJq8xv7m0SeH2V7jV7cQO3Yl2oT1rSObJ9z0X7VqJG', 'Administrador');
CREATE TABLE clientes (
    id INT NOT NULL AUTO_INCREMENT,
    user_id INT NOT NULL,
    num_ordenes INT DEFAULT 0,
    estado ENUM('Activo', 'Inactivo') DEFAULT 'Activo',
    PRIMARY KEY (id),
    UNIQUE KEY uq_clientes_user (user_id),
    CONSTRAINT fk_clientes_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4;
CREATE TABLE tecnicos (
    id INT NOT NULL AUTO_INCREMENT,
    user_id INT NOT NULL,
    disponibilidad ENUM('Disponible', 'Ocupado') DEFAULT 'Disponible',
    especialidad VARCHAR(100),
    ticket_count INT DEFAULT 0,
    PRIMARY KEY (id),
    UNIQUE KEY uq_tecnicos_user (user_id),
    CONSTRAINT fk_tecnicos_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4;
CREATE TABLE supervisores (
    id INT NOT NULL AUTO_INCREMENT,
    user_id INT NOT NULL,
    num_tecnicos INT DEFAULT 0,
    estado ENUM('Activo', 'Inactivo') DEFAULT 'Activo',
    PRIMARY KEY (id),
    UNIQUE KEY uq_supervisores_user (user_id),
    CONSTRAINT fk_supervisores_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4;
CREATE TABLE administradores (
    id INT NOT NULL AUTO_INCREMENT,
    user_id INT NOT NULL,
    PRIMARY KEY (id),
    UNIQUE KEY uq_admins_user (user_id),
    CONSTRAINT fk_admins_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4;

INSERT INTO administradores (user_id)
SELECT id FROM users WHERE email = 'admin@admin.com' AND role = 'Administrador';
CREATE TABLE categorias (
    id INT NOT NULL AUTO_INCREMENT,
    name VARCHAR(50) NOT NULL,
    PRIMARY KEY (id)
) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4;
INSERT INTO categorias (name) VALUES
('Celulares'),('Computadoras'),('Laptops'),('Tablets'),('Televisiones'),('Consolas'),('Audio/Video');
CREATE TABLE categorias_inventario (
    id INT NOT NULL AUTO_INCREMENT,
    name VARCHAR(50) NOT NULL,
    PRIMARY KEY (id)
) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4;
INSERT INTO categorias_inventario (name) VALUES
('Pantallas'),('Baterías'),('Cables/Flex'),('Cámaras'),('Botones'),('Puertos/Conectores'),('Placas/Boards'),('Carcasas'),('Protectores/Consumibles');
CREATE TABLE estados_tickets (
    id INT NOT NULL AUTO_INCREMENT,
    name VARCHAR(50) NOT NULL,
    PRIMARY KEY (id),
    UNIQUE KEY uq_estados_name (name)
) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4;
INSERT INTO estados_tickets (name) VALUES
('Nuevo'),('Diagnóstico'),('Presupuesto'),('En espera'),('En reparación'),('En pruebas'),('Listo para retirar'),('Finalizado'),('Cancelado');

CREATE TABLE dispositivos (
    id INT NOT NULL AUTO_INCREMENT,
    user_id INT NOT NULL,
    categoria_id INT NOT NULL,
    marca VARCHAR(50),
    modelo VARCHAR(50),
    numero_serie VARCHAR(50),
    descripcion_falla TEXT,
    img_dispositivo VARCHAR(255) DEFAULT NULL,
    fecha_registro DATETIME DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    CONSTRAINT fk_dispositivos_cliente FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    CONSTRAINT fk_dispositivos_categoria FOREIGN KEY (categoria_id) REFERENCES categorias(id) ON DELETE CASCADE
) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4;
CREATE TABLE tickets (
    id INT NOT NULL AUTO_INCREMENT,
    dispositivo_id INT NOT NULL,
    cliente_id INT NOT NULL,
    tecnico_id INT NULL,
    supervisor_id INT NULL,
    estado_id INT NOT NULL,
    descripcion_falla TEXT NOT NULL,
    fecha_creacion DATETIME DEFAULT CURRENT_TIMESTAMP,
    fecha_cierre DATETIME NULL,
    PRIMARY KEY (id),
    CONSTRAINT fk_tickets_dispositivo FOREIGN KEY (dispositivo_id) REFERENCES dispositivos(id) ON DELETE CASCADE,
    CONSTRAINT fk_tickets_cliente FOREIGN KEY (cliente_id) REFERENCES clientes(id) ON DELETE CASCADE,
    CONSTRAINT fk_tickets_tecnico FOREIGN KEY (tecnico_id) REFERENCES tecnicos(id) ON DELETE SET NULL,
    CONSTRAINT fk_tickets_supervisor FOREIGN KEY (supervisor_id) REFERENCES supervisores(id) ON DELETE SET NULL,
    CONSTRAINT fk_tickets_estado FOREIGN KEY (estado_id) REFERENCES estados_tickets(id) ON DELETE RESTRICT
) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4;
CREATE TABLE ticket_estado_historial (
    id INT NOT NULL AUTO_INCREMENT,
    ticket_id INT NOT NULL,
    estado_id INT NOT NULL,
    user_id INT NOT NULL,
    user_role ENUM('Cliente','Tecnico','Supervisor','Administrador') NOT NULL,
    comentario VARCHAR(255) DEFAULT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    CONSTRAINT fk_teh_ticket FOREIGN KEY (ticket_id) REFERENCES tickets(id) ON DELETE CASCADE,
    CONSTRAINT fk_teh_estado FOREIGN KEY (estado_id) REFERENCES estados_tickets(id) ON DELETE CASCADE,
    CONSTRAINT fk_teh_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
    ) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4;
CREATE TABLE inventarios (
    id INT NOT NULL AUTO_INCREMENT,
    categoria_id INT NOT NULL,
    name_item VARCHAR(100) NOT NULL,
    valor_unitario DECIMAL(10, 2) NOT NULL CHECK (valor_unitario >= 0),
    descripcion TEXT,
    stock_actual INT NOT NULL DEFAULT 0 CHECK (stock_actual >= 0),
    stock_minimo INT NOT NULL DEFAULT 0 CHECK (stock_minimo >= 0),
    foto_item VARCHAR(255),
    fecha_creacion DATETIME DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    CONSTRAINT fk_inventarios_categoria FOREIGN KEY (categoria_id) REFERENCES categorias_inventario(id) ON DELETE CASCADE
) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4;
CREATE TABLE item_ticket (
    id INT NOT NULL AUTO_INCREMENT,
    ticket_id INT NOT NULL,
    inventario_id INT NOT NULL,
    tecnico_id INT NOT NULL,
    supervisor_id INT NOT NULL,
    cantidad INT NOT NULL DEFAULT 1,
    valor_total DECIMAL(10, 2) NOT NULL,
    fecha_asignacion DATETIME DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    CONSTRAINT fk_item_ticket_ticket FOREIGN KEY (ticket_id) REFERENCES tickets(id) ON DELETE CASCADE,
    CONSTRAINT fk_item_ticket_inventario FOREIGN KEY (inventario_id) REFERENCES inventarios(id) ON DELETE CASCADE,
    CONSTRAINT fk_item_ticket_tecnico FOREIGN KEY (tecnico_id) REFERENCES tecnicos(id) ON DELETE CASCADE,
    CONSTRAINT fk_item_ticket_supervisor FOREIGN KEY (supervisor_id) REFERENCES supervisores(id) ON DELETE CASCADE
) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4;

CREATE TABLE historial (
    id INT NOT NULL AUTO_INCREMENT,
    acciones VARCHAR(255) NOT NULL,
    detalles TEXT,
    fecha DATETIME DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id)
) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4;

CREATE TABLE pagos (
    id INT NOT NULL AUTO_INCREMENT,
    ticket_id INT NOT NULL,
    amount DECIMAL(10,2) NOT NULL,
    method ENUM('EFECTIVO','TARJETA','TRANSFERENCIA','OTRO') NOT NULL DEFAULT 'EFECTIVO',
    reference VARCHAR(100) NULL,
    user_id INT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    CONSTRAINT fk_pagos_ticket FOREIGN KEY (ticket_id) REFERENCES tickets(id) ON DELETE CASCADE,
    CONSTRAINT fk_pagos_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL
) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4;
-- mail_queue removido: no se implementará
CREATE TABLE ticket_ratings (
    id INT NOT NULL AUTO_INCREMENT,
    ticket_id INT NOT NULL,
    tecnico_id INT NOT NULL,
    cliente_id INT NOT NULL,
    stars TINYINT NOT NULL,
    comment VARCHAR(255) NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    CONSTRAINT fk_ratings_ticket FOREIGN KEY (ticket_id) REFERENCES tickets(id) ON DELETE CASCADE,
    CONSTRAINT fk_ratings_tecnico FOREIGN KEY (tecnico_id) REFERENCES tecnicos(id) ON DELETE CASCADE,
    CONSTRAINT fk_ratings_cliente FOREIGN KEY (cliente_id) REFERENCES clientes(id) ON DELETE CASCADE
) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4;
CREATE TABLE tecnico_stats (
    id INT NOT NULL AUTO_INCREMENT,
    tecnico_id INT NOT NULL,
    avg_rating DECIMAL(3,2) NOT NULL DEFAULT 3.00,
    ratings_count INT NOT NULL DEFAULT 0,
    labor_min DECIMAL(10,2) DEFAULT 0,
    labor_max DECIMAL(10,2) DEFAULT 0,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    UNIQUE KEY uq_tecnico_stats_tecnico (tecnico_id),
    CONSTRAINT fk_stats_tecnico FOREIGN KEY (tecnico_id) REFERENCES tecnicos(id) ON DELETE CASCADE
) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4;
DELIMITER $$
CREATE TRIGGER trigger_insertar_usuario_por_rol
AFTER INSERT ON users
FOR EACH ROW
BEGIN
    IF NEW.role = 'Cliente' THEN
        INSERT INTO clientes(user_id) VALUES (NEW.id);
    ELSEIF NEW.role = 'Tecnico' THEN
        INSERT INTO tecnicos(user_id, disponibilidad) VALUES (NEW.id, 'Disponible');
    ELSEIF NEW.role = 'Supervisor' THEN
        INSERT INTO supervisores(user_id) VALUES (NEW.id);
    ELSEIF NEW.role = 'Administrador' THEN
        INSERT INTO administradores(user_id) VALUES (NEW.id);
    END IF;
END $$

CREATE TRIGGER trigger_actualizar_usuario_por_rol
AFTER UPDATE ON users
FOR EACH ROW
BEGIN
    IF OLD.role <> NEW.role THEN
        
        IF OLD.role = 'Cliente' THEN
            DELETE FROM clientes WHERE user_id = OLD.id;
        ELSEIF OLD.role = 'Tecnico' THEN
            DELETE FROM tecnicos WHERE user_id = OLD.id;
        ELSEIF OLD.role = 'Supervisor' THEN
            DELETE FROM supervisores WHERE user_id = OLD.id;
        ELSEIF OLD.role = 'Administrador' THEN
            DELETE FROM administradores WHERE user_id = OLD.id;
        END IF;
        
        IF NEW.role = 'Cliente' THEN
            INSERT INTO clientes(user_id) VALUES (NEW.id);
        ELSEIF NEW.role = 'Tecnico' THEN
            INSERT INTO tecnicos(user_id, disponibilidad) VALUES (NEW.id, 'Disponible');
        ELSEIF NEW.role = 'Supervisor' THEN
            INSERT INTO supervisores(user_id) VALUES (NEW.id);
        ELSEIF NEW.role = 'Administrador' THEN
            INSERT INTO administradores(user_id) VALUES (NEW.id);
        END IF;
    END IF;
END $$
DELIMITER ;

-- Notificaciones
CREATE TABLE notifications (
    id INT NOT NULL AUTO_INCREMENT,
    title VARCHAR(150) NOT NULL,
    body TEXT NOT NULL,
    audience ENUM('ALL','ROLE','USER') NOT NULL DEFAULT 'ALL',
    audience_role ENUM('Cliente','Tecnico','Supervisor','Administrador') NULL,
    target_user_id INT NULL,
    created_by INT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    CONSTRAINT fk_notif_target_user FOREIGN KEY (target_user_id) REFERENCES users(id) ON DELETE CASCADE,
    CONSTRAINT fk_notif_created_by FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE SET NULL
) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4;

CREATE TABLE notification_user (
    notification_id INT NOT NULL,
    user_id INT NOT NULL,
    is_read TINYINT(1) NOT NULL DEFAULT 0,
    read_at DATETIME NULL,
    PRIMARY KEY (notification_id, user_id),
    CONSTRAINT fk_notif_user_notif FOREIGN KEY (notification_id) REFERENCES notifications(id) ON DELETE CASCADE,
    CONSTRAINT fk_notif_user_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4;
