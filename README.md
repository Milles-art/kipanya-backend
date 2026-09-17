# Kipanya Backend

Laravel 13 backend and storefront for KP Wear.

## Requirements
- PHP 8.3+
- Composer 2.x
- Node.js 20+ and npm
- MySQL 8+/MariaDB or SQLite for local development

## Setup
1. `composer install`
2. Copy `.env.example` to `.env`
3. Set database and application values
4. `php artisan key:generate`
5. `php artisan storage:link --force`
6. `php artisan migrate`
7. `npm install`
8. `npm run build`

## Security production settings
Keep CORS limited to trusted frontend origins via `CORS_ALLOWED_ORIGINS`. Use HTTPS in production and set `APP_FORCE_HTTPS=true` when HTTPS is active. Session cookies are configured as HttpOnly and can be forced Secure through `APP_FORCE_HTTPS`.

Set `APP_DEBUG=false`, `APP_FORCE_HTTPS=true` when HTTPS is active, `AUTH_EXPOSE_OTP_CODES=false`, and `AUTH_LOG_OTP_CODES=false`. Never ship `.env`, runtime logs, caches, or compiled build artifacts from another environment.

## Admin
Set `ADMIN_NAME`, `ADMIN_EMAIL`, and a valid Tanzanian `ADMIN_PHONE`, then run `php artisan db:seed --class=AdminUserSeeder`.

## Scheduler
Reservation expiry requires the Laravel scheduler. Configure the server to run `php artisan schedule:run` every minute.

## Tests
Run `php artisan test`. For release validation, run migrations and tests against a disposable MySQL database as well as SQLite where applicable.

## Build
Frontend source lives in `resources/js` and `resources/css`. Run `npm run build` after source changes.

## Deliberately not included
SMS provider integration and real payment gateway integration remain separate implementation work.
