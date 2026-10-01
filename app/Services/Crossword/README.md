# Servicios del crucigrama

## Flujo de datos

1. La vista Blade [`resources/views/estudiante/juegos/crucigrama.blade.php`](../../../resources/views/estudiante/juegos/crucigrama.blade.php) monta `#crossword-app` con URLs de API.
2. [`public/crucigrama/api.js`](../../../public/crucigrama/api.js) envía peticiones JSON autenticadas (CSRF).
3. [`CrosswordPlayController`](../../Http/Controllers/Controllers_Estudiantes/CrosswordPlayController.php) orquesta lectura/guardado.
4. [`CrosswordProgressService`](CrosswordProgressService.php) persiste progreso, monedas, XP (`XpAwardService`) y eventos.
5. Modelos: `CrosswordProgress`, `CrosswordEvent`, `CrosswordWord`.

## Agregar palabras (docente)

Panel: `/docente/crucigrama` → crear palabra con respuesta, pista, dificultad y nivel (1–20).

## Monedas

Regla: `coins_earned = floor(total_correct_attempts / 2)`.

Se recalcula en `CrosswordProgress::syncCoinsFromAttempts()` tras cada acierto nuevo.

## Tienda futura

Usar `coins_earned` en `crossword_progress` como saldo. Los eventos guardan `coins_awarded` por acción para auditoría.
