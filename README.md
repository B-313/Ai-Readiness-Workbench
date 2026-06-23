# AI Adoption Advisory Workbench

A self-contained admin workbench for an AI & digital-adoption. Enterprise solution to manage an client database, send invitations, track responses, and benchmark each client's scores against public sector data. Map those insights onto the government's policy-development phases to surface the support available at each stage.
---

## Ideology

The guiding idea: The workbench turns scattered outreach and survey data into a single, honest picture:
- Every adoption score is benchmarked against a published figure for  the client's sector.
- Analyse customer record, response funnel, insights, and the recommended next step.
- Each client is placed on a policy-development path with the concrete, publicly-available support for that phase.
- The prototype stores everything in the browser. No accounts/servers, this is appropriate for SME and  confidentiality-sensitive practice.

### Frameworks (public)

| Source | Used for |
| --- | --- |
| UK AI Adoption Research (gov.uk, 2025) | Sector adoption benchmarks, technology mix, barriers, readiness |
| Open Policy Making Toolkit (gov.uk) | The four-phase path: Diagnosis → Discovery → Development → Delivery, and the support available per phase |

---

## Technical Architecture

### Macro level

The prototype is a **single, dependency-light HTML file** — a deliberate choice so it can be opened from `file://`, hosted anywhere static, and handed over without a build step.

```
┌──────────────────────── index.html ────────────────────────┐
│  Top banner (brand · live · clock)                                         │
│  ┌───────────────┐  ┌──────────────────────────────────────────────────┐  │
│  │  Sidebar nav  │  │  Main views (one <section> per screen)            │  │
│  │  (floating    │  │  Dashboard · Services · Customers · Email ·       │  │
│  │   groups)     │  │  Tracking · Insights · Govt Support               │  │
│  └───────────────┘  └──────────────────────────────────────────────────┘  │
│  Bottom banner (provenance)                                                │
│                                                                            │
│  State  ──►  localStorage (aaa_services / aaa_customers / aaa_invites)      │
│  Render ──►  per-view render functions  ──►  Chart.js canvases / tables     │
└────────────────────────────────────────────────────────────────────────────┘
```

- **No framework, no bundler.** Plain ES5-style JavaScript, Bootstrap for layout, Chart.js for
  visuals, SheetJS for Excel export — all from CDNs.
- **Single source of truth.** Three arrays (`services`, `customers`, `invites`) hold all state; they
  are hydrated from `localStorage` on load and re-saved on every mutation via `lsSave()`.
- **View = function.** `nav(id)` toggles the active `<section>` and calls that view's render function;
  rendering is always derived fresh from state, so views can never drift out of sync.

### Micro level

**Data model**

- `customers` — `{ id, name, email, sector, size, state, status, scored, usagePct, staffPct, techs[], readiness }`
  - `state` ∈ `Current user | Planner | Non-adopter` (gov.uk adoption states)
  - `status` ∈ `Active | Closed` (engagement lifecycle)
- `invites` — `{ id, customerId, subject, service, stage, sentTs, respondedTs, scanScore }`
  - `stage` lifecycle: `Draft → Sent → Opened → Responded`
- `services` — `{ id, name, desc, dur, price }`

**Key logic**

- `calcScore(c)` — weighted AI Adoption Score (0–100) from usage %, staff penetration, technology
  breadth and readiness: `0.4·usage + 0.25·staff + 0.2·techBreadth + 0.15·readiness`.
- `vsBench(c)` — compares the client's usage % against `BENCH[sector]` and returns a traffic-light
  verdict (`ahead` ≥ +5, `behind` ≤ −5, else `at sector`).
- `phaseFor(score)` — buckets a score into one of the four Open-Policy phases, each carrying its own
  publicly-available support description.
- `advance(id)` — moves an invite through its lifecycle; on `Responded` it generates a scan score and
  back-fills the customer's adoption profile (demo simulation of a real survey return).

**Rendering**

- Each screen has a `render*()` function that reads state, builds table HTML and (re)creates Chart.js
  instances via `mkChart()` (which destroys the prior chart to avoid leaks).
- The Dashboard outreach chart supports a **7 Days / Month / Year** range filter driven by
  `outreachChart()`.
- `exportExcel(kind)` serialises customers or invites to a downloadable `.xlsx` via SheetJS.

## Tools & Tech Used

| Concern | Choice | Why |
| --- | --- | --- |
| Markup / layout | HTML5 + **Bootstrap 5.3** (CDN) | Grid, forms and tables without custom CSS scaffolding |
| Icons | **Font Awesome 6.5** (CDN) | Recognisable section/action glyphs |
| Charts | **Chart.js 4.4** (CDN) | Bar, doughnut, polar-area and funnel visuals |
| Export | **SheetJS / xlsx 0.18** (CDN) | One-click Excel export of customers and invites |
| Logic | Vanilla JavaScript (ES5-style) | Zero build, maximum portability |
| Persistence | Browser **localStorage** | Local-first, private, no backend |
| Theming | CSS custom properties | Single-point colour control |

No package manager, transpiler or server is required to run the prototype.

---

## Running It

Open the file directly:

```
index.html   →   double-click, or drag into any browser
```

or serve it statically:

```bash
python -m http.server 8099
# then visit http://127.0.0.1:8099/index.html
```

Click **Load Demo Data** on the Dashboard to populate the workbench with a sample portfolio of
12 SMEs across sectors, mixed active/closed engagements, and invites in varied lifecycle stages.

---

## Two Versions

This repository ships the workbench in two independently runnable forms:

| Version | Location | Use |
| --- | --- | --- |
| **Portfolio** — standalone single file | `index.html` | Drop-in static showcase; opens from `file://` or any static host (GitHub Pages serves it as the index). |
| **Application** — Laravel (PHP · Blade) | `Workbench/` | Server-rendered version demonstrating routing, controllers and Blade templating. |

Both share the same UI and the same **no-database** model: all client data lives in the browser's
`localStorage` and is exported via Excel. They share no files, so each can be split into its own repo.

## Laravel (PHP · Blade) Version

The `Workbench/` app re-implements the workbench as a server-rendered Laravel project. Laravel handles
routing, controllers and Blade view composition; the client-side engine handles state and rendering.

- **Routing** — `routes/web.php` defines seven named routes plus a root redirect, grouped on a single controller.
- **Controller** — `WorkbenchController` returns one Blade view per screen and injects the public reference data (sector benchmarks, policy phases, barriers) into every view.
- **Blade** — a shared layout (`layouts/workbench.blade.php`) provides the theme, sidebar and banners; each screen `@extends` it. Reference data is passed from PHP to JavaScript with `@json`.
- **Reference data in PHP** — `app/Support/Adoption.php` holds the benchmarks, technology list, barriers and policy phases as versioned constants.
- **No database** — the same client engine (`partials/engine.blade.php`) persists to `localStorage` and exports to Excel via SheetJS, exactly like the standalone.

**Run it**

```bash
cd Workbench
composer install
cp .env.example .env
php artisan key:generate
php artisan serve          # http://127.0.0.1:8000
```

Requires PHP 8.3+ and Composer. No database connection is needed (`SESSION_DRIVER=cookie`, `CACHE_STORE=file`).

| Concern | Choice |
| --- | --- |
| Framework | Laravel 13 (PHP 8.3+) |
| Templating | Blade — layout inheritance, `@json` data passing |
| Front-end | Bootstrap 5.3 · Chart.js 4.4 · SheetJS (CDN) |
| Persistence | Browser localStorage (no database) |

## File Structure

```
.
├── index.html                              # Portfolio version — standalone single-file app
├── README.md
└── Workbench/                              # Application version — Laravel (PHP · Blade)
    ├── app/
    │   ├── Http/Controllers/
    │   │   └── WorkbenchController.php      # returns views + injects reference data
    │   └── Support/
    │       └── Adoption.php                # benchmarks, phases, barriers (public constants)
    ├── resources/views/
    │   ├── layouts/
    │   │   └── workbench.blade.php          # shared layout: theme, sidebar, banners
    │   ├── partials/
    │   │   └── engine.blade.php             # client engine: localStorage + charts + Excel
    │   └── workbench/                       # one Blade view per screen
    │       ├── dashboard.blade.php
    │       ├── services.blade.php
    │       ├── customers.blade.php
    │       ├── emails.blade.php
    │       ├── tracking.blade.php
    │       ├── insights.blade.php
    │       └── support.blade.php
    ├── routes/
    │   └── web.php                          # named routes → WorkbenchController
    ├── composer.json
    └── .env.example
```

---

## License

Released under the **MIT License**.

```
MIT License

Copyright (c) 2026 Bhanuja Kumar

Permission is hereby granted, free of charge, to any person obtaining a copy
of this software and associated documentation files (the "Software"), to deal
in the Software without restriction, including without limitation the rights
to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
copies of the Software, and to permit persons to whom the Software is
furnished to do so, subject to the following conditions:

The above copyright notice and this permission notice shall be included in all
copies or substantial portions of the Software.

THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE
SOFTWARE.
```

---

### Acknowledgements
Benchmark and policy frameworks are public UK Government publications (gov.uk). 
No proprietary material is reproduced.
Ai paired programming (Claude)
Please use the materials responsibly.
