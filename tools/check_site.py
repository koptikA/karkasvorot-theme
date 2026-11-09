"""Site checks: python tools/check_site.py http://127.0.0.1:9400

Loads every tools/checks/*.py (except _*.py) and calls check(base, s) -> list[str] of failures.
s is a urllib OpenerDirector logged in as admin (or None if login failed). Exit code 1 on any failure.
"""
import importlib.util
import pathlib
import sys

HERE = pathlib.Path(__file__).resolve().parent
sys.path.insert(0, str(HERE))
from stand import admin_session  # noqa: E402


def main():
    sys.stdout.reconfigure(encoding="utf-8")
    if len(sys.argv) != 2:
        sys.exit(__doc__)
    base = sys.argv[1].rstrip("/")
    s = admin_session(base)
    total = 0
    for path in sorted((HERE / "checks").glob("*.py")):
        if path.name.startswith("_"):
            continue
        spec = importlib.util.spec_from_file_location("kv_check_" + path.stem, path)
        mod = importlib.util.module_from_spec(spec)
        try:
            spec.loader.exec_module(mod)
            fails = mod.check(base, s)
        except Exception as e:  # a crashing module is a failure, not a pass
            fails = ["crashed: %r" % e]
        for f in fails:
            print("FAIL %s: %s" % (path.stem, f))
        print("%s %s (%d failures)" % ("ok  " if not fails else "FAIL", path.stem, len(fails)))
        total += len(fails)
    print("GREEN" if total == 0 else "RED: %d failures" % total)
    sys.exit(1 if total else 0)


if __name__ == "__main__":
    main()
