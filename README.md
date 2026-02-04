# Extendable Orders & Payments API

Laravel 12 + PHP 8.3 REST API for an extendable orders and payments system. The codebase favors Clean Code/SOLID practices, thin controllers, and a service layer for business logic. Input validation is handled via FormRequest classes.

## Setup Instructions
- Clone the repository.

```bash
git clone https://github.com/Mohamed-Adel-91/extendable-order-payment-api
cd extendable-order-payment-api
```

- Install PHP dependencies.

```bash
composer install
```

- Create and configure `.env`.

```bash
copy .env.example .env
```

- Update required environment variables.

```env
JWT_SECRET=

KASHIER_DEBUG_MODE=true
KASHIER_MERCHANT_ID=
KASHIER_API_KEY=
KASHIER_SECRET=
KASHIER_MODE=test
KASHIER_MERCHANT_REDIRECT=http://127.0.0.1:8000/payment/callback
KASHIER_MERCHANT_WEBHOOK=http://127.0.0.1:8000/payment/webhook
KASHIER_CURRENCY=EGP
KASHIER_LANGUAGE=en
```

- Generate the application key.

```bash
php artisan key:generate
```

- Run migrations and seeders.

```bash
php artisan migrate --seed
```

- Configure JWT.

```bash
php artisan jwt:secret
```

- Run the application.

```bash
php artisan serve
```


## Payment Gateway Extensibility
**Strategy Pattern**
- Gateways implement `app/Interfaces/PaymentGatewayInterface.php` and are resolved at runtime based on `PaymentMethod`.
- The registry is `app/Services/payments/PaymentGatewayManager.php`, which maps enum values to concrete gateway classes.
- The payment workflow is initiated in `app/Services/payments/PaymentService.php`, which delegates to the gateway and stores the returned `payment_url`.

**Key Roles**
- `PaymentMethod` enum: `app/Enums/PaymentMethod.php` defines gateway identifiers (integer values) using BenSampo Enum.
- Gateway contract: `app/Interfaces/PaymentGatewayInterface.php` defines `initiate()` and `verifySignature()`.
- Gateway registry/factory: `app/Services/payments/PaymentGatewayManager.php` resolves the gateway class without controller-level branching.

**How a Payment Is Started**
1. `PaymentController::start()` validates the request and delegates to `PaymentService::startPayment()`.
2. `PaymentService` creates a `Payment` in `PENDING` status and calls the resolved gateway.
3. The gateway builds a `payment_url` (Kashier HPP) and the service stores it on the payment record.

**How Callbacks/Webhooks Are Processed**
- Redirect callback route: `GET /payment/callback` is defined in `routes/web.php`.
- `KashierCallbackController` calls `KashierPaymentCallbackService`, which validates required payload fields, verifies the signature, maps the gateway status to internal status, and updates payment + order in a DB transaction with `lockForUpdate`.

### How to Add a New Payment Gateway
- Add a new integer value to `app/Enums/PaymentMethod.php`.
- Create a new gateway class implementing `app/Interfaces/PaymentGatewayInterface.php`.
- Implement payment initialization and signature verification in the gateway.
- Register the gateway in `app/Services/payments/PaymentGatewayManager.php`.
- Add gateway configuration keys to `config/payments.php` and `.env`.

For payment initiation, adding a new gateway requires only a new class + enum value + registry entry; no controller or service changes are needed.

For callbacks, create a gateway-specific callback service and route (see `KashierPaymentCallbackService` and `KashierCallbackController` as the reference implementation).

## Callback & Payment Flow Overview
- **Redirect callback vs webhook**: The project currently implements a redirect callback in `routes/web.php`. Webhook handling can be added with a dedicated controller/service pair following the same pattern.
- **Signature verification**: `KashierPaymentCallbackService` verifies the signature using HMAC SHA-256 with the configured Kashier API key.
- **Status mapping**: Gateway status strings are mapped to internal `PaymentStatus` values, and successful payments mark the order as `PAID`.
- **Idempotency**: Callbacks update the latest payment by `merchant_order_id` inside a transaction. Repeated callbacks re-apply the same status to the same record.
- **Transactions + row locks**: Order updates are wrapped in `DB::transaction()` and use `lockForUpdate()` to prevent race conditions.

## Additional Notes & Assumptions
- Authentication uses JWT with separate guards and tables for users (`api`) and admins (`admin_api`).
- Users can create and pay for orders without admin confirmation.
- Payments are linked to orders; gateway callbacks update both records.
- Callbacks may be received multiple times and are expected to be safely repeatable.
- The gateway is treated as the source of truth for final payment status.
- Soft deletes are enabled for Users, Admins, and Orders. The Payments table includes `deleted_at`; enable the `SoftDeletes` trait on `Payment` if you want model-level soft deletes.
- Soft-deleted accounts are excluded from authentication by default Eloquent scopes.
- Orders with payments are assumed to remain in the system for auditability.
- Redirect callback is implemented; webhook support can be added using the same service pattern.
