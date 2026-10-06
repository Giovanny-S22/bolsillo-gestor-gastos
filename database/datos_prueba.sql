USE gastos_app;

SET @u = 1;


INSERT INTO presupuestos (id_usuario, nombre, fecha_inicio, fecha_fin, limite, activo)
VALUES (@u, 'Gastos del mes', CURDATE() - INTERVAL 10 DAY, CURDATE() + INTERVAL 20 DAY, 1500000, 1);
SET @p_activo = LAST_INSERT_ID();

-- Presupuesto INACTIVO (ya terminado)
INSERT INTO presupuestos (id_usuario, nombre, fecha_inicio, fecha_fin, limite, activo)
VALUES (@u, 'Mes anterior', CURDATE() - INTERVAL 60 DAY, CURDATE() - INTERVAL 31 DAY, 1200000, 0);
SET @p_anterior = LAST_INSERT_ID();

INSERT INTO gastos (id_usuario, id_presupuesto, fecha, monto, tipo_gasto, descripcion) VALUES
  (@u, @p_activo,   CURDATE(),                      18000, 'Comida',        'Almuerzo'),
  (@u, @p_activo,   CURDATE(),                       4500, 'Transporte',    'Bus'),
  (@u, @p_activo,   CURDATE() - INTERVAL 1 DAY,     62000, 'Mercado',       'Compras de la semana'),
  (@u, @p_activo,   CURDATE() - INTERVAL 2 DAY,     25000, 'Ocio',          'Cine'),
  (@u, @p_activo,   CURDATE() - INTERVAL 3 DAY,     13500, 'Comida',        'Desayuno y café'),
  (@u, @p_activo,   CURDATE() - INTERVAL 4 DAY,    120000, 'Servicios',     'Internet'),
  (@u, @p_activo,   CURDATE() - INTERVAL 5 DAY,     38000, 'Transporte',    'Recarga'),
  (@u, @p_activo,   CURDATE() - INTERVAL 6 DAY,     21000, 'Comida',        'Cena'),
  
  -- Gastos del presupuesto inactivo (NO deben aparecer en el dashboard)
  (@u, @p_anterior, CURDATE() - INTERVAL 40 DAY,    90000, 'Mercado',       'Compras del mes'),
  (@u, @p_anterior, CURDATE() - INTERVAL 45 DAY,    30000, 'Ocio',          'Salida con amigos'),
  (@u, @p_anterior, CURDATE() - INTERVAL 50 DAY,    15000, 'Transporte',    'Taxi');