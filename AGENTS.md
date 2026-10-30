# AGENTS.md

Laravel 13 app (PHP 8.3) for a multi-level request approval workflow. Application code lives in `src/`; run it through the root `docker-compose.yml`. Root is a git repo.

## Commands

Run from repo root unless noted.

- Start stack: `docker compose up -d` (app, web, db). App served at http://localhost:8080.
- Vite dev server: `docker compose --profile tools up -d node`, then `docker compose exec node npm run dev` (port 5173).
- Build assets: `docker compose exec node npm run build` (or run `npm run build` in `src/` with local Node).
- Migrate: `docker compose exec app php artisan migrate --force`
- Seed demo users/types: `docker compose exec app php artisan db:seed`
- Tests: `docker compose exec app php artisan test` (phpunit.xml forces sqlite `:memory:`; no MySQL needed). `composer test` runs `config:clear` then test.
- Single test: `docker compose exec app php artisan test tests/Feature/FooTest.php` or `... --filter=FooTest`.
- Lint/format: `docker compose exec app vendor/bin/pint` (fix) or `... pint --test`.
- Tinker: `docker compose exec app php artisan tinker`.

## Architecture

- Business logic is in `app/Services/SolicitudService.php` (inside `src/`); controllers stay thin. Keep state transitions there.
- Roles: `App\Enums\Rol` (`Colaborador`, `Aprobador`, `Administrador`; values lowercase). `UserFactory` does not set `rol`, so factory users have null role; views like `navigation.blade.php` assume non-null `rol` and will 500. Use seeded users or set `rol` in tests.
- `SolicitudPolicy` is role-based; `create` is only `Colaborador`.
- Approval levels: `SolicitudService::nivelesRequeridos()` returns 2 when monto exceeds `config('solicitudes.umbral_segunda_aprobacion')` (default 50000), else 1. Threshold is env `SOLICITUDES_UMBRAL_SEGUNDA_APROBACION`.
- Resource route is declared with `->parameters(['solicitudes' => 'solicitud'])` to avoid Laravel singularizing to "solicitude".
- Uploads use local disk under `storage/app/solicitudes/{id}`; nginx allows 20M (`client_max_body_size`), but PHP-FPM keeps its default upload limits unless overridden.

## Conventions / gotchas

- DB is MySQL 8 in Docker (`db`, host `db`, port 3306 inside; 3307 on host). Tests override to sqlite in-memory.
- Container entrypoint runs `migrate --force` and seeds demo data when `SEED_DEMO=true`; re-running migrate manually is only needed outside the container flow.
- `.env` already present and points at Docker DB; do not commit secrets.
- Seeded users (password `password`): admin@example.com, aprobador1/2@example.com, colaborador1/2@example.com.
- No static analysis tool configured; rely on Pint + PHPUnit.
- Pre-existing test failures: `ExampleTest` expects `/` to return 200 but `/` redirects to `/login`; `ProfileTest` fails because factory users lack `rol`. Do not treat as regressions unless you changed them.