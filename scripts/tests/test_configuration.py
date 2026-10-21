"""Check deployment wiring using Ruby's bundled YAML parser (no Docker daemon)."""

import json
from pathlib import Path
import subprocess
import unittest


ROOT = Path(__file__).resolve().parents[2]


def read_yaml(path):
    result = subprocess.run(
        ["ruby", "-ryaml", "-rjson", "-e",
         "puts JSON.generate(YAML.load_file(ARGV[0]))", str(ROOT / path)],
        text=True, capture_output=True, check=True,
    )
    return json.loads(result.stdout)


class ConfigurationTests(unittest.TestCase):
    @classmethod
    def setUpClass(cls):
        cls.compose = read_yaml("docker-compose.prod.yml")
        cls.deploy = read_yaml(".github/workflows/deploy.yml")
        cls.ci = read_yaml(".github/workflows/ci.yml")

    def test_deployment_requires_successful_main_push_ci_from_this_repository(self):
        # Ruby's YAML 1.1 parser reads an unquoted "on" key as boolean true.
        trigger = self.deploy.get("on", self.deploy.get("true"))
        self.assertEqual(trigger, {"workflow_run": {
            "workflows": [self.ci["name"]],
            "types": ["completed"], "branches": ["main"],
        }})
        condition = self.deploy["jobs"]["deploy"]["if"]
        for requirement in (
            "github.event.workflow_run.conclusion == 'success'",
            "github.event.workflow_run.event == 'push'",
            "github.event.workflow_run.head_repository.full_name == github.repository",
        ):
            self.assertIn(requirement, condition)
        self.assertEqual(self.deploy["permissions"], {"contents": "read"})

    def test_deployments_are_serial_and_the_tested_script_is_transmitted(self):
        self.assertEqual(self.deploy["concurrency"], {
            "group": "passport-production", "cancel-in-progress": False,
        })
        steps = self.deploy["jobs"]["deploy"]["steps"]
        self.assertEqual(steps[0]["with"]["ref"], "${{ github.event.workflow_run.head_sha }}")
        self.assertFalse(steps[0]["with"]["persist-credentials"])
        self.assertIn("base64 -w 0 scripts/deploy.sh", steps[1]["run"])
        self.assertNotIn("@master", steps[2]["uses"])
        self.assertEqual(steps[2]["env"]["DEPLOY_SHA"], "${{ github.event.workflow_run.head_sha }}")
        self.assertIn("steps.script.outputs.payload", steps[2]["with"]["script"])
        self.assertIn('bash -c "$payload"', steps[2]["with"]["script"])
        self.assertIn('sudo -n', steps[2]["with"]["script"])

    def test_helpers_are_opt_in_and_do_not_publish_development_ports(self):
        services = self.compose["services"]
        for helper in ("npm", "composer", "artisan"):
            self.assertEqual(services[helper]["profiles"], ["tools"])
            self.assertNotIn("ports", services[helper])
        self.assertEqual(services["npm"]["image"], "node:22-alpine")
        steps = self.ci["jobs"]["build-and-test"]["steps"]
        node = next(step for step in steps if step.get("uses", "").startswith("actions/setup-node@"))
        self.assertEqual(node["with"]["node-version"], "22")

    def test_runtime_identity_and_data_storage_are_preserved(self):
        services = self.compose["services"]
        self.assertEqual(services["app"]["container_name"], "laravel-nginx")
        self.assertEqual(services["app"]["networks"], ["laravel", "passport-web"])
        self.assertEqual(self.compose["networks"]["passport-web"], {
            "external": True, "name": "passport-frontend_web",
        })
        self.assertEqual(services["mysql"]["image"], "mariadb:11.0")
        self.assertEqual(services["mysql"]["volumes"], ["./mysql:/var/lib/mysql"])
        self.assertEqual(services["redis"]["image"], "redis:8.2-alpine")
        self.assertEqual(services["redis"]["volumes"], ["redis-data:/data"])
        self.assertEqual(self.compose["volumes"], {"redis-data": None})

    def test_horizon_receives_signals_and_has_time_to_finish_imports(self):
        horizon = self.compose["services"]["horizon"]
        self.assertEqual(horizon["command"], ["php", "artisan", "horizon"])
        self.assertEqual(horizon["stop_signal"], "SIGTERM")
        self.assertEqual(horizon["stop_grace_period"], "360s")

    def test_ci_runs_deployment_regression_checks(self):
        commands = "\n".join(
            step.get("run", "") for step in self.ci["jobs"]["deployment-safety"]["steps"]
        )
        self.assertIn("bash -n scripts/deploy.sh", commands)
        self.assertIn("shellcheck scripts/deploy.sh", commands)
        self.assertIn("python3 -m unittest discover -s scripts/tests -v", commands)
        self.assertIn("--project-name laraveldockerprod", commands)
        self.assertIn("--env-file /dev/null", commands)
        self.assertIn("config --quiet", commands)


if __name__ == "__main__":
    unittest.main()
