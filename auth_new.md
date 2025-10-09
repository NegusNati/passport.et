# API Auth Update – Frontend Action Items

_Dated: 2025-10-01_

Recent backend revisions extend the authentication responses so the React dashboard can distinguish admin operators and align UI permissions with API guards. This note captures the payload deltas and the client work needed to stay in sync.

## What Changed
- `App\Http\Resources\UserResource` now includes three new fields on every auth-aware response (`/api/v1/auth/login`, `/api/v1/auth/register`, `/api/v1/auth/me`, `/api/v1/profile`):
  - `is_admin` – boolean, true when the user has the `admin` role.
  - `roles` – string array of all assigned role names (Spatie Permission, e.g. `['admin']`).
  - `permissions` – string array of resolved permission names (e.g. `['upload-files']`).
- These fields are derived from the same role/ability checks that guard `/api/v1/admin/*`, so they are the source of truth for rendering admin-only UI affordances.

## Frontend To‑Do
1. **Update the auth store shape**
   - Extend the user type/interface to accept the new attributes.
   - Ensure state persistence (React Query cache, Zustand store, etc.) keeps `is_admin`, `roles`, and `permissions` alongside the existing profile fields.
2. **Tighten route guards**
   - Gate admin dashboards, article management pages, and PDF ingestion flows using `user.is_admin` (preferred) or `user.permissions.includes('upload-files')`.
   - Fall back to server responses for ultimate enforcement; the API will still return `403` when a non-admin attempts admin routes.
3. **Token bootstrap**
   - After login or register, immediately propagate the new fields by updating `setAuthToken(token)` as before and storing the returned `user` object verbatim.
   - On app boot, revalidate via `GET /api/v1/auth/me`; if the call fails with `401`, clear auth state as usual.
4. **UI affordances**
   - Use the boolean to toggle admin call-to-actions (`Create Article`, `Upload PDF`, etc.).
   - Optionally surface role/permission data in an account settings panel for debugging.
5. **Telemetry / logging**
   - If the frontend emits analytics or error logs, include the new fields (or a derived “role” label) so operators can track admin usage.

## Backward Compatibility
- Existing clients that ignore the new fields continue to function; the payload is additive.
- The API still respects throttling tiers based on subscription plan (`plan_type`) and roles. `is_admin` does _not_ alter rate limits by itself.

## Testing Checklist
- Verify login/register flows parse responses without JSON schema errors.
- Hit `GET /api/v1/auth/me` with admin and non-admin accounts; confirm the flag toggles correctly.
- Attempt to open admin routes as non-admin; expect the client guard to redirect before the API returns a `403`.
- Upload a PDF as an admin to ensure the UI still posts `multipart/form-data` successfully.

For questions, drop a note in `#passport-frontend` or review the implementation in `src/app/Http/Resources/UserResource.php`.
