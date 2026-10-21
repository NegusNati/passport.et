"""Exercise the production checkout guard with isolated, synthetic Git history."""

import os
from pathlib import Path
import subprocess
import tempfile
import unittest


ROOT = Path(__file__).resolve().parents[2]
SCRIPT = (ROOT / "scripts/deploy.sh").read_text()
FUNCTIONS = SCRIPT[SCRIPT.index("fail() {"):SCRIPT.index('\n[[ "$DEPLOY_DIR" = /* ]]')]
OLD_ASSET = "src/public/build/js/old.js"
INCOMING = "src/app/Incoming.php"


class WorktreeTests(unittest.TestCase):
    def setUp(self):
        self.temp = tempfile.TemporaryDirectory(prefix="passport-worktree-test-")
        self.addCleanup(self.temp.cleanup)
        self.directory = Path(self.temp.name)
        self.git("init", "-q")
        files = {
            ".gitignore": ".env\n/ignored-state/\n",
            OLD_ASSET: "old generated output\n",
            "src/app/Keep.php": "existing source\n",
        }
        self.before = self.commit(self.tree(files, "before"))
        self.after_files = {
            ".gitignore": files[".gitignore"] + "/src/public/build/\n",
            "src/app/Keep.php": files["src/app/Keep.php"],
            INCOMING: "new source\n",
        }
        self.after = self.commit(self.tree(self.after_files, "after"), self.before)
        self.git("update-ref", "HEAD", self.before)
        self.git("read-tree", self.before)
        self.git("checkout-index", "--all")

    def git(self, *args, data=None, environment=None):
        return subprocess.run(
            ["git", "-C", str(self.directory), *args], input=data,
            text=True, capture_output=True, check=True, env=environment,
        ).stdout

    def tree(self, files, name):
        environment = {
            **os.environ,
            "GIT_INDEX_FILE": str(self.directory / ".git" / ("index-" + name)),
        }
        self.git("read-tree", "--empty", environment=environment)
        for path, content in files.items():
            blob = self.git("hash-object", "-w", "--stdin", data=content).strip()
            self.git("update-index", "--add", "--cacheinfo", "100644", blob, path,
                     environment=environment)
        return self.git("write-tree", environment=environment).strip()

    def commit(self, tree, parent=None):
        # Synthetic object data, not git commit or author/configuration overrides.
        fixture_identity = "Deployment fixture <fixture@example.invalid> 0 +0000"
        body = f"tree {tree}\n"
        if parent:
            body += f"parent {parent}\n"
        body += (f"author {fixture_identity}\ncommitter {fixture_identity}\n\n"
                 "Deployment safety fixture\n")
        return self.git("hash-object", "-t", "commit", "-w", "--stdin", data=body).strip()

    def write(self, path, content="server-local content\n"):
        target = self.directory / path
        target.parent.mkdir(parents=True, exist_ok=True)
        target.write_text(content)
        return target

    def check_worktree(self):
        index = self.git("ls-files", "--stage", "-z")
        result = subprocess.run(
            ["bash", "-c", "set -Eeuo pipefail\n" + FUNCTIONS +
             '\ncheck_worktree "$1" "$2"', "preflight", self.before, self.after],
            cwd=self.directory, text=True, capture_output=True, check=False,
            timeout=10,
        )
        self.assertEqual(self.git("ls-files", "--stage", "-z"), index)
        self.assertEqual(self.git("rev-parse", "HEAD").strip(), self.before)
        return result

    def test_legacy_asset_deletions_and_server_only_files_are_preserved(self):
        (self.directory / OLD_ASSET).unlink()
        paths = [
            "src/public/build/css/current.css", "src/public/build/js/current.js",
            "src/public/build/manifest.json", ".env", ".env.example", "src/ToDo.md",
            "src/app/Http/Controllers/TelegramPDFController_depreciated.php",
        ]
        for path in paths:
            self.write(path)
        result = self.check_worktree()
        self.assertEqual(result.returncode, 0, result.stderr)
        self.assertFalse((self.directory / OLD_ASSET).exists())
        for path in paths:
            self.assertEqual((self.directory / path).read_text(), "server-local content\n")

        # The actual migration merge also preserves live untracked files.
        self.git("merge", "--ff-only", "--no-overwrite-ignore", self.after)
        for path in paths:
            self.assertEqual((self.directory / path).read_text(), "server-local content\n")

        # Another asset build/deploy must not recreate the dirty-checkout failure.
        self.before = self.after
        self.write("src/public/build/js/current.js", "second build\n")
        self.after = self.commit(self.tree(self.after_files, "repeat"), self.before)
        result = self.check_worktree()
        self.assertEqual(result.returncode, 0, result.stderr)

    def test_tracked_source_changes_are_refused(self):
        path = self.write("src/app/Keep.php", "local source edit\n")
        result = self.check_worktree()
        self.assertNotEqual(result.returncode, 0)
        self.assertIn("tracked source changes", result.stderr)
        self.assertEqual(path.read_text(), "local source edit\n")

    def test_staged_source_changes_are_refused(self):
        self.write("src/app/Keep.php", "staged source edit\n")
        self.git("add", "--", "src/app/Keep.php")
        result = self.check_worktree()
        self.assertNotEqual(result.returncode, 0)
        self.assertIn("tracked source changes", result.stderr)

    def test_incoming_untracked_file_collision_is_refused(self):
        path = self.write(INCOMING)
        result = self.check_worktree()
        self.assertNotEqual(result.returncode, 0)
        self.assertIn("server-local file or directory", result.stderr)
        self.assertEqual(path.read_text(), "server-local content\n")

    def test_incoming_ignored_file_collision_is_refused(self):
        self.after_files[".env"] = "incoming configuration\n"
        self.after = self.commit(self.tree(self.after_files, "ignored"), self.before)
        path = self.write(".env")
        result = self.check_worktree()
        self.assertNotEqual(result.returncode, 0)
        self.assertIn("server-local file or directory", result.stderr)
        self.assertEqual(path.read_text(), "server-local content\n")

    def test_incoming_file_cannot_replace_a_local_directory(self):
        path = self.write(INCOMING + "/keep.txt")
        result = self.check_worktree()
        self.assertNotEqual(result.returncode, 0)
        self.assertIn("server-local file or directory", result.stderr)
        self.assertTrue(path.exists())

    def test_incoming_directory_cannot_replace_a_local_file(self):
        self.after_files["ignored-state/nested/new.php"] = "incoming source\n"
        self.after = self.commit(self.tree(self.after_files, "parent"), self.before)
        path = self.write("ignored-state")
        result = self.check_worktree()
        self.assertNotEqual(result.returncode, 0)
        self.assertIn("server-local parent", result.stderr)
        self.assertTrue(path.is_file())

    def test_incoming_directory_cannot_follow_a_local_symlink(self):
        outside = self.directory / "outside"
        outside.mkdir()
        link = self.directory / "ignored-state"
        link.symlink_to(outside, target_is_directory=True)
        self.after_files["ignored-state/new.php"] = "incoming source\n"
        self.after = self.commit(self.tree(self.after_files, "symlink"), self.before)
        result = self.check_worktree()
        self.assertNotEqual(result.returncode, 0)
        self.assertIn("server-local parent", result.stderr)
        self.assertTrue(link.is_symlink())
        self.assertEqual(list(outside.iterdir()), [])

    def test_added_paths_with_newlines_are_not_split(self):
        name = "ignored-state/server\nnotes.txt"
        self.after_files[name] = "incoming content\n"
        self.after = self.commit(self.tree(self.after_files, "newline"), self.before)
        path = self.write(name)
        result = self.check_worktree()
        self.assertNotEqual(result.returncode, 0)
        self.assertIn("server-local file or directory", result.stderr)
        self.assertEqual(path.read_text(), "server-local content\n")

    def test_modified_legacy_asset_is_not_discarded(self):
        path = self.write(OLD_ASSET, "manually modified asset\n")
        result = self.check_worktree()
        self.assertNotEqual(result.returncode, 0)
        self.assertIn("without overwriting local changes", result.stderr)
        self.assertEqual(path.read_text(), "manually modified asset\n")

    def test_project_build_output_is_ignored_and_untracked(self):
        for path in ("css/current.css", "js/current.js", ".vite/manifest.json",
                     "manifest.json", "assets/current.js"):
            subprocess.run(
                ["git", "-C", str(ROOT), "check-ignore", "--no-index", "-q", "--",
                 "src/public/build/" + path], check=True,
            )
        result = subprocess.run(
            ["git", "-C", str(ROOT), "ls-files", "--", "src/public/build"],
            text=True, capture_output=True, check=True,
        )
        self.assertEqual(result.stdout, "")


if __name__ == "__main__":
    unittest.main()
