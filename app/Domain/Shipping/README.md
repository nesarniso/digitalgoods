# Shipping module

## Current status

Internal shipping configuration is implemented. `ShippingMethod`,
`ShippingZone`, `ShippingRate`, `ShippingService`, and
`ShippingConfigService` support active methods, catch-all rates, zone-specific
rates, flat/free/weight-based/price-based pricing, and free-above thresholds.
The admin web UI is under `/admin/shipping`.

`ShippingSeeder` creates local `Standard delivery` and `Free delivery` options.
Checkout reads active methods from the database and calculates the authoritative
amount server-side.

## Current status

- Shipment/fulfilment records linked to orders are in place.
- Courier interface + manual/mock adapter are in place, and the architecture is
  ready for swap-in real adapters.
- Pathao, Steadfast, and RedX courier adapter classes exist as stubbed
  implementations behind config flags.
- Courier webhook verification exists and rejects invalid signatures while
  ignoring duplicate event IDs so retries do not double-apply state.

## Still to build

- Production API integration for Pathao/Steadfast/RedX with real credentials.
- Return-to-sender and failed-delivery workflow.
- Full admin/customer tracking UI tied to live courier payloads.

Use `ShippingConfigService` for configuration mutations and add focused tests
under `tests/Feature/Shipping/`.
