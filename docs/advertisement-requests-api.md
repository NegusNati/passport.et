# Advertisement Requests API Documentation

## Overview
The Advertisement Requests API allows businesses to submit advertising requests through the passport.et platform. Admins can manage these requests via dedicated admin endpoints.

## Database Schema
- **Table**: `advertisement_requests`
- **Fields**:
  - `id` - Primary key
  - `phone_number` - Contact phone number (required)
  - `email` - Contact email (optional)
  - `full_name` - Full name of contact person (required)
  - `company_name` - Company name (optional)
  - `description` - Advertisement request description (required, min 10 chars)
  - `file_path` - Uploaded file path (optional, max 10MB)
  - `status` - Request status (pending|contacted|rejected|approved, default: pending)
  - `admin_notes` - Internal admin notes (admin only)
  - `contacted_at` - Timestamp when contacted
  - Timestamps and soft deletes enabled

## Public API Endpoints

### Create Advertisement Request
**POST** `/api/v1/advertisement-requests`

Creates a new advertisement request. This endpoint is public and requires no authentication.

**Request Body**:
```json
{
  "phone_number": "+251912345678",
  "email": "business@example.com",
  "full_name": "John Doe",
  "company_name": "Tech Corp",
  "description": "We would like to advertise our new product on your platform.",
  "file": null
}
```

**File Upload**:
- Field name: `file`
- Supported formats: pdf, doc, docx, jpg, jpeg, png
- Max size: 10MB
- Files are stored in `storage/app/public/advertisements/files/`

**Response** (201 Created):
```json
{
  "data": {
    "id": 1,
    "phone_number": "+251912345678",
    "email": "business@example.com",
    "full_name": "John Doe",
    "company_name": "Tech Corp",
    "description": "We would like to advertise...",
    "file_url": "http://example.com/storage/advertisements/files/xyz.pdf",
    "status": "pending",
    "contacted_at": null,
    "created_at": "2025-10-08T08:43:29+00:00",
    "updated_at": "2025-10-08T08:43:29+00:00"
  }
}
```

**Telegram Notification**:
When a new request is created, a Telegram notification is sent to the configured admin chat with:
- Requester name and company
- Phone number and email
- Description preview
- Creation timestamp

## Admin API Endpoints

All admin endpoints require authentication via Sanctum token and the `manage-advertisements` ability.

**Authentication Header**:
```
Authorization: Bearer {token}
```

### List Advertisement Requests
**GET** `/api/v1/admin/advertisement-requests`

Retrieves a paginated list of advertisement requests with optional filters.

**Query Parameters**:
- `full_name` - Filter by full name (partial match)
- `company_name` - Filter by company name (partial match)
- `phone_number` - Filter by phone number (prefix match)
- `status` - Filter by status (pending|contacted|rejected|approved)
- `created_after` - Filter by creation date (YYYY-MM-DD)
- `created_before` - Filter by creation date (YYYY-MM-DD)
- `sort` - Sort column (created_at|status|full_name)
- `sort_dir` - Sort direction (asc|desc, default: desc)
- `page` - Page number (default: 1)
- `per_page` - Items per page (default: 20, max: 100)

**Example Request**:
```bash
GET /api/v1/admin/advertisement-requests?status=pending&per_page=10
```

**Response** (200 OK):
```json
{
  "data": [
    {
      "id": 1,
      "phone_number": "+251912345678",
      "email": "business@example.com",
      "full_name": "John Doe",
      "company_name": "Tech Corp",
      "description": "We would like to advertise...",
      "file_url": null,
      "status": "pending",
      "contacted_at": null,
      "created_at": "2025-10-08T08:43:29+00:00",
      "updated_at": "2025-10-08T08:43:29+00:00",
      "admin_notes": null
    }
  ],
  "links": {
    "first": "http://example.com/api/v1/admin/advertisement-requests?page=1",
    "last": "http://example.com/api/v1/admin/advertisement-requests?page=5",
    "prev": null,
    "next": "http://example.com/api/v1/admin/advertisement-requests?page=2"
  },
  "meta": {
    "current_page": 1,
    "from": 1,
    "to": 20,
    "per_page": 20,
    "total": 100,
    "last_page": 5,
    "has_more": true
  },
  "filters": {
    "status": "pending"
  }
}
```

### Show Single Advertisement Request
**GET** `/api/v1/admin/advertisement-requests/{id}`

Retrieves details of a specific advertisement request.

**Response** (200 OK):
```json
{
  "data": {
    "id": 1,
    "phone_number": "+251912345678",
    "email": "business@example.com",
    "full_name": "John Doe",
    "company_name": "Tech Corp",
    "description": "We would like to advertise...",
    "file_url": "http://example.com/storage/advertisements/files/xyz.pdf",
    "status": "contacted",
    "contacted_at": "2025-10-08T00:00:00+00:00",
    "created_at": "2025-10-08T08:43:29+00:00",
    "updated_at": "2025-10-08T08:44:34+00:00",
    "admin_notes": "Called the business owner, will schedule a meeting."
  }
}
```

### Update Advertisement Request
**PATCH** `/api/v1/admin/advertisement-requests/{id}`

Updates the status, admin notes, or contacted timestamp of a request.

**Request Body** (all fields optional):
```json
{
  "status": "contacted",
  "admin_notes": "Called the business owner, will schedule a meeting next week.",
  "contacted_at": "2025-10-08"
}
```

**Response** (200 OK):
```json
{
  "data": {
    "id": 1,
    "status": "contacted",
    "admin_notes": "Called the business owner...",
    "contacted_at": "2025-10-08T00:00:00+00:00",
    ...
  }
}
```

### Delete Advertisement Request
**DELETE** `/api/v1/admin/advertisement-requests/{id}`

Soft deletes an advertisement request and removes associated file if present.

**Response** (204 No Content)

## Cache Management

The API uses Redis cache with the following tags:
- `advertisements` - All advertisement-related data
- `advertisements.search` - Search results

Cache is automatically invalidated when:
- A new request is created
- A request is updated
- A request is deleted

Cache TTL: 60 seconds for search results

## Status Workflow

1. **pending** - Initial status when request is created
2. **contacted** - Admin has contacted the business
3. **approved** - Request approved, proceeding with advertising
4. **rejected** - Request declined

## Error Responses

**400 Bad Request** - Validation errors:
```json
{
  "message": "The phone number field is required.",
  "errors": {
    "phone_number": ["The phone number field is required."]
  }
}
```

**401 Unauthorized** - Missing or invalid authentication:
```json
{
  "message": "Unauthenticated."
}
```

**403 Forbidden** - Insufficient permissions:
```json
{
  "message": "This action is unauthorized."
}
```

**404 Not Found** - Resource not found:
```json
{
  "message": "No query results for model [AdvertisementRequest] {id}"
}
```

**429 Too Many Requests** - Rate limit exceeded:
```json
{
  "status": "error",
  "code": "rate_limit_exceeded",
  "message": "Too many requests. Please slow down and try again shortly."
}
```

## Rate Limiting

API rate limits (per minute):
- Premium users: 240 requests
- Authenticated users: 120 requests
- Anonymous users: 60 requests

## Testing

Run the API tests:
```bash
docker compose exec php php artisan test --filter=Advertisement
```

## Implementation Files

### Domain Layer
- `app/Domain/Advertisement/Models/AdvertisementRequest.php`
- `app/Domain/Advertisement/Data/AdvertisementSearchParams.php`

### Actions
- `app/Actions/Advertisement/SearchAdvertisementRequestsAction.php`

### Controllers
- `app/Http/Controllers/Api/V1/AdvertisementRequestController.php`
- `app/Http/Controllers/Api/V1/Admin/AdvertisementRequestAdminController.php`

### Resources
- `app/Http/Resources/AdvertisementRequestResource.php`
- `app/Http/Resources/AdvertisementRequestCollection.php`

### Requests
- `app/Http/Requests/Advertisement/StoreAdvertisementRequestRequest.php`
- `app/Http/Requests/Advertisement/UpdateAdvertisementRequestRequest.php`
- `app/Http/Requests/Advertisement/SearchAdvertisementRequestRequest.php`

### Events & Listeners
- `app/Events/AdvertisementRequestCreated.php`
- `app/Listeners/NotifyTelegramAdvertisementRequest.php`

### Observers
- `app/Observers/AdvertisementRequestObserver.php`

### Support Classes
- `app/Support/AdvertisementFilters.php`
- Updated: `app/Support/CacheKeys.php`

### Database
- `database/migrations/2025_10_08_113856_create_advertisement_requests_table.php`
