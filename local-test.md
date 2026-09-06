# Local Testing Workflow

Test new plugin versions locally **before** pushing to GitHub.

## Stack (installed on this machine)

| Piece | Location / Notes |
|---|---|
| WordPress 7.1 | `X:\Wordpress Local instal\wordpress-7.1\wordpress` |
| MariaDB 11.4.13 | `C:\tools\mariadb\mariadb-11.4.13-winx64` (no Windows service) |
| PHP 8.5 | `C:\Users\Iliyas-Iliev\php` (php.ini enables mysqli/pdo_mysql) |
| WP-CLI | `wp` on PATH (`C:\Users\Iliyas-Iliev\php\wp.bat`) |
| Plugin | Junction: `wp-content\plugins\sanctuary-hotel-booking` → this repo (always current code, no copying) |
| Credentials | `wp-content\local-db-credentials.txt` (DB + WP admin, dev only, not in git) |

## Daily workflow

```bat
REM 1. Start MariaDB (starts detached; safe to double-click or run from a terminal)
C:\tools\mariadb\mariadb-11.4.13-winx64\start-mariadb.bat
REM    -> prints "MariaDB started on port 3306" (or "already running")
REM    Stop with: C:\tools\mariadb\mariadb-11.4.13-winx64\stop-mariadb.bat
REM    (root password is read from stop.cnf next to the scripts)

REM 2. Serve the site (from any folder)
wp server --host=127.0.0.1 --port=8080 --path="X:\Wordpress Local instal\wordpress-7.1\wordpress"
REM → http://localhost:8080  (wp-admin: /wp-admin, admin/admin password in the credentials file)

REM 3. Run the plugin verification suite (all checks must PASS)
wp eval-file tests\verify.php --path="X:\Wordpress Local instal\wordpress-7.1\wordpress"
```

If MariaDB fails to start, check `C:\tools\mariadb\mariadb-11.4.13-winx64\data\*.err`.
A past cause: the data files only allowed Administrators/SYSTEM to write, so a
normal-user `mysqld` aborted with "InnoDB: The data file './ibdata1' must be
writable". Fixed by granting `BUILTIN\Users` Modify recursively on the data
dir. If you ever re-init or move the data dir and it stops starting, re-apply
that ACL grant.

Because the plugin is a **junction** to this repo, code edits are live
immediately — just refresh the browser (PHP re-reads files per request).

## What to test before a release

1. `wp eval-file tests\verify.php` — full sanity suite (schema, booking
   creation/cancellation, room type defaults, search filters, delete guards).
2. **Hotel Booking → Rooms** — create a room via Room Setup (add an image +
   gallery), edit it, view the room page.
3. **Hotel Booking → Locations / Room Types** — create/edit; check delete
   guards.
4. Front end: room single page (`/?shb_room=<slug>` with plain permalinks, or
   `/?page_id=<search page>`), the search page (location + room type + bed +
   views + amenities chips), and the booking form price summary.
5. Check `wp-content\debug.log` after exercising pages — must stay empty (no
   notices/warnings). Delete it between checks.

## Notes / gotchas

- **Use `wp server`**, not a hand-rolled `php -S` router — block themes render
  empty bodies under the custom router on this setup.
- Plain permalinks are enabled (`?shb_room=...` / `?page_id=...`); pretty
  permalinks need `wp rewrite structure '/%postname%/'` + `wp rewrite flush`.
- **Avast**: add exclusions for `C:\Users\Iliyas-Iliev\php`,
  `C:\tools\mariadb`, the repo, and the WP folder — otherwise dev-server
  spawns get flagged as IDP.Generic/IDP.HELU false positives. Avoid spawning
  PowerShell with inline `-Command` strings from agent shells.
- WP-CLI prints PHP 8.5 deprecation noise; harmless (error_reporting already
  excludes E_DEPRECATED in php.ini for future processes).
- Credentials file (`local-db-credentials.txt`) is local-only — never commit.
