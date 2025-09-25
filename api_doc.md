# Passport.et API v1 – Frontend Integration Guide

Base URL: `/api/v1`

These endpoints power the React client for listing and searching passports and for retrieving distinct locations. They respond with JSON only and are rate‑limited under the `api.v1.default` limiter.

- Auth: Not required for the endpoints documented here.
- Headers: Send `Accept: application/json` (axios examples below already do this).


## Endpoints

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
