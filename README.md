# Ludoteca

Una app web para llevar tu biblioteca de videojuegos: marcas lo que juegas, terminas, abandonas o tienes pendiente, lo puntúas de 0,5 a 5 estrellas y ves tus estadísticas. Todo gratis, sin planes de pago.

La hice porque en la app que usaba la búsqueda se quedaba cargando y las medias y estadísticas estaban detrás de una suscripción.

## Qué tiene

- **Búsqueda que siempre carga.** Primero responde la base local (instantáneo) y a la vez se consulta RAWG con timeout de 4 s y caché de un día. Si RAWG no contesta, se siguen viendo los resultados guardados y no se queda nada colgado.
- Biblioteca con estados: jugando, pendiente, completado, platinado y abandonado.
- Nota con medias estrellas, horas, plataforma, reseña y favoritos.
- **Estadísticas gratis**: horas, nota media, distribución de notas, géneros más jugados, juegos terminados por año, plataformas y porcentaje de juegos que termino frente a los que abandono.
- Media de la comunidad de la app por juego, además de la nota de RAWG y Metacritic.
- Listas propias y perfiles públicos (`/u/usuario`).
- Pensada para el móvil primero. Se puede instalar como app (PWA).

## Stack

Laravel 13, PHP 8.3, Blade + Alpine.js, Tailwind 4 (Vite), PostgreSQL en producción y SQLite en local. Datos de juegos de [RAWG](https://rawg.io).

## Cómo funciona la búsqueda

`GET /api/buscar?src=local` consulta la tabla `games` y responde al momento. `GET /api/buscar?src=remoto` llama a RAWG, guarda los juegos que devuelve (así la próxima vez ya son locales) y cachea la respuesta. El cliente lanza las dos a la vez y va pintando según llegan, así que lo local nunca espera a lo remoto. Código en `app/Services/Catalogo.php`.

## Instalar en local

```bash
composer install
cp .env.example .env
php artisan key:generate
touch database/database.sqlite
php artisan migrate --seed
npm install && npm run build
php artisan serve
```

Hace falta una clave gratuita de RAWG en `RAWG_API_KEY` (sin ella solo funciona el catálogo sembrado, unos 200 juegos). El seeder crea una cuenta de ejemplo en `/demo`.

## Tests

```bash
php artisan test
```
