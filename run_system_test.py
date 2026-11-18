"""Deprecated manual HTTP smoke test entrypoint.

The former script contained hard-coded demo credentials, fixed order IDs, and
side-effecting requests against localhost. Use the isolated Laravel suite
instead: ``php artisan test``.
"""

if __name__ == "__main__":
    print("This legacy HTTP script is disabled. Run `php artisan test` instead.")
