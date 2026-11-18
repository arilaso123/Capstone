"""Deprecated manual HTTP flow test entrypoint.

This file previously logged into the local server using fixture accounts and
hard-coded order IDs at import time. That behavior was unsafe and brittle.
The authoritative, isolated tests live in ``tests/Feature`` and run with
``php artisan test``.
"""

if __name__ == "__main__":
    print("This legacy HTTP script is disabled. Run `php artisan test` instead.")
