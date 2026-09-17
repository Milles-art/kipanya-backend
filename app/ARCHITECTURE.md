# KP Wear Backend Architecture

## Source of truth

This repository is the KP Wear application baseline. The Laravel backend and Blade storefront share the same application and API contracts.

## Layering

`HTTP Request -> Form Request -> Controller -> Action/Service -> Model/Database -> API Resource -> JSON`

Cross-cutting concerns are isolated into policies, middleware, integrations, jobs, events/listeners and support utilities.

## Product boundary

The production runtime contains only KP Wear commerce and its required shared foundation:

- Identity/authentication: users, OTP, Sanctum, profiles and notification preferences.
- Administration: roles, permissions, audit logs and store/storefront settings.
- Commerce: catalog, variants, collections, cart, wishlist, addresses, checkout, orders, inventory, stock reservations, returns and payment abstractions.
- Contact/support: customer enquiries and related administration.
- Storefront: Blade pages and the shared store JavaScript client.

Cartoon, Books, Motors and TV are not part of the KP Wear runtime. Their previous source has been removed from this repository and is no longer autoloaded or route-loaded.

## Dependency direction

Controllers should remain transport-focused. Business rules belong in Actions/Services and persistence belongs in Models. External providers are accessed through replaceable integration adapters. Client input is never authoritative for price, stock, payment status or permissions.

## Production principle

Keep the existing KP Wear architecture intact while removing unrelated application boundaries. Real SMS and payment providers remain integration concerns and are added only after the core commerce flow is verified.
