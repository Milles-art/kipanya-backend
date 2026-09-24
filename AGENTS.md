# Project Rules — kipanya-backend (KP Wear)

## Blade edits restriction

Do not edit any blade templates inside `resources/views/pages/` (e.g. home, catalog/shop, collections, about, contact, checkout) unless the user explicitly allows it for the specific task.

## Verification commands

- `php artisan view:clear` then `php artisan view:cache` to refresh compiled Blade views.
- `node node_modules/vite/bin/vite.js build` to rebuild frontend assets.
- `php artisan test --testsuite=Feature` for the Feature test suite.