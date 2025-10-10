# Production Deployment Instructions

**Date**: 2025-10-09  
**Issue**: Route caching error + Missing SSL certificates

## Summary of Changes

Two issues resolved:

1. ✅ **Route naming conflict fixed** - Duplicate `passport.show` route names renamed
2. ✅ **Cloudflare SSL setup completed** - Nginx configured for origin certificates

---

## Step 1: Push Code Changes to Git

**On your local machine:**

```bash
cd /Users/negusnati/Documents/dev/personal/passport.et

# Check commits are ready
git log --oneline -3
# Should show:
# - Fix route caching error
# - Migrate from Traefik to Cloudflare Origin Certificates

# Push to remote
git push origin main
```

---

## Step 2: Deploy to Production Server

**SSH into production:**

```bash
ssh negus@PassportET
cd ~/laraveldocker.prod
```

### 2.1: Pull Latest Code

```bash
# Pull updates from git
git pull origin main

# Verify changes
git log --oneline -3
# Should show the same commits as local

# Check docker-compose file was updated
cat docker-compose.prod.yml | grep "traefik:"
# Should return NOTHING (traefik removed)

cat docker-compose.prod.yml | grep "certificates:/etc/nginx/ssl"
# Should show: - ./certificates:/etc/nginx/ssl:ro
```

### 2.2: Verify Certificate Files Exist

```bash
ls -la certificates/
# Should show:
# -rw-r--r-- cloudflare-origin.pem (1671 bytes)
# -rw------- cloudflare-origin.key (1704 bytes)

# If files are empty or missing, you need to:
# 1. Generate Cloudflare Origin Certificate in dashboard
# 2. Copy certificate content to these files
# 3. Set permissions: chmod 644 *.pem && chmod 600 *.key
```

### 2.3: Stop Old Services

```bash
# Stop everything
docker compose down

# Remove Traefik completely (if it still exists)
docker compose rm -f traefik 2>/dev/null || true

# Clean up unused containers
docker system prune -f
```

### 2.4: Rebuild and Start Services

```bash
# Rebuild nginx with new SSL config
docker compose build app

# Start all services
docker compose up -d

# Check all containers are running
docker compose ps
```

---

## Step 3: Verify Deployment

### 3.1: Check Nginx Container

```bash
# Verify app container is running
docker compose ps app
# Should show:
# STATUS: Up
# PORTS: 0.0.0.0:80->80/tcp, 0.0.0.0:443->443/tcp

# Check nginx configuration is valid
docker compose exec app nginx -t
# Should output: configuration file test is successful

# Check logs for SSL errors
docker compose logs app --tail 30
# Should NOT show "cannot load certificate" errors
```

### 3.2: Test Route Caching

```bash
# Clear all caches
docker compose exec php php artisan optimize:clear

# Test route caching (this was failing before)
docker compose exec php php artisan route:cache
# Should succeed without errors

# Verify no duplicate route names
docker compose exec php php artisan route:list --name=passport
# Should show all passport routes with unique names

# Cache config for production
docker compose exec php php artisan config:cache
```

### 3.3: Test HTTPS Access

**From production server:**

```bash
# Test HTTP redirects to HTTPS
curl -I http://localhost
# Should show: HTTP/1.1 301 Moved Permanently
# Location: https://api.passport.et/

# Test HTTPS from public domain
curl -I https://api.passport.et
# Should return: HTTP/2 200
```

**From your browser:**

- Visit: `https://api.passport.et`
- Should load without certificate warnings
- Check SSL certificate: Should show "Cloudflare" issuer (via Cloudflare proxy)

---

## Step 4: Post-Deployment Checks

### 4.1: Test API Endpoints

```bash
# Test passport search API
curl https://api.passport.et/api/v1/locations
# Should return JSON with location list

# Test with filters
curl "https://api.passport.et/api/v1/passports?location=Addis%20Ababa&limit=5"
# Should return JSON with passport results
```

### 4.2: Check Application Services

```bash
# Verify Horizon is running
docker compose exec php php artisan horizon:status
# Should output: running

# Test Redis connection
docker compose exec php php artisan redis:ping
# Should output: PONG

# Check database connection
docker compose exec php php artisan migrate:status
# Should show all migrations
```

### 4.3: Monitor Logs

```bash
# Watch application logs
docker compose logs -f app php horizon

# In separate terminal, make test requests and watch for errors
curl https://api.passport.et/api/v1/locations
```

---

## Troubleshooting

### Issue: "cannot load certificate" errors persist

**Cause**: Certificate files are empty or not mounted correctly

**Fix**:
```bash
# Check file contents
head -n 1 certificates/cloudflare-origin.pem
# Should show: -----BEGIN CERTIFICATE-----

# Check volume mount inside container
docker compose exec app ls -la /etc/nginx/ssl/
# Should show both .pem and .key files

# If missing, restart services
docker compose down && docker compose up -d
```

### Issue: Route cache still fails

**Cause**: Code not updated on production

**Fix**:
```bash
# Verify web.php has the fix
grep "passport.search.show" src/routes/web.php
# Should return the line with the new route name

# If not found, git pull was not successful
git pull origin main --force
```

### Issue: Cloudflare returns 522 errors

**Cause**: Nginx not listening on port 443

**Fix**:
```bash
# Check nginx is running
docker compose ps app
# Should show "Up" status

# Check port 443 is exposed
netstat -tlnp | grep 443
# Should show docker-proxy listening

# Restart nginx
docker compose restart app
```

### Issue: Nginx starts but immediately exits

**Cause**: Configuration syntax error

**Fix**:
```bash
# Test configuration
docker compose exec app nginx -t

# Check logs for specific error
docker compose logs app --tail 50
```

---

## Rollback Plan

If deployment fails completely:

```bash
# On production server
cd ~/laraveldocker.prod

# Revert to previous commit
git log --oneline -5
# Note the commit hash before today's changes

git checkout <previous-commit-hash>

# Rebuild and restart
docker compose down
docker compose build
docker compose up -d
```

---

## Success Criteria

Deployment is successful when:

- ✅ `docker compose ps` shows all services "Up"
- ✅ `php artisan route:cache` completes without errors
- ✅ `curl https://api.passport.et` returns HTTP/2 200
- ✅ No certificate errors in `docker compose logs app`
- ✅ API endpoints return valid JSON
- ✅ Horizon shows "running" status
- ✅ No Cloudflare 522 or 525 errors

---

## Next Steps After Deployment

1. **Update DNS (if needed)**:
   - Ensure `api.passport.et` points to production server IP
   - Cloudflare proxy should be enabled (orange cloud)

2. **Monitor for 24 hours**:
   - Check logs: `docker compose logs -f`
   - Watch for certificate errors
   - Monitor API response times

3. **Clean up old files** (optional):
   ```bash
   # Remove old Traefik/Let's Encrypt artifacts
   rm -rf traefik/ letsencrypt/
   ```

4. **Update Cloudflare SSL mode** (if not done yet):
   - Dashboard → SSL/TLS → Overview
   - Set to: **Full (strict)**

---

## Support

If issues persist after following this guide:

1. Check current container status: `docker compose ps`
2. Collect logs: `docker compose logs app php > deployment-logs.txt`
3. Verify git status: `git log --oneline -5` and `git status`
4. Share error messages for debugging

---

**Last updated**: 2025-10-09
