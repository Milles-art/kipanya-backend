# Kipanya Backend Baseline

This repository is the Laravel backend/API baseline for the Kipanya ecosystem.

The public frontend is maintained separately. Blade/web presentation code is intentionally not part of this repository.

The approved backend architecture is documented in `app/ARCHITECTURE.md`.

The original 26 migration files are preserved unchanged. Foundation V2 is added through new migrations so the database history remains incremental and reversible.

Do not use `migrate:fresh` against a shared, staging, or production database. Add new migrations incrementally and review destructive migrations before deployment.


## 2026-09-09 Wear Commerce V2 extension

The current backend now includes persistent guest/authenticated carts, account-at-checkout, wishlist, addresses, server-side checkout preview, order creation, order status history, inventory reservations, payment transaction persistence, an abstract payment gateway, and deterministic idempotency support for checkout. Real payment-provider integration remains intentionally deferred.
