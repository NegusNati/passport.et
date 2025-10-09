# AGENTS

This document summarizes the strategy for turning the existing Laravel/Inertia project inside `src/` into a high-performance API platform that powers an external React client. It distills the current codebase, highlights bottlenecks, and presents best practices for API design, performance tuning, search, and phone-number-first authentication.

## 1. Current State Snapshot
- Web-only routing still powers the legacy UI via `src/routes/web.php`, while a new versioned scaffold in `src/routes/api.php` exposes stub JSON endpoints for the upcoming API without affecting existing Inertia responses.
- Core data is stored in `p_d_f_to_s_q_lites`, surfaced through `App\Models\PDFToSQLite` with wide-open `$guarded = []`, and queried directly in controllers with `like` filters and `simplePaginate`.
- Caching already appears in controllers (`Cache::remember` for passport lists, city lookup) and in the closure front page count, but cache invalidation is missing.
- `RateLimitMiddleware` implements per-plan limits on top of `Cache`, but does not guard against unauthenticated visitors (null `$user`).
- Auth still relies on the default Laravel email/password stack with Inertia-driven UI.

### Current API Targets (Phase 0 Baseline)
- **Controllers & Flows**: `src/app/Http/Controllers/PassportSearchController.php` renders `Passport/Show` (search) and `Passport/TableView` (all passports) and queries `PDFToSQLite` with prefix `LIKE` filters, limiting to 60 results or `simplePaginate(50)` for archives. `FilterByCityController` caches distinct locations (`cities`) for an hour and memoizes per-location pagination results; `SubscriptionController` assigns `plan` values on the authenticated user before redirecting to the dashboard.
- **Caching Touchpoints**: Global count cache on `/` (`passport_count` key), per-location caches (`passports_location_{location}_page_{n}`), `cities` lookup cache, and the legacy `RateLimitMiddleware` that increments counters without null-guarding `$user`. New API limiter (`api.v1.default`) returns JSON errors and uses user-id/IP keys.
- **Data Model & Indexes**: `p_d_f_to_s_q_lites` columns include `no`, `firstName`, `middleName`, `lastName`, unique `requestNumber`, `location`, and `dateOfPublish` with timestamps. Indexed fields: `location`, `firstName`, composite `firstName+middleName+lastName`, and `requestNumber`.
- **Authentication Stack**: `config/auth.php` keeps the default `web` session guard backed by `App\Models\User`; Laravel Breeze routes in `routes/auth.php` handle registration, login, password reset, and email verification. No API guard or token issuing is configured yet.
- **Outstanding Baseline Checks**: Dockerized commands are partially complete. `composer validate` and `php artisan test --testsuite=Unit` were executed successfully inside the Docker stack on 2025-09-23. `npm run lint` still needs to be run (command: `docker compose run --rm npm "npm run lint"`).

### Phase 2 Progress Log
- Shared domain model `App\Domain\Passport\Models\Passport` now owns search scopes (`filter`, `sort`, `limitForSearch`) with mass-assignment protection and `dateOfPublish` casting. Legacy `App\Models\PDFToSQLite` extends it to keep existing references working.
- `PassportSearchController` delegates search logic to `App\Actions\Passport\SearchPassportsAction`, reducing controller complexity and preparing for API reuse. Inputs are normalized via `PassportSearchParams` DTO, enabling consistent casing, date handling, and pagination defaults.
- Centralized filter metadata and cache helpers live in `App\Support\PassportFilters` and `App\Support\CacheKeys`; search caching uses hashed keys with 60-second TTL (overrideable later).
- New Pest unit tests under `tests/Unit/Domain/Passport/PassportSearchTest.php` cover request-number filtering, date/location filters, and the action’s pagination behaviour to guard against regressions as the API layer adopts the service.

### Phase 3 Progress Log
- `/api/v1/passports` now resolves through `PassportController`, reusing `SearchPassportsAction` with API-specific validation (`SearchPassportRequest`) and returning `PassportCollection` resources that expose consistent `data`, `meta`, `links`, and a `filters` echo for the React client.
- `/api/v1/passports/{id}` surfaces individual records through `PassportResource`, while `/api/v1/locations` serves cached distinct locations (`api.v1.locations` key, 5-minute TTL).
- `PassportResource` now shapes fields for API consumers (normalized names, ISO timestamps) and `PassportCollection` adds `meta.has_more` for paginated responses while tracking counts for non-paginated searches.
- Feature coverage added in `tests/Feature/Api/PassportApiTest.php` validating pagination metadata, filtering semantics, detail responses, and location listings. Run via `docker compose exec php php artisan test --testsuite=Feature --filter=Api`.
- React `Passport/TableView` now consumes the API directly (axios client in `resources/js/api/passports.js`), adding server-side filters for request number, location, and publish dates plus client-side pagination controls that align with the new JSON schema.

### Phase 4 Progress Log
- Selected Sanctum personal access tokens for API authentication. Added `App\Http\Controllers\Api\V1\Auth\AuthController` with `login`, `me`, and `logout` endpoints and protected them with `auth:sanctum` middleware.
- Hardened validation via `App\Http\Requests\Auth\ApiLoginRequest` and normalized user payloads through `App\Http\Resources\UserResource`.
- Enabled token issuance by adding the Sanctum migration (`0001_01_01_000003_create_personal_access_tokens_table.php`) and applying `HasApiTokens` to `App\Models\User`.
- Added API auth feature coverage in `tests/Feature/Api/AuthApiTest.php` to guard login success/failure, profile retrieval, and logout token revocation.
- Decision: migrate toward a phone-number-first login flow (OTP optional later). API passport endpoints remain publicly accessible—no plan-based gating required beyond rate limiting.

### Phase 5 Progress Log
- Docker environments (local and prod compose files) now include a dedicated Redis 7 service with persistent volumes; `.env.example` defaults cache/session/queue drivers to Redis.
- Rate limiting now mirrors web behaviour: `api.v1.default` gives premium subscribers 240 req/min, other authenticated users 120 req/min, and anonymous clients 60 req/min, all returning a consistent JSON throttle payload. Implementation lives in `App\Providers\AppServiceProvider`.
- A `redis:ping` artisan command is registered for smoke-testing connectivity inside the containers.
- Cache lookups for passport counts, listings, and locations use tagged stores via `App\Support\CacheKeys`, while `SearchPassportsAction` and controllers share the same naming scheme.
- `PassportObserver` flushes the `passports` cache tag on create/update/delete/restore so API and legacy pages stay in sync.

### Phase 6 – Quality Gates, Monitoring & Rollout
- GitHub Actions workflow (`.github/workflows/ci.yml`) now runs migrations, unit & API feature tests, PHPStan, Pint, asset builds, and a Horizon status check on each push/PR.
- Horizon dashboard is deployed alongside Laravel Pulse, guarded by the admin role, and documented checks (`README.md`).
- Horizon events trigger optional Telegram alerts via `NotifyHorizonViaTelegram` (`JobFailed`, `JobProcessing`, `LongWaitDetected`) and new-user notifications via `NotifyTelegramUserRegistered`; credentials live in `TELEGRAM_BOT_TOKEN` / `TELEGRAM_CHAT_ID` env vars.
- Added `tests/Performance/PassportLoadTest.js` for k6 smoke testing; instructions are in the README.
- Rollout & fallback playbook lives in `docs/rollout.md`, covering pre-deploy verification, post-deploy smoke tests, and rollback steps.

Use the observations above to drive the migration plan and as regression targets when testing the new API surface.

## 2. Migration Strategy Overview
1. **Split web vs API concerns**: Keep legacy Inertia routes under `routes/web.php` for now, but build the public contract in `routes/api.php`. Phase out Inertia responses once the React SPA is ready.
2. **Introduce versioned API namespaces**: Place REST controllers under `App/Http/Controllers/Api/V1`. Expose only JSON responses and lean on `JsonResource` transformers.
3. **Establish service/action layers**: Move business logic (search, filtering, subscriptions) out of controllers into `app/Domain/**` or `app/Actions/**` classes. Controllers become thin orchestrators.
4. **Harden models and database layer**: Lock down mass-assignment, add dedicated query scopes, and ensure indexes exist for all search columns (`requestNumber`, name fields, `location`).
5. **Add automated tests**: Feature tests against the new API, unit/integration tests for services, and contract tests for search/pagination logic.
6. **Progressive rollout**: Start by duplicating critical flows (passport search, city filter, subscription) in the API layer, run both in parallel, then retire the Inertia endpoints once the React app is stable.

## 3. Recommended Project Layout
```
app/
  Actions/            # Single-purpose classes invoked by controllers (e.g. SearchPassportAction)
  Domain/
    Passport/
      DTOs/
      Models/
      Services/
    Subscription/
  Http/
    Controllers/
      Api/
        V1/
          PassportController.php
          LocationController.php
          SubscriptionController.php
  Http/Resources/     # ApiResource transformers
  Http/Requests/      # FormRequest validation per endpoint
  Support/            # Traits, helpers
routes/
  api.php             # Versioned grouping, rate limiting, middleware
  web.php             # Legacy Inertia routes (to delete later)
```
- Keep configuration in `config/` and avoid per-feature config files under `app/`.
- For cross-cutting concerns (search specs, caching keys), centralize constants in `app/Support/Constants.php` or config files.
- Store business-specific validators inside `app/Domain/{Feature}/Validators` when rules depend on domain knowledge.

## 4. API Design and Implementation Best Practices
- **Routing & Versioning**
  - Use `Route::prefix('v1')->name('api.v1.')->group(...)` inside `routes/api.php`.
  - Prefer resourceful routes (`Route::apiResource`) and explicit verbs (`GET /passports`, `POST /subscriptions`).
  - Return proper HTTP response codes (`201 Created` on resource creation, `204 No Content` on deletes).
- **Controllers**
  - Keep logic thin: validate, call an action/service, return a `JsonResource`.
  - Use typed dependencies via constructor injection (e.g. `SearchPassportsAction $search`).
  - Replace ad-hoc pagination with `paginate()` plus query builders/scopes.
- **Validation**
  - Move validation rules into `FormRequest` classes (e.g. `SearchPassportRequest`) for clarity and reuse.
  - Normalize phone numbers with custom validation rules before persisting or sending OTPs.
- **Resources / Transformers**
  - Create `PassportResource` and `PassportCollection` to control JSON shape, hide internal columns, and append computed flags.
  - Include pagination metadata (`links`, `meta`) for the React client.
- **Error Handling**
  - Throw domain-specific exceptions inside services (e.g. `PassportNotFound`) and convert to JSON in exception handler.
  - Standardize error payloads (`code`, `message`, `details`) for consistency.
- **Rate Limiting**
  - Replace the custom middleware with Laravel’s `RateLimiter` in `App\Providers\RouteServiceProvider`. Configure per-plan and per-endpoint limits, falling back to IP-based limits for anonymous users.

## 5. Performance & Scaling Tactics
- **Configuration-level**
  - Use `php artisan config:cache`, `route:cache`, and `event:cache` in production deployments.
  - Set `OPCACHE` and PHP-FPM tuning via Docker (align pool size with queue workers).
- **Database**
  - Ensure composite indexes for search combinations: `(requestNumber)`, `(firstName, middleName, lastName)`, `(location)`. Add migrations to create them.
  - Replace `LIKE 'value%'` with full-text or trigram indexes where possible; fallback to case-insensitive columns.
  - Monitor slow queries via `DB::listen` in local dev and `laravel-debugbar`/`Clockwork`.
- **Caching**
  - Prefer Redis (configure in `config/cache.php`) for query and rate-limit caches.
  - Introduce cache tags (e.g. `Cache::tags(['passports'])->remember(...)`) so invalidation per update import is straightforward.
  - Define cache keys and TTLs centrally (constant or config) to avoid drift; use short TTL for search results, longer for metadata (city list).
  - Implement cache invalidation hooks in model observers (e.g. `PassportObserver` clearing `passports.*` tags after updates/imports).
- **Queues & Jobs**
  - Offload heavy tasks (PDF parsing, search index updates, OTP sending) to queued jobs, using Redis or SQS drivers. Configure horizon for visibility.
- **HTTP Layer**
  - Enable response compression (Nginx/CloudFront) and ETags where resources allow.
  - Add `Cache-Control` headers for read-heavy endpoints to let CDN edge caches serve results.
- **Observability**
  - Hook up request/DB metrics to Telescope or external tools (Sentry, New Relic) to catch regressions early.

## 6. Filtering, Pagination, and Sorting Patterns
- Implement reusable query scopes on models:
  ```php
  public function scopeFilter(Builder $query, array $filters)
  {
      return $query
          ->when($filters['request_number'] ?? null, fn($q, $value) => $q->where('request_number', 'ilike', "$value%"))
          ->when($filters['name'] ?? null, fn($q, $value) => $q->whereFullText(['first_name', 'middle_name', 'last_name'], $value))
          ->when($filters['location'] ?? null, fn($q, $value) => $q->where('location', $value))
          ->when($filters['issued_after'] ?? null, fn($q, $value) => $q->whereDate('issued_at', '>=', $value));
  }
  ```
- Centralize allowed filters and sorting keys (e.g. `PassportFilter.php` class) to prevent arbitrary SQL conditions.
- Always return cursor or length-aware pagination metadata; expose `page`, `perPage`, `total`, `hasMore` to React.
- Support consistent sorting defaults via query params (`?sort=-issued_at`). Normalize them before applying to the query builder.

## 7. Search Optimization
- Evaluate Laravel Scout with Meilisearch or Typesense for fuzzy name and request-number lookup. Keep primary DB queries for exact matches and fallback to the search index for partials.
- For MySQL full-text:
  - Add full-text indexes on `first_name`, `middle_name`, `last_name`, and `location`.
  - Use `whereFullText` with boolean mode for prefix matching (`"+${term}*"`).
- Preprocess search inputs: strip diacritics, uppercase/lowercase normalization, remove whitespace noise from request numbers.
- Cache popular queries (top N request numbers) and warm the cache after each data import.

## 8. Phone-Number-First Authentication Flow
1. **Data Model**
   - Add `phone_number`, `phone_region`, `phone_verified_at` columns to `users`. Enforce uniqueness with normalized E.164 numbers.
2. **Verification**
   - Implement an OTP service (custom or 3rd party like Twilio Verify). Store short-lived tokens in Redis with rate limits per number and per IP.
   - Expose endpoints: `POST /v1/auth/request-otp`, `POST /v1/auth/verify-otp`, `POST /v1/auth/logout`.
   - Return JWT/Sanctum tokens after successful verification.
3. **Session Management**
   - Use Laravel Sanctum for SPA token issuance. Configure SPA domain in `SANCTUM_STATEFUL_DOMAINS` to support cookies.
   - Add device metadata (UA, IP) on login for auditing.
4. **Security**
   - Enforce cooldown windows, blocklist disposable numbers, and log verification attempts.
   - Provide optional backup email/password or authenticator app for premium plans.

## 9. React Integration Guidelines
- Consume the Laravel API through a dedicated client module. Handle token refresh (Sanctum cookie or Bearer token) centrally.
- Adopt RTK Query, React Query, or SWR for data fetching with optimistic updates and caching.
- Mirror API pagination metadata; build reusable table components that accept `data`, `meta`, `filters`.
- Keep form schemas in sync between Laravel FormRequest rules and React validators (e.g. Zod or Yup). Export validation metadata via an endpoint if necessary.
- Implement feature flags and gradually roll out new API-powered screens while verifying parity with the existing Inertia views.

## 10. Deployment & DevOps Checklist
- Docker: Update `docker-compose.yml` to include Redis and Meilisearch; ensure worker containers are defined for queues.
- CI/CD: Add workflows running `phpunit`, `phpstan` (level 8+), `pint`, and frontend tests (`npm test`). Cache Composer/npm dependencies between runs.
- Environment: Keep `.env.example` aligned, key placeholders for `SANCTUM_STATEFUL_DOMAINS`, Redis, OTP provider credentials.
- Migrations: Use zero-downtime deployment steps (e.g. `php artisan down --render`) only when necessary; prefer additive migrations and background data backfills.

## 11. Testing Priorities
- Feature tests for passport search, location filtering, subscription updates, and OTP flows.
- Unit tests for service classes (cache invalidation, query scopes).
- Contract tests to assert JSON schema stability (use `spatie/laravel-json-api-paginate` or custom assertions).
- Load tests (k6, artillery) for passport lookup endpoints to validate caching and DB tuning.

## 12. Next Steps
- Scaffold `routes/api.php` with versioned groups and basic passport endpoints.
- Extract search logic from `PassportSearchController` into a dedicated action and reuse it from both web and API layers during transition.
- Harden `RateLimitMiddleware` or replace it with framework-native rate limiting.
- Plan database migrations for indexes and phone auth fields before rolling out the React client.
- Stand up Redis and, if needed, Meilisearch locally via Docker to develop caching and search in parity with production.

Keep AGENTS.md updated as the architecture evolves so contributors have a single source of truth for API and performance conventions.
