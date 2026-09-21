# Security Policy

## Reporting a vulnerability

Please **do not open a public issue** for security problems.

Report privately to: **<security contact email - fill in before publishing>**
or use GitHub's "Report a vulnerability" (Security tab -> Advisories) if enabled.

Include what you found, the steps to reproduce it, and the impact you expect.
You will get an acknowledgement, and we will keep you informed until it is fixed.

## Scope notes for operators

Before going live, confirm the production checklist:

- `APP_ENV=production`, `APP_DEBUG=false`, HTTPS enforced, `SESSION_SECURE_COOKIE=true`, `SESSION_ENCRYPT=true`
- `ADMIN_REQUIRE_2FA=true` (the production guard refuses to boot without it)
- `CACHE_STORE=redis` (rate limiters and OTP lockouts should not share the database transaction)
- `TRUSTED_PROXIES` set to your proxy's real addresses; do not expose the origin server directly
- All `SELCOM_*` and `NOTIFY_AFRICA_*` variables set; restrict `SELCOM_ALLOWED_REDIRECT_HOSTS`
- Run `php artisan config:cache` only after the values above are in `.env`; never call `env()` outside `config/`
- Set a spend cap and spike alerts on the SMS provider account
