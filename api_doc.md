# Passport.et API v1 – Frontend Integration Guide

Base URL: `/api/v1`

These endpoints power the React client for listing and searching passports and for retrieving distinct locations. They respond with JSON only and are rate‑limited under the `api.v1.default` limiter.

- Auth: Not required for the endpoints documented here.
- Headers: Send `Accept: application/json` (axios examples below already do this).


## Endpoints

### GET /articles
List and search public articles (published only). Paginated by default.

Query parameters
- `title` (string): Prefix match on the title only (fast; uses index). If provided, this takes precedence over `q`.
- `q` (string): Free-text search in title/excerpt/content (prefix + contains).
- `category` (string): Category slug to filter by.
- `tag` (string): Tag slug to filter by.
- `published_after` / `published_before` (YYYY-MM-DD)
- `per_page`, `page` (pagination)
- `sort` (published_at|created_at|updated_at|title), `sort_dir` (asc|desc)

Response
```
200 OK
{
  "data": [
    {
      "id": 1,
      "slug": "how-to-apply-passport",
      "title": "How to Apply for a Passport",
      "excerpt": "A step-by-step guide...",
      "content": "<p>Rich HTML...</p>",
      "featured_image_url": "https://cdn.example.com/hero.jpg",
      "canonical_url": "https://site.example.com/articles/how-to-apply-passport",
      "meta_title": "Apply for a Passport (2025 Guide)",
      "meta_description": "Step-by-step passport application guide.",
      "og_image_url": "https://cdn.example.com/og.jpg",
      "status": "published",
      "published_at": "2025-09-24T10:00:00Z",
      "reading_time": 5,
      "word_count": 1120,
      "author": { "id": 3, "name": "Admin" },
      "tags": [{"id":2,"name":"Guides","slug":"guides"}],
      "categories": [{"id":1,"name":"Passports","slug":"passports"}],
      "created_at": "2025-09-20T12:45:00Z",
      "updated_at": "2025-09-24T10:00:00Z"
    }
  ],
  "links": {"first":"...","last":"...","prev":"...","next":"..."},
  "meta": {"current_page":1,"per_page":20,"total":123,"last_page":7,"has_more":true},
  "filters": {"q":"passport","category":null,"tag":null}
}
```

Examples
- `GET /api/v1/articles?q=passport&per_page=20&page=1&sort=published_at&sort_dir=desc`
- `GET /api/v1/articles?category=passports&tag=guides`
- `GET /api/v1/articles?title=Passport` (title starts with "Passport")

SEO tips
- React page should render `<title>`, `<meta name=description>`, and `<link rel=canonical>` using `meta_title`, `meta_description`, `canonical_url`.
- Expose structured data (JSON‑LD Article) in the React page using these fields for faster indexing.

### GET /articles/{slug}
Fetch one article by slug.

```
200 OK
{ "data": { /* same shape as in list */ } }
```

### GET /categories
List active categories (with published article counts).

```
200 OK
{ "data": [{"id":1,"name":"Passports","slug":"passports","articles_count": 42}], "meta": {"count": 5} }
```

### GET /tags
List active tags (with published article counts).

```
200 OK
{ "data": [{"id":2,"name":"Guides","slug":"guides","articles_count": 18}], "meta": {"count": 12} }
```

### GET /feeds/articles.rss (XML)
RSS 2.0 feed of the latest 50 published articles.
- Content-Type: `application/rss+xml; charset=UTF-8`

### GET /feeds/articles.atom (XML)
Atom 1.0 feed of the latest 50 published articles.
- Content-Type: `application/atom+xml; charset=UTF-8`

### GET /passports
List and search passports. In API context, responses are paginated by default.

Query parameters
- `request_number` (string): Prefix match on request number. Takes precedence over name filters.
- `first_name` (string): Prefix match on first name.
- `middle_name` (string): Prefix match on middle name.
- `last_name` (string): Prefix match on last name.
- `location` (string): Exact match on the location field.
- `published_after` (date, YYYY-MM-DD): Include rows with `date_of_publish` on/after this date.
- `published_before` (date, YYYY-MM-DD): Include rows with `date_of_publish` on/before this date.
- `per_page` (int, 1–200): Page size. Default 25.
- `page` (int, >=1): Page number. Default 1.
- `sort` (enum): One of `dateOfPublish`, `requestNumber`, `created_at`. Default `dateOfPublish`.
- `sort_dir` (enum): `asc` or `desc`. Default `desc`.

Notes
- Normalization: `request_number` is uppercased and stripped of spaces/hyphens; names are title‑cased; dates are parsed to `YYYY-MM-DD`.
- Precedence: When `request_number` is provided, name filters are ignored.
- Pagination: API responses are always paginated; `limit` is ignored in API context.

Successful response (paginated)
```
200 OK
{
  "data": [
    {
      "id": 123,
      "request_number": "AA12345",
      "first_name": "Abebe",
      "middle_name": "Bekele",
      "last_name": "Kebede",
      "full_name": "Abebe Bekele Kebede",
      "location": "Addis Ababa",
      "date_of_publish": "2024-06-15",
      "created_at": "2024-06-15T10:02:17Z",
      "updated_at": "2024-06-20T08:14:55Z"
    }
  ],
  "links": {
    "first": "https://example.com/api/v1/passports?page=1",
    "last": "https://example.com/api/v1/passports?page=10",
    "prev": "https://example.com/api/v1/passports?page=1",
    "next": "https://example.com/api/v1/passports?page=3"
  },
  "meta": {
    "current_page": 2,
    "per_page": 25,
    "total": 234,
    "last_page": 10,
    "has_more": true
  },
  "filters": {
    "request_number": "AA1",
    "first_name": null,
    "middle_name": null,
    "last_name": null,
    "location": null,
    "published_after": null,
    "published_before": null
  }
}
```

Examples
- Get page 2 of size 10, newest first: `GET /api/v1/passports?per_page=10&page=2&sort=dateOfPublish&sort_dir=desc`
- Search by request number prefix: `GET /api/v1/passports?request_number=AA1`
- Filter by location and date range: `GET /api/v1/passports?location=Dire%20Dawa&published_after=2024-01-01&published_before=2024-12-31`


### GET /passports/{id}
Get a single passport record by its numeric `id`.

Successful response
```
200 OK
{
  "data": {
    "id": 123,
    "request_number": "AA12345",
    "first_name": "Abebe",
    "middle_name": "Bekele",
    "last_name": "Kebede",
    "full_name": "Abebe Bekele Kebede",
    "location": "Addis Ababa",
    "date_of_publish": "2024-06-15",
    "created_at": "2024-06-15T10:02:17Z",
    "updated_at": "2024-06-20T08:14:55Z"
  }
}
```


### GET /locations
Return distinct passport locations (sorted alphabetically). Cached for 5 minutes server‑side.

Successful response
```
200 OK
{
  "data": ["Addis Ababa", "Dire Dawa", "Hawassa"],
  "meta": { "count": 3 }
}
```


## Search Semantics (Server)
- Prefix matching: `request_number`, `first_name`, `middle_name`, `last_name` all use “starts with” matching.
- Exact match: `location` must match exactly (case sensitive per DB collation).
- Date filtering: `published_after` → `date_of_publish >= value`; `published_before` → `date_of_publish <= value`.
- Sorting: If `sort` is not one of the allowed columns, default `dateOfPublish desc` is used.


## React Usage
A small axios client already exists at `src/resources/js/api/passports.js`:
```js
import axios from "axios";

const client = axios.create({
  baseURL: "/api/v1",
  headers: { Accept: "application/json" },
});

export const fetchPassports = (params = {}) =>
  client.get("/passports", { params }).then((r) => r.data);

export const fetchLocations = () =>
  client.get("/locations").then((r) => r.data);

export const fetchPassport = (id) =>
  client.get(`/passports/${id}`).then((r) => r.data);
```

Articles client (add a new module): `src/resources/js/api/articles.js`
```js
import axios from "axios";

const client = axios.create({ baseURL: "/api/v1", headers: { Accept: "application/json" } });

export const fetchArticles = (params = {}) => client.get("/articles", { params }).then(r => r.data);
export const fetchArticle = (slug) => client.get(`/articles/${slug}`).then(r => r.data);
export const fetchCategories = () => client.get("/categories").then(r => r.data);
export const fetchTags = () => client.get("/tags").then(r => r.data);

export default { fetchArticles, fetchArticle, fetchCategories, fetchTags };
```

### Table with Pagination (React Query example)
```jsx
import { useQuery } from "@tanstack/react-query";
import api from "../../resources/js/api/passports"; // adjust path as needed

function usePassports(params) {
  return useQuery({
    queryKey: ["passports", params],
    queryFn: () => api.fetchPassports(params),
    keepPreviousData: true,
  });
}

export default function PassportsTable() {
  const [page, setPage] = useState(1);
  const [perPage, setPerPage] = useState(25);
  const [filters, setFilters] = useState({ request_number: "", location: "" });

  const { data, isLoading } = usePassports({
    ...filters,
    per_page: perPage,
    page,
    sort: "dateOfPublish",
    sort_dir: "desc",
  });

  if (isLoading) return <div>Loading…</div>;

  const rows = data.data; // array of passports
  const meta = data.meta; // pagination info incl. has_more

  return (
    <div>
      <form onSubmit={(e) => e.preventDefault()}>
        <input
          placeholder="Request #"
          value={filters.request_number}
          onChange={(e) => setFilters((f) => ({ ...f, request_number: e.target.value }))}
        />
        {/* Add location select populated from /locations */}
      </form>

      <table>
        <thead>
          <tr>
            <th>Request #</th>
            <th>Full Name</th>
            <th>Location</th>
            <th>Published</th>
          </tr>
        </thead>
        <tbody>
          {rows.map((p) => (
            <tr key={p.id}>
              <td>{p.request_number}</td>
              <td>{p.full_name}</td>
              <td>{p.location}</td>
              <td>{p.date_of_publish}</td>
            </tr>
          ))}
        </tbody>
      </table>

      <div className="pagination">
        <button disabled={meta.current_page <= 1} onClick={() => setPage((n) => n - 1)}>
          Prev
        </button>
        <span>
          Page {meta.current_page} / {meta.last_page}
        </span>
        <button disabled={!meta.has_more} onClick={() => setPage((n) => n + 1)}>
          Next
        </button>
      </div>
    </div>
  );
}
```

### Locations for Filters
```jsx
import { useQuery } from "@tanstack/react-query";
import api from "../../resources/js/api/passports";

function LocationFilter({ value, onChange }) {
  const { data } = useQuery({ queryKey: ["locations"], queryFn: api.fetchLocations });
  const locations = data?.data ?? [];

  return (
    <select value={value} onChange={(e) => onChange(e.target.value)}>
      <option value="">All locations</option>
      {locations.map((loc) => (
        <option key={loc} value={loc}>
          {loc}
        </option>
      ))}
    </select>
  );
}
```

### Articles Listing with Filters (React Query example)
```jsx
import { useQuery } from "@tanstack/react-query";
import api from "../../resources/js/api/articles";

export default function ArticlesPage() {
  const [page, setPage] = useState(1);
  const [q, setQ] = useState("");
  const [category, setCategory] = useState("");
  const [tag, setTag] = useState("");

  const { data, isLoading } = useQuery({
    queryKey: ["articles", { page, q, category, tag }],
    queryFn: () => api.fetchArticles({ per_page: 20, page, q, category, tag }),
    keepPreviousData: true,
  });

  if (isLoading) return <div>Loading…</div>;
  const rows = data.data;
  const meta = data.meta;

  return (
    <div>
      <input value={q} onChange={(e) => setQ(e.target.value)} placeholder="Search" />
      <select value={category} onChange={(e) => setCategory(e.target.value)}>{/* populate from /categories */}</select>
      <select value={tag} onChange={(e) => setTag(e.target.value)}>{/* populate from /tags */}</select>
      <ul>
        {rows.map((a) => (
          <li key={a.id}>
            <a href={`/articles/${a.slug}`}>{a.title}</a>
          </li>
        ))}
      </ul>
      <button disabled={meta.current_page <= 1} onClick={() => setPage(p => p - 1)}>Prev</button>
      <button disabled={!meta.has_more} onClick={() => setPage(p => p + 1)}>Next</button>
    </div>
  );
}
```

## Seed Data & Sitemap
- Seed categories, tags, and sample articles:
  - `docker compose exec php php artisan db:seed --class=CategoryTagSeeder`
  - `docker compose exec php php artisan db:seed --class=ArticleSeeder`
- Sitemap generation (daily via scheduler): `docker compose exec php php artisan app:generate-sitemap`
  - Output: `public/sitemap.xml` (robots.txt already points to it)



## Errors
- Validation (422)
```
{
  "message": "The given data was invalid.",
  "errors": {
    "per_page": ["The per page must be between 1 and 200."],
    "published_after": ["The published after is not a valid date."]
  }
}
```
- Not Found (404)
```
{
  "message": "No query results for model [Passport] 999999"
}
```
- Rate limit (429)
```
{
  "status": "error",
  "code": "rate_limit_exceeded",
  "message": "Too many requests. Please slow down and try again shortly."
}
```


## Implementation References
- Routes: `src/routes/api.php`
- Controllers: `src/app/Http/Controllers/Api/V1/PassportController.php`, `src/app/Http/Controllers/Api/V1/LocationController.php`
- Validation: `src/app/Http/Requests/Passport/SearchPassportRequest.php`
- Resources: `src/app/Http/Resources/PassportResource.php`, `src/app/Http/Resources/PassportCollection.php`
- Search/action: `src/app/Actions/Passport/SearchPassportsAction.php`, `src/app/Domain/Passport/Data/PassportSearchParams.php`, `src/app/Domain/Passport/Models/Passport.php`
