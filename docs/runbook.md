# Live Site Runbook

## Current Stack
- Caddy at `/etc/caddy/Caddyfile` serves `mechanicstaugustine.com` from `/mnt/sda5/mechanicsaintaugustine.com/site`.
- Cloudflared tunnel `mechanicsain-tunnel` runs via systemd (`sudo systemctl status cloudflared`).
- DNS (`mechanicstaugustine.com`, `www`) is proxied CNAME to `ac25c77a-477c-47ea-ab37-40992c075ab7.cfargotunnel.com`.

## Bring Site Up After Reboot
1. `sudo systemctl restart cloudflared` – tunnel back online.
2. `sudo systemctl restart caddy` (only if config changed).
3. `curl -I https://mechanicstaugustine.com --resolve mechanicstaugustine.com:443:127.0.0.1` – origin check.
4. Confirm Cloudflare redirect rules only match the intended hostnames (see below).
5. `curl -I https://mechanicstaugustine.com` – expect `HTTP/2 200`.

## Cloudflare Redirect Rules
- `Redirect from WWW to root`: condition must be `http.host eq "www.mechanicstaugustine.com"` with target `${1}`. Do **not** apply to all hosts.
- `Redirect to a different domain`: condition must be `http.host eq "ezmobilemechanic.com"` before pointing elsewhere. Remove the `https://` prefix in the filter.

## Quick DNS Check
```
dig mechanicstaugustine.com
```
Should return Cloudflare anycast IPs (104.21.x.x, 172.67.x.x).

## Cloudflared Logs
```
journalctl -u cloudflared -n 50 --no-pager
```
Look for `Registered tunnel connection` and absence of errors.


## CRM Permissions
If Rukovoditel dashboard shows upload folders not writable, run:
```
sudo chown -R www-data:www-data /mnt/sda5/mechanicsaintaugustine.com/site/crm/{uploads,backups,tmp,cache,log}
sudo find /mnt/sda5/mechanicsaintaugustine.com/site/crm/{uploads,backups,tmp,cache,log} -type d -exec chmod 775 {} +
sudo find /mnt/sda5/mechanicsaintaugustine.com/site/crm/{uploads,backups,tmp,cache,log} -type f -exec chmod 664 {} +
```

## Quote Intake Troubleshooting
- Endpoint: `https://mechanicstaugustine.com/quote/quote_intake_handler.php`
- Use `curl -s -w '%{http_code}' ...` to verify 200 + JSON.
- Twilio errors in response (`twilio_http_400`) mean Twilio rejected the SMS; check number or messaging service.
- `crm.body` returning `No match for Username and/or Password` means the CRM username/password/API key provided via environment variables (see `site/api/.env.local.php`) need updating before retrying.

## Voice/Recording Checks
- Recording callback: `curl -I https://mechanicstaugustine.com/voice/recording_callback.php` should return 200.
- Tail log: `tail -f /mnt/sda5/mechanicsaintaugustine.com/site/voice/voice.log` during a test call.
- If no new entries appear, verify Twilio webhooks point to the `mechanicstaugustine.com/voice/...` URLs and that Cloudflare isn't redirecting.
