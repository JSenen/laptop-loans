-- Create database first: CREATE DATABASE laptop_loans CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
-- Then use it: USE laptop_loans;

CREATE TABLE people (
  id INT AUTO_INCREMENT PRIMARY KEY,
  nombre VARCHAR(100) NOT NULL,
  apellidos VARCHAR(150) NOT NULL,
  dni VARCHAR(12) NOT NULL,
  tip VARCHAR(12) NULL,
  telefono VARCHAR(20) NULL,
  email VARCHAR(150) NULL,
  creado_en TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  UNIQUE KEY uk_people_dni (dni)
);

CREATE TABLE laptops (
  id INT AUTO_INCREMENT PRIMARY KEY,
  num_serie VARCHAR(100) NOT NULL,
  marca VARCHAR(80) NULL,
  modelo VARCHAR(120) NULL,
  estado ENUM('disponible','prestado','baja','mantenimiento') DEFAULT 'disponible',
  observaciones TEXT NULL,
  creado_en TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  UNIQUE KEY uk_laptops_num_serie (num_serie)
);

CREATE TABLE courses (
  id INT AUTO_INCREMENT PRIMARY KEY,
  nombre VARCHAR(150) NOT NULL,
  descripcion TEXT NULL,
  fecha_inicio DATE NULL,
  fecha_fin DATE NULL,
  creado_en TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  UNIQUE KEY uk_courses_nombre (nombre)
);

CREATE TABLE handovers (
  id BIGINT AUTO_INCREMENT PRIMARY KEY,
  laptop_id INT NOT NULL,
  person_id INT NOT NULL,
  course_id INT NULL,
  tipo ENUM('entrega','devolucion') NOT NULL,
  fecha DATETIME NOT NULL,
  observaciones TEXT NULL,
  recibido_por VARCHAR(150) NULL,
  firma_recibido LONGBLOB NULL,
  recibo_pdf_path VARCHAR(255) NULL,
  creado_en TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (laptop_id) REFERENCES laptops(id),
  FOREIGN KEY (person_id) REFERENCES people(id),
  FOREIGN KEY (course_id) REFERENCES courses(id),
  INDEX idx_handovers_laptop_fecha (laptop_id, fecha),
  INDEX idx_handovers_persona_fecha (person_id, fecha)
);

ALTER TABLE people ADD UNIQUE KEY uk_people_tip (tip); 

-- 1) Tabla de ubicaciones
CREATE TABLE IF NOT EXISTS locations (
  id INT AUTO_INCREMENT PRIMARY KEY,
  nombre VARCHAR(120) NOT NULL,
  tipo ENUM('Zona','Comandancia','Otro') DEFAULT 'Otro',
  descripcion VARCHAR(255) NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 2) Portátiles: ubicación actual (cuando están "disponible")
ALTER TABLE laptops
  ADD COLUMN ubicacion_id INT NULL DEFAULT NULL,
  ADD CONSTRAINT fk_laptops_ubicacion
    FOREIGN KEY (ubicacion_id) REFERENCES locations(id) ON DELETE SET NULL;

-- 3) Movimientos: almacén/lugar donde se hizo la entrega/devolución
ALTER TABLE handovers
  ADD COLUMN location_id INT NULL DEFAULT NULL,
  ADD CONSTRAINT fk_handovers_location
    FOREIGN KEY (location_id) REFERENCES locations(id) ON DELETE SET NULL;

-- (Opcional) algunas ubicaciones de ejemplo
INSERT INTO locations (nombre,tipo) VALUES
 ('Zona','Zona'), ('Comandancia','Comandancia')
 ON DUPLICATE KEY UPDATE nombre=VALUES(nombre);
 
 -- Para poder editar personas y cursos sin perderlos de la base
ALTER TABLE people  ADD COLUMN activo TINYINT(1) NOT NULL DEFAULT 1 AFTER email;
ALTER TABLE courses ADD COLUMN activo TINYINT(1) NOT NULL DEFAULT 1 AFTER fecha_fin;
 -- Añadir campo preferencia en los portatiles
ALTER TABLE laptops ADD COLUMN uso_preferente VARCHAR(50) NULL AFTER modelo;




