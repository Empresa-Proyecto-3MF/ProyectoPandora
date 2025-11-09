SET FOREIGN_KEY_CHECKS = 0;

DROP TRIGGER IF EXISTS trigger_insertar_usuario_por_rol;
DROP TRIGGER IF EXISTS trigger_actualizar_usuario_por_rol;

DROP TABLE IF EXISTS ticket_estado_historial;
DROP TABLE IF EXISTS pagos;
DROP TABLE IF EXISTS ticket_ratings;
DROP TABLE IF EXISTS tecnico_stats;
DROP TABLE IF EXISTS item_ticket;
DROP TABLE IF EXISTS ticket_labor;
DROP TABLE IF EXISTS mail_queue;
DROP TABLE IF EXISTS historial;
DROP TABLE IF EXISTS inventarios;
DROP TABLE IF EXISTS tickets;
DROP TABLE IF EXISTS dispositivos;
DROP TABLE IF EXISTS administradores;
DROP TABLE IF EXISTS supervisores;
DROP TABLE IF EXISTS tecnicos;
DROP TABLE IF EXISTS clientes;
DROP TABLE IF EXISTS users;
DROP TABLE IF EXISTS estados_tickets;
DROP TABLE IF EXISTS categorias_inventario;
DROP TABLE IF EXISTS categorias;
DROP TABLE IF EXISTS notification_user;
DROP TABLE IF EXISTS notifications;
SET FOREIGN_KEY_CHECKS = 1;

CREATE TABLE categorias (
    id INT(11) NOT NULL AUTO_INCREMENT,
    name VARCHAR(50) NOT NULL,
    PRIMARY KEY (id)
);

INSERT INTO categorias (name) VALUES
('Celulares'),
('Computadoras'),
('Laptops'),
('Tablets'),
('Televisiones'),
('Consolas'),
('Audio/Video');
CREATE TABLE categorias_inventario (
    id INT(11) NOT NULL AUTO_INCREMENT,
    name VARCHAR(50) NOT NULL,
    PRIMARY KEY (id)
);

INSERT INTO categorias_inventario (name) VALUES
('Pantallas'),
('Baterías'),
('Cables/Flex'),
('Cámaras'),
('Botones'),
('Puertos/Conectores'),
('Placas/Boards'),
('Carcasas'),
('Protectores/Consumibles');
CREATE TABLE estados_tickets (
    id INT(11) NOT NULL AUTO_INCREMENT,
    name VARCHAR(50) NOT NULL,
    PRIMARY KEY (id)
);

INSERT INTO estados_tickets (name) VALUES
('Nuevo'),
('Diagnóstico'),
('Presupuesto'),
('En espera'),
('En reparación'),
('En pruebas'),
('Listo para retirar'),
('Finalizado'),
('Cancelado');
CREATE TABLE users (
    id INT(11) NOT NULL AUTO_INCREMENT,
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
);


INSERT INTO users (name, email, password, role)
VALUES ('Admin', 'admin@admin.com', '$2y$10$0J4vZrY0oQvEJq8xv7m0SeH2V7jV7cQO3Yl2oT1rSObJ9z0X7VqJG', 'Administrador');
CREATE TABLE clientes (
    id INT(11) NOT NULL AUTO_INCREMENT,
    user_id INT(11) NOT NULL,
    num_ordenes INT(11) DEFAULT 0,
    estado ENUM('Activo', 'Inactivo') DEFAULT 'Activo',
    PRIMARY KEY (id),
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

CREATE TABLE tecnicos (
    id INT(11) NOT NULL AUTO_INCREMENT,
    user_id INT(11) NOT NULL,
    disponibilidad ENUM('Disponible', 'Ocupado') DEFAULT 'Disponible',
    especialidad VARCHAR(100),
    ticket_count INT(11) DEFAULT 0,
    PRIMARY KEY (id),
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);
CREATE TABLE supervisores (
    id INT(11) NOT NULL AUTO_INCREMENT,
    user_id INT(11) NOT NULL,
    num_tecnicos INT(11) DEFAULT 0,
    estado ENUM('Activo', 'Inactivo') DEFAULT 'Activo',
    PRIMARY KEY (id),
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);
CREATE TABLE administradores (
    id INT(11) NOT NULL AUTO_INCREMENT,
    user_id INT(11) NOT NULL,
    PRIMARY KEY (id),
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);


INSERT INTO administradores (user_id)
SELECT id FROM users WHERE email = 'admin@admin.com' AND role = 'Administrador';

CREATE TABLE dispositivos (
    id INT(11) NOT NULL AUTO_INCREMENT,
    user_id INT(11) NOT NULL,
    categoria_id INT(11) NOT NULL,
    marca VARCHAR(50),
    modelo VARCHAR(50),
    descripcion_falla TEXT,
    img_dispositivo VARCHAR(255) DEFAULT NULL,
    fecha_registro DATETIME DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (categoria_id) REFERENCES categorias(id) ON DELETE CASCADE
);
CREATE TABLE tickets (
    id INT(11) NOT NULL AUTO_INCREMENT,
    dispositivo_id INT (11) NOT NULL,
    cliente_id INT(11) NOT NULL,
    tecnico_id INT(11),
    supervisor_id INT(11),
    estado_id INT(11) NOT NULL,
    descripcion_falla TEXT NOT NULL,
    fecha_creacion DATETIME DEFAULT CURRENT_TIMESTAMP,
    fecha_cierre DATETIME,
    PRIMARY KEY (id),
    FOREIGN KEY (dispositivo_id) REFERENCES dispositivos(id) ON DELETE CASCADE,
    FOREIGN KEY (cliente_id) REFERENCES clientes(id) ON DELETE CASCADE,
    FOREIGN KEY (tecnico_id) REFERENCES tecnicos(id) ON DELETE SET NULL,
    FOREIGN KEY (supervisor_id) REFERENCES supervisores(id) ON DELETE SET NULL,
    FOREIGN KEY (estado_id) REFERENCES estados_tickets(id)
);
CREATE TABLE inventarios (
    id INT(11) NOT NULL AUTO_INCREMENT,
    categoria_id INT(11) NOT NULL,
    name_item VARCHAR(100) NOT NULL,
    valor_unitario DECIMAL(10, 2) NOT NULL CHECK (valor_unitario >= 0),
    descripcion TEXT,
    foto_item VARCHAR(255) DEFAULT NULL,
    stock_actual INT(11) NOT NULL DEFAULT 0 CHECK (stock_actual >= 0),
    stock_minimo INT(11) NOT NULL DEFAULT 0 CHECK (stock_minimo >= 0),
    fecha_creacion DATETIME DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    FOREIGN KEY (categoria_id) REFERENCES categorias_inventario(id) ON DELETE CASCADE
);
CREATE TABLE item_ticket (
    id INT(11) NOT NULL AUTO_INCREMENT,
    ticket_id INT(11) NOT NULL,
    inventario_id INT(11) NOT NULL,
    tecnico_id INT(11) NOT NULL,
    supervisor_id INT(11) NOT NULL,
    cantidad INT(11) NOT NULL DEFAULT 1,
    valor_total DECIMAL(10, 2) NOT NULL,
    fecha_asignacion DATETIME DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    FOREIGN KEY (ticket_id) REFERENCES tickets(id) ON DELETE CASCADE,
    FOREIGN KEY (inventario_id) REFERENCES inventarios(id) ON DELETE CASCADE,
    FOREIGN KEY (tecnico_id) REFERENCES tecnicos(id) ON DELETE CASCADE,
    FOREIGN KEY (supervisor_id) REFERENCES supervisores(id) ON DELETE CASCADE
);

CREATE TABLE ticket_labor (
    ticket_id INT(11) PRIMARY KEY,
    tecnico_id INT(11) NOT NULL,
    labor_amount DECIMAL(10,2) NOT NULL DEFAULT 0,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (ticket_id) REFERENCES tickets(id) ON DELETE CASCADE,
    FOREIGN KEY (tecnico_id) REFERENCES tecnicos(id) ON DELETE CASCADE
);
CREATE TABLE historial (
    id INT(11) NOT NULL AUTO_INCREMENT,
    acciones VARCHAR(255) NOT NULL,
    detalles TEXT,
    fecha DATETIME DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id)
);

CREATE TABLE ticket_estado_historial (
    id INT(11) NOT NULL AUTO_INCREMENT,
    ticket_id INT(11) NOT NULL,
    estado_id INT(11) NOT NULL,
    user_id INT(11) NOT NULL,
    user_role ENUM('Cliente','Tecnico','Supervisor','Administrador') NOT NULL,
    comentario VARCHAR(255) DEFAULT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    FOREIGN KEY (ticket_id) REFERENCES tickets(id) ON DELETE CASCADE,
    FOREIGN KEY (estado_id) REFERENCES estados_tickets(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

CREATE TABLE pagos (
    id INT(11) NOT NULL AUTO_INCREMENT,
    ticket_id INT(11) NOT NULL,
    amount DECIMAL(10,2) NOT NULL,
    method ENUM('EFECTIVO','TARJETA','TRANSFERENCIA','OTRO') NOT NULL DEFAULT 'EFECTIVO',
    reference VARCHAR(100) NULL,
    user_id INT(11) NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    FOREIGN KEY (ticket_id) REFERENCES tickets(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL
);

-- mail_queue removido: no se implementará

CREATE TABLE ticket_ratings (
    id INT(11) NOT NULL AUTO_INCREMENT,
    ticket_id INT(11) NOT NULL,
    tecnico_id INT(11) NOT NULL,
    cliente_id INT(11) NOT NULL,
    stars TINYINT NOT NULL CHECK (stars BETWEEN 1 AND 5),
    comment VARCHAR(255) NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    FOREIGN KEY (ticket_id) REFERENCES tickets(id) ON DELETE CASCADE,
    FOREIGN KEY (tecnico_id) REFERENCES tecnicos(id) ON DELETE CASCADE,
    FOREIGN KEY (cliente_id) REFERENCES clientes(id) ON DELETE CASCADE
);

CREATE TABLE tecnico_stats (
    id INT(11) NOT NULL AUTO_INCREMENT,
    tecnico_id INT(11) NOT NULL,
    avg_rating DECIMAL(3,2) NOT NULL DEFAULT 3.00,
    ratings_count INT(11) NOT NULL DEFAULT 0,
    labor_min DECIMAL(10,2) DEFAULT 0,
    labor_max DECIMAL(10,2) DEFAULT 0,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    UNIQUE KEY uq_tecnico_stats_tecnico (tecnico_id),
    FOREIGN KEY (tecnico_id) REFERENCES tecnicos(id) ON DELETE CASCADE
);

-- Notificaciones
CREATE TABLE notifications (
    id INT(11) NOT NULL AUTO_INCREMENT,
    title VARCHAR(150) NOT NULL,
    body TEXT NOT NULL,
    audience ENUM('ALL','ROLE','USER') NOT NULL DEFAULT 'ALL',
    audience_role ENUM('Cliente','Tecnico','Supervisor','Administrador') NULL,
    target_user_id INT(11) NULL,
    created_by INT(11) NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    FOREIGN KEY (target_user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE SET NULL
);

CREATE TABLE notification_user (
    notification_id INT(11) NOT NULL,
    user_id INT(11) NOT NULL,
    is_read TINYINT(1) NOT NULL DEFAULT 0,
    read_at DATETIME NULL,
    PRIMARY KEY (notification_id, user_id),
    FOREIGN KEY (notification_id) REFERENCES notifications(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

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