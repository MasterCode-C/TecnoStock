# Análisis y levantamiento de requerimientos - TecnoStock

## 1. Descripción del problema

TecnoStock requiere una solución informática que permita gestionar el inventario de productos de manera centralizada, manteniendo información actualizada sobre productos, categorías, existencias y movimientos de entrada y salida.

El sistema debe permitir controlar el stock disponible y prevenir operaciones que produzcan inconsistencias, especialmente salidas superiores a las existencias disponibles.

## 2. Objetivo del sistema

Desarrollar una aplicación web de gestión de inventario que permita registrar y administrar productos y categorías, controlar movimientos de entrada y salida, actualizar automáticamente las existencias y consultar información relevante para el control del inventario.

## 3. Alcance

El sistema TecnoStock contempla:

- Autenticación de usuarios.
- Gestión de productos.
- Gestión de categorías.
- Registro de entradas y salidas de inventario.
- Actualización automática del stock.
- Consulta del historial de movimientos.
- Búsqueda y filtrado de productos.
- Identificación de productos con stock bajo.
- Activación y desactivación de productos y categorías.
- Validación de datos en cliente y servidor.

El sistema corresponde a una aplicación web ejecutada en un entorno local mediante XAMPP, PHP y MySQL.

## 4. Actor del sistema

### Usuario administrador

Es el usuario autorizado para acceder al sistema y realizar las operaciones de gestión del inventario.

Entre sus principales acciones se encuentran:

- Iniciar y cerrar sesión.
- Registrar productos.
- Consultar productos.
- Modificar productos.
- Activar o desactivar productos.
- Administrar categorías.
- Registrar entradas de inventario.
- Registrar salidas de inventario.
- Consultar movimientos.
- Revisar productos con stock bajo.

## 5. Requisitos funcionales

### RF01 - Autenticación

El sistema debe permitir el inicio de sesión mediante credenciales válidas y restringir el acceso a las páginas protegidas cuando no exista una sesión activa.

### RF02 - Registro de productos

El sistema debe permitir registrar productos indicando código, nombre, descripción, categoría, precio, stock inicial, stock mínimo y estado.

### RF03 - Código único

El sistema debe impedir el registro de dos productos con el mismo código.

### RF04 - Consulta de productos

El sistema debe permitir visualizar los productos registrados junto con su información principal.

### RF05 - Búsqueda y filtros

El sistema debe permitir buscar productos por código o nombre y aplicar filtros por categoría, estado y condición de stock bajo.

### RF06 - Modificación de productos

El sistema debe permitir modificar la información de los productos registrados.

El stock actual no se modifica directamente durante la edición del producto, ya que las variaciones posteriores del inventario se controlan mediante movimientos.

### RF07 - Estado de productos

El sistema debe permitir activar y desactivar productos sin eliminarlos físicamente de la base de datos.

### RF08 - Gestión de categorías

El sistema debe permitir registrar, modificar, activar y desactivar categorías.

### RF09 - Entrada de inventario

El sistema debe permitir registrar movimientos de tipo ENTRADA y aumentar automáticamente el stock del producto según la cantidad ingresada.

### RF10 - Salida de inventario

El sistema debe permitir registrar movimientos de tipo SALIDA y descontar automáticamente la cantidad correspondiente del stock disponible.

### RF11 - Control de stock

El sistema debe impedir una salida cuando la cantidad solicitada sea superior al stock disponible.

### RF12 - Historial de movimientos

El sistema debe conservar un registro de los movimientos realizados, incluyendo fecha y hora, tipo de movimiento, cantidad, producto, usuario y observación cuando corresponda.

### RF13 - Productos y categorías inactivas

El sistema no debe permitir registrar movimientos sobre productos inactivos ni sobre productos pertenecientes a categorías inactivas.

### RF14 - Stock bajo

El sistema debe identificar los productos cuyo stock actual sea inferior al stock mínimo definido.

### RF15 - Dashboard

El sistema debe presentar información resumida del inventario, incluyendo productos activos e inactivos, productos con stock bajo y movimientos registrados.

## 6. Requisitos no funcionales

### RNF01 - Usabilidad

La interfaz debe presentar formularios, botones, mensajes y opciones de navegación comprensibles para el usuario.

### RNF02 - Validación

Los datos críticos deben ser validados antes de realizar operaciones sobre la base de datos.

Las validaciones inmediatas se realizan mediante JavaScript y las validaciones definitivas se realizan mediante PHP en el servidor.

### RNF03 - Integridad de datos

El sistema debe mantener la consistencia entre los productos y sus movimientos de inventario.

### RNF04 - Seguridad de acceso

Las páginas internas deben requerir una sesión de usuario válida.

### RNF05 - Persistencia

La información debe almacenarse en una base de datos MySQL.

### RNF06 - Trazabilidad

Los movimientos de inventario deben conservarse para permitir consultar las operaciones realizadas.

### RNF07 - Interfaz

La aplicación debe utilizar una interfaz web consistente y adaptable mediante Bootstrap.

### RNF08 - Acceso a base de datos

Las operaciones con la base de datos deben realizarse mediante PDO y consultas preparadas.

## 7. Reglas de negocio

### RN01 - Código de producto

Cada producto debe poseer un código único.

### RN02 - Valores de stock

El stock actual y el stock mínimo no pueden contener valores negativos.

### RN03 - Cantidad de movimiento

Toda entrada o salida debe registrar una cantidad entera mayor que cero.

### RN04 - Salidas

Una salida no puede superar el stock disponible del producto.

### RN05 - Actualización de existencias

Una ENTRADA aumenta el stock y una SALIDA lo disminuye.

### RN06 - Modificación del stock

Después de creado el producto, las variaciones del stock se realizan mediante movimientos de inventario y no mediante la edición directa del producto.

### RN07 - Productos inactivos

Un producto inactivo no puede utilizarse para registrar nuevos movimientos.

### RN08 - Categorías inactivas

Los productos pertenecientes a una categoría inactiva no pueden utilizarse para registrar nuevos movimientos.

### RN09 - Conservación del historial

Los productos y categorías se desactivan en lugar de eliminarse físicamente cuando su eliminación pueda afectar la trazabilidad y las relaciones existentes.

### RN10 - Stock bajo

Un producto se considera con stock bajo cuando:

`stock_actual < stock_minimo`

## 8. Validaciones principales

El sistema contempla, entre otras, las siguientes validaciones:

- Campos obligatorios.
- Código único de producto.
- Precio válido.
- Stock actual igual o mayor que cero.
- Stock mínimo igual o mayor que cero.
- Cantidad de movimiento entera y mayor que cero.
- Salida no superior al stock disponible.
- Longitud máxima de campos de texto.
- Existencia del producto.
- Estado del producto.
- Existencia y estado de la categoría.
- Existencia de una sesión válida.

## 9. Modelo general de datos

El sistema utiliza cuatro entidades principales:

### Usuario

Almacena los datos necesarios para la autenticación y la identificación del usuario que realiza movimientos.

### Categoría

Permite clasificar los productos y controlar su estado.

### Producto

Almacena la información de los artículos gestionados, incluyendo código, precio, stock actual, stock mínimo, categoría y estado.

### Movimiento

Registra las entradas y salidas realizadas sobre los productos y mantiene la relación con el usuario responsable de la operación.

Las relaciones entre estas entidades se implementan mediante claves primarias y foráneas en MySQL.

## 10. Decisiones de diseño

### Control del stock mediante movimientos

Se decidió que el stock actual no pueda modificarse directamente desde la edición de un producto. Después de su creación, las existencias se modifican mediante movimientos de ENTRADA o SALIDA.

Esta decisión permite mantener mayor trazabilidad sobre los cambios realizados en el inventario.

### Desactivación en lugar de eliminación

Los productos y categorías pueden desactivarse en lugar de eliminarse físicamente. Esto permite conservar la información histórica y evita afectar las relaciones con los movimientos existentes.

### Validación cliente y servidor

JavaScript proporciona retroalimentación inmediata al usuario, mientras que PHP vuelve a comprobar las reglas antes de realizar modificaciones en la base de datos.

La validación del servidor constituye el control definitivo, debido a que las validaciones realizadas en el navegador pueden ser omitidas o manipuladas.

### Transacciones en movimientos

El registro de movimientos y la actualización del stock se realizan dentro de una transacción. De esta forma, ambas operaciones se confirman conjuntamente o se revierten ante un error.

## 11. Criterios de aceptación principales

El sistema se considera funcional cuando:

- Un usuario autorizado puede iniciar sesión.
- Un usuario sin sesión no puede acceder a las páginas protegidas.
- Es posible registrar y modificar productos.
- No es posible registrar códigos duplicados.
- Es posible administrar categorías.
- Las entradas aumentan correctamente el stock.
- Las salidas disminuyen correctamente el stock.
- Una salida superior al stock disponible es rechazada.
- Los movimientos quedan registrados en el historial.
- Los productos inactivos no pueden utilizarse para nuevos movimientos.
- Los productos de categorías inactivas tampoco pueden utilizarse para nuevos movimientos.
- Los productos con stock bajo pueden ser identificados.
- La información permanece almacenada en la base de datos.

## 12. Conclusión

El análisis permitió definir un sistema enfocado en el control de inventario y en la integridad de las existencias. Las funcionalidades implementadas responden a las operaciones principales requeridas para TecnoStock, incorporando validaciones, trazabilidad de movimientos, control de estados y mecanismos para prevenir stock negativo.