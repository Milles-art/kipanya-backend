# Kipanya Wear Commerce V2 — Progress

## Implemented in this milestone
- Persistent carts for authenticated users and guests.
- Guest cart token passed through `X-Guest-Cart-Token`.
- Guest cart token stored only as a SHA-256 hash in the database.
- Cart item add/update/remove/clear APIs.
- Guest-to-account cart merge API.
- Server-side cart stock validation.
- Wear wishlist API.
- Authenticated user address API.
- Checkout preview API that recalculates prices/totals server-side.
- Order status history table foundation.
- Payment transaction table foundation with idempotency key.

## API surface added
- `GET /api/v1/cart`
- `POST /api/v1/cart/items`
- `PUT /api/v1/cart/items/{variant}`
- `DELETE /api/v1/cart/items/{variant}`
- `DELETE /api/v1/cart`
- `POST /api/v1/cart/merge` (authenticated)
- `GET /api/v1/cart/checkout/preview` (authenticated)
- `GET|POST|PUT|PATCH|DELETE /api/v1/addresses` (authenticated; show excluded)
- `GET|POST /api/v1/wishlist` (authenticated)
- `DELETE /api/v1/wishlist/{product}` (authenticated)

## Design decisions
- Guests can browse and build a cart without an account.
- Authentication is required at checkout.
- The client retains the guest cart token; the database retains only its hash.
- The backend is authoritative for stock and prices.
- Payment provider integration is intentionally not implemented yet; provider selection remains a project decision.

## Verification
- PHP syntax validation passed for application, migrations, routes and tests touched by this milestone.
- Full Laravel test execution is pending on the developer machine because this workspace does not contain Composer's `vendor/` directory.


## Next milestone: Orders, inventory reservations, and payment abstraction — 2026-09-09

Implemented:
- Wear order creation through `POST /api/v1/checkout`.
- Checkout requires an authenticated Kipanya account and a user-owned address.
- `Idempotency-Key` is required for checkout requests and is persisted on `wear_orders`.
- Order item prices are snapshotted when the order is created.
- Inventory is reserved transactionally for 15 minutes instead of being immediately deducted.
- Reserved stock is calculated from active, non-expired reservations while the variant row is locked.
- Order status history is recorded from creation and on cancellation/payment confirmation.
- Pending orders can be cancelled and release their active reservation.
- Expired reservations are handled by `kipanya:expire-stock-reservations`.
- Payment transactions are separate from order status.
- Payment provider access is isolated behind `PaymentGateway`.
- `FakePaymentGateway` is bound for development/testing; no real provider is hard-wired.
- Successful payment fulfillment is idempotent: an already-paid transaction returns without double-decrementing stock.
- Successful payment fulfills the reservation, decrements stock atomically, marks the payment paid, and confirms the order.
- Order listing/detail APIs are scoped to the authenticated owner unless the user has `commerce.manage`.

## Current Wear commerce API
- `GET /api/v1/cart`
- `POST /api/v1/cart/items`
- `PUT /api/v1/cart/items/{variant}`
- `DELETE /api/v1/cart/items/{variant}`
- `DELETE /api/v1/cart`
- `POST /api/v1/cart/merge` (authenticated)
- `GET /api/v1/cart/checkout/preview` (authenticated)
- `POST /api/v1/checkout` (authenticated; requires `Idempotency-Key`)
- `GET /api/v1/orders` (authenticated; own orders)
- `GET /api/v1/orders/{order_number}` (authenticated; owner or commerce permission)
- `POST /api/v1/orders/{order_number}/cancel` (authenticated; pending-payment orders)
- `GET|POST|PUT|PATCH|DELETE /api/v1/addresses` (authenticated; show excluded)
- `GET|POST /api/v1/wishlist` (authenticated)
- `DELETE /api/v1/wishlist/{product}` (authenticated)

## Payment decision
- Real Selcom/payment-provider integration is intentionally deferred.
- The backend is provider-agnostic and uses an adapter boundary so Selcom can be added without rewriting checkout/order logic.

## Verification target
The developer machine should run:
`php artisan migrate`
`php artisan route:list --path=api`
`php artisan test`

The expected regression goal for this milestone is zero failing tests before moving to real payment integration.
