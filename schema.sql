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