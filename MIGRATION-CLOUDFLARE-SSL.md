# Migration to Cloudflare Origin Certificates

**Date**: 2025-01-09  
**Status**: ✅ Complete (Awaiting Certificate Installation)

## Summary

Migrated from Traefik + Let's Encrypt to Nginx + Cloudflare Origin Certificates to resolve SSL/TLS certificate issuance failures caused by Cloudflare proxy interference with ACME HTTP-01 challenges.

## What Changed

### Removed Components ❌

1. **Traefik reverse proxy service** (`docker-compose.prod.yml`)
   - Removed entire `traefik` service definition
   - Removed Traefik labels from `app` service
   - Removed `traefik` from `app` depends_on

2. **Let's Encrypt ACME configuration**
   - `traefik/traefik.yml` (no longer needed)
   - `letsencrypt/acme.json` (can be deleted)
   - All ACME challenge configuration

### Added Components ✅

1. **Direct TLS termination in Nginx**
   - New config: `dockerfiles/nginx/ssl.conf`
   - Listens on ports 80 (redirect) and 443 (TLS)
   - Configures modern TLS 1.2/1.3 with secure ciphers

2. **Certificate storage structure**
   - New directory: `certificates/`
   - Contains: `cloudflare-origin.pem` and `cloudflare-origin.key`
   - Mounted read-only into nginx container at `/etc/nginx/ssl/`

3. **Documentation**
   - `certificates/README.md` - Certificate setup instructions
   - `docs/cloudflare-ssl-setup.md` - Complete migration guide
   - Updated `docs/rollout.md` with SSL/TLS checklist

### Modified Components 🔄

1. **docker-compose.prod.yml**
   ```diff
   - # ports: commented out
   + ports:
   +   - "80:80"
   +   - "443:443"
   
   + volumes:
   +   - ./certificates:/etc/nginx/ssl:ro
   
   - depends_on:
   -   - traefik
   + depends_on:  # traefik removed
   ```

2. **dockerfiles/nginx.dockerfile**
   ```diff
   + ADD ./nginx/ssl.conf /etc/nginx/conf.d/
   + RUN mkdir -p /etc/nginx/ssl
   ```

## File Structure

```
passport.et/
├── certificates/                      # NEW
│   ├── .gitignore                    # Ignores *.pem, *.key
│   ├── README.md                     # Setup instructions
│   ├── cloudflare-origin.pem         # ⚠️ NEEDS REAL CERTIFICATE
│   └── cloudflare-origin.key         # ⚠️ NEEDS REAL CERTIFICATE
├── dockerfiles/
│   ├── nginx/
│   │   ├── default.conf              # Unchanged (HTTP only)
│   │   └── ssl.conf                  # NEW (HTTPS config)
│   └── nginx.dockerfile              # Modified (adds ssl.conf)
├── docker-compose.prod.yml            # Modified (removed traefik)
├── docs/
│   ├── cloudflare-ssl-setup.md       # NEW (full guide)
│   └── rollout.md                    # Updated (added SSL checklist)
└── traefik/                          # ⚠️ CAN BE DELETED
    └── traefik.yml                   # No longer used
```

## Why This Change?

### Problem

Traefik's Let's Encrypt integration was failing because:

1. `api.passport.et` is proxied through Cloudflare (orange cloud)
2. Cloudflare intercepts HTTP-01 ACME challenges before they reach origin
3. Let's Encrypt validation fails → no certificates issued
4. Traefik container runs but produces no logs (silent failure)
5. Cloudflare returns 522 errors (connection timeout) to clients

### Solution Benefits

✅ **Compatibility**: Origin certificates designed for Cloudflare-proxied domains  
✅ **Simplicity**: No ACME challenges, no rate limits, no renewal automation  
✅ **Security**: 15-year certificates with strong encryption (RSA 2048/TLS 1.3)  
✅ **Performance**: One fewer container/proxy in the stack  
✅ **Reliability**: No dependency on external ACME endpoints

## Next Steps (REQUIRED)

### 1. Generate Cloudflare Origin Certificate

**In Cloudflare Dashboard:**
1. Go to: [https://dash.cloudflare.com](https://dash.cloudflare.com)
2. Select domain: `passport.et`
3. Navigate: **SSL/TLS** → **Origin Server**
4. Click: **"Create Certificate"**
5. Configure:
   - Hostnames: `api.passport.et`, `*.passport.et`
   - Private key: RSA (2048)
   - Validity: 15 years
6. Click **"Create"**

### 2. Save Certificate Files

Copy the displayed certificate and key into these files:

**On local machine:**
```bash
# Save "Origin Certificate" content to:
nano certificates/cloudflare-origin.pem

# Save "Private Key" content to:
nano certificates/cloudflare-origin.key

# Set correct permissions
chmod 644 certificates/cloudflare-origin.pem
chmod 600 certificates/cloudflare-origin.key
```

⚠️ **CRITICAL**: Private key is only shown once! Save immediately.

### 3. Upload to Production Server

```bash
# From local machine to production server
scp certificates/cloudflare-origin.pem negus@PassportET:~/laraveldocker.prod/certificates/
scp certificates/cloudflare-origin.key negus@PassportET:~/laraveldocker.prod/certificates/

# On production server, verify upload
ssh negus@PassportET
cd ~/laraveldocker.prod
ls -la certificates/
# Should show both .pem and .key files with correct permissions
```

### 4. Configure Cloudflare SSL Mode

**In Cloudflare Dashboard:**
1. Go to: **SSL/TLS** → **Overview**
2. Change mode from "Flexible" to: **Full (strict)**
3. Wait 1-2 minutes for propagation

### 5. Deploy on Production

```bash
# Stop Traefik (now obsolete)
docker compose -f docker-compose.prod.yml stop traefik
docker compose -f docker-compose.prod.yml rm -f traefik

# Rebuild nginx with new SSL config
docker compose -f docker-compose.prod.yml build app

# Start all services
docker compose -f docker-compose.prod.yml up -d
```

### 6. Verify Deployment

```bash
# Check nginx is running on both ports
docker compose -f docker-compose.prod.yml ps app
# Expected: 0.0.0.0:80->80/tcp, 0.0.0.0:443->443/tcp

# Check nginx configuration syntax
docker compose -f docker-compose.prod.yml exec app nginx -t
# Expected: configuration file test is successful

# Test HTTPS from external
curl -I https://api.passport.et
# Expected: HTTP/2 200

# Check nginx logs
docker compose -f docker-compose.prod.yml logs app --tail 50
# Should show TLS handshake logs
```

## Rollback Plan

If issues occur after deployment:

### Quick Rollback (Restore Traefik)

```bash
# 1. Checkout previous commit
git checkout <previous-commit-hash>

# 2. Rebuild and restart
docker compose -f docker-compose.prod.yml down
docker compose -f docker-compose.prod.yml up -d --build

# 3. Wait for Let's Encrypt to retry (may take 5-10 minutes)
docker compose -f docker-compose.prod.yml logs traefik -f
```

### Alternative: Use DNS-01 Challenge with Traefik

If you prefer to keep Traefik, you can configure DNS-01 challenge:

```yaml
# traefik.yml
certificatesResolvers:
  myresolver:
    acme:
      email: natnaelbirhanu22@gmail.com
      storage: /letsencrypt/acme.json
      dnsChallenge:
        provider: cloudflare
        resolvers:
          - "1.1.1.1:53"
          - "8.8.8.8:53"

# docker-compose.prod.yml
environment:
  - CF_API_EMAIL=your-email@example.com
  - CF_DNS_API_TOKEN=your-token-here
```

But this is more complex than using origin certificates.

## Testing Checklist

After deployment, verify:

- [ ] Nginx container status: `docker compose ps app` shows "Up"
- [ ] Port bindings: 80 and 443 exposed to host
- [ ] Certificate files mounted: `docker compose exec app ls -la /etc/nginx/ssl/`
- [ ] Nginx config valid: `docker compose exec app nginx -t`
- [ ] HTTP redirects to HTTPS: `curl -I http://api.passport.et`
- [ ] HTTPS responds: `curl -I https://api.passport.et`
- [ ] No browser certificate warnings (when accessed via domain)
- [ ] API endpoints working: `curl https://api.passport.et/api/v1/locations`
- [ ] Laravel application logs show no errors
- [ ] Horizon dashboard accessible (if admin)

## Troubleshooting

### Issue: "No such file or directory" on startup

**Cause**: Certificate files missing  
**Fix**:
```bash
# Verify files exist
ls -la certificates/
# If missing, follow step 2 above to create them
```

### Issue: Cloudflare Error 525 (SSL handshake failed)

**Cause**: Certificate/key mismatch or incorrect SSL mode  
**Fix**:
1. Verify Cloudflare SSL mode is "Full (strict)"
2. Regenerate origin certificate and replace both files
3. Restart nginx: `docker compose restart app`

### Issue: Cloudflare Error 522 (Connection timed out)

**Cause**: Nginx not listening on 443 or firewall blocking  
**Fix**:
```bash
# Check nginx is running
docker compose ps app

# Check firewall (on host)
sudo ufw status
sudo ufw allow 443/tcp

# Check nginx error logs
docker compose logs app
```

### Issue: Browser shows certificate warning

**Cause**: Accessing origin server directly (bypassing Cloudflare)  
**Context**: Origin certificates are NOT trusted by browsers  
**Fix**: Always access via domain name through Cloudflare proxy

## Security Notes

🔒 **Certificate files are sensitive** - they are excluded from git via `.gitignore`  
🔒 **Never commit** `.pem` or `.key` files to version control  
🔒 **Origin certificates** only work with Cloudflare proxy (not trusted by browsers directly)  
🔒 **Private key** should be `chmod 600` (readable only by owner)

## References

- [Cloudflare Origin CA Documentation](https://developers.cloudflare.com/ssl/origin-configuration/origin-ca)
- [Cloudflare SSL/TLS Encryption Modes](https://developers.cloudflare.com/ssl/origin-configuration/ssl-modes)
- Full setup guide: `docs/cloudflare-ssl-setup.md`
- Certificate instructions: `certificates/README.md`
