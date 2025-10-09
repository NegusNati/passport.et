# Advertisement CRM API Documentation

## Overview

The Advertisement CRM API provides comprehensive management of advertisement slots displayed across the frontend. It includes public endpoints for displaying active ads and tracking metrics, as well as admin endpoints for full CRUD operations.

---

## Table of Contents

- [Authentication](#authentication)
- [Rate Limiting](#rate-limiting)
- [Public Endpoints](#public-endpoints)
  - [Get Active Advertisements](#get-active-advertisements)
  - [Track Advertisement Impression](#track-advertisement-impression)
  - [Track Advertisement Click](#track-advertisement-click)
- [Admin Endpoints](#admin-endpoints)
  - [List Advertisements](#list-advertisements)
  - [Get Advertisement Details](#get-advertisement-details)
  - [Create Advertisement](#create-advertisement)
  - [Update Advertisement](#update-advertisement)
  - [Delete Advertisement](#delete-advertisement)
  - [Restore Advertisement](#restore-advertisement)
  - [Get Statistics](#get-statistics)
- [Automation](#automation)
  - [Scheduled Commands](#scheduled-commands)
  - [Telegram Notifications](#telegram-notifications)
- [Data Models](#data-models)

---

## Authentication

Admin endpoints require authentication using Laravel Sanctum tokens and the `manage-advertisements` permission.

**Headers:**
```
Authorization: Bearer {token}
Accept: application/json
```

Public endpoints (active ads, tracking) do not require authentication.

---

## Rate Limiting

- **Anonymous users:** 60 requests/minute
- **Authenticated users:** 120 requests/minute
- **Premium users:** 240 requests/minute

---

## Public Endpoints

### Get Active Advertisements

Fetches all currently active advertisements for frontend display.

**Endpoint:** `GET /api/v1/advertisements/active`

**Query Parameters:**
- `slot_number` (optional) - Filter by specific ad slot

**Response:** `200 OK`

```json
{
  "data": [
    {
      "id": 1,
      "ad_slot_number": "homepage-banner-1",
      "ad_title": "Special Promotion",
      "ad_excerpt": "Limited time offer",
      "ad_desktop_asset": "https://cdn.example.com/ads/desktop-1.jpg",
      "ad_mobile_asset": "https://cdn.example.com/ads/mobile-1.jpg",
      "ad_client_link": "https://client.example.com/promo",
      "priority": 10,
      "impressions_count": 125000,
      "clicks_count": 3500
    }
  ],
  "meta": {
    "count": 1
  }
}
```

**Caching:** 5 minutes, Redis tags: `['ad_crm', 'ad_crm.active']`

---

### Track Advertisement Impression

Records when an advertisement is displayed to a user.

**Endpoint:** `POST /api/v1/advertisements/{id}/impression`

**Body:**
```json
{
  "session_id": "optional-unique-session-id"
}
```

**Response:** `204 No Content`

**Notes:**
- Impressions are queued asynchronously to avoid blocking
- Deduplication: Same session+ad within 10 seconds is ignored
- Rate limit: 240 requests/minute per IP

---

### Track Advertisement Click

Records when a user clicks on an advertisement.

**Endpoint:** `POST /api/v1/advertisements/{id}/click`

**Body:**
```json
{
  "session_id": "optional-unique-session-id"
}
```

**Response:** `204 No Content`

**Notes:**
- Clicks are queued asynchronously
- Deduplication: Same session+ad within 60 seconds is ignored
- Rate limit: 240 requests/minute per IP

---

## Admin Endpoints

All admin endpoints require `Authorization: Bearer {token}` and `can:manage-advertisements` permission.

### List Advertisements

Search and filter all advertisements with pagination.

**Endpoint:** `GET /api/v1/admin/advertisements`

**Query Parameters:**
- `ad_title` (string) - Filter by title (partial match)
- `ad_slot_number` (string) - Filter by slot number
- `client_name` (string) - Filter by client name (partial match)
- `status` (string) - Filter by status: `draft`, `active`, `paused`, `expired`, `scheduled`
- `payment_status` (string) - Filter by payment status: `pending`, `paid`, `refunded`, `failed`
- `package_type` (string) - Filter by package: `weekly`, `monthly`, `yearly`
- `published_after` (date) - Filter ads published on or after this date
- `published_before` (date) - Filter ads published on or before this date
- `ending_after` (date) - Filter ads ending on or after this date
- `ending_before` (date) - Filter ads ending on or before this date
- `sort` (string) - Sort column (default: `created_at`)
- `sort_dir` (string) - Sort direction: `asc` or `desc` (default: `desc`)
- `per_page` (integer) - Results per page (default: 20, max: 100)
- `page` (integer) - Page number

**Response:** `200 OK`

```json
{
  "data": [
    {
      "id": 1,
      "ad_slot_number": "homepage-banner-1",
      "ad_title": "Special Promotion",
      "ad_desc": "Full description...",
      "ad_excerpt": "Short excerpt",
      "ad_desktop_asset": "https://...",
      "ad_mobile_asset": "https://...",
      "ad_client_link": "https://...",
      "status": "active",
      "package_type": "monthly",
      "ad_published_date": "2025-01-01",
      "ad_ending_date": "2025-02-01",
      "payment_status": "paid",
      "payment_amount": "500.00",
      "client_name": "ACME Corp",
      "impressions_count": 125000,
      "clicks_count": 3500,
      "priority": 10,
      "created_at": "2025-01-01T00:00:00Z",
      "updated_at": "2025-01-05T12:30:00Z",
      "admin_notes": "Internal notes...",
      "expiry_notification_sent": false,
      "days_until_expiry": 5,
      "is_active": true,
      "is_expired": false,
      "advertisement_request_id": 42
    }
  ],
  "links": {
    "first": "...",
    "last": "...",
    "prev": null,
    "next": "..."
  },
  "meta": {
    "current_page": 1,
    "from": 1,
    "to": 20,
    "per_page": 20,
    "total": 45,
    "last_page": 3,
    "has_more": true
  },
  "filters": {
    "status": "active"
  }
}
```

---

### Get Advertisement Details

Retrieve a single advertisement by ID.

**Endpoint:** `GET /api/v1/admin/advertisements/{id}`

**Response:** `200 OK`

```json
{
  "data": {
    "id": 1,
    "ad_slot_number": "homepage-banner-1",
    // ... (same structure as list endpoint)
  }
}
```

---

### Create Advertisement

Create a new advertisement.

**Endpoint:** `POST /api/v1/admin/advertisements`

**Headers:**
```
Content-Type: multipart/form-data
```

**Body (multipart form-data):**

| Field | Type | Required | Description |
|-------|------|----------|-------------|
| ad_slot_number | string | Yes | Unique slot identifier (max 50 chars) |
| ad_title | string | Yes | Advertisement title (max 255 chars) |
| ad_desc | text | No | Full description (max 2000 chars) |
| ad_excerpt | text | No | Short excerpt (max 500 chars) |
| ad_desktop_asset | file/url | No | Desktop asset (jpg,png,gif,svg,mp4,webp, max 10MB) |
| ad_mobile_asset | file/url | No | Mobile asset (jpg,png,gif,svg,mp4,webp, max 10MB) |
| ad_client_link | url | No | Client website URL (max 255 chars) |
| client_name | string | No | Client company name (max 255 chars) |
| package_type | enum | Yes | `weekly`, `monthly`, or `yearly` |
| ad_published_date | date | Yes | Publication date (must be today or future) |
| ad_ending_date | date | No | Ending date (must be after publication date) |
| status | enum | Yes | `draft`, `active`, `paused`, `scheduled` |
| payment_status | enum | Yes | `pending`, `paid`, `refunded`, `failed` |
| payment_amount | decimal | Yes | Payment amount (min: 0, max: 999999.99) |
| priority | integer | No | Display priority (0-100, default: 0) |
| admin_notes | text | No | Internal admin notes (max 1000 chars) |
| advertisement_request_id | integer | No | Link to advertisement request |

**Business Rules:**
- If `ad_published_date` is in the future, status is auto-set to `scheduled`
- Cannot set status to `active` if `payment_status` is `pending` (auto-corrected to `scheduled`)
- `ad_slot_number` must be unique (ignores soft-deleted records)

**Response:** `201 Created`

```json
{
  "data": {
    "id": 1,
    "ad_slot_number": "homepage-banner-1",
    // ... (full advertisement object)
  }
}
```

**Validation Errors:** `422 Unprocessable Entity`

```json
{
  "message": "The given data was invalid.",
  "errors": {
    "ad_slot_number": ["The ad slot number has already been taken."],
    "ad_ending_date": ["The ending date must be after the publication date."]
  }
}
```

---

### Update Advertisement

Update an existing advertisement.

**Endpoint:** `PATCH /api/v1/admin/advertisements/{id}`

**Body:** Same as Create endpoint, but all fields are optional

**Additional Business Rules:**
- If status changes to `active` and `ad_published_date` is null, it's set to now
- If `ad_ending_date` changes, `expiry_notification_sent` is reset to `false`
- Cannot set status to `active` if `payment_status` is `pending`
- When uploading new asset files, old files are deleted

**Response:** `200 OK`

---

### Delete Advertisement

Soft delete an advertisement.

**Endpoint:** `DELETE /api/v1/admin/advertisements/{id}`

**Response:** `204 No Content`

**Side Effects:**
- Associated asset files are deleted from storage
- Cache is flushed

---

### Restore Advertisement

Restore a soft-deleted advertisement.

**Endpoint:** `POST /api/v1/admin/advertisements/{id}/restore`

**Response:** `200 OK`

```json
{
  "data": {
    "id": 1,
    // ... (full advertisement object)
  }
}
```

---

### Get Statistics

Retrieve dashboard statistics for advertisements.

**Endpoint:** `GET /api/v1/admin/advertisements/stats`

**Response:** `200 OK`

```json
{
  "data": {
    "total_active": 15,
    "expiring_soon": 3,
    "expired_pending_renewal": 5,
    "total_impressions": 1250000,
    "total_clicks": 35000,
    "avg_ctr": 2.8,
    "revenue_this_month": 12500.00
  }
}
```

**Caching:** 10 minutes, Redis tags: `['ad_crm', 'ad_crm.stats']`

---

## Automation

### Scheduled Commands

The following commands run automatically via Laravel's scheduler:

#### 1. Notify Expiring Advertisements

**Command:** `advertisements:notify-expiring`

**Schedule:** Daily at 09:00 AM

**Purpose:** Send Telegram notifications for ads expiring in 3 days

**Manual Run:**
```bash
php artisan advertisements:notify-expiring
php artisan advertisements:notify-expiring --days=5  # Custom threshold
```

---

#### 2. Auto-Expire Advertisements

**Command:** `advertisements:auto-expire`

**Schedule:** Daily at 00:15 AM

**Purpose:** Automatically mark advertisements as expired when their ending date passes

**Manual Run:**
```bash
php artisan advertisements:auto-expire
```

**Side Effects:**
- Updates `status` to `expired`
- Dispatches `AdvertisementExpired` event
- Flushes cache

---

#### 3. Auto-Activate Advertisements

**Command:** `advertisements:auto-activate`

**Schedule:** Every 5 minutes

**Purpose:** Automatically activate scheduled advertisements when their publish date arrives

**Conditions:**
- Status must be `scheduled`
- `ad_published_date` <= now
- `payment_status` must be `paid`

**Manual Run:**
```bash
php artisan advertisements:auto-activate
```

**Side Effects:**
- Updates `status` to `active`
- Flushes cache

---

### Telegram Notifications

Notifications are sent to the configured Telegram chat when:

1. **Advertisement Expiring (3 days before)**
   - Triggered by `advertisements:notify-expiring` command
   - Message includes: slot, title, client, days left, payment info, performance metrics

2. **Advertisement Request Created**
   - Triggered when a new ad request is submitted via public API

**Configuration:**

Set in `.env`:
```env
TELEGRAM_BOT_TOKEN=your_bot_token
TELEGRAM_CHAT_ID=your_chat_id
```

---

## Data Models

### Advertisement Status Values

- `draft` - Not yet published
- `active` - Currently live on the site
- `paused` - Temporarily disabled by admin
- `expired` - Ending date has passed
- `scheduled` - Will activate on publish date

### Payment Status Values

- `pending` - Awaiting payment
- `paid` - Payment received
- `refunded` - Payment refunded
- `failed` - Payment failed

### Package Types

- `weekly` - 7-day duration
- `monthly` - 30-day duration
- `yearly` - 365-day duration

---

## Performance Considerations

### Caching Strategy

- **Active ads list:** 5-minute TTL, tagged with `['ad_crm', 'ad_crm.active']`
- **Admin search results:** 5-minute TTL, tagged with `['ad_crm', 'ad_crm.search']`
- **Statistics:** 10-minute TTL, tagged with `['ad_crm', 'ad_crm.stats']`
- **Slot-specific caches:** 10-minute TTL

All caches are automatically flushed when advertisements are created, updated, or deleted via the `AdvertisementObserver`.

### Database Indexes

The following indexes optimize query performance:

- Composite: `(status, ad_published_date, ad_ending_date)`
- Single: `ad_slot_number`, `package_type`

### Queue Jobs

The following operations are queued asynchronously:

- `IncrementAdImpressionJob` - Updates impression count
- `IncrementAdClickJob` - Updates click count
- Telegram notifications

**Queue Driver:** Redis (via Horizon)

---

## Error Responses

### 401 Unauthorized

```json
{
  "message": "Unauthenticated."
}
```

### 403 Forbidden

```json
{
  "message": "This action is unauthorized."
}
```

### 404 Not Found

```json
{
  "message": "Advertisement not found."
}
```

### 422 Unprocessable Entity

```json
{
  "message": "The given data was invalid.",
  "errors": {
    "field_name": ["Error message"]
  }
}
```

### 429 Too Many Requests

```json
{
  "status": "error",
  "code": "rate_limit_exceeded",
  "message": "Too many requests. Please slow down and try again shortly."
}
```

---

## Example Workflows

### Creating and Publishing an Advertisement

1. **Create draft advertisement:**
   ```bash
   POST /api/v1/admin/advertisements
   {
     "ad_slot_number": "homepage-banner-1",
     "ad_title": "Summer Sale",
     "package_type": "monthly",
     "ad_published_date": "2025-06-01",
     "ad_ending_date": "2025-07-01",
     "status": "draft",
     "payment_status": "pending",
     "payment_amount": 1000.00
   }
   ```

2. **Update payment status:**
   ```bash
   PATCH /api/v1/admin/advertisements/1
   {
     "payment_status": "paid"
   }
   ```

3. **Schedule or activate:**
   - If `ad_published_date` is future → status auto-set to `scheduled`
   - If `ad_published_date` is today/past → can set status to `active`
   - Scheduled ads auto-activate when publish date arrives

4. **Monitor performance:**
   ```bash
   GET /api/v1/admin/advertisements/1
   # Check impressions_count, clicks_count
   ```

5. **Renewal notification:**
   - 3 days before expiry, Telegram alert sent automatically
   - Admin extends ending date or creates new campaign

---

## Testing

Run the test suite:

```bash
# Feature tests (admin + public APIs)
php artisan test --testsuite=Feature --filter=Advertisement

# Unit tests (model scopes, helpers)
php artisan test --testsuite=Unit --filter=Advertisement

# All tests
php artisan test
```

---

## Support

For issues or questions, contact the development team or submit a ticket.
