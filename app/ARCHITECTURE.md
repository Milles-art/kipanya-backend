# Kipanya Backend Architecture

## Source of truth

The Kipanya App Master System Blueprint is the product and architecture authority. The backend provides a stable API for the web client and future Android/iOS clients while the admin API uses the same application foundation.

## Layering

`HTTP Request -> Form Request -> Controller -> Action/Service -> Model/Database -> API Resource -> JSON`

Cross-cutting concerns are isolated into policies, middleware, integrations, jobs, events/listeners and support utilities.

## Directory responsibilities

- `Actions/`: one application operation/use case with a clear boundary.
- `DTOs/`: typed data passed between application layers.
- `Enums/`: stable domain states and finite values.
- `Http/Controllers/Api/V1/`: transport-only HTTP controllers.
- `Http/Requests/Api/V1/`: input validation and request authorization.
- `Http/Resources/Api/V1/`: explicit public API response contracts.
- `Models/`: Eloquent domain/data models grouped by product area.
- `Policies/`: resource-level authorization rules.
- `Services/`: reusable domain/application services grouped by concern.
- `Integrations/`: replaceable adapters for external providers.
- `Jobs/`: asynchronous work safe to retry.
- `Events/`: business events; `Listeners/` react without coupling callers.
- `Support/`: cross-cutting primitives such as API responses, audit logging and phone normalization.

## Dependency direction

Controllers should not contain substantial business rules. Domain work belongs in Actions/Services and persistence belongs in Models. External providers are called through Integration adapters. Client input never becomes authoritative for price, stock, payment status, permissions or points.

## Frontend boundary

The public frontend is intentionally outside this Laravel repository. The API is the contract consumed by the frontend, mobile clients and future integrations. Blade/public web routes are not part of this backend baseline.
