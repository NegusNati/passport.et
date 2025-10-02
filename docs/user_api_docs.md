# User Administration API (v1)

The User Administration API powers the React admin console with endpoints for listing users and promoting accounts to the `admin` role. All routes live under the versioned namespace and require Sanctum authentication with an administrator account.

Base URL pattern:

```
/ api / v1 / admin /
```

Use HTTPS in all real deployments.

## Authentication & Authorization

- Clients must authenticate with Sanctum bearer tokens (`Authorization: Bearer {token}`) created for accounts that already hold the `admin` role. Non-admin tokens receive `403 Forbidden` responses.
- Requests inherit global API middleware (`api`, `throttle:api.v1.default`) and any network-level rate limits.
- Tokens should be stored securely; rotate them if compromised.

## Listing Users

**Route**

```
GET /api/v1/admin/users
```

**Purpose**: Provides a paginated, filterable catalog of users including role and permission metadata for admin dashboards.

**Query Parameters**

| Name       | Type   | Required | Default | Description |
|------------|--------|----------|---------|-------------|
| `page`     | int    | No       | `1`     | Page cursor following Laravel pagination semantics. |
| `per_page` | int    | No       | `25`    | Page size, constrained between 1 and 100. |
| `search`   | string | No       | —       | Performs a case-insensitive match against `first_name`, `last_name`, `email`, and `phone_number`. |

**Response Shape**

Successful responses reuse `App\Http\Resources\UserResource` and standard Laravel pagination wrappers.

```json
{
  "data": [
    {
      "id": 1,
      "first_name": "Abebe",
      "last_name": "Bekele",
      "email": "abebe@example.com",
      "phone_number": "0912000000",
      "email_verified_at": "2025-09-28T18:42:00Z",
      "plan_type": "premium",
      "is_admin": true,
      "roles": ["admin"],
      "permissions": ["upload-files"],
      "created_at": "2025-09-20T12:34:56Z",
      "updated_at": "2025-09-30T09:12:45Z"
    }
  ],
  "links": {
    "first": "https://api.passport.et/api/v1/admin/users?page=1",
    "last": "https://api.passport.et/api/v1/admin/users?page=4",
    "prev": null,
    "next": "https://api.passport.et/api/v1/admin/users?page=2"
  },
  "meta": {
    "current_page": 1,
    "per_page": 25,
    "from": 1,
    "to": 25,
    "total": 86
  }
}
```

**Error Codes**

| Status | Code | Notes |
|--------|------|-------|
| 401 | `unauthenticated` | Missing or invalid Sanctum token. |
| 403 | `forbidden` | Token belongs to a non-admin user. |

## Checking Admin Abilities

**Route**

```
GET /api/v1/admin/abilities
```

**Purpose**: Returns gate evaluations for the authenticated admin, allowing the React client to toggle access to Horizon, Pulse, and editorial tooling without duplicating role logic.

**Response Shape**

```json
{
  "data": {
    "abilities": {
      "viewPulse": true,
      "viewHorizon": true,
      "manageArticles": true
    }
  }
}
```

**Error Codes**

| Status | Code | Notes |
|--------|------|-------|
| 401 | `unauthenticated` | Missing or invalid Sanctum token. |
| 403 | `forbidden` | Token belongs to a non-admin user. |

The endpoint leverages the gates defined in `App\Providers\AppServiceProvider::defineGates()` to ensure parity between the backend dashboards and API consumers.

## Promoting a User to Admin

**Route**

```
PATCH /api/v1/admin/users/{user}/role
```

- `{user}` accepts a numeric user ID.
- Once promoted, users remain admins until removed manually (role removal is not yet implemented).

**Request Body**

All payloads are JSON by default.

| Field | Type | Required | Allowed Values | Description |
|-------|------|----------|----------------|-------------|
| `role` | string | Yes | `admin` | The target role to assign. Additional roles can be added in future revisions. |

**Example**

```http
PATCH /api/v1/admin/users/42/role HTTP/1.1
Authorization: Bearer {token}
Content-Type: application/json

{
  "role": "admin"
}
```

**Responses**

- `200 OK` – returns the refreshed `UserResource` for the promoted account.
- `422 Unprocessable Entity` – validation failure (e.g., unsupported role value). Payload includes the standard Laravel validation error format with `code` set to `validation_error`.
- `403 Forbidden` – the acting user lacks the admin role.

```json
{
  "data": {
    "id": 42,
    "first_name": "Lensa",
    "last_name": "Kebede",
    "email": "lensa@example.com",
    "phone_number": "0911000000",
    "is_admin": true,
    "roles": ["admin"],
    "permissions": ["upload-files"],
    "created_at": "2025-08-10T07:30:00Z",
    "updated_at": "2025-10-01T14:05:12Z"
  }
}
```

## Implementation Notes

- Controller: `App\Http\Controllers\Api\V1\Admin\UserAdminController`
  - `index()` handles pagination, eager loads `roles`, `permissions`, and `subscription`, and applies fuzzy search.
  - `updateRole()` consumes `UpdateUserRoleRequest` for validation and idempotent role assignment.
- Validation: `App\Http\Requests\Admin\UpdateUserRoleRequest`
  - Authorizes only admin actors.
  - Currently restricts assignments to the `admin` role to avoid accidental privilege escalation.
- Resource Transformer: `App\Http\Resources\UserResource`
  - Normalizes timestamps, exposes plan, role, and permission details for FE consumption.
- RBAC: Spatie Permission manages roles/permissions. Ensure the seeder (`Database\Seeders\PermissionSeeder`) has run so the `admin` role and `upload-files` permission exist.

## Testing Checklist

Run targeted feature tests from the project root:

```
cd src
php artisan test --testsuite=Feature --filter=UserAdmin
```

If running outside Docker, use the SQLite testing profile or boot the MySQL container defined in `docker-compose.yml` to satisfy database connectivity.

## Change Log

- **2025-10-01** – Initial release of the User Administration API documentation. Covers user listing and admin promotion endpoints introduced alongside `UserAdminController`.
