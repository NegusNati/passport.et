# API Rollout & Fallback Checklist

## Pre-deployment
- [ ] **SSL/TLS Setup**: Ensure Cloudflare Origin certificates are installed (see `docs/cloudflare-ssl-setup.md`)
  - Verify `certificates/cloudflare-origin.pem` and `certificates/cloudflare-origin.key` exist
  - Confirm Cloudflare SSL mode is set to "Full (strict)"
- [ ] Confirm `.env` values for Redis, Horizon, Sanctum, and Horizon queue vars.
- [ ] Ensure GitHub Actions CI (ci.yml) completes successfully.
- [ ] Execute k6 smoke test: `BASE_URL=<staging> k6 run tests/Performance/PassportLoadTest.js`.
- [ ] Verify Horizon dashboard (https://<domain>/horizon) works for admin.
- [ ] Notify stakeholders and set maintenance window if required.

## Deployment Steps
1. `git pull` latest main branch.
2. `docker compose build app php artisan horizon`.
3. `docker compose up -d --remove-orphans`.
4. Run migrations: `docker compose exec php php artisan migrate --force`.
5. Warm caches: `docker compose exec php php artisan config:cache && php artisan route:cache`.

## Post-deployment smoke checks
- [ ] `docker compose exec php php artisan horizon:status` → running.
- [ ] `docker compose exec php php artisan redis:ping` → PONG.
- [ ] Hit `/api/v1/passports` and `/api/v1/locations` to confirm 200 responses.
- [ ] Verify React client screens (search, table, location) against API data.

## Fallback Plan
- [ ] If deployment fails, roll back to previous commit/tag and rerun compose stack.
- [ ] Stop Horizon to pause jobs: `docker compose stop horizon`.
- [ ] Temporarily point frontend to legacy Inertia endpoints if API outage persists.
- [ ] Communicate outage via Pulse alerts / Slack notifications.
