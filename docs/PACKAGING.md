# elePHPant — Web Production & Desktop Packaging Guide

This guide documents how to configure and package **elePHPant** cleanly for both **web production** (PostgreSQL + isolated sandbox worker) and **standalone desktop builds** (`.exe` / `.dmg` / `.AppImage` backed by SQLite).

---

## 1. Database Portability (SQLite ↔ PostgreSQL)

All migrations in `database/migrations/` and Eloquent queries avoid MySQL-only or SQLite-only syntax (standard ANSI `CASE` ordering, standard `json` columns, and portable foreign keys/unique indexes).

### Local Development & Desktop Builds (SQLite)

```env
DB_CONNECTION=sqlite
# Uses database/database.sqlite by default (or an absolute user-data path in desktop wrappers)
```

### Web Production (PostgreSQL)

```env
DB_CONNECTION=pgsql
DB_HOST=127.0.0.1
DB_PORT=5432
DB_DATABASE=elephpant
DB_USERNAME=elephpant
DB_PASSWORD=strong-secret
```

After switching databases, initialize schema and sync the 15-challenge launch pack:

```bash
php artisan migrate --force
php artisan challenges:sync
```

---

## 2. Sandbox Runner Portability (`ChallengeRunnerInterface`)

All untrusted player code runs strictly outside the Laravel application process via `App\Contracts\ChallengeRunnerInterface` configured in `config/sandbox.php`:

| Target Environment                                         | `SANDBOX_DRIVER`         | Configuration                                                                                                                                                                                                                                                                                                                                      |
| :--------------------------------------------------------- | :----------------------- | :------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------- |
| **Local WSL2 (Debian / cgroup v2)**                        | `docker`                 | Hardened ephemeral `php:8.5.13-cli-alpine` containers (`--rm`, `--network=none`, `--read-only`, `--tmpfs /tmp:rw,noexec,nosuid,size=16m`, `--cap-drop=ALL`, `--security-opt=no-new-privileges`, `--pids-limit=64`, `--memory=128m`, `--cpus=0.5`, `--user=65534:65534`, 3s timeout). Pull via `docker compose -f docker-compose.sandbox.yml pull`. |
| **Web Production**                                         | `piston`                 | Point `SANDBOX_PISTON_URL` to a private, network-isolated Piston/gVisor worker host with `SANDBOX_PISTON_API_KEY` set.                                                                                                                                                                                                                             |
| **Automated CI / Pest Tests**                              | `fake`                   | Configured automatically in `phpunit.xml` for deterministic test execution without Docker.                                                                                                                                                                                                                                                         |
| **Future Offline Desktop (`.exe` / `.dmg` / `.AppImage`)** | `wasm` _(future driver)_ | Implement `ChallengeRunnerInterface` backed by client-side `php-wasm` / SQLite WASM for offline practice while keeping ranked web submissions server-verified.                                                                                                                                                                                     |

---

## 3. Asset & Worker Bundling Audit

- **Phaser 3.90.0 Assets:** All tilesets (`/assets/tilesets/*.png`), maps (`/assets/maps/*.json`), and logos (`/images/elephpant-logo.png`) use root-relative paths with zero hardcoded `localhost:8000` URLs.
- **Monaco Editor Workers:** Bundled locally via Vite's native `new Worker(new URL(..., import.meta.url), { type: 'module' })` into `public/build/assets/`—zero CDN runtime dependencies.
- **Production Build Command:**
    ```bash
    npm run build
    ```
- **Desktop Packaging Readiness (NativePHP / Electron / Tauri):**
    1. Run `npm run build` and `composer install --no-dev --optimize-autoloader`.
    2. Bundle `public/build/`, `public/assets/`, `public/images/`, `public/docs/`, and `challenges/`.
    3. Point `DB_DATABASE` to the OS app-data SQLite file and run `php artisan migrate --force && php artisan challenges:sync` on first launch.
    4. Configure `APP_URL=http://localhost:<embedded-port>` and `PASSKEYS_RP_ID=localhost` for local WebAuthn support.
