<div align="center">

# elePHPant

<img src="public/images/elephpant-logo.png" alt="elePHPant 16-bit Pixel Logo" width="160" />

### _A 2D 16-Bit Developer Adventure RPG for PHP & Laravel Artisans_

**Making coding fun, approachable, and convenient to learn—one Code-Locked Terminal at a time.**

[![PHP 8.5+](https://img.shields.io/badge/PHP-8.5%2B-777BB4?style=for-the-badge&logo=php&logoColor=white)](https://www.php.net)
[![Laravel 13](https://img.shields.io/badge/Laravel-13-FF2D20?style=for-the-badge&logo=laravel&logoColor=white)](https://laravel.com)
[![Vue 3](https://img.shields.io/badge/Vue-3-4FC08D?style=for-the-badge&logo=vuedotjs&logoColor=white)](https://vuejs.org)
[![Phaser 3](https://img.shields.io/badge/Phaser-3.90-6366F1?style=for-the-badge)](https://phaser.io)
[![Tailwind CSS v4](https://img.shields.io/badge/Tailwind_CSS-v4-06B6D4?style=for-the-badge&logo=tailwindcss&logoColor=white)](https://tailwindcss.com)
[![Docker Sandbox](https://img.shields.io/badge/Docker-Hardened_PHP_8.5-2496ED?style=for-the-badge&logo=docker&logoColor=white)](docker/php-sandbox/Dockerfile)

[Explore the Repo](https://www.github.com/jmdev11webdev/elephpant) • [Quick Start](#-quick-start) • [The Stack Region](#-the-stack-region) • [Controls](#-controls) • [Contributing](#-contributing)

</div>

---

## 🎯 Purpose of the Game

> **elePHPant** is an open-source 2D 16-bit GBA-style RPG built for **PHP** and **Laravel** learners and builders.
> Instead of grinding dry syntax drills in isolated browser tabs, you explore **The Stack Region**, talk to mentors, unlock world gates by solving real PHP 8.5 challenges inside an embedded VS Code (Monaco) editor, and level up across three real-world engineering disciplines—making coding **fun, convenient to learn, practical, and genuinely enjoyable**.

---

## 🗺️ The Stack Region

Walk freely from **Localhost Town (Foundations Hub)** along cardinal roads—or press **`M`** anytime to open the interactive **16-Bit Region Town Map** and Fast Travel:

```text
                        ┌─────────────────────────┐
                        │   🏙️ ARTISAN CITY       │
                        │    Developer's Path     │
                        │   (North • 5 Trials)    │
                        └────────────┬────────────┘
                                     │
┌─────────────────────────┐    ┌─────┴──────────────────┐    ┌─────────────────────────┐
│   ⚓ LEGACY HARBOR      │    │  🏛️ FOUNDATIONS HUB    │    │  🌲 ALGORITHM WOODS     │
│    Maintainer's Path    ├────┤    Localhost Town      ├────┤   Programmer's Path     │
│    (West • 5 Trials)    │    │  Localhost Academy (8) │    │   (East • 5 Trials)     │
└─────────────────────────┘    └─────┬──────────────────┘    └─────────────────────────┘
                                     │
                        ┌────────────┴────────────┐
                        │  ☁️ ??? — COMING SOON   │
                        │   Southern Frontier     │
                        └─────────────────────────┘
```

### Three Non-Exclusive Engineering Paths (+ Optional Onboarding)

| District                 | Path / Discipline            | Focus                                                                                                                                           | Launch Trials |
| :----------------------- | :--------------------------- | :---------------------------------------------------------------------------------------------------------------------------------------------- | :-----------: |
| **🏛️ Localhost Academy** | **Foundations** _(Optional)_ | Zero-knowledge PHP onboarding (`echo`, variables, math, `if/else`, arrays, loops, functions, first bug fix) → unlocks **Syntax Awakened** badge |     **8**     |
| **🌲 Algorithm Woods**   | **Programmer's Path**        | Hash maps, LIFO stacks, directed graph cycle detection, sliding-window rate limiters, TTL LRU caching                                           |     **5**     |
| **🏙️ Artisan City**      | **Developer's Path**         | HTTP route responses, payload normalization, multi-file payment ledgers, cursor pagination, HMAC webhook guards                                 |     **5**     |
| **⚓ Legacy Harbor**     | **Maintainer's Path**        | Timezone bugs, payment regressions, discount engine refactoring, SQL injection audits, stock race conditions                                    |     **5**     |

---

## ✨ Key Features

- **🎮 16-Bit Multi-Screen Overworld (Phaser 3.90):** Explore four expanded `48×36` tile districts with smooth lerped camera following, 5-layer Tiled JSON maps, enterable guild interiors, typewriter NPC/signpost dialogue, and Code-Locked Terminals with live visual unlock states.
- **💻 Embedded VS Code Workspace (Monaco Editor):** Multi-file tabs, Diff View against starter code, formal Markdown problem specifications, 3-level progressive hints (_Concept Nudge → Syntax Pattern → Near-Complete Example_), and instant public/hidden grading.
- **🛡️ Hardened Ephemeral Docker PHP 8.5 Sandbox:** Player code never runs inside the Laravel process. Every run executes in an isolated `elephpant-php-sandbox:8.5` container (`--rm --network=none --read-only --cap-drop=ALL --security-opt=no-new-privileges --memory=64m --pids-limit=32 --user 1000:1000`) with dangerous functions disabled and strict 5-second timeouts.
- **🗺️ Pokémon-Style Region Town Map (`M`) & GBA Save/Load (`Esc`):** Interactive SVG region map with live proportional player position, solved/unsolved terminal dots, keyboard/mouse Fast Travel, Slot 0 Auto-Save, and 3 manual SQLite save slots that **never** roll back authoritative XP or solved challenges.
- **🎵 Hybrid 16-Bit Web Audio Engine:** Zero-dependency GBA-style chiptune BGM per district with ~400ms crossfades, Monaco low-pass ducking, retro SFX (footsteps, dialogue blips, test fanfares, save chimes), HUD Mute/Volume mixer, and drop-in `public/audio/<zone>.mp3` override support.
- **📚 Curated Ecosystem Resource Directory (`/resources`):** Out-of-game reference directory with official brand logos for PHP, Composer, Packagist, Laravel, Livewire, Alpine.js, Tailwind CSS, Inertia.js, Vue, React, and Svelte.

---

## 🕹️ Controls

| Key                             | Action                                                        |
| :------------------------------ | :------------------------------------------------------------ |
| `W` `A` `S` `D` or `Arrow Keys` | Move across The Stack Region / Navigate Region Map            |
| `E` / `Space` / `Enter`         | Interact with Terminals, NPCs, & Signposts / Advance Dialogue |
| `M`                             | Open / Close **Stack Region Town Map** (Fast Travel)          |
| `Esc`                           | Open / Close **GBA Save & Load Menu** (or close active modal) |

---

## 🛠️ Tech Stack

| Layer            | Technology                                                                       |
| :--------------- | :------------------------------------------------------------------------------- |
| **Backend**      | Laravel 13, PHP 8.5+, Fortify (Passkeys / 2FA), SQLite (PostgreSQL-ready)        |
| **Frontend**     | Vue 3, TypeScript, Inertia.js v3, Tailwind CSS v4, Laravel Wayfinder             |
| **Game Engine**  | Phaser 3.90.0 (`pixelArt: true`, `960×540` logical canvas, `3×` GBA zoom)        |
| **Code IDE**     | Monaco Editor (embedded in `/play` via typed Phaser ↔ Vue event bridge)          |
| **Code Sandbox** | Hardened ephemeral Docker `php:8.5-cli-alpine` container (`docker/php-sandbox`)  |
| **Audio**        | Native Web Audio API 16-bit synthesizer + optional `.ogg`/`.mp3` track overrides |
| **Testing & QA** | Pest, PHPStan (Larastan), Laravel Pint, Vite+ / `vue-tsc`                        |

---

## 🚀 Quick Start

### Prerequisites

- **PHP 8.5+** & **Composer**
- **Node.js 22+** & **npm**
- **Docker Engine** (Linux / WSL2 cgroup v2 recommended for live sandbox execution)

### 1. Clone & Install Dependencies

```bash
git clone https://www.github.com/jmdev11webdev/elephpant.git
cd elephpant
cp .env.example .env
composer install
npm install
php artisan key:generate
```

### 2. Migrate Database & Sync All 23 Challenges

```bash
php artisan migrate
php artisan challenges:sync
```

### 3. Build the Hardened PHP 8.5 Sandbox Image

```bash
php artisan sandbox:build
```

_(Ensure your user has Docker access—on WSL2/Linux, add yourself to the `docker` group and restart your shell.)_

### 4. Launch the Dev Server

```bash
composer run dev
```

Open **`http://localhost:8000`** (or the port printed by `php artisan serve`), create an account, and click **Continue Adventure** to enter **Localhost Town**!

---

## 🧪 Running Tests & Quality Checks

```bash
# Run Pest test suite (verifies all 23 reference solutions, save slots, and sandbox security)
php artisan test --compact

# Static analysis & formatting
composer types:check
vendor/bin/pint --dirty --format agent
npm run types:check
npm run build
```

---

## 🤝 Contributing

**elePHPant** is actively built in the open—ideas, new PHP/Laravel challenge bundles, pixel art improvements, audio tracks, and pull requests are always welcome!

1. Fork the repository at [github.com/jmdev11webdev/elephpant](https://www.github.com/jmdev11webdev/elephpant)
2. Create a feature branch (`git checkout -b feat/your-feature`)
3. Run `php artisan test --compact` and `npm run build`
4. Open a Pull Request!

---

<div align="center">

Built with ❤️ for the **PHP & Laravel** community.

</div>
