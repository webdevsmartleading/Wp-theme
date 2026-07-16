# AGENTS.md

## Cursor Cloud specific instructions

### What this repo is
This repository is a single **WordPress theme** — "Smart Leading Custom Theme" (text domain `sls-theme`), located in the `Custom Theme/` directory. Its core feature is a Shopify-style, **JSON-driven modular page-section builder** (`inc/section-engine.php` + `inc/admin-section-builder.php`) with four built-in sections: `hero`, `usb`, `results`, `about` (each under `sections/<slug>/` with `section.json`, `template.php`, `style.css`).

There is **no package manager, build step, lint config, or test suite** in this repo. PHP/CSS/JS assets are hand-written and served directly. To sanity-check a PHP file, use `php -l <file>`.

### Dev environment layout (persisted on the VM)
The theme cannot run on its own; it is plugged into a full WordPress install that lives **outside the repo**:
- WordPress core: `~/wp` (installed via WP-CLI).
- The repo theme is symlinked in: `~/wp/wp-content/themes/sls-custom-theme -> /workspace/Custom Theme` (the update script re-creates this symlink; it is idempotent).
- Database: **MariaDB**, db `wordpress`, user `wpuser` / password `wppass`, host `127.0.0.1`.
- Site URL: `http://localhost:8080` — WP admin at `/wp-admin`, login `admin` / `admin123`.

### Starting services (they do NOT auto-start on VM boot)
Neither the database nor the web server starts automatically after a VM snapshot restore. Start them manually before testing:
```bash
# 1) Start MariaDB (if `sudo mariadb -e 'SELECT 1'` fails)
sudo mysqld_safe --datadir=/var/lib/mysql &

# 2) Start the WordPress dev server (foreground; run in tmux/background as needed)
cd ~/wp && wp server --host=0.0.0.0 --port=8080 --allow-root
```
Use `wp` (WP-CLI) for admin tasks, e.g. `cd ~/wp && wp theme activate sls-custom-theme --allow-root`. WP-CLI is run with `--allow-root` in this environment.

### Non-obvious behavior gotchas
- Sections **only render on Pages or the site front page** (`is_singular('page') || is_front_page()`), not on posts/archives. See `index.php` and `sls_get_active_sections()`.
- A section is only output (and its CSS/JS only enqueued) if it has **visible text/image content**; empty sections are skipped (`sls_section_has_visible_content()`).
- Section configuration is stored in the page post meta key `_sls_page_sections` as a JSON string (also exposed read-only over REST as the `sls_sections` field on `page`). You can seed a page from the CLI: `wp post meta update <page_id> _sls_page_sections '<json>' --allow-root`.
- The admin "Page Sections" builder is a classic metabox rendered **below** the block editor; its JS (`assets/js/admin-section-builder.js`) depends on the `wp-data` script handle and the WP media library.
