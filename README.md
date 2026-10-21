![alt text](https://github.com/NegusNati/passport.et/blob/main/PASSPORT1-webp.webp?raw=true)
# [Passport.ET](https://passport.et/) + Containerized Deployment 🐳


A simple way to look if your passport is ready to collect and which day of the week you should collect. It is Role & Permission based site with PDF to SQLite(MarianaDB for prod) parsing, Queued jubs for PDF parsing in the background, Automated daily updates, Payment integration with [Chapa payment gateway](https://chapa.co)!


- Current Demo  

https://github.com/NegusNati/passport-site/assets/84500378/7acb2146-307e-44dc-a599-2376942c2370

- Rate Limmiting
  
https://github.com/NegusNati/passport-site/assets/84500378/3564eea0-9faf-4a6a-b2f8-62ced124da09

- Landing Page    
<img src="https://github.com/NegusNati/Airbnb-Experiences-Clone/assets/84500378/d26ace89-7e34-4cab-a540-ffeffc67068d"  >

- Pricing Section
 <img src="https://github.com/NegusNati/Airbnb-Experiences-Clone/assets/84500378/0b3c62af-62d3-4827-b7e5-913e2ba8a317"  >

- Payment Integration with Chapa
 <img src="https://github.com/NegusNati/Airbnb-Experiences-Clone/assets/84500378/e25b52c5-25e8-4b7d-84b8-2ca1558d7548">

- Dashboard Page
 <img src="https://github.com/NegusNati/Airbnb-Experiences-Clone/assets/84500378/e23679c6-dfba-47b1-9263-d7f09be8aacc">
 
 - Search By Name or Request Number
 <img src="https://github.com/NegusNati/Airbnb-Experiences-Clone/assets/84500378/b2babbbb-213f-4c0c-9d11-9f4002646a12">

 - Permission based Admin routes for data entery
 <img src="https://github.com/NegusNati/Airbnb-Experiences-Clone/assets/84500378/65b04087-cbd9-4ce8-ab25-9c85cbfb532b">

 - Searching Only by Name or by any related feilds
 <img src="https://github.com/NegusNati/Airbnb-Experiences-Clone/assets/84500378/e342046b-aa5a-4871-ad06-3e62999c5583">

  - Detailed info about your Passport and where to pick it up
 <img src="https://github.com/NegusNati/Airbnb-Experiences-Clone/assets/84500378/8d8ec474-db7f-41ac-b428-19988054924d">

 - Paginated list of Latest Passport ready for pick up 
 <img src="https://github.com/NegusNati/Airbnb-Experiences-Clone/assets/84500378/ae3132ca-28f6-43cc-b342-1e2322eb18b7">


## API v1 (work in progress)
- Versioned API routes now live under `src/routes/api.php` and are mounted at `/api/v1/*`.
- Stub controllers in `App\Http\Controllers\Api\V1` currently return placeholder JSON while the domain layer is extracted.
- Apply the `api` middleware stack plus the new `throttle:api.v1.default` limiter to keep behaviour isolated from the legacy Inertia flows.

## Production deployment on the shared VPS

PHP images and CI are pinned to **PHP 8.3.36**, fixing CVE-2026-109618 and
CVE-2026-109619. Composer supports the patched 8.3 branch (`~8.3.36`).
The October 21, 2026 public header check advertised vulnerable PHP **8.3.33**.
Repository changes alone do not patch that running server. See the
[security verification record](docs/security/php-2026-10-21.md) for evidence,
validation limitations, and post-deployment checks.

Pushing to `main` first runs CI. Only a successful **push** CI run from this
repository can deploy its exact tested commit. Pull requests and pushes to `api`
do not deploy. Deployment requests are serialized, and a server-side lock prevents
overlapping manual/workflow deployments. A tested commit is skipped if `main` has
already advanced; the newer commit must pass its own CI before deployment.

The workflow runs `scripts/deploy.sh` in `~/laraveldocker.prod` with the explicit
Compose project **`laraveldockerprod`**. It preserves `laravel-nginx`, the private
`laraveldockerprod_laravel` network, and the external `passport-frontend_web`
network. It never restarts Nginx Proxy Manager, other projects, MariaDB, or Redis.
It does not run Docker pruning, orphan removal, stack-wide `down`, or scaling.

### Requirements

- Existing GitHub Actions secrets: `VPS_HOST`, `VPS_USERNAME`, `VPS_SSH_KEY`,
  and `SUDO_PASSWORD` (optional for root/passwordless sudo).
- Linux VPS with Bash, Git, `flock`, GNU `timeout`, Docker Engine API 1.42+
  (Docker 23+), and Docker Compose v2+. The Docker CLI must support `stop --signal`
  and `-t`; this is checked before maintenance independently of the daemon API.
- An existing production checkout on branch `main`, with no tracked source
  changes. Generated `src/public/build/` output is ignored and is not versioned.
  Server-only untracked files, including ignored files, may remain in place;
  deployment refuses incoming file/directory collisions before maintenance.
  The root `.env`, `src/.env`, production certificates, and existing volumes
  remain server-managed.
- The existing app, PHP, MariaDB, and Redis containers must be running and have
  the expected `laraveldockerprod` project/service labels. Horizon must exist with
  matching labels, but may already be stopped after a failed deployment.
- Both existing networks must exist, and `laravel-nginx` must already be attached
  to `passport-frontend_web`. Missing infrastructure requires manual investigation,
  not automatic creation or renaming.

### Deployment behavior and downtime

1. Validate the commit, tracked source, incoming path collisions, container
   ownership, and infrastructure. Dry-run Git's checkout rules without changing
   live files. Never stash, reset, or clean the server checkout automatically.
2. Put Passport in maintenance mode with a pre-rendered 503 page that still works
   while Composer files change. Gracefully terminate Horizon and stop only
   its validated container, explicitly using `SIGTERM` and a 360-second timeout
   for the existing 300-second import jobs, including on the first deployment.
   The stop command uses portable `-t 360`, compatible with both `--time` and
   `--timeout` CLI variants.
3. Fast-forward to the tested commit and build `app`, `php`, and `horizon` images
   serially to reduce shared-host load. PHP builds use `--pull` to refresh their
   pinned base images; there is no fallback to an older PHP image.
4. Check CLI and FPM versions in both newly built PHP images before installing
   dependencies. Explicitly install production Composer dependencies using the PHP runtime
   image and build assets using the npm helper. The Composer/npm/Artisan helpers
   are opt-in (`tools` profile), never long-running default services. They publish
   no development ports. CI and the production asset helper both use Node 22.
5. Update PHP, refresh Laravel configuration/route/view caches, then update and
   reload Passport Nginx so it resolves PHP's current address. All application
   `up` operations use `--no-deps --no-build`.
6. Start Horizon, check database/Redis/Horizon readiness, leave maintenance mode,
   and check the internal HTTP `/up` endpoint. Checks retry with a timeout and
   fail the workflow instead of assuming a fixed sleep means success. Both
   running PHP containers must also pass `scripts/check-php-runtime.php`, which
   rejects PHP below 8.3.36, prereleases, other branches, or mismatched CLI/FPM builds.

**This is not zero downtime.** Source, dependencies, and assets are bind-mounted,
so Passport stays in maintenance mode during builds and installation. Cached
builds may be quick; image/dependency changes and active imports can extend the
interruption to several minutes. Other projects are not deployment targets, but
server-side builds can still consume shared CPU, RAM, and disk.

No database migrations, database upgrades, or data-volume changes run automatically.
Schema changes need a separately reviewed, compatible migration procedure.
The automated `/up` probe checks Passport internally, not Cloudflare or the shared
proxy. After the first deployment, also verify `https://api.passport.et/up` and
the other public sites, without restarting the shared proxy.

### Failure and recovery

Before maintenance starts, a failed preflight leaves the running release alone.
After it starts, a failure stops deployment, reports the previous commit, and
does **not** automatically roll back code, clear maintenance mode, or prune Docker.
Passport may remain in maintenance mode and Horizon may remain stopped.

- Inspect the failed step and Passport's logs privately. Preserve server-local
  changes, credentials, uploads, database files, and volumes.
- Fix the reported cause, then re-run the failed deployment if its tested SHA is
  still the tip of `main`. An already stopped Horizon is supported. If `main`
  advanced, wait for the newer commit's successful CI/deployment instead.
- If PHP or Passport Nginx is not running, restore only those application services
  after checking that source/dependencies are compatible. Do not start the entire
  Compose file or bring down the stack. The deployment preflight intentionally
  refuses to guess how to repair missing infrastructure.
- If a code rollback is needed, prefer a reviewed revert commit through CI.
  There is no automatic database rollback. Do not reset a dirty server checkout.
- Older releases tracked generated CSS/JS. The build-output migration tolerates
  their deletion by Vite and stops tracking them; the asset build regenerates
  them during maintenance. Do not restore old assets or remove server-local
  files to make `git status` empty. A normal build must not dirty tracked source.
  If a real source edit or incoming collision blocks deployment, preserve and
  review that file separately rather than bypassing the guard.

For scoped inspection, run from the production checkout:

```bash
cd ~/laraveldocker.prod
sudo docker compose --project-name laraveldockerprod \
  --project-directory "$PWD" --env-file "$PWD/.env" \
  -f "$PWD/docker-compose.prod.yml" ps
```

Only after repairing the release and verifying readiness, an operator can
explicitly leave maintenance mode:

```bash
sudo docker compose --project-name laraveldockerprod \
  --project-directory "$PWD" --env-file "$PWD/.env" \
  -f "$PWD/docker-compose.prod.yml" exec -T php php artisan up
```

### Deployment regression checks

These checks require Git, Python 3, Ruby's bundled YAML parser, and a PHP CLI for
dependency-free version-policy tests. Deployment commands are mocked; checkout
safety tests also use isolated temporary Git repositories with synthetic history
without invoking `git commit` or changing author configuration.
An optional actual Docker CLI parsing check targets a nonexistent temporary socket;
it does not use a running daemon. The checks do not start containers or contact a server:

```bash
bash -n scripts/deploy.sh
python3 -B -m unittest discover -s scripts/tests -v
```

CI also runs ShellCheck and validates production Compose with dummy database
variables and `--env-file /dev/null`, without loading production secrets.
It separately builds both PHP images, checks their actual CLI/FPM versions, and
runs Composer platform requirements and the application tests on PHP 8.3.36.

## Contributing guideline to `passport.et`

 Thank you for considering contributing to the passport.et project! By participating, you’re helping to improve this project, and your contributions are valued.
 
### Getting Started
##### 1. Fork the Repository
Start by forking the repository to your GitHub account.

##### 2. Clone Your Fork on your machine

 ```bash
    git clone https://github.com/NegusNati/passport.et.git
```
##### 3. Configure .env and docker-compose.yml file.
   - Ensure Redis variables are set (`CACHE_STORE=redis`, `QUEUE_CONNECTION=redis`, `SESSION_DRIVER=redis`, `REDIS_HOST=redis`).
##### 4. Then just build the images and containers 
 ```bash
    docker-compose up -d --build
```
   You can validate the cache infrastructure with `docker compose exec php php artisan redis:ping`.
##### 5. Horizon health check
After workers come up, confirm Horizon is running:
 ```bash
    docker compose exec php php artisan horizon:status
 ```

If you enable Telegram alerts for Horizon failures, remember to set `TELEGRAM_BOT_TOKEN` and `TELEGRAM_CHAT_ID` in your environment. The listener will automatically forward `JobFailed` and long-wait events once your bot is configured.
Install the Telegram notification channel package if you haven’t already:

```bash
docker compose exec php composer require laravel-notification-channels/telegram
```
##### 5. check it at `app.localhost` (if you didn't change it)
##### 6. Submit a Pull Request
Once you've pushed your changes, come back to this repository and submit a pull request. Make sure you include the following in your pull request:

A detailed description of what the PR does.
The issue number (if applicable).
Any additional information that may help with the review process.

### Code Style Guidelines
Please follow the [PSR-12](https://www.php-fig.org/psr/psr-12/) coding standards for PHP code and use [ESLint](https://eslint.org/) rules for JavaScript code.

### Bug Reports and Feature Requests
If you encounter a bug or have a feature request, please open an issue on the repository. Provide as much detail as possible, including steps to reproduce the issue or a description of the requested feature.


## Follow me for updates
 [Telegram](https://t.me/negusnatiChannel)

### Load Testing

Run a k6 smoke test against the API (requires k6 installed locally):

```bash
BASE_URL=http://app.localhost k6 run tests/Performance/PassportLoadTest.js
```

Adjust `BASE_URL` to your staging/prod domain before running a heavier test.
