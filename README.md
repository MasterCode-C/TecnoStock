# TecnoStock

Sistema web de gestión de inventario desarrollado en PHP, MySQL, JavaScript y Bootstrap.

## Descripción

TecnoStock permite administrar productos, categorías y movimientos de inventario, manteniendo control sobre las existencias disponibles y evitando inconsistencias en el stock.

## Funcionalidades principales

- Inicio y cierre de sesión.
- Registro de productos.
- Edición de productos.
- Activación y desactivación de productos.
- Registro y edición de categorías.
- Activación y desactivación de categorías.
- Búsqueda y filtrado de productos.
- Registro de entradas y salidas de inventario.
- Actualización automática del stock.
- Prevención de salidas superiores al stock disponible.
- Alerta de productos con stock bajo.
- Historial de movimientos.
- Validaciones en JavaScript y PHP.

## Tecnologías utilizadas

- PHP
- MySQL
- JavaScript
- Bootstrap 5
- HTML5
- CSS
- PDO
- XAMPP
- GitHub

## Base de datos

El proyecto utiliza una base de datos MySQL llamada:

`tecnostock`

El archivo de respaldo se encuentra en:

`sql/tecnostock.sql`

## Estructura principal

- `auth/`: autenticación y control de sesión.
- `categorias/`: gestión de categorías.
- `productos/`: gestión de productos.
- `movimientos/`: gestión de entradas y salidas.
- `config/`: conexión a la base de datos.
- `sql/`: respaldo de la base de datos.
- `dashboard.php`: panel principal.
- `index.php`: acceso inicial al sistema.

## Reglas de negocio

- El código de cada producto debe ser único.
- No se permiten cantidades negativas.
- Una salida no puede superar el stock disponible.
- El stock se actualiza mediante movimientos de entrada y salida.
- Los productos inactivos no pueden registrar movimientos.
- Los productos pertenecientes a categorías inactivas tampoco pueden registrar movimientos.
- Los movimientos se conservan para mantener la trazabilidad del inventario.

## Ejecución local

1. Instalar XAMPP.
2. Copiar el proyecto en:

   `C:\xampp\htdocs\TecnoStock`

3. Iniciar Apache y MySQL.
4. Crear la base de datos `tecnostock`.
5. Importar el archivo:

   `sql/tecnostock.sql`

6. Abrir en el navegador:

   `http://localhost/TecnoStock/`

## Autor MasterCode-C

Proyecto desarrollado para el caso TecnoStock.