# Dev stack with Docker

This brings up Caddy + PHP-FPM 8.3 + MariaDB + phpMyAdmin for this repo.

## Prereqs
- Docker + Docker Compose

## Start
```bash
# from repo root
docker compose up -d --build
```

- App: http://localhost:8080
- phpMyAdmin: http://localhost:8081 (host `db`, user `crm`, pass `crm`)

## CRM database config
Point Rukovoditel at the bundled DB:
- Host: `db`
- Database: `crm`
- User: `crm`
- Password: `crm`

Edit `crm/config/database.php` accordingly.

## Twilio webhooks (dev)
Expose Caddy via a tunnel, then set Twilio to call your public URL.
```bash
# Example with ngrok
ngrok http http://localhost:8080
```
Set Twilio Voice webhook to:
- `https://YOUR_PUBLIC_URL/voice/incoming.php`
- Recording callback: `https://YOUR_PUBLIC_URL/voice/recording_callback.php`

## Notes
- This stack mounts your working directory at `/var/www/html`.
- The docker Caddyfile (`docker/caddy/Caddyfile`) is used in containers and supports PHP.
- Local `Caddyfile` is left unchanged for non-docker usage.

## Troubleshooting
- If CRM writes fail (HTTP 500/timeout), verify `api/.env.local.php` and align field mappings with your CRM entity. Logs:
  - `voice/voice.log`
  - `api/quote_intake.log`
