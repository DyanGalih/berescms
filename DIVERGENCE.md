# BeresCMS Divergence Log

This document tracks all intentional divergences between BeresCMS and upstream [Winter CMS](https://github.com/wintercms/winter) core.

Per the BeresCMS Architecture Constitution:
- Every core change or architectural divergence must be recorded here with its reason and pull request / change reference.
- Tracking these differences ensures upstream security fixes can still be evaluated and backported cleanly while BeresCMS pursues its own roadmap.

---

## Divergence Registry

| Date | Component / Area | Nature of Divergence | Reason & Impact | Reference |
|---|---|---|---|---|
| 2026-11-27 | CI / Workflows | Deleted `subsplit.yml` and `manifest.yml`; disabled `docker.yml` triggers | Upstream workflows publish to `wintercms/*` organization repositories and require upstream secrets. BeresCMS manages its own distribution. | PR #1 (`remove-winter-infra`) |
| 2026-11-27 | `modules/system`, `config/cms.php` | Decoupled marketplace update calls (`disableUpdates = true`, `updateServer = null`) | Prevents unconfigured outbound HTTP requests to `api.wintercms.com`. Safe-by-default for self-hosted instances. | PR #1 (`remove-winter-infra`) |
| 2026-11-27 | `composer.json` | Renamed package to `berescms/berescms`; updated support/metadata URLs | Reflects BeresCMS identity as an independent tracking fork. | `init-divergence-log` |
| 2026-11-27 | `composer.json` | Pinned `winter/storm` to `~1.2.15` (previously `dev-develop as 1.2`) | Deterministic build stability; prevents upstream development branches from introducing surprise breaking changes. | `init-divergence-log` |
| 2026-11-27 | `composer.json`, Foundation | Forked `storm` to `DyanGalih/storm` (`dev-feature/modern-laravel as 1.3`, tagged `v1.3.0-alpha1`) and upgraded to modern Laravel (`^11.0\|^12.0`, PHP `>=8.2`) | Executes Hard Fork Transition Triggers 1 & 2 to retire EOL Laravel 9 and run on modern PHP 8.2+. | `upgrade-to-modern-laravel` |
| 2026-11-27 | Monorepo Structure | Consolidated `System`, `Backend`, and `Cms` into unified root autoloading; deleted internal module `composer.json` files and retired `wikimedia/composer-merge-plugin` | Transforms BeresCMS into a clean cohesive monolith; eliminates artificial package boundaries and Composer dependency resolution conflicts. | `consolidate-core-modules` |
| 2026-11-27 | Monorepo / Storm | Absorbed Storm library directly into `modules/storm`; configured root PSR-4 autoloading for `Winter\Storm\` and declared `"replace"` for `winter/storm` and `berescms/storm` | Eliminates external Storm repository maintenance, Git VCS entries, and multi-repo friction; completes monolith consolidation. | `group-absorb-storm-into-monorepo` (PR #4) |
| 2026-11-27 | Storm / System | Decoupled Storm syntax parser upward references to System module; added dynamic filter registration and configurable file model resolution | Enforces clean modular monolith layering (foundation has zero upward coupling to domain modules). | `group-decouple-storm-syntax-parser` |
| 2026-11-27 | Repository Root | Updated `README.md` and added 2026 copyright line to `LICENSE` | Clarified independent fork status, MIT license attribution, and project governance. | `ag-init` |
| 2026-11-27 | Developer Experience / CI | Configured PHPStan 2.x static analysis (`phpstan.neon.dist`, `phpstan-baseline.neon`, `phpstan-bootstrap.php`), added `composer phpstan` script, and added CI analysis job | Enforces static type checking and prevents regressions across `storm`, `system`, `backend`, and `cms` modules while baselining existing legacy code. | `group-setup-phpstan` |

---

## Upstream Sync Guidelines
1. Sync or cherry-pick upstream Winter fixes on a monthly cadence or whenever upstream publishes security advisories.
2. Review incoming diffs against the table above. If an upstream change touches any divergent area (such as `UpdateManager.php`), resolve conflicts in favor of BeresCMS governance rules.
3. If an upstream sync or framework upgrade (e.g. Laravel) requires breaking changes across core models or Storm, initiate a formal Hard Fork Proposal via OpenSpec.
