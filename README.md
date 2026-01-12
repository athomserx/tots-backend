<p align="center"><a href="https://laravel.com" target="_blank"><img src="https://raw.githubusercontent.com/laravel/art/master/logo-lockup/5%20SVG/2%20CMYK/1%20Full%20Color/laravel-logolockup-cmyk-red.svg" width="400" alt="Laravel Logo"></a></p>

# Backend for reservation system

En este archivo se describen algunos detalles del backend para el sistema de reservas de la prueba técnica TOTS

## Diseño de la base de datos

**spaces**

-   `name`
-   `description`
-   `type`
-   `price_per_hour`
-   `capacity`
-   `images`

**users**

-   `name`
-   `email`
-   `phone`
-   `role_id`

**roles**

-   `name`

**reservations**

-   `user_id`
-   `space_id`
-   `start`
-   `end`
-   `type`
-   `event_name`
-   `created_at`

**availability_rules**

-   `space_id` (null si aplica para todos los espacios)
-   `day_of_week`: Día de la semana (0 para domingo, 6 para sábado).
-   `open_time`: Hora de apertura.
-   `close_time`: Hora de cierre.
-   `is_active`: (por si se quiere desactivar la regla).

**exceptions**

-   `date`: Fecha específica del cambio.
-   `is_closed`: Define si el espacio está cerrado o abierto ese día.
-   `space_id` (null si aplica para todos los espacios)
-   `override_open_time`: Hora de apertura especial (nullable).
-   `override_close_time`: Hora de cierre especial (nullable).

## Reglas de reserva

1. Si hay una excepción (`exceptions`) para esa fecha. (Prioridad máxima).
2. Si no, ¿qué dice `availability_rules` para ese día de la semana?
3. Si está dentro del horario, ¿hay alguna reserva o bloqueo que se cruce en `reservations`?

## Corriendo el proyecto

Para correr el proyecto, se debe tener instalado composer y php.

1. Clonar este repositorio: `git clone https://github.com/athomserx/tots-backend.git`
2. Instalar dependencias con `composer install`
3. Crear archivo `.env` en la raiz del proyecto y copiar el contenido de `.env.example`.
4. Generar la key para JWT con `php artisan jwt:secret`
5. Crear una base de datos postgresql y que las credenciales coincidan con las del archivo `.env`
6. Correr las migraciones con `php artisan migrate`
7. Correr los seeders con `php artisan db:seed`
8. Iniciar el servidor con `php artisan serve`

### usuario admin

-   Email: `admin@admin.com`
-   Contraseña: `password`

Para crear un usuario de cliente, se debe usar la página de registro del frontend.
