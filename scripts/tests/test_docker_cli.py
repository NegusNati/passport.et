"""Parse the actual stop command against a guaranteed absent Docker socket."""

import os
from pathlib import Path
import shlex
import shutil
import subprocess
import tempfile
import unittest


ROOT = Path(__file__).resolve().parents[2]


class DockerCliTests(unittest.TestCase):
    @unittest.skipUnless(shutil.which("docker"), "Docker CLI is not installed")
    def test_stop_flags_parse_without_a_running_daemon(self):
        command = next(line for line in (ROOT / "scripts/deploy.sh").read_text().splitlines()
                       if line.startswith("docker stop "))
        args = shlex.split(command)[1:-1] + ["passport-cli-parse-probe"]
        environment = {key: value for key, value in os.environ.items()
                       if key not in ("DOCKER_CONTEXT", "DOCKER_HOST", "DOCKER_TLS",
                                      "DOCKER_TLS_VERIFY", "DOCKER_CERT_PATH")}
        with tempfile.TemporaryDirectory(prefix="passport-cli-test-") as directory:
            socket = "unix://" + str(Path(directory) / "absent.sock")
            result = subprocess.run(
                ["docker", "--host", socket, *args], env=environment,
                text=True, capture_output=True, check=False, timeout=10,
            )
        self.assertNotEqual(result.returncode, 0)
        self.assertNotIn("unknown flag", result.stderr.lower())
        self.assertIn("connect", result.stderr.lower(), result.stderr)
        self.assertIn("absent.sock", result.stderr)


if __name__ == "__main__":
    unittest.main()
