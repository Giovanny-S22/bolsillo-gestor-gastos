USE gastos_app;

SET @u = 1;


INSERT INTO presupuestos (id_usuario, nombre, fecha_inicio, fecha_fin, limite, activo)
VALUES (@u, 'Gastos del mes', CURDATE() - INTERVAL 10 DAY, CURDATE() + INTERVAL 20 DAY, 1500000, 1);
SET @p_activo = LAST_INSERT_ID();

-- Presupuesto INACTIVO (ya terminado)
INSERT INTO presupuestos (id_usuario, nombre, fecha_inicio, fecha_fin, limite, activo)
VALUES (@u, 'Mes anterior', CURDATE() - INTERVAL 60 DAY, CURDATE() - INTERVAL 31 DAY, 1200000, 0);
SET @p_anterior = LAST_INSERT_ID();



SET @cat_comida = (SELECT id FROM categorias WHERE nombre = 'Comida');
SET @cat_transporte = (SELECT id FROM categorias WHERE nombre = 'Transporte');
SET @cat_mercado = (SELECT id FROM categorias WHERE nombre = 'Mercado');
SET @cat_servicios = (SELECT id FROM categorias WHERE nombre = 'Servicios');
SET @cat_ocio = (SELECT id FROM categorias WHERE nombre = 'Ocio');


INSERT INTO gastos
  (id_usuario, id_presupuesto, fecha, monto, id_categoria, descripcion)
VALUES
  (@u, @p_activo, CURDATE(), 18000, @cat_comida, 'Almuerzo'),
  (@u, @p_activo, CURDATE(), 4500, @cat_transporte, 'Bus'),
  (@u, @p_activo, CURDATE() - INTERVAL 1 DAY, 62000, @cat_mercado, 'Compras de la semana'),
  (@u, @p_activo, CURDATE() - INTERVAL 2 DAY, 25000, @cat_ocio, 'Cine'),
  (@u, @p_activo, CURDATE() - INTERVAL 3 DAY, 13500, @cat_comida, 'Desayuno y café'),
  (@u, @p_activo, CURDATE() - INTERVAL 4 DAY, 120000, @cat_servicios, 'Internet'),
  (@u, @p_activo, CURDATE() - INTERVAL 5 DAY, 38000, @cat_transporte, 'Recarga'),
  (@u, @p_activo, CURDATE() - INTERVAL 6 DAY, 21000, @cat_comida, 'Cena'),

  -- Gastos del presupuesto inactivo
  (@u, @p_anterior, CURDATE() - INTERVAL 40 DAY, 90000, @cat_mercado, 'Compras del mes'),
  (@u, @p_anterior, CURDATE() - INTERVAL 45 DAY, 30000, @cat_ocio, 'Salida con amigos'),
  (@u, @p_anterior, CURDATE() - INTERVAL 50 DAY, 15000, @cat_transporte, 'Taxi');
