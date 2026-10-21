"""Regression checks for the October 21, 2026 PHP security release."""

import json
from pathlib import Path
import subprocess
import unittest

from test_configuration import read_yaml


ROOT = Path(__file__).resolve().parents[2]


class PhpSecurityTests(unittest.TestCase):
    def test_both_images_pin_the_patched_php_release(self):
        for name in ("php.dockerfile", "php.root.dockerfile"):
            with self.subTest(dockerfile=name):
                lines = (ROOT / "dockerfiles" / name).read_text().splitlines()
                self.assertEqual(lines[0], "FROM php:8.3.36-fpm-alpine")

    def test_composer_requires_the_supported_patched_branch(self):
        composer = json.loads((ROOT / "src/composer.json").read_text())
        lock = json.loads((ROOT / "src/composer.lock").read_text())
        self.assertEqual(composer["require"]["php"], "~8.3.36")
        self.assertEqual(composer["config"]["platform"]["php"], "8.3.36")
        self.assertEqual(lock["platform"]["php"], "~8.3.36")
        self.assertEqual(lock["platform-overrides"]["php"], "8.3.36")

    def test_ci_uses_patched_php_and_checks_real_images(self):
        ci = read_yaml(".github/workflows/ci.yml")
        steps = ci["jobs"]["build-and-test"]["steps"]
        php = next(step for step in steps if step.get("uses", "").startswith("shivammathur/setup-php@"))
        self.assertEqual(php["with"]["php-version"], "8.3.36")
        commands = "\n".join(step.get("run", "") for step in steps)
        self.assertIn("composer check-platform-reqs", commands)
        image_steps = ci["jobs"]["php-runtime-images"]["steps"]
        commands = "\n".join(step.get("run", "") for step in image_steps)
        self.assertIn("--pull", commands)
        self.assertIn("php.root.dockerfile", commands)
        self.assertIn("php.dockerfile", commands)
        self.assertIn("check-php-runtime.php", commands)

    def test_runtime_policy_rejects_old_and_prerelease_versions(self):
        guard = ROOT / "src/scripts/check-php-runtime.php"
        self.assertTrue(guard.is_file(), "A runtime check must reject stale image caches.")
        fixtures = {
            "8.3.0": False, "8.3.33": False, "8.3.35": False,
            "8.3.36": True, "8.3.37": True,
            "8.3.36RC1": False, "8.3.36-dev": False, "8.3.37-beta1": False,
            "8.2.35": False, "8.4.27": False, "8.5.11": False,
            "8.5.12": False, "not-a-version": False,
        }
        program = (
            "require $argv[1]; "
            "$result = []; "
            "foreach (json_decode($argv[2], true) as $version => $_) { "
            "$result[$version] = isSupportedPhpRuntime($version); } "
            "echo json_encode($result);"
        )
        result = subprocess.run(
            ["php", "-n", "-r", program, str(guard), json.dumps(fixtures)],
            text=True, capture_output=True, check=True,
        )
        self.assertEqual(json.loads(result.stdout), fixtures)


if __name__ == "__main__":
    unittest.main()
