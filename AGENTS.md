# AGENTS

This document summarizes the strategy for turning the existing Laravel/Inertia project inside `src/` into a high-performance API platform that powers an external React client. It distills the current codebase, highlights bottlenecks, and presents best practices for API design, performance tuning, search, and phone-number-first authentication.

## 1. Current State Snapshot
- Web-only routing lives in `src/routes/web.php` and most controllers (for example `src/app/Http/Controllers/PassportSearchController.php`) return Inertia views. There is no `routes/api.php` yet.
- Core data is stored in `p_d_f_to_s_q_lites`, surfaced through `App\Models\PDFToSQLite` with wide-open `$guarded = []`, and queried directly in controllers with `like` filters and `simplePaginate`.
- Caching already appears in controllers (`Cache::remember` for passport lists, city lookup) and in the closure front page count, but cache invalidation is missing.
- `RateLimitMiddleware` implements per-plan limits on top of `Cache`, but does not guard against unauthenticated visitors (null `$user`).
- Auth still relies on the default Laravel email/password stack with Inertia-driven UI.

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
