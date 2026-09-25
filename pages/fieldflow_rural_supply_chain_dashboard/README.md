# FieldFlow — Rural Supply Chain Farmer Dashboard

A responsive front-end prototype for a three-role agricultural supply-chain platform.

## Roles
- Farmer: manage stock, review buyer orders, prepare shipments, generate QR labels and monitor movement.
- Buyer: discover published stock, place orders and confirm delivery.
- Transporter: scan shipment QR labels, update pickup/transit/delivery events and location.

## Current demo
This package focuses on the Farmer Command Center, with interactive prototype views for:
- Overview
- My Stock
- Buyer Orders
- Shipments + QR label preview
- Live Tracking
- Reports
- Add Stock modal
- Responsive mobile sidebar
- Toast notifications

## Run
Open `index.html` in a modern browser. No build step is required.

## Backend integration
The UI is intentionally dependency-free. Connect these actions to PHP/MySQL endpoints:
- GET/POST `/api/stock`
- GET `/api/orders`
- PATCH `/api/orders/{id}`
- POST `/api/shipments`
- GET `/api/shipments/{id}`
- POST `/api/shipments/{id}/tracking`
- POST `/api/shipments/{id}/delivery`

Recommended tables:
`users`, `stock`, `orders`, `shipments`, `shipment_tracking`.

For production, replace demo state changes with server-side authorization, prepared statements, CSRF protection, validation, audit logs and transaction-safe inventory updates.
