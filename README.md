# Invoice Recorder Challenge Sample (v1.0) [ES]

API REST que expone endpoints que permite registrar comprobantes en formato xml.
De estos comprobantes se obteniene la información como el emisor y receptor, sus documentos (dni, ruc, etc), los artículos o líneas, y los montos totales y por cada artículo.
Un comprobante es un documento que respalda una transacción financiera o comercial, y en su versión XML es un archivo estructurado que contiene todos los datos necesarios para cumplir con los requisitos legales y fiscales.
Utilizando el lenguaje XML, se generan comprobantes digitales, que contienen información del emisor, receptor, conceptos, impuestos y el monto total de la transacción.
La API utiliza Json Web Token para la autenticación.

## Detalles de la API

-   Usa PHP 8.1
-   Usa una base de datos en MySQL
-   Puede enviar correos

## Inicia el proyecto con docker

-   Clona el archivo `.env.example` a `.env`
-   Reemplaza las credenciales de correo por las tuyas (puedes obtener unas con gmail siguiendo [esta guía](https://programacionymas.com/blog/como-enviar-mails-correos-desde-laravel#:~:text=Para%20dar%20la%20orden%20a,su%20orden%20ha%20sido%20enviada.))
-   En una terminal ejecuta:

```
docker-compose up
```

-   En otra terminal, ingresa al contenedor web y ejecuta:

```
composer install --ignore-platform-reqs
php artisan migrate
```

-   Consulta la API en http://localhost:8090/api/v1

## Información inicial

Puedes encontrar información inicial para popular la DB en el siguiente enlace:

[Datos iniciales](https://drive.google.com/drive/folders/103WGuWMLSkuHCD9142ubzyXPbJn77ZVO?usp=sharing)

## Nuevas funcionalidades

### 1. Registro de serie, número, tipo del comprobante y moneda

Se desea poder registrar la serie, número, tipo de comprobante y moneda. Para comprobantes existentes, debería extraerse esa información a regularizar desde el campo xml_content de vouchers.

### 2. Carga de comprobantes en segundo plano

Actualmente el registro de comprobantes se realiza en primer plano, se desea que se realice en segundo plano.
Además, en lugar de enviar una notificación por correo para informar subida de comprobantes, ahora deberá enviar dos listados de comprobantes:

-   Los que se subieron correctamente
-   Los que no pudieron registrarse (y la razón)

### 3. Endpoint de montos totales

Se necesita un nuevo endpoint que devuelva la información total acumulada en soles y dólares.

### 4. Eliminación de comprobantes

Se necesita poder eliminar comprobantes por su id.

### 5. Filtro en listado de comprobantes

Se necesita poder filtrar en el endpoint de listado por serie, número y por un rango de fechas (que actuarán sobre las fechas de creación).

**Nota**: En todos los casos de nuevas funcionalidades, se tratan de comprobantes por usuarios.

## Funcionalidades implementadas

### 1. Registro de serie, número, tipo del comprobante y moneda

Se creo una migracion adicional para crear los nuevos campos de la tabla vouchers y se creo una endpoint para actualizar los registros con esos nuevos campos.

### 2. Carga de comprobantes en segundo plano

Se creo un Job para el procesamiento y se reemplazo en el service, se cambio las variables de entorno para el corecto funcionanmiento atravez de database y se incorporo nuevo parametro para los errores de los vouchers que no se lograron crear capturando el error en un trycatch, previamente en el service se capturan solo archivos xml para evitar procesamientos innecesarios.

### 3. Endpoint de montos totales

Se creo una constante para defiinir las diferentes divisas y el tipo de cambio a una divisas destino, usando una funcion donde se usara por parametro la divisa destino para obtener el monto total en la respectiva divisa y para este caso se obtuvo en dolares y soles.

### 4. Eliminación de comprobantes

Se necesita pasar el valor del id del voucher para eliminar el registro, se capturan errores usando trycatch.

### 5. Filtro en listado de comprobantes

Se crearon neuvos parametros opccionales para filtrar los distintos campos que se mencionaron, en caso de las fechas se considera pasar ambas para obtener registros de ese rango de tiempo.

## Consideraciones

-   Se valorará el uso de código limpio, estándares, endpoints optimizados, tolerancia a fallos y concurrencia.

## Envío del reto

Deberás enviar el enlace del repositorio lo correos `administracion@idbi.pe`, `people@idbi.pe` y `product@idbi.pe`. Puedes indicar documentación de las nuevas funcionalidades o una descripción/diagramas/etc que creas necesario.

## ¿Tienes alguna duda?

Puedes enviar un correo a `anthony.rosado.idbi@gmail.com` enviando tus consultas y se te responderá a la brevedad.
