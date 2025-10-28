# Mechanic Saint Augustine Website

This is the starting point for the mobile mechanic service website.

- Static HTML site
- Caddy server recommended
- Rukovoditel CRM integration planned
- Call recording and price quote features coming soon

## Local dev quickstart

- Start stack: docker compose up -d --build
- App: http://localhost:8080 • phpMyAdmin: http://localhost:8081 (host db / user crm / pass crm)
- Probe: open / (index), /crm/health.php, /voice/incoming.php – expect 200s.

## Smoke tests (optional)

- POST quote: /quote/quote_intake_handler.php with JSON { name, phone, service, year, make, model, engine, text_opt_in:false } – expect success and estimate.amount.
- Example services: oil change → ~50, alternator with V8 → ~420 (includes V8/age multipliers).
