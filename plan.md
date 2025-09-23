# API Modernization & Performance Plan

This plan decomposes the migration from the current Laravel/Inertia experience to a versioned, high-performance API that powers an external React client while keeping legacy Inertia flows untouched during the rollout. Every command assumes the Docker stack defined in `docker-compose.yml`; use `docker compose exec php <command>` for PHP/Laravel tasks, `docker compose run --rm composer <command>` for Composer utilities, and `docker compose run --rm npm <command>` for Node workflows unless noted otherwise.

---

## Phase 0 – Baseline Recon & Alignment
**Goal:** Build a precise picture of the existing search, subscription, and authentication flows as regression targets for the API layer without changing behaviour.

**Todo List**
- [ ] Confirm repository health: run `docker compose run --rm composer validate`, `docker compose run --rm npm "npm run lint"`, and `docker compose exec php php artisan test` (or `phpunit`) to document current pass/fail state.
- [ ] Inventory all controllers that touch passports (`PassportSearchController`, `FilterByCityController`, `SubscriptionController`) and note which Inertia views they render.
- [ ] Extract all database columns used for passports (request number, name fields, `dateOfPublish`, `location`) from the migration at `src/database/migrations/2024_06_06_084851_create_p_d_f_to_s_q_lites_table.php` and capture current indexes.
- [ ] Document existing caching touchpoints (Cache::remember calls, cache keys) and rate limiting logic in `App\Http\Middleware\RateLimitMiddleware`.
- [ ] Trace authentication stack: guards, providers, and middleware from `config/auth.php`, Sanctum/Passport usage (if any), and login routes in `routes/web.php`.
- [ ] Summarize findings in `AGENTS.md` under a new **Current API Targets** subsection for future reference.

**Acceptance Criteria**
- An evidence-based summary is added to `AGENTS.md` describing data model, controller entrypoints, cache usage, and auth dependencies.
- Baseline Dockerized command outputs (tests, linters) are captured in the engineering log so later phases can compare regressions.
- No production or source code is modified beyond documentation during this phase.

---

## Phase 1 – API Infrastructure & Versioning
**Goal:** Scaffold the API surface area without disturbing legacy Inertia routes, establishing namespaces, routing, and shared response conventions.

**Todo List**
- [ ] Introduce `routes/api.php` with a versioned group (`/v1`, name prefix `api.v1.`) and middleware stack (`api`, auth, throttle) mirroring Laravel best practices.
- [ ] Register new controllers under `App/Http/Controllers/Api/V1` with base namespace and abstract controller (e.g. `ApiController`) for shared helpers.
- [ ] Enable Laravel’s `RouteServiceProvider` API rate limiting via `RateLimiter::for()` to replace custom middleware in API context while leaving existing web limiter in place.
- [ ] Set up PSR-4 autoloading for new `App\Actions`, `App\Domain`, and `App\Http\Resources` namespaces in `composer.json` (run `docker compose run --rm composer dump-autoload`).
- [ ] Generate placeholder resource classes (`PassportResource`, `PassportCollection`) and Form Requests (`SearchPassportRequest`) with TODO markers for later phases.
- [ ] Update `AGENTS.md` and `README.md` to call out the new API entrypoint and explain that legacy Inertia routes remain untouched.

**Acceptance Criteria**
- `docker compose exec php php artisan route:list --path=api` shows versioned API routes with JSON defaults while web routes are unchanged.
- The API controllers respond with stub JSON (e.g. `{ "status": "not implemented" }`) to confirm wiring, returning HTTP 501 or 200 with warning headers.
- Automated tests or smoke checks confirm that `/passport` Inertia route still renders without regression.
- Documentation reflects how to run the API inside Docker and the boundary between `/web.php` and `/api.php`.

---

## Phase 2 – Domain & Service Layer Extraction
**Goal:** Move search and filtering logic out of web controllers into reusable actions/services that both web and API layers can consume without duplicating queries.

**Todo List**
- [ ] Create `app/Domain/Passport/Models/Passport.php` (or reuse `PDFToSQLite` via alias) with guarded fillable properties, casting, and dedicated query scopes (`scopeFilter`, `scopeSort`, `scopePublishedBetween`).
- [ ] Introduce an action/service class (`App\Actions\Passport\SearchPassportsAction`) encapsulating request number, name, location, and publish date filters with dependency-injected `CacheRepository` for optional caching.
- [ ] Add DTOs (e.g. `PassportSearchParams`, `PassportResult`) to decouple internal models from API payloads.
- [ ] Wire the Inertia `PassportSearchController` to resolve the new action so behaviour stays identical while enabling reuse.
- [ ] Define centralized filter metadata (`PassportFilters` constant/config) for allowed query parameters and default sort to prevent injection.
- [ ] Update or add unit tests covering the action, query scopes, and edge cases (empty filters, partial names, invalid dates) using `docker compose exec php php artisan test --testsuite=Unit` (or Pest equivalent).

**Acceptance Criteria**
- Legacy Inertia endpoints rely on the new action/service without behaviour changes (confirmed via existing browser tests or manual verification).
- Query scope unit tests cover combinations of request number, name, location, and publish date filters with deterministic sample data fixtures.
- Mass-assignment protection is enforced (`$fillable` or `$guarded` tightened) and existing seed/import scripts still succeed.
- Documentation in `AGENTS.md` reflects the new domain structure and explicitly notes shared usage between web and API layers.

---

## Phase 3 – Passport & Location API Endpoints
**Goal:** Deliver production-ready JSON endpoints that power the React table with dynamic filters while keeping pagination and caching consistent.

**Todo List**
- [ ] Implement `GET /api/v1/passports` returning a paginated `PassportCollection` with filter params (`request_number`, `full_name`, `location`, `published_after`, `published_before`) mapped from query strings.
- [ ] Provide `GET /api/v1/passports/{id}` for detailed view parity, reusing the action/service and `PassportResource` serializer.
- [ ] Add `GET /api/v1/locations` (and optional autocomplete endpoint) leveraging cached city lists with cache tags for invalidation.
- [ ] Ensure pagination uses `LengthAwarePaginator` with `links` and `meta` keys matching the React client expectations; include `per_page`, `current_page`, `total`, and `has_more`.
- [ ] Normalize name searches server-side (trim, uppercase, diacritics) and request numbers (strip whitespace) before querying.
- [ ] Add feature tests covering success scenarios, empty results, validation failures, and unauthorized requests (where applicable) via `docker compose exec php php artisan test --testsuite=Feature`.

**Acceptance Criteria**
- Automated feature tests demonstrate filtering by request number, partial name, location, and publish date, matching fixtures or seeded test data.
- API responses conform to a documented JSON schema stored under `tests/Contracts` or similar, validated via schema assertions.
- The React client (or a mocked consumer) can fetch data with filters and receive consistent pagination metadata.
- Cache invalidation strategy is documented and implemented (e.g. tags cleared on passport import jobs).

---

## Phase 4 – API Authentication & Authorization
**Goal:** Extend or refine the existing authentication stack to issue secure tokens/cookies for API consumers while accommodating future phone-first auth enhancements.

**Todo List**
- [ ] Audit the current auth configuration (guards, providers, Sanctum/Passport usage) and summarize options for API access (Sanctum SPA tokens vs. personal access tokens vs. JWT).
- [ ] Prototype an API login flow that reuses the existing credentials (email/password) and issues a token usable by the React SPA.
- [ ] Expose endpoints `/api/v1/auth/login`, `/api/v1/auth/logout`, `/api/v1/auth/me` with proper rate limiting and error handling.
- [ ] Evaluate feasibility of phone-number-first authentication; gather requirements from product owner (questions around OTP provider, phone normalization rules, user data model changes).
- [ ] Update middleware stacks so passport endpoints require auth scopes/abilities when mandated (e.g. premium-only data) and gracefully handle anonymous access when allowed.
- [ ] Add feature tests simulating login, token refresh, and protected endpoint access, covering both cookie-based and bearer token flows (`docker compose exec php php artisan test --group=auth`).

**Acceptance Criteria**
- Chosen authentication mechanism is documented with sequence diagrams and configuration steps in `AGENTS.md`; `.env.example` gains the necessary Docker-aware variables (e.g. `SANCTUM_STATEFUL_DOMAINS=app.localhost`).
- Security review checklist (CSRF for cookies, XSS headers, token TTL) is completed and stored alongside the plan.
- Tests confirm that authenticated users can access protected API routes and receive HTTP 401 when unauthenticated.
- Open questions for phone-based auth are explicitly captured for stakeholder feedback before implementation begins.

---

## Phase 5 – Redis Caching & Rate Limiting
**Goal:** Introduce Redis to handle caching, queues, and rate limiting, replacing ad-hoc Cache usage and enabling efficient invalidation.

**Todo List**
- [ ] Add a `redis` service to both `docker-compose.yml` and `docker-compose.prod.yml`, with environment variables (`REDIS_HOST`, `REDIS_PASSWORD`) propagated to `.env` and `.env.example`.
- [ ] Configure Laravel cache, session, queue, and rate limiting stores to use Redis in `config/cache.php`, `config/queue.php`, and `App\Providers\RouteServiceProvider`.
- [ ] Refactor cache usages (`Cache::remember`) to leverage tags (`Cache::tags(['passports'])`) and centralized key naming constants (`App\Support\CacheKeys`).
- [ ] Replace `RateLimitMiddleware` with Laravel’s `RateLimiter` definitions using Redis backend and guard against unauthenticated users (IP-based fallback).
- [ ] Implement observers or events to clear relevant cache tags after passport imports or updates.
- [ ] Add smoke tests or artisan commands verifying Redis connectivity inside containers (e.g. `docker compose exec php php artisan cache:clear`, `php artisan tinker` connection checks).

**Acceptance Criteria**
- Local and CI Docker environments start with Redis running alongside existing services; `docker compose exec php php artisan tinker --execute='cache()->getStore() instanceof Illuminate\\Cache\\RedisStore'` returns `true`.
- Cache invalidation works end-to-end (update/import triggers visible cache bust in tests or manual verification).
- Rate limiting rejects excessive anonymous and authenticated requests with proper headers while respecting plan types.
- Documentation describes Redis setup, fallback strategy, and operational considerations (flush commands, monitoring) within the Docker context.

---

## Phase 6 – Quality Gates, Monitoring & Rollout
**Goal:** Ensure the new API surface is production-ready with automated coverage, performance baselines, and a migration path for the React client.

**Todo List**
- [ ] Extend CI pipelines (GitHub Actions) to run API feature tests, `phpstan`, `laravel pint`, and front-end contract tests on every push using Docker-based jobs or matching container images.
- [ ] Add load testing scripts (k6 or artillery) targeting `/api/v1/passports` to validate response times with Redis caching enabled; run via Dockerized tooling (`docker compose run --rm k6 run scripts/passports.js` if a k6 service is added).
- [ ] Configure logging/monitoring (Laravel Telescope, Sentry, or preferred APM) for API routes and document alert thresholds.
- [ ] Draft a rollout checklist covering environment variable updates, database migration order (indexes, phone auth columns), and feature flag toggles for React adoption.
- [ ] Plan and document fallback/rollback strategies should the API deployment fail (switch to legacy endpoints, cached responses, etc.).
- [ ] Update `README.md` with deployment steps, including cache warm-up (`docker compose exec php php artisan config:cache`), route caching, and index creation commands.

**Acceptance Criteria**
- CI pipeline badges or logs show all quality gates passing; failing tests block merges by policy.
- Load testing results meet agreed SLAs (e.g. P95 latency under 300ms for cached searches) and are recorded in the repository.
- Monitoring dashboards or configuration notes are accessible to the team with clear runbooks for common incidents.
- Rollout plan is approved by stakeholders and includes explicit criteria for deprecating the Inertia endpoints once the React client stabilizes.

---

## Open Questions for Stakeholders
- Should premium vs guest access remain enforced for the new API, and how should rate limits differ per plan when anonymous access is allowed? Answer: it should be ennforced, but a relaxed version of it since we don't have full payment integration yet
- Which OTP provider (if any) is preferred for phone-number-first authentication, and are there compliance requirements (GDPR, local telecom regulations)? Answer: there will be no opt for now, just regiter and login 
- Are there legacy clients that depend on the current web responses (e.g. scrapers) that need parallel support or redirects when API launches? Answer: no
- What analytics or tracking must be preserved when migrating traffic from Inertia pages to the React SPA? Answer: None

Answering these questions early will reduce rework in Phases 4–6.

