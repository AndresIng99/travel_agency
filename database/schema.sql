// ============================================
// 5. DATABASE/SCHEMA.SQL - Estructura de Base de Datos
// ============================================
?>
-- Crear base de datos
CREATE DATABASE IF NOT EXISTS travel_agency CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE travel_agency;

-- Tabla de usuarios
CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) UNIQUE NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    full_name VARCHAR(100) NOT NULL,
    role ENUM('admin', 'agent') NOT NULL DEFAULT 'agent',
    active BOOLEAN DEFAULT TRUE,
    last_login DATETIME NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Tabla de configuración de la empresa
CREATE TABLE company_settings (
    id INT AUTO_INCREMENT PRIMARY KEY,
    company_name VARCHAR(100) NOT NULL DEFAULT 'Travel Agency',
    logo_url VARCHAR(255) NULL,
    background_image VARCHAR(255) NULL,
    primary_color VARCHAR(7) DEFAULT '#667eea',
    secondary_color VARCHAR(7) DEFAULT '#764ba2',
    language VARCHAR(5) DEFAULT 'es',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Insertar usuario administrador por defecto
INSERT INTO users (username, email, password, full_name, role) VALUES
('admin', 'admin@travelagency.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Administrador', 'admin'),
('agente1', 'agente@travelagency.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Agente de Viajes', 'agent');

-- Insertar configuración por defecto
INSERT INTO company_settings (company_name) VALUES ('Travel Agency');

-- Nota: La contraseña por defecto es "password" para ambos usuarios