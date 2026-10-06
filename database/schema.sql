CREATE DATABASE IF NOT EXISTS gastos_app
  CHARACTER SET utf8mb4
  COLLATE utf8mb4_unicode_ci;

USE gastos_app;


CREATE TABLE IF NOT EXISTS usuarios (
  id             INT UNSIGNED  NOT NULL AUTO_INCREMENT,
  nombre         VARCHAR(80)   NOT NULL,
  email          VARCHAR(120)  NOT NULL,
  password_hash  VARCHAR(255)  NOT NULL,          
  creado_en      TIMESTAMP     NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  UNIQUE KEY uq_usuarios_email (email)
) ENGINE=InnoDB;


CREATE TABLE IF NOT EXISTS presupuestos (
  id            INT UNSIGNED   NOT NULL AUTO_INCREMENT,
  id_usuario    INT UNSIGNED   NOT NULL,
  nombre        VARCHAR(100)   NOT NULL,           -- ej: "Gastos de octubre"
  fecha_inicio  DATE           NOT NULL,
  fecha_fin     DATE           NOT NULL,
  limite        DECIMAL(12,2)  NOT NULL,           -- dinero disponible para gastar en el plazo
  activo        TINYINT(1)     NOT NULL DEFAULT 0, -- 1 = activo, 0 = inactivo
  creado_en     TIMESTAMP      NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  KEY idx_presupuestos_usuario_activo (id_usuario, activo),
  CONSTRAINT fk_presupuestos_usuario
    FOREIGN KEY (id_usuario) REFERENCES usuarios (id) ON DELETE CASCADE,
  CONSTRAINT chk_presupuestos_fechas CHECK (fecha_fin >= fecha_inicio),
  CONSTRAINT chk_presupuestos_limite CHECK (limite > 0),
  CONSTRAINT chk_presupuestos_activo CHECK (activo IN (0, 1))
) ENGINE=InnoDB;


CREATE TABLE IF NOT EXISTS gastos (
  id              INT UNSIGNED   NOT NULL AUTO_INCREMENT,
  id_usuario      INT UNSIGNED   NOT NULL,
  id_presupuesto  INT UNSIGNED   NULL,
  fecha           DATE           NOT NULL,          -- el día del gasto
  monto           DECIMAL(12,2)  NOT NULL,          -- cantidad de dinero
  tipo_gasto      VARCHAR(50)    NOT NULL,          -- en qué se gastó: Comida, Transporte...
  descripcion     VARCHAR(150)   NULL,              -- detalle opcional
  creado_en       TIMESTAMP      NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  KEY idx_gastos_usuario_fecha (id_usuario, fecha),
  KEY idx_gastos_presupuesto_fecha (id_presupuesto, fecha),
  CONSTRAINT fk_gastos_usuario
    FOREIGN KEY (id_usuario) REFERENCES usuarios (id) ON DELETE CASCADE,
  CONSTRAINT fk_gastos_presupuesto
    FOREIGN KEY (id_presupuesto) REFERENCES presupuestos (id) ON DELETE SET NULL,
  CONSTRAINT chk_gastos_monto CHECK (monto > 0)
) ENGINE=InnoDB;