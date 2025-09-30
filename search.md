# Passport Search API

This guide describes how to query the `/api/v1/passports` endpoint for high-performance, debounced search flows in the React client. The endpoint reuses the shared `SearchPassportsAction`, so all filters behave identically across web and API surfaces.

## Endpoint

```
GET /api/v1/passports
Content-Type: application/json
Accept: application/json
```

## Query Parameters

| Name | Type | Description |
| ---- | ---- | ----------- |
| `request_number` | `string` (min 3, max 255) | Exact or prefix match on the sanitized request number. Spaces and hyphens are removed and the value is upper-cased before querying. When present it can be combined with any other filter. |
| `first_name` | `string` | Prefix match on the stored `firstName` column. |
| `middle_name` | `string` | Prefix match on `middleName`. Optional when searching by full name. |
| `last_name` | `string` | Prefix match on `lastName`. |
| `name` | `string` | Composite name input. The API splits the value into `first_name`, `middle_name`, and `last_name` parts (first token → first name, last token → last name, remaining tokens → middle name). Useful for debounced free-text name searches. |
| `query` | `string` | Generic quick-search input. Values containing digits (length ≥ 3) are treated as `request_number`; other values fall back to the `name` pipeline. Use this for single input/search-as-you-type experiences. |
| `location` | `string` | Exact match on `location`. Combine with other filters to narrow results. |
| `published_after` | `date` (`YYYY-MM-DD`) | Returns passports published on or after the provided date. |
| `published_before` | `date` (`YYYY-MM-DD`) | Returns passports published on or before the provided date. |
| `per_page` | `integer` (options: 10, 20, 25, 30, 40, 50) | Enables paginated mode. Defaults to 25 for API requests. |
| `page_size` | `integer` (options: 10, 20, 25, 30, 40, 50) | Alias for `per_page`. Useful when the client prefers camelCase query parameters. |
| `page` | `integer` (≥1) | The page number to return when paginating. |
| `limit` | `integer` (1–200) | Maximum number of records to return when pagination is disabled. Defaults to 60. Ignored when `per_page` is supplied. |
| `sort` | `string` | Sortable columns: `dateOfPublish`, `requestNumber`, `created_at`. Defaults to `dateOfPublish`. |
| `sort_dir` | `string` (`asc` or `desc`) | Sort direction. Defaults to `desc`. |

## Normalisation Rules

* **Request numbers** – whitespace and hyphens are stripped; the value is upper-cased. Searches shorter than three characters are rejected.
* **Names** – inputs are trimmed and squished. Composite `name`/`query` values split into first, middle (optional), and last segments. Each segment is title-cased to match stored records.
* **Generic query** – when `query` contains digits and the sanitized value is at least three characters, it is treated as a request number; otherwise it flows through the name splitter.
* **Caching** – searches with at least one filter are cached for the configured TTL (60 seconds by default) under the `passports.search` cache tag.
* **Pagination options** – the response always includes `meta.page_size_options` (`[10, 20, 25, 30, 40, 50]`) and the currently active `meta.page_size` so React tables can render size pickers without hardcoding values.

## Response Structure

```
{
  "data": [
    {
      "id": 123,
      "request_number": "AA12345",
      "first_name": "Abebe",
      "middle_name": "Bekele",
      "last_name": "Tesfaye",
      "location": "Addis Ababa",
      "date_of_publish": "2024-09-01",
      ...
    }
  ],
  "meta": {
    "current_page": 1,
    "per_page": 25,
    "total": 120,
    "last_page": 5,
    "has_more": true,
    "page_size": 25,
    "page_size_options": [10, 20, 25, 30, 40, 50]
  },
  "links": {
    "first": "https://api.example.com/api/v1/passports?page=1",
    "last": "https://api.example.com/api/v1/passports?page=5",
    "prev": null,
    "next": "https://api.example.com/api/v1/passports?page=2"
  },
  "filters": {
    "request_number": "AA123",
    "first_name": "Abebe",
    "middle_name": "Bekele",
    "last_name": "Tesfaye",
    "location": "Addis Ababa",
    "published_after": null,
    "published_before": null,
    "name": "Abebe Bekele Tesfaye",
    "query": "aa-123"
  }
}
```

* When pagination is disabled, `meta` contains `{"count": <result-count>, "has_more": false}` and `links` is omitted.
* The `filters` payload echoes the sanitized values that were applied after normalisation, making it safe to hydrate client-side form state.

## Usage Patterns

### 1. Debounced Request Number Search

Call the endpoint with the debounced value once it reaches three characters:

```
GET /api/v1/passports?request_number=AA1
```

The response returns any passports whose request number begins with `AA1`. Continue updating the request as the user types to keep the server cache hot.

### 2. Debounced Name Search from a Composite Input

```
GET /api/v1/passports?name=lensa%20amanuel%20bekele
```

This call is split into `first_name=Lensa`, `middle_name=Amanuel`, and `last_name=Bekele` before querying. Results are prefixed-matched on each column, so partial middle or last names are supported.

### 3. General Quick-Search Box

```
GET /api/v1/passports?query=bb-678
```

`query` detects the digit-containing payload and treats it as `request_number=BB678`. If the payload contained no digits (e.g. `query=tesfaye`), it would fall back to the composite name logic.

### 4. Filtering by Location and Publish Date

```
GET /api/v1/passports?location=Addis%20Ababa&published_after=2024-08-15&per_page=50
```

Combines exact location filtering with a date lower-bound and paginated results. Sorting defaults to `dateOfPublish desc`, keeping recent passports at the top.

## Validation & Errors

* **422 Unprocessable Entity** – triggered when validation fails (e.g. `request_number=AA`). Payload follows Laravel’s standard error shape: `{ "message": "The given data was invalid.", "errors": { "request_number": ["The request number must be at least 3 characters to enable indexed search."] } }`.
* **429 Too Many Requests** – enforced by the `api.v1.default` rate limiter (unauthenticated: 60 req/min, standard subscribers: 120 req/min, premium: 240 req/min). The response body includes `retry_after` seconds and a human-readable message.
* **404 Not Found** – returned by `/api/v1/passports/{id}` when a record cannot be located.

## Performance Notes

* Search queries are index-backed (`requestNumber`, `firstName`, `middleName`, `lastName`, `lastName+firstName`, `location`, `dateOfPublish`). Prefix matches (`LIKE 'value%'`) stay sargable, so keep user inputs trimmed to the leading characters they know.
* Cache tags (`passports`, `passports.search`) allow automatic invalidation via `PassportObserver` whenever passport data changes.
* Combine debouncing on the client (250–300 ms) with the short server-side cache TTL (60 s) to minimise database load while keeping results fresh.

## Recommended Client Workflow

1. **Number tab** – fire `/api/v1/passports?request_number=<value>` when the debounced value length ≥ 3. Reset to the first page on each change and include `page_size` to keep the current table size.
2. **Name tab** – send discrete fields (`first_name`, `middle_name`, `last_name`) or a composite `name`. Trim empty inputs before sending; the API ignores null filters.
3. **Result handling** – hydrate UI from `data`, `meta`, `links`, and reuse `filters` to keep forms in sync with the server’s canonical view of the query.
4. **Fallback full fetch** – omit all filters to fetch the default paginated listing (25 per page) when users clear the form.

## Testing

Automated coverage for these behaviours lives in:

* `src/tests/Unit/Domain/Passport/PassportSearchTest.php`
* `src/tests/Feature/Api/PassportApiTest.php`

Run the targeted suites (inside Docker or with a configured database):

```
src/vendor/bin/pest src/tests/Unit/Domain/Passport/PassportSearchTest.php src/tests/Feature/Api/PassportApiTest.php
```

Ensure the test environment has access to the project’s MySQL service; otherwise Pest will report connection errors.
