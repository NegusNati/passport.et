# Cloudflare SSL/TLS Setup Guide

This document explains how to configure SSL/TLS for the API using Cloudflare Origin Certificates.

## Why Cloudflare Origin Certificates?

The application was migrated from Traefik + Let's Encrypt to Cloudflare Origin Certificates because:

1. **Cloudflare Proxy Compatibility**: The domain `api.passport.et` is proxied through Cloudflare (orange cloud), which blocks Let's Encrypt HTTP-01 challenge validation
2. **Simplicity**: No ACME challenges, no rate limits, no renewal automation needed
3. **Security**: 15-year certificates that encrypt traffic between Cloudflare edge and your origin server
4. **Cost**: Free with any Cloudflare plan

## Architecture

```
Browser → [HTTPS] → Cloudflare Edge → [HTTPS] → Origin Server (Nginx)
          (Public Cert)              (Origin Cert)
```

- **Cloudflare handles public-facing TLS** with automatically managed certificates
- **Origin certificates secure the connection** between Cloudflare and your server
- **Nginx terminates TLS** directly (no Traefik reverse proxy needed)

## Prerequisites

- Domain `passport.et` added to Cloudflare account
- DNS record for `api.passport.et` pointing to your server IP with proxy enabled (orange cloud)
- Access to Cloudflare dashboard

## Step-by-Step Setup

### 1. Generate Origin Certificate in Cloudflare

1. Go to [Cloudflare Dashboard](https://dash.cloudflare.com)
2. Select domain: **passport.et**
3. Navigate to **SSL/TLS** → **Origin Server**
4. Click **"Create Certificate"**
5. Configure:
   - **Private key type**: RSA (2048)
   - **Hostnames**: `api.passport.et` and `*.passport.et`
   - **Validity**: 15 years
6. Click **"Create"**

### 2. Save Certificate Files

Cloudflare will display two text blocks:

**Origin Certificate:**
```
-----BEGIN CERTIFICATE-----
[long base64 string]
-----END CERTIFICATE-----
```
→ Copy and save to `certificates/cloudflare-origin.pem`

**Private Key:**
```
-----BEGIN PRIVATE KEY-----
[long base64 string]
-----END PRIVATE KEY-----
```
→ Copy and save to `certificates/cloudflare-origin.key`

⚠️ **CRITICAL**: The private key is only shown once! Save it immediately.

### 3. Upload to Production Server

```bash
# On your local machine
scp certificates/cloudflare-origin.pem negus@PassportET:~/laraveldocker.prod/certificates/
scp certificates/cloudflare-origin.key negus@PassportET:~/laraveldocker.prod/certificates/

# On production server
cd ~/laraveldocker.prod
chmod 644 certificates/cloudflare-origin.pem
chmod 600 certificates/cloudflare-origin.key
```

### 4. Configure Cloudflare SSL Mode

In Cloudflare Dashboard:

1. Go to **SSL/TLS** → **Overview**
2. Set encryption mode to: **Full (strict)**

**Why "Full (strict)"?**
- **Flexible**: ❌ No encryption to origin (insecure)
- **Full**: ⚠️ Encrypts to origin but doesn't validate certificate
- **Full (strict)**: ✅ Encrypts and validates origin certificate (recommended)

### 5. Deploy Changes

```bash
# Stop current services
docker compose -f docker-compose.prod.yml down

# Remove old Traefik containers/volumes
docker compose -f docker-compose.prod.yml rm -f traefik

# Rebuild with new nginx config
docker compose -f docker-compose.prod.yml up -d --build app

# Start remaining services
docker compose -f docker-compose.prod.yml up -d
```

### 6. Verify Setup

**Check container status:**
```bash
docker compose ps app
```

Expected output:
```
NAME                    IMAGE           PORTS
laraveldockerprod-app-1  nginx-custom   0.0.0.0:80->80/tcp, 0.0.0.0:443->443/tcp
```

**Check nginx configuration:**
```bash
docker compose exec app nginx -t
```

**Check logs:**
```bash
docker compose logs app --tail 50
```

**Test HTTPS:**
```bash
curl -I https://api.passport.et
```

Expected: HTTP/2 200 response

## Troubleshooting

### Error: "No such file or directory" (SSL cert files)

**Cause**: Certificate files missing or incorrect path

**Solution**:
```bash
ls -la certificates/
# Should show cloudflare-origin.pem and cloudflare-origin.key

# If missing, check file names match exactly
mv certificates/*.pem certificates/cloudflare-origin.pem
mv certificates/*.key certificates/cloudflare-origin.key
```

### Error: "PEM_read_bio:bad end line"

**Cause**: Certificate file has incorrect line endings or extra whitespace

**Solution**:
- Re-copy certificate from Cloudflare dashboard
- Ensure no extra spaces at beginning/end
- Use plain text editor (not Word or rich text editors)

### Cloudflare shows "Error 525: SSL handshake failed"

**Cause**: Origin certificate/key mismatch or nginx not listening on 443

**Solution**:
```bash
# Check nginx is running
docker compose ps app

# Check certificate files are readable
docker compose exec app ls -la /etc/nginx/ssl/

# Check nginx can read certificates
docker compose exec app cat /etc/nginx/ssl/cloudflare-origin.pem | head -n 1
# Should show: -----BEGIN CERTIFICATE-----

# Restart nginx
docker compose restart app
```

### Cloudflare shows "Error 522: Connection timed out"

**Cause**: Server firewall blocking port 443 or nginx not binding to 443

**Solution**:
```bash
# Check nginx is listening on 443
docker compose exec app netstat -tlnp | grep 443

# Check port 443 is exposed from container
docker compose port app 443

# On host machine, check firewall
sudo ufw status
sudo ufw allow 443/tcp  # If firewall is active
```

### HTTP redirects to HTTPS cause redirect loops

**Cause**: Cloudflare SSL mode set to "Flexible"

**Solution**:
- Change Cloudflare SSL/TLS mode to "Full (strict)"
- Wait 1-2 minutes for propagation

### Browser shows "Certificate Not Trusted" warning

**Cause**: Testing origin certificate directly (bypassing Cloudflare)

**Context**:
- Origin certificates are NOT trusted by browsers
- They only work when traffic comes through Cloudflare
- Direct IP access will show certificate warnings

**If testing production:**
- Always access via domain name (api.passport.et)
- Ensure DNS is proxied through Cloudflare (orange cloud)

## Removed Components

The following are no longer needed and have been removed:

- **Traefik service** - Reverse proxy removed
- **traefik/traefik.yml** - Static configuration removed
- **letsencrypt/** directory - ACME certificates removed
- **Traefik labels** on app service - Docker labels removed

## Migration Notes

**What changed:**
- Nginx now binds directly to ports 80/443 (previously behind Traefik)
- SSL termination moved from Traefik to Nginx
- Certificate management moved from ACME to Cloudflare

**What stayed the same:**
- Internal service communication unchanged
- Laravel application code unchanged
- Database/Redis connections unchanged
- Environment variables unchanged

## Security Considerations

✅ **Do:**
- Keep certificate files out of version control (already in `.gitignore`)
- Use `chmod 600` on private key file
- Set Cloudflare to "Full (strict)" mode
- Regenerate certificates if compromised

❌ **Don't:**
- Commit certificate files to git
- Share private key files
- Use "Flexible" SSL mode in production
- Access origin server directly (always use Cloudflare proxy)

## Certificate Renewal

Origin certificates are valid for **15 years** and do not auto-renew. Set a reminder to regenerate in 2039.

To renew:
1. Generate new certificate in Cloudflare dashboard
2. Replace files in `certificates/` directory
3. Restart nginx: `docker compose restart app`

No downtime required if you prepare new certificates before old ones expire.

## Additional Resources

- [Cloudflare Origin CA documentation](https://developers.cloudflare.com/ssl/origin-configuration/origin-ca)
- [Cloudflare SSL/TLS encryption modes](https://developers.cloudflare.com/ssl/origin-configuration/ssl-modes)
- [Nginx SSL configuration](https://nginx.org/en/docs/http/configuring_https_servers.html)
