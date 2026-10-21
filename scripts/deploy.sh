#!/usr/bin/env bash
set -Eeuo pipefail

readonly PROJECT=laraveldockerprod
readonly WEB_NETWORK=passport-frontend_web
readonly HORIZON_CONTAINER="${PROJECT}-horizon-1"
readonly DEPLOY_DIR="${DEPLOY_DIR:-$HOME/laraveldocker.prod}"
readonly DEPLOY_SHA="${DEPLOY_SHA:?Set DEPLOY_SHA to the commit that passed CI}"
readonly HEALTH_ATTEMPTS="${HEALTH_ATTEMPTS:-12}"
readonly HEALTH_INTERVAL="${HEALTH_INTERVAL:-5}"

fail() {
    printf 'Deployment refused: %s\n' "$*" >&2
    exit 1
}

check_worktree() {
    local before=$1 after=$2 worktree_status path parent
    # Vite replaces generated output; only tracked source changes block a deploy.
    worktree_status="$(git status --porcelain --untracked-files=no -- . ':(exclude)src/public/build')"
    [[ -z "$worktree_status" ]] || fail "The server checkout has tracked source changes."

    # Git normally permits overwriting ignored files. Protect those too, including
    # server-managed .env files and symlinks in an incoming path's parent folders.
    git diff --no-renames --diff-filter=A --name-only -z "$before" "$after" |
    while IFS= read -r -d '' path; do
        [[ ! -e "$path" && ! -L "$path" ]] || fail "An incoming path would overwrite a server-local file or directory: $path"
        parent=$path
        while [[ "$parent" = */* ]]; do
            parent=${parent%/*}
            if [[ -L "$parent" || ( -e "$parent" && ! -d "$parent" ) ]]; then
                fail "An incoming path conflicts with a server-local parent: $parent"
            fi
        done
    done || fail "Unable to safely inspect incoming paths."

    # Dry-run Git's own checkout rules without changing the index or live files.
    git read-tree --dry-run -m -u "$before" "$after" ||
        fail "The tested commit cannot be applied without overwriting local changes."
}

[[ "$DEPLOY_DIR" = /* ]] || fail "DEPLOY_DIR must be an absolute path."
[[ "$DEPLOY_SHA" =~ ^[0-9a-f]{40}$ ]] || fail "DEPLOY_SHA must be a full commit SHA."
[[ "$HEALTH_ATTEMPTS" =~ ^[1-9][0-9]?$ ]] || fail "Invalid HEALTH_ATTEMPTS."
[[ "$HEALTH_INTERVAL" =~ ^[0-9]{1,2}$ ]] || fail "Invalid HEALTH_INTERVAL."
for command in git docker flock timeout; do
    command -v "$command" >/dev/null || fail "Missing required command: $command."
done
cd "$DEPLOY_DIR"
[[ -f .env ]] || fail "The existing production .env is missing."

# Do not inherit Compose settings that can select another stack or delete orphans.
unset COMPOSE_FILE COMPOSE_PROJECT_NAME COMPOSE_PROFILES COMPOSE_ENV_FILES
export COMPOSE_REMOVE_ORPHANS=false COMPOSE_IGNORE_ORPHANS=true COMPOSE_PARALLEL_LIMIT=1
export GIT_TERMINAL_PROMPT=0
unset SUDO_PASSWORD
readonly COMPOSE=(
    docker compose --project-name "$PROJECT" --project-directory "$DEPLOY_DIR"
    --env-file "$DEPLOY_DIR/.env" --file "$DEPLOY_DIR/docker-compose.prod.yml"
)

git_dir="$(git rev-parse --absolute-git-dir)"
exec 9>"$git_dir/passport-deploy.lock"
flock -n 9 || fail "Another Passport deployment is already running."

started=false
previous_sha=""
on_exit() {
    local status=$?
    if (( status != 0 )); then
        printf 'Passport deployment failed. No automatic rollback or Docker cleanup was attempted.\n' >&2
        if [[ "$started" = true ]]; then
            printf 'Passport may remain in maintenance mode with Horizon stopped. Follow README recovery steps. Previous commit: %s\n' "$previous_sha" >&2
        fi
    fi
}
trap on_exit EXIT

[[ "$(git branch --show-current)" = main ]] || fail "The server checkout must be on main."
git fetch --no-tags origin refs/heads/main:refs/remotes/origin/main
git cat-file -e "$DEPLOY_SHA^{commit}" || fail "The tested commit is unavailable."
latest_sha="$(git rev-parse refs/remotes/origin/main)"
if [[ "$DEPLOY_SHA" != "$latest_sha" ]]; then
    if git merge-base --is-ancestor "$DEPLOY_SHA" "$latest_sha"; then
        printf 'Skipping a stale deployment; main has advanced beyond %s.\n' "$DEPLOY_SHA"
        exit 0
    fi
    fail "The requested commit is not on origin/main."
fi
previous_sha="$(git rev-parse HEAD)"
git merge-base --is-ancestor "$previous_sha" "$DEPLOY_SHA" || fail "The server checkout has diverged from the tested commit."
check_worktree "$previous_sha" "$DEPLOY_SHA"

# Existing infrastructure is a prerequisite, never something this script starts.
api_version="$(docker version --format '{{.Server.APIVersion}}')"
if [[ ! "$api_version" =~ ^1\.([0-9]+)$ ]]; then
    fail "Unable to verify the Docker Engine API version."
fi
(( BASH_REMATCH[1] >= 42 )) || fail "Docker Engine API 1.42 or newer is required for an explicit stop signal."
# The CLI can be older than the daemon. Check flags before entering maintenance.
stop_help="$(docker stop --help)" || fail "Unable to inspect Docker stop options."
[[ "$stop_help" = *"--signal"* && "$stop_help" = *"-t,"* ]] ||
    fail "The Docker CLI must support docker stop --signal and -t."
docker network inspect "$WEB_NETWORK" >/dev/null || fail "The existing shared web network is missing."
docker network inspect "${PROJECT}_laravel" >/dev/null || fail "The existing private Laravel network is missing."
horizon_running=false
for service in app php horizon mysql redis; do
    name="${PROJECT}-${service}-1"
    [[ "$service" != app ]] || name=laravel-nginx
    state="$(docker inspect --format '{{index .Config.Labels "com.docker.compose.project"}} {{index .Config.Labels "com.docker.compose.service"}} {{.State.Running}}' "$name")"
    if [[ "$service" = horizon && "$state" = "$PROJECT $service false" ]]; then
        continue
    fi
    [[ "$state" = "$PROJECT $service true" ]] || fail "$name must be running and owned by $PROJECT/$service."
    [[ "$service" != horizon ]] || horizon_running=true
done
connected="$(docker inspect --format '{{if index .NetworkSettings.Networks "passport-frontend_web"}}connected{{end}}' laravel-nginx)"
[[ "$connected" = connected ]] || fail "laravel-nginx is not attached to the existing shared web network."
"${COMPOSE[@]}" config --quiet

retry() {
    local label=$1 attempt
    shift
    for (( attempt=1; attempt<=HEALTH_ATTEMPTS; attempt++ )); do
        if timeout 15 "$@" >/dev/null 2>&1; then
            printf 'Healthy: %s\n' "$label"
            return 0
        fi
        if (( attempt < HEALTH_ATTEMPTS )); then
            sleep "$HEALTH_INTERVAL"
        fi
    done
    printf 'Health check failed: %s\n' "$label" >&2
    return 1
}

printf 'Deploying %s (previous commit: %s).\n' "$DEPLOY_SHA" "$previous_sha"
# Source, vendor, and assets are bind-mounted. Pause requests/workers before changing them.
started=true
"${COMPOSE[@]}" exec -T php php artisan down --retry=15 --render=errors::503
if [[ "$horizon_running" = true ]]; then
    # Notify the master even if the old container still has a shell as PID 1.
    terminate_status=0
    "${COMPOSE[@]}" exec -T horizon php artisan horizon:terminate || terminate_status=$?
    # An idle master can exit, taking this exec process with it.
    [[ "$terminate_status" = 0 || "$terminate_status" = 137 || "$terminate_status" = 143 ]] || fail "Unable to terminate Horizon gracefully."
fi
# Override the old PHP image's SIGQUIT as well as the old 10-second grace period.
# -t works with CLIs that name the long option either --time or --timeout.
docker stop --signal SIGTERM -t 360 "$HORIZON_CONTAINER"
git merge --ff-only --no-overwrite-ignore "$DEPLOY_SHA"
[[ "$(git rev-parse HEAD)" = "$DEPLOY_SHA" ]] || fail "The checkout does not match the tested commit."
"${COMPOSE[@]}" config --quiet

for service in app php horizon; do
    if [[ "$service" = app ]]; then
        "${COMPOSE[@]}" build "$service"
    else
        "${COMPOSE[@]}" build --pull "$service"
    fi
done
for service in php horizon; do
    "${COMPOSE[@]}" run --rm --no-deps -T --entrypoint php "$service" scripts/check-php-runtime.php
done
"${COMPOSE[@]}" run --rm --no-deps -T --entrypoint composer php install --no-dev --no-interaction --prefer-dist --optimize-autoloader
"${COMPOSE[@]}" run --rm --no-deps -T npm
"${COMPOSE[@]}" up -d --no-deps --no-build php
"${COMPOSE[@]}" exec -T php php scripts/check-php-runtime.php
"${COMPOSE[@]}" exec -T php php artisan config:cache
"${COMPOSE[@]}" exec -T php php artisan route:clear
"${COMPOSE[@]}" exec -T php php artisan view:clear

# shellcheck disable=SC2016 # PHP variables must reach the container unexpanded.
retry database "${COMPOSE[@]}" exec -T php php -r 'require "vendor/autoload.php"; $app = require "bootstrap/app.php"; $app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap(); Illuminate\Support\Facades\DB::select("SELECT 1");'
retry Redis "${COMPOSE[@]}" exec -T php php artisan redis:ping

"${COMPOSE[@]}" up -d --no-deps --no-build app
"${COMPOSE[@]}" exec -T app nginx -t
"${COMPOSE[@]}" exec -T app nginx -s reload
"${COMPOSE[@]}" up -d --no-deps --no-build horizon
"${COMPOSE[@]}" exec -T horizon php scripts/check-php-runtime.php
retry Horizon "${COMPOSE[@]}" exec -T horizon php artisan horizon:status
"${COMPOSE[@]}" exec -T php php artisan up
retry HTTP "${COMPOSE[@]}" exec -T app wget -q -T 5 -O /dev/null http://127.0.0.1/up
printf 'Passport deployment succeeded: %s.\n' "$DEPLOY_SHA"
