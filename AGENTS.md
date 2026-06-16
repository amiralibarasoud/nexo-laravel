# AGENTS.md

## Cursor Cloud specific instructions

This is a **Laravel 12** app ("نکسو کورس" — a Persian/RTL online course platform) with a
**Filament 3** admin panel and an **Inertia + Vue 3** public frontend. Localization is Persian
(Jalali calendar). PHP 8.3, Node 22, and Composer are preinstalled in this environment.

### Services / processes

- **Web app** — `php artisan serve --host=0.0.0.0 --port=8000` (public site + Filament admin at `/admin`).
- **Vite dev server** — `npm run dev` (required for the Inertia/Vue frontend HMR; run alongside `serve`).
- **Database** — MariaDB (MySQL-compatible), used by `DB_CONNECTION=mysql` in `.env`.
- A combined `composer dev` script exists (runs serve + queue + pail + vite via `concurrently`), but
  running `php artisan serve` and `npm run dev` separately is simpler for debugging.

### Database startup (not handled by the update script)

The DB server is not auto-started. Start MariaDB and ensure the app DB/user exist:

```bash
sudo mysqld_safe --datadir=/var/lib/mysql &        # start MariaDB
sudo mysql -e "CREATE DATABASE IF NOT EXISTS nexocourse CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"
sudo mysql -e "CREATE USER IF NOT EXISTS 'nexo'@'127.0.0.1' IDENTIFIED BY 'nexo'; GRANT ALL PRIVILEGES ON *.* TO 'nexo'@'127.0.0.1'; FLUSH PRIVILEGES;"
```

Gotcha: connecting over TCP to `127.0.0.1` as MySQL `root` maps to `root@localhost`, which uses
unix-socket auth and fails with "Access denied". Use a dedicated user instead. The committed
`.env.example` defaults to `root` with an empty password (matches the maintainer's local XAMPP);
in this VM set `DB_USERNAME=nexo` / `DB_PASSWORD=nexo` in `.env`.

### First-run app setup (after deps are installed)

```bash
cp .env.example .env        # only if .env is missing (.env is gitignored)
php artisan key:generate
php artisan migrate          # ALWAYS migrate before seeding
php artisan db:seed --class=SettingsSeeder
php artisan db:seed --class=BlogSeeder
php artisan db:seed          # DatabaseSeeder: admin user + sample course
npm run build                # or run `npm run dev` for HMR
```

Important: **run `php artisan migrate` before any seeder.** Seeding before the migrations have
been applied (e.g. `db:seed --class=BlogSeeder` on a DB missing the `2024_01_01_000070_create_blog_tables`
migration) fails with `Base table or view not found ... 'blog_categories' doesn't exist`.

### Admin / login

- Filament admin: `http://localhost:8000/admin/login` — `admin@nexocourse.ir` / `Admin@12345`
  (created by `DatabaseSeeder`; login is by **email**, and access requires `is_admin = true`).
- Public user login uses SMS.ir OTP, which needs `SMSIR_API_KEY`; without it, OTP login won't work.

### Tests / lint / build

- Tests: `php artisan test` (PHPUnit; uses an in-memory SQLite DB per `phpunit.xml`).
  Note: the default `tests/Feature/ExampleTest` hits `/`, which queries the DB but does not run
  migrations, so it fails out of the box — this is a pre-existing repo condition, not an env issue.
- Lint: `./vendor/bin/pint --test` (style check) / `./vendor/bin/pint` (auto-fix). The existing
  codebase has many unformatted files, so `--test` currently reports failures (pre-existing).
- Build: `npm run build` (Vite).

### Notes

- `vendor/` and `public/build/` are committed to the repo (for cPanel shared-hosting deploys via
  `.cpanel.yml`). Running `composer install` (adds dev deps) and `npm run build` will modify these
  tracked files locally — do not commit those incidental changes.
