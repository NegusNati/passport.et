# Dashboard API Guide: Articles & PDF Imports

All endpoints documented below are registered in `src/routes/api.php` under the `/api/v1` prefix and run behind the `api` middleware stack plus the `throttle:api.v1.default` rate limiter (60 req/min for anonymous clients, 120 req/min for standard authenticated users, 240 req/min for premium subscribers). JSON responses follow Laravel's resource conventions and include helpful metadata for the external dashboard.

## Authentication & Authorization
- Public article endpoints are open but still rate-limited.
- All admin endpoints require:
  - `auth:sanctum` session or token authentication.
  - Specific abilities enforced via policies/gates:
    - `can:manage-articles` for article management.
    - `can:upload-files` for PDF ingestion.
- Failed authorization returns `403 Forbidden`; failed authentication returns `401 Unauthorized`.

---

## Articles – Public Read API

### GET `/api/v1/articles`
List articles for the public dashboard, returning paginated results by default.

**Query Parameters**
| Name | Type | Description |
| --- | --- | --- |
| `title` | string | Prefix match on the article title. |
| `q` | string | Full-text fuzzy search across title and excerpt. |
| `category` | string | Filter by category slug. |
| `tag` | string | Filter by tag slug. |
| `status` | enum (`draft`, `published`, `scheduled`, `archived`) | Only available to authenticated contexts; defaults to published-only when omitted. |
| `author_id` | integer | Filter by author ID. |
| `published_after` | date (`YYYY-MM-DD`) | Include articles published on or after this date. |
| `published_before` | date (`YYYY-MM-DD`) | Include articles published on or before this date. |
| `per_page` | integer (1–200) | Page size; enables length-aware pagination (default 20). |
| `page` | integer ≥ 1 | Pagination cursor (default 1). |
| `paginate` | boolean | Force pagination on/off. When `false`, respects `limit`. |
| `limit` | integer (1–200) | Max items when pagination is disabled. |
| `sort` | string | Sortable columns: `published_at`, `created_at`, `updated_at`, `title`. |
| `sort_dir` | enum (`asc`, `desc`) | Sort direction (default `desc`). |

**Response Shape**
```json
{
  "data": [
    {
      "id": 42,
      "slug": "travel-checklist",
      "title": "Travel Checklist",
      "excerpt": "Key steps before you travel...",
      "content": "<p>...</p>",
      "featured_image_url": "https://.../image.jpg",
      "canonical_url": "https://example.com/blog/travel-checklist",
      "meta_title": "Travel Checklist",
      "meta_description": "Key steps...",
      "og_image_url": "https://.../og.jpg",
      "status": "published",
      "published_at": "2025-09-20T10:15:00Z",
      "reading_time": 6,
      "word_count": 1340,
      "author": { "id": 3, "name": "Alex Writer" },
      "tags": [ { "id": 5, "name": "Travel", "slug": "travel" } ],
      "categories": [ { "id": 2, "name": "Guides", "slug": "guides" } ],
      "created_at": "2025-09-10T18:08:12Z",
      "updated_at": "2025-09-19T22:41:03Z"
    }
  ],
  "links": { "first": "…", "last": "…", "prev": null, "next": "…" },
  "meta": { "current_page": 1, "last_page": 5, "per_page": 20, "total": 84, "has_more": true },
  "filters": { "q": "travel" }
}
```
- When pagination is disabled (`paginate=false`), `links` are omitted and `meta` contains `{ "count": <int> }`.
- Cached for 120 seconds when filters are provided; cache keys hash the filter & pagination mix to avoid collisions.

### GET `/api/v1/articles/{slug}`
Fetch one published article by slug.
- Loads associated tags, categories, and author.
- Returns the same resource shape as the collection items.
- Responds with `404 Not Found` if the slug does not exist or is unpublished for public contexts.

---

## Articles – Admin Management API
All routes live under `/api/v1/admin/articles` and require both `auth:sanctum` and the `manage-articles` ability.

### POST `/api/v1/admin/articles`
Create a new article. Send `application/json` payload.

**Body Fields**
| Field | Type | Required | Notes |
| --- | --- | --- | --- |
| `title` | string ≤255 | ✅ | Title used to auto-generate slug when omitted. |
| `slug` | string ≤255 | ❌ | Optional custom slug; unique constraint enforced. |
| `excerpt` | string | ❌ | Markdown/HTML teaser. |
| `content` | string | ❌ | Full HTML content. |
| `featured_image_url` | URL | ❌ | Absolute URL to hero image. |
| `canonical_url` | URL | ❌ | Canonical link rel value. |
| `meta_title` | string ≤255 | ❌ | Overrides for SEO. |
| `meta_description` | string | ❌ | SEO description. |
| `og_image_url` | URL | ❌ | Social sharing image. |
| `status` | enum (`draft`, `published`, `scheduled`, `archived`) | ✅ | Workflow status. |
| `published_at` | date-time | ❌ | ISO8601; required when scheduling. |
| `tags` | string[] | ❌ | Array of tag slugs or names; tags are created on demand. |
| `categories` | string[] | ❌ | Array of category slugs or names; created if missing. |

**Behaviour & Response**
- The authenticated user becomes the article author.
- Reading time and word count are auto-derived from `content` (225 wpm assumption).
- Flushes `articles` cache tags to keep public listings fresh.
- Returns `201 Created` with the `ArticleResource` payload.

### PATCH `/api/v1/admin/articles/{slug}`
Partial update. Accepts any subset of the POST fields (use JSON `null` to clear nullable columns).
- Changing `title` without specifying `slug` regenerates a unique slug.
- Updating `content` recalculates reading metrics.
- Syncs tags & categories when arrays are provided (empty array clears associations).
- Returns updated `ArticleResource` with `200 OK`.

### DELETE `/api/v1/admin/articles/{slug}`
Soft-deletes the article and flushes affected cache tags.
- Response: `204 No Content` on success.
- Deleted resources remain queryable via admin-only tooling until permanently removed.

**Error Codes**
- `422 Unprocessable Entity` for validation failures (e.g., missing required fields, invalid URLs).
- `429 Too Many Requests` when throttled; payload `{ "status": "error", "code": "rate_limit_exceeded", "message": "Too many requests…" }`.

---

## PDF-to-SQLite Import – Admin API
Endpoints allow privileged operators to ingest PDF passport records into the `p_d_f_to_s_q_lites` table asynchronously.

### GET `/api/v1/admin/pdf-to-sqlite`
Human-friendly helper returning usage guidance.
```json
{
  "message": "Use POST /api/v1/admin/pdf-to-sqlite to upload a PDF for processing.",
  "constraints": {
    "pdf_file": "required PDF file up to 10MB",
    "date": "required date (YYYY-MM-DD)",
    "location": "required string",
    "linesToSkip": "required (integer or numeric)"
  }
}
```
- Useful for dashboard UIs to display allowed parameters before uploading.

### POST `/api/v1/admin/pdf-to-sqlite`
Upload a PDF and start the ingestion job. Requires `multipart/form-data`.

**Form Fields**
| Field | Type | Required | Notes |
| --- | --- | --- | --- |
| `pdf_file` | file (PDF ≤ 10MB) | ✅ | Stored on the `public` disk under `pdfs/`. |
| `date` | date (`YYYY-MM-DD`) | ✅ | Saved as `dateOfPublish` on imported rows. |
| `location` | string | ✅ | Used for the `location` column and cache tagging. |
| `linesToSkip` | string/integer | ✅ | Sentinel text; ingestion starts after the first line containing this value. |

**Processing Steps**
1. File is persisted via Laravel's filesystem (`storage/app/public/pdfs/...`).
2. `PDFToSQLiteJob` is queued with the resolved storage path and metadata.
3. The job parses the PDF with `Smalot\PdfParser\Parser`, splits it into lines, and begins reading after encountering `linesToSkip`.
4. Each parsed record maps to `no`, `firstName`, `middleName`, `lastName`, `requestNumber`, `dateOfPublish`, `location`. Rows are inserted in chunks using `insertOrIgnore` to avoid duplicates.
5. Logs are emitted for traceability (`Log::info` around store/dispatch/insert operations).

**Responses**
- `202 Accepted` success payload:
  ```json
  {
    "status": "success",
    "message": "PDF uploaded and processing started.",
    "data": {
      "path": "pdfs/2025/10/passport-batch.pdf"
    }
  }
  ```
- `422 Unprocessable Entity` on validation failure or upload errors:
  ```json
  {
    "status": "error",
    "code": "pdf_upload_failed",
    "message": "The pdf file failed to upload."
  }
  ```

**Operational Notes**
- Ensure the queue worker (e.g., Horizon) is running; otherwise jobs remain pending.
- Successful ingestion triggers cache invalidation via the `PassportObserver` (flushes `passports` tag) so downstream queries reflect new data.
- Importing large PDFs may take several seconds; the asynchronous design keeps the dashboard responsive.

---

## Integration Tips for the Dashboard
- Surface validation constraints in the UI using the tables above to minimize failed submissions.
- For uploads, display progress states (uploading → queued → completed) by polling dedicated status endpoints or observing queue events (future enhancement).
- Cache article filter presets client-side using the `filters` echo returned by the index endpoint so users can restore their last search quickly.
- Handle `429` responses by backing off and showing a friendly retry message; the `Retry-After` header communicates the cooldown window.
