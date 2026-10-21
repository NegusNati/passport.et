"""Exercise deployment commands without contacting GitHub, Docker, or a server."""

import base64
import json
import os
from pathlib import Path
import subprocess
import tempfile
import unittest

from test_configuration import read_yaml


ROOT = Path(__file__).resolve().parents[2]
TARGET = "a" * 40
PREVIOUS = "b" * 40

MOCK = r'''#!/usr/bin/env python3
import json
import os
from pathlib import Path
import subprocess
import sys

tool = Path(sys.argv[0]).name
args = sys.argv[1:]
scenario = os.environ["MOCK_SCENARIO"]
state_path = Path(os.environ["MOCK_STATE"])
state = json.loads(state_path.read_text()) if state_path.exists() else {}
with open(os.environ["MOCK_LOG"], "a") as log:
    log.write(json.dumps({
        "tool": tool, "args": args,
        "orphans": os.environ.get("COMPOSE_REMOVE_ORPHANS"),
        "parallel": os.environ.get("COMPOSE_PARALLEL_LIMIT"),
        "profiles": os.environ.get("COMPOSE_PROFILES"),
        "has_sudo_password": "SUDO_PASSWORD" in os.environ,
    }) + "\n")

def finish(code=0, output=""):
    state_path.write_text(json.dumps(state))
    if output:
        print(output)
    sys.exit(code)

if tool == "flock":
    finish(1 if scenario == "locked" else 0)
if tool == "sleep":
    finish()
if tool == "timeout":
    sys.exit(subprocess.run(args[1:], check=False).returncode)
if tool == "id":
    assert args == ["-u"]
    finish(output=os.environ.get("MOCK_UID", "0"))
if tool == "sudo":
    if "-S" in args:
        assert args[:4] == ["-S", "-p", "", "--"]
        assert sys.stdin.readline().rstrip("\n") == os.environ["SUDO_PASSWORD"]
        command = args[4:]
    else:
        assert args[:2] == ["-n", "--"]
        command = args[2:]
    sys.exit(subprocess.run(command, check=False).returncode)
if tool == "git":
    if args == ["rev-parse", "--absolute-git-dir"]:
        finish(output=os.environ["DEPLOY_DIR"] + "/.git")
    if args == ["branch", "--show-current"]:
        finish(output="other" if scenario == "branch" else "main")
    if args[:2] == ["status", "--porcelain"]:
        finish(output=" M user-file" if scenario == "dirty" else "")
    if args[0] == "fetch":
        finish(1 if scenario == "fetch" else 0)
    if args[0] == "cat-file":
        finish(1 if scenario == "missing_commit" else 0)
    if args == ["rev-parse", "refs/remotes/origin/main"]:
        finish(output=("c" if scenario in ("stale", "unrelated") else "a") * 40)
    if args == ["rev-parse", "HEAD"]:
        finish(output=("a" if state.get("merged") else "b") * 40)
    if args[:2] == ["merge-base", "--is-ancestor"]:
        finish(1 if scenario in ("diverged", "unrelated") else 0)
    if args[:2] == ["merge", "--ff-only"]:
        if scenario == "merge":
            finish(1)
        state["merged"] = True
        finish()
if tool == "docker":
    if args == ["version", "--format", "{{.Server.APIVersion}}"]:
        finish(output="1.41" if scenario == "old_docker" else "1.47")
    if args[0] == "stop":
        assert args == ["stop", "--signal", "SIGTERM", "--timeout", "360", "laraveldockerprod-horizon-1"]
        finish(1 if scenario == "stop" else 0)
    if args[:2] == ["network", "inspect"]:
        finish(1 if scenario == "network" else 0)
    if args[0] == "inspect":
        name = args[-1]
        if "NetworkSettings" in args[2]:
            finish(output="" if scenario == "disconnected" else "connected")
        service = "app" if name == "laravel-nginx" else name.split("-")[-2]
        if scenario == "missing_database" and service == "mysql":
            finish(1)
        project = "another-project" if scenario == "ownership" else "laraveldockerprod"
        running = "false" if (scenario == "stopped_database" and service == "mysql") or (scenario == "stopped_horizon" and service == "horizon") else "true"
        finish(output=f"{project} {service} {running}")
    if args[0] == "compose":
        expected = [
            "compose", "--project-name", "laraveldockerprod",
            "--project-directory", os.environ["DEPLOY_DIR"],
            "--env-file", os.environ["DEPLOY_DIR"] + "/.env",
            "--file", os.environ["DEPLOY_DIR"] + "/docker-compose.prod.yml",
        ]
        assert args[:9] == expected, args
        assert os.environ["COMPOSE_REMOVE_ORPHANS"] == "false"
        assert os.environ["COMPOSE_PARALLEL_LIMIT"] == "1"
        assert "COMPOSE_PROFILES" not in os.environ
        operation = args[9:]
        if operation == ["config", "--quiet"]:
            finish(1 if scenario == "config" else 0)
        if operation[0] == "build":
            assert operation[1:] in (["app"], ["--pull", "php"], ["--pull", "horizon"])
            finish(1 if scenario == "build" and operation[-1] == "php" else 0)
        if operation[0] == "run":
            if operation[-1] == "scripts/check-php-runtime.php":
                assert operation[:6] == ["run", "--rm", "--no-deps", "-T", "--entrypoint", "php"]
                assert operation[-2] in ("php", "horizon")
                finish(1 if scenario == "old_" + operation[-2] + "_image" else 0)
            assert operation in (
                ["run", "--rm", "--no-deps", "-T", "--entrypoint", "composer", "php", "install", "--no-dev", "--no-interaction", "--prefer-dist", "--optimize-autoloader"],
                ["run", "--rm", "--no-deps", "-T", "npm"],
            )
            helper = "composer" if "--entrypoint" in operation else "npm"
            finish(1 if scenario == helper else 0)
        if operation[0] == "up":
            assert operation[1:4] == ["-d", "--no-deps", "--no-build"]
            assert operation[4:] in (["php"], ["app"], ["horizon"])
            finish(1 if scenario == "up" and operation[-1] == "php" else 0)
        if operation[0] == "exec":
            assert operation[1] == "-T"
            assert operation[2] in ("php", "app", "horizon")
            if operation[-1] == "scripts/check-php-runtime.php":
                assert operation[2] in ("php", "horizon")
                finish(1 if scenario == "old_" + operation[2] + "_container" else 0)
            probe = None
            if "SELECT 1" in operation[-1]:
                probe = "database"
            elif "redis:ping" in operation:
                probe = "redis"
            elif "horizon:status" in operation:
                probe = "horizon"
            elif operation[-1] == "http://127.0.0.1/up":
                probe = "http"
            if probe:
                count = state.get(probe, 0)
                state[probe] = count + 1
                finish(1 if scenario == probe or (scenario == "transient" and count == 0) else 0)
            if operation[2:] in (
                ["php", "php", "artisan", "down", "--retry=15", "--render=errors::503"],
                ["php", "php", "artisan", "config:cache"],
                ["php", "php", "artisan", "route:clear"],
                ["php", "php", "artisan", "view:clear"],
                ["php", "php", "artisan", "up"],
                ["horizon", "php", "artisan", "horizon:terminate"],
                ["app", "nginx", "-t"],
                ["app", "nginx", "-s", "reload"],
            ):
                if operation[-1] == "horizon:terminate":
                    finish({"terminate": 1, "terminate_exit": 137}.get(scenario, 0))
                finish(1 if scenario == "reload" and operation[-1] == "reload" else 0)
raise AssertionError(f"Unexpected mock command: {tool} {args}")
'''


class DeploymentTests(unittest.TestCase):
    def setUp(self):
        self.temp = tempfile.TemporaryDirectory(prefix="passport-deploy-test-")
        self.addCleanup(self.temp.cleanup)
        self.home = Path(self.temp.name)
        self.directory = self.home / "laraveldocker.prod"
        self.directory.mkdir()
        (self.directory / ".git").mkdir()
        (self.directory / ".env").write_text("MYSQL_DATABASE=test_only\n")
        self.bin = self.directory / "bin"
        self.bin.mkdir()
        mock = self.bin / "mock"
        mock.write_text(MOCK)
        mock.chmod(0o755)
        for command in ("git", "docker", "flock", "timeout", "sleep", "id", "sudo"):
            (self.bin / command).symlink_to(mock)
        self.log = self.directory / "commands.jsonl"
        self.environment = {
            **os.environ,
            "PATH": str(self.bin) + os.pathsep + os.environ["PATH"],
            "HOME": str(self.home),
            "DEPLOY_DIR": str(self.directory),
            "DEPLOY_SHA": TARGET,
            "HEALTH_ATTEMPTS": "2",
            "HEALTH_INTERVAL": "0",
            "MOCK_LOG": str(self.log),
            "MOCK_STATE": str(self.directory / "state.json"),
            "COMPOSE_FILE": "/unrelated/compose.yml",
            "COMPOSE_PROJECT_NAME": "another-project",
            "COMPOSE_PROFILES": "unrelated",
            "COMPOSE_REMOVE_ORPHANS": "true",
        }

    def run_deploy(self, scenario="success", bootstrap=False, **environment):
        command = ["bash", str(ROOT / "scripts/deploy.sh")]
        if bootstrap:
            workflow = read_yaml(".github/workflows/deploy.yml")
            wrapper = workflow["jobs"]["deploy"]["steps"][2]["with"]["script"]
            payload = base64.b64encode((ROOT / "scripts/deploy.sh").read_bytes()).decode()
            wrapper = wrapper.replace("${{ steps.script.outputs.payload }}", payload)
            command = ["sh", "-c", wrapper]
        result = subprocess.run(
            command,
            env={**self.environment, "MOCK_SCENARIO": scenario, **environment},
            text=True, capture_output=True, timeout=30, check=False,
        )
        self.calls = [
            json.loads(line) for line in self.log.read_text().splitlines()
        ] if self.log.exists() else []
        self.operations = [
            call["args"][9:] for call in self.calls
            if call["tool"] == "docker" and call["args"][0] == "compose"
        ]
        # Every scenario must remain scoped, including failure paths.
        for call in self.calls:
            if call["tool"] == "docker":
                self.assertIn(call["args"][0], ("version", "network", "inspect", "compose", "stop"))
                if call["args"][0] == "stop":
                    self.assertEqual(call["args"], ["stop", "--signal", "SIGTERM", "--timeout", "360", "laraveldockerprod-horizon-1"])
                self.assertEqual(call["orphans"], "false")
                self.assertEqual(call["parallel"], "1")
                self.assertIsNone(call["profiles"])
                self.assertFalse(call["has_sudo_password"])
                self.assertNotIn("--remove-orphans", call["args"])
                self.assertNotIn("--scale", call["args"])
            if call["tool"] == "git":
                self.assertNotIn(call["args"][0], ("reset", "clean", "checkout"))
        return result

    def assert_no_release_changes(self):
        self.assertFalse(any(op[0] in ("stop", "build", "run", "up", "exec") for op in self.operations))
        self.assertFalse(any(call["tool"] == "git" and call["args"][0] == "merge" for call in self.calls))
        self.assertFalse(any(call["tool"] == "docker" and call["args"][0] == "stop" for call in self.calls))

    def test_success_is_scoped_and_ordered(self):
        result = self.run_deploy()
        self.assertEqual(result.returncode, 0, result.stderr)
        self.assertIn("deployment succeeded", result.stdout)
        self.assertIn(PREVIOUS, result.stdout)
        self.assertEqual([op for op in self.operations if op[0] == "build"],
                         [["build", "app"], ["build", "--pull", "php"], ["build", "--pull", "horizon"]])
        checks = [op for op in self.operations if op[-1] == "scripts/check-php-runtime.php"]
        self.assertEqual(checks, [
            ["run", "--rm", "--no-deps", "-T", "--entrypoint", "php", "php", "scripts/check-php-runtime.php"],
            ["run", "--rm", "--no-deps", "-T", "--entrypoint", "php", "horizon", "scripts/check-php-runtime.php"],
            ["exec", "-T", "php", "php", "scripts/check-php-runtime.php"],
            ["exec", "-T", "horizon", "php", "scripts/check-php-runtime.php"],
        ])
        composer = next(op for op in self.operations if "--entrypoint" in op and "composer" in op)
        self.assertLess(self.operations.index(checks[1]), self.operations.index(composer))
        self.assertEqual([op for op in self.operations if op[0] == "up"], [
            ["up", "-d", "--no-deps", "--no-build", "php"],
            ["up", "-d", "--no-deps", "--no-build", "app"],
            ["up", "-d", "--no-deps", "--no-build", "horizon"],
        ])
        down = self.operations.index(["exec", "-T", "php", "php", "artisan", "down", "--retry=15", "--render=errors::503"])
        terminate = self.operations.index(["exec", "-T", "horizon", "php", "artisan", "horizon:terminate"])
        build = self.operations.index(["build", "app"])
        self.assertLess(down, terminate)
        self.assertLess(terminate, build)
        stop_call = next(i for i, call in enumerate(self.calls) if call["tool"] == "docker" and call["args"][0] == "stop")
        merge_call = next(i for i, call in enumerate(self.calls) if call["tool"] == "git" and call["args"][0] == "merge")
        self.assertLess(stop_call, merge_call)
        self.assertLess(
            self.operations.index(["up", "-d", "--no-deps", "--no-build", "php"]),
            self.operations.index(["exec", "-T", "app", "nginx", "-s", "reload"]),
        )
        self.assertEqual(self.operations[-1][-1], "http://127.0.0.1/up")
        merge = next(call for call in self.calls if call["tool"] == "git" and call["args"][0] == "merge")
        self.assertEqual(merge["args"], ["merge", "--ff-only", TARGET])
        self.assertEqual(self.calls[1]["tool"], "flock")

    def test_preflight_failures_never_change_the_release(self):
        for scenario in (
            "locked", "branch", "dirty", "fetch", "missing_commit", "diverged",
            "unrelated", "network", "ownership", "missing_database",
            "stopped_database", "disconnected", "config", "old_docker",
        ):
            with self.subTest(scenario=scenario):
                self.log.unlink(missing_ok=True)
                result = self.run_deploy(scenario)
                self.assertNotEqual(result.returncode, 0)
                self.assertNotIn("deployment succeeded", result.stdout)
                self.assert_no_release_changes()

    def test_stale_commit_is_skipped_without_release_changes(self):
        result = self.run_deploy("stale")
        self.assertEqual(result.returncode, 0, result.stderr)
        self.assertIn("Skipping a stale deployment", result.stdout)
        self.assert_no_release_changes()

    def test_lock_is_required_before_fetch_or_docker(self):
        result = self.run_deploy("locked")
        self.assertNotEqual(result.returncode, 0)
        self.assertFalse(any(call["tool"] == "docker" for call in self.calls))
        self.assertFalse(any(call["tool"] == "git" and call["args"][0] == "fetch" for call in self.calls))

    def test_missing_environment_file_is_refused(self):
        (self.directory / ".env").unlink()
        result = self.run_deploy()
        self.assertNotEqual(result.returncode, 0)
        self.assertIn(".env is missing", result.stderr)
        self.assert_no_release_changes()

    def test_invalid_inputs_are_refused(self):
        for environment in (
            {"DEPLOY_SHA": "main"},
            {"DEPLOY_DIR": "relative"},
            {"HEALTH_ATTEMPTS": "0"},
            {"HEALTH_INTERVAL": "invalid"},
        ):
            with self.subTest(environment=environment):
                result = self.run_deploy(**environment)
                self.assertNotEqual(result.returncode, 0)
                self.assert_no_release_changes()

    def test_release_failures_stop_without_automatic_recovery(self):
        for scenario in ("terminate", "stop", "merge", "build", "composer", "npm", "up", "reload"):
            with self.subTest(scenario=scenario):
                self.log.unlink(missing_ok=True)
                (self.directory / "state.json").unlink(missing_ok=True)
                result = self.run_deploy(scenario)
                self.assertNotEqual(result.returncode, 0)
                self.assertIn("No automatic rollback", result.stderr)
                self.assertIn("maintenance mode", result.stderr)
                self.assertNotIn(["exec", "-T", "php", "php", "artisan", "up"], self.operations)
                self.assertNotIn("deployment succeeded", result.stdout)
                if scenario in ("terminate", "stop", "merge", "build", "composer", "npm"):
                    self.assertFalse(any(op[0] == "up" for op in self.operations))

    def test_health_failures_are_bounded_and_fail_the_deployment(self):
        for scenario in ("database", "redis", "horizon", "http"):
            with self.subTest(scenario=scenario):
                self.log.unlink(missing_ok=True)
                (self.directory / "state.json").unlink(missing_ok=True)
                result = self.run_deploy(scenario)
                self.assertNotEqual(result.returncode, 0)
                self.assertIn("Health check failed:", result.stderr)
                self.assertNotIn("deployment succeeded", result.stdout)
                state = json.loads((self.directory / "state.json").read_text())
                self.assertEqual(state[scenario], 2)
                if scenario != "http":
                    self.assertNotIn(["exec", "-T", "php", "php", "artisan", "up"], self.operations)

    def test_transient_health_failures_are_retried(self):
        result = self.run_deploy("transient")
        self.assertEqual(result.returncode, 0, result.stderr)
        state = json.loads((self.directory / "state.json").read_text())
        for probe in ("database", "redis", "horizon", "http"):
            self.assertEqual(state[probe], 2)

    def test_stopped_horizon_can_be_recovered_by_rerunning(self):
        result = self.run_deploy("stopped_horizon")
        self.assertEqual(result.returncode, 0, result.stderr)
        self.assertNotIn(["exec", "-T", "horizon", "php", "artisan", "horizon:terminate"], self.operations)

    def test_idle_horizon_exit_during_termination_is_allowed(self):
        result = self.run_deploy("terminate_exit")
        self.assertEqual(result.returncode, 0, result.stderr)

    def test_stale_runtime_images_fail_before_dependency_installation(self):
        for scenario in ("old_php_image", "old_horizon_image"):
            with self.subTest(scenario=scenario):
                self.log.unlink(missing_ok=True)
                (self.directory / "state.json").unlink(missing_ok=True)
                result = self.run_deploy(scenario)
                self.assertNotEqual(result.returncode, 0)
                self.assertFalse(any("--entrypoint" in op and "composer" in op for op in self.operations))
                self.assertFalse(any(op[0] == "up" for op in self.operations))
                self.assertNotIn("deployment succeeded", result.stdout)

    def test_stale_running_containers_cannot_report_success(self):
        for scenario in ("old_php_container", "old_horizon_container"):
            with self.subTest(scenario=scenario):
                self.log.unlink(missing_ok=True)
                (self.directory / "state.json").unlink(missing_ok=True)
                result = self.run_deploy(scenario)
                self.assertNotEqual(result.returncode, 0)
                self.assertNotIn(["exec", "-T", "php", "php", "artisan", "up"], self.operations)
                self.assertNotIn("deployment succeeded", result.stdout)

    def test_ssh_bootstrap_as_root(self):
        result = self.run_deploy(bootstrap=True, MOCK_UID="0")
        self.assertEqual(result.returncode, 0, result.stderr)
        self.assertFalse(any(call["tool"] == "sudo" for call in self.calls))

    def test_ssh_bootstrap_with_passwordless_sudo(self):
        result = self.run_deploy(bootstrap=True, MOCK_UID="1000", SUDO_PASSWORD="")
        self.assertEqual(result.returncode, 0, result.stderr)
        sudo = next(call for call in self.calls if call["tool"] == "sudo")
        self.assertEqual(sudo["args"][:2], ["-n", "--"])

    def test_ssh_bootstrap_with_password_sudo(self):
        password = "test-only '$password'"
        result = self.run_deploy(bootstrap=True, MOCK_UID="1000", SUDO_PASSWORD=password)
        self.assertEqual(result.returncode, 0, result.stderr)
        self.assertNotIn(password, result.stdout + result.stderr)
        sudo = next(call for call in self.calls if call["tool"] == "sudo")
        self.assertEqual(sudo["args"][:4], ["-S", "-p", "", "--"])


if __name__ == "__main__":
    unittest.main()
