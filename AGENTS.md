# AGENTS.md

## Cursor Cloud specific instructions

This repo is a single **WordPress theme** (Smart Leading Custom Theme / `SLS`) living in `Custom Theme/`. It has no package manager, no build step, and no automated tests. To exercise it you need a running WordPress stack (PHP + MariaDB + WordPress core) with the theme symlinked in.

The environment snapshot already contains the installed system packages (PHP 8.3, MariaDB, WP-CLI) and a configured WordPress install at `~/wordpress` (DB `wordpress`, admin user `admin` / `admin123`). The startup update script re-links the theme into that install, but **services are not started automatically** — start them yourself each session:

### Start services (each session)

- **MariaDB** (systemd is not available in this VM, start it directly):
  ```bash
  sudo mysqld_safe --datadir=/var/lib/mysql &   # then: sudo mysqladmin ping
  ```
- **WordPress dev server** (PHP built-in server via WP-CLI, from the WP install dir):
  ```bash
  cd ~/wordpress && wp server --host=0.0.0.0 --port=8080 --allow-root
  ```
  Site: `http://localhost:8080/` — WP admin: `http://localhost:8080/wp-admin/` (`admin` / `admin123`).

Run long-running services in a `tmux` session so they survive between tool calls.

### Key layout & gotchas

- The theme is symlinked as `~/wordpress/wp-content/themes/custom-theme -> /workspace/Custom Theme`. Edits under `Custom Theme/` are picked up immediately (no build/restart needed); assets are cache-busted via `filemtime()`.
- Sections are defined by `Custom Theme/sections/<slug>/section.json` (+ `template.php`, `style.css`, optional `script.js`). Page content is stored in the `_sls_page_sections` post meta as a JSON string on `page` posts, editable via the "Page Sections" metabox in the block-editor page screen.
- Only pages / the front page render sections. To see them, set a page as the front page (`wp option update show_on_front page` / `page_on_front <id>`).
- REST output for headless use: `GET /wp-json/wp/v2/pages/<id>` includes an `sls_sections` array (or `?rest_route=/wp/v2/pages/<id>` when pretty permalinks are off).

### Lint / test / build

- **Build:** none (copy/symlink the theme into WordPress).
- **Tests:** none configured. Manual verification only.
- **Lint (available check):** `find "Custom Theme" -name '*.php' -print0 | xargs -0 -n1 php -l` for PHP syntax; validate each `section.json` with a JSON parser.
