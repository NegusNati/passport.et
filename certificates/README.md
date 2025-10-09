# Cloudflare Origin Certificates

This directory contains SSL/TLS certificates for securing communication between Cloudflare and your origin server (this application).

## Quick Setup Guide

### 1. Generate Cloudflare Origin Certificate

1. Log in to your [Cloudflare Dashboard](https://dash.cloudflare.com)
2. Select your domain: **passport.et**
3. Navigate to **SSL/TLS** → **Origin Server**
4. Click **"Create Certificate"**

### 2. Certificate Configuration

Use these settings when creating the certificate:

- **Private key type**: RSA (2048)
- **Certificate validity**: 15 years (default)
- **Hostnames**: Add the following:
  - `api.passport.et`
  - `*.passport.et` (optional, for other subdomains)

### 3. Download and Save

After clicking "Create", Cloudflare will display two text blocks:

1. **Origin Certificate** → Save as `cloudflare-origin.pem`
2. **Private Key** → Save as `cloudflare-origin.key`

**IMPORTANT**: You can only view the private key **once**. Save it immediately!

### 4. Save Files to This Directory

Place both files in this `certificates/` directory:

```
certificates/
├── README.md (this file)
├── cloudflare-origin.pem
└── cloudflare-origin.key
```

### 5. Set Correct Permissions

```bash
chmod 644 cloudflare-origin.pem
chmod 600 cloudflare-origin.key
```

### 6. Configure Cloudflare SSL Mode

In Cloudflare Dashboard → **SSL/TLS** → **Overview**:

- Set SSL/TLS encryption mode to: **Full (strict)**

This ensures Cloudflare validates your origin certificate.

### 7. Restart Docker Services

```bash
docker compose -f docker-compose.prod.yml down
docker compose -f docker-compose.prod.yml up -d --build
```

## Verification

1. Check nginx is listening on both ports:
   ```bash
   docker compose ps app
   ```

2. Test HTTPS locally:
   ```bash
   curl -I https://api.passport.et
   ```

3. Check nginx logs for any SSL errors:
   ```bash
   docker compose logs app
   ```

## Security Notes

- **Never commit certificate files to git** - they are already in `.gitignore`
- Origin certificates are only trusted by Cloudflare (not browsers directly)
- They provide encryption between Cloudflare and your server
- Cloudflare handles the public-facing certificate for browsers
- Certificates are valid for 15 years (no renewal needed)

## Troubleshooting

### "No such file or directory" error

The nginx container expects these files. If missing, create placeholder files temporarily:

```bash
touch certificates/cloudflare-origin.pem
touch certificates/cloudflare-origin.key
```

Then replace with real certificates from Cloudflare.

### SSL handshake errors

- Verify files are readable: `ls -l certificates/`
- Check Cloudflare SSL mode is set to "Full (strict)"
- Ensure hostnames in certificate match your domain

### 522 Errors from Cloudflare

- Make sure nginx is running: `docker compose ps app`
- Check port 443 is exposed: `netstat -an | grep 443`
- Verify firewall allows inbound traffic on ports 80 and 443
