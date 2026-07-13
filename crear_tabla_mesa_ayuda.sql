CREATE TABLE IF NOT EXISTS mesa_ayuda (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(255) NOT NULL,
    email VARCHAR(255) NOT NULL,
    asunto VARCHAR(255) NOT NULL,
    mensaje LONGTEXT NOT NULL,
    estado VARCHAR(50) DEFAULT 'Abierto',
    creado_en TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_estado (estado),
    INDEX idx_creado_en (creado_en)
);
