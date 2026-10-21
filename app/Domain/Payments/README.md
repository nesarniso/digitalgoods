# Payments module

## Current status

`PaymentTransaction`, `PaymentService`, gateway contracts, COD, and bank
transfer gateways are implemented. Admins can list transactions, manually
confirm pending manual payments, and issue full or partial refunds from the
web panel. Payment actions are audited.

Enabled config keys are `cash_on_delivery` and `bank_transfer` in
`config/payments.php`. Checkout must use those keys; `cod` is invalid.
Stripe, SSLCommerz, bKash, and Nagad are intentionally disabled until real
gateway adapters and webhook verification are implemented.

## Still to build

- Real gateway adapters with signature verification and idempotent webhooks.
- Customer-facing payment receipts and payment failure recovery.
- Refund request/approval workflow linked to returns/RMA.
- Production reconciliation, retry handling, and gateway health monitoring.

Reuse `PaymentService` rather than mutating transaction status directly. Add
focused tests under `tests/Feature/Payments/`.
