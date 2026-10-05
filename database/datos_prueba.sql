USE gastos_app;

SET @u = 1;

INSERT INTO presupuestos (id_usuario, nombre, fecha_inicio, fecha_fin, limite)
VALUES (@u, 'Gastos del mes', DATE_FORMAT(CURDATE(), '%Y-%m-01'), LAST_DAY(CURDATE()), 1500000);
SET @p = LAST_INSERT_ID();

INSERT INTO gastos (id_usuario, id_presupuesto, fecha, monto, tipo_gasto, descripcion) VALUES
  (@u, @p, CURDATE(),                      18000, 'Comida',        'Almuerzo'),
  (@u, @p, CURDATE(),                       4500, 'Transporte',    'Bus'),
  (@u, @p, CURDATE() - INTERVAL 1 DAY,     62000, 'Mercado',       'Compras de la semana'),
  (@u, @p, CURDATE() - INTERVAL 2 DAY,     25000, 'Ocio',          'Cine'),
  (@u, @p, CURDATE() - INTERVAL 3 DAY,     13500, 'Comida',        'Desayuno y café'),
  (@u, @p, CURDATE() - INTERVAL 4 DAY,    120000, 'Servicios',     'Internet'),
  (@u, @p, CURDATE() - INTERVAL 5 DAY,     38000, 'Transporte',    'Recarga'),
  (@u, @p, CURDATE() - INTERVAL 6 DAY,     21000, 'Comida',        'Cena');
