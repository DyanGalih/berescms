<!-- architecture-guard:start -->

## Architecture Guard

Use these governance rules across all SDD workflow phases.
Read the installed governance commands or skills at:
- `.agent/skills/*`
- `.opencode/commands/*`

- **Ponytail Core Contract**: Before spec/plan/tasks/implement, read and apply the ponytail pragmatism contract.
- **After each phase**: Run architecture review for boundary drift, DRY violations, and repository hygiene.
- **SDD Adapter Resolution**: Project uses the adapter selected during CLI init. Read `.architecture-guard/adapters/resolve.md` before first command.
- **Engine Resources**: Lean installs use the callable Architecture Guard CLI for shared templates, presets, hygiene rules, and Sonar rules; vendor/full mode copies those resources under .architecture-guard/. The hygiene check remains CLI-backed.
- **Direct Discovery Guard**: When checking adapter-resolved hidden paths such as `.architecture-guard/**`, use direct directory inspection first, then read listed files. A Glob no-match result is inconclusive and MUST NOT be reported as missing. Report a path as unavailable only after direct inspection confirms it does not exist. Always include loaded rule files and counts in governance reports.
<!-- architecture-guard:end -->


## Security Review

Use these continuous security governance rules across all code, plan, task, and review phases.
Read installed security prompts and skills at:
- `.agent/skills/*`
- `.opencode/commands/*`

- **Mandatory Whitebox Verification**: Review all external entrypoints, SQL queries, auth flows, and cryptographic operations before production release.
- **Strict Evidence Standards**: Ensure all security reviews include ISO timestamps, canonical severity counts (CRITICAL, HIGH, MEDIUM, LOW), and OWASP / CWE identifiers.
- **Non-Destructive Execution**: Never wipe databases or execute arbitrary destructive commands.


# Agent Guide — BeresCMS Application (`src/`)

BeresCMS is a single-site modular monolith and an independent open-source fork of Winter CMS with its own roadmap.

All changes in this repository must comply with the governing project constitutions:
- **Architecture Constitution**: `openspec/architecture.md`
- **Security Constitution**: `openspec/security.md`

---

## 1. Governance & Architectural Rules

- **Repository Separation**: The root workspace repository owns specification, OpenSpec artifacts, and architecture governance. The `src/` directory is an independent application repository with its own remote. Core application code, tests, migrations, and runtime configs belong in `src/`.
- **Core Modules**: Core modules live in `modules/{system,backend,cms,storm}`. Note that the **Storm** library is absorbed directly into `modules/storm/` as a core monorepo module (declared in root `composer.json` via PSR-4 autoloading).
- **Fork & Upstream Policy**: BeresCMS diverges where its roadmap requires. Every intentional divergence from upstream Winter core must be recorded in the divergence log. BeresCMS must not publish to or depend on external Winter CMS infrastructure (e.g. subsplit workflows, Winter meta manifest, or external update servers).
- **Ponytail Core Contract**: Apply pragmatic, minimally invasive diffs. Avoid speculative abstractions, unnecessary refactoring, or introducing repository layers when direct ORM usage with Winter/Storm model base suffices.
- **Non-Destructive Operations**: Never drop tables, schemas, or wipe persistent databases.

---

## 2. Security Boundaries & Standards

- **Server-Side Execution Permissions**: CMS page, layout, and partial code sections execute server-side PHP. Treat editing permissions as code-execution privilege, restrict to trusted administrators, and ensure `cms.enableSafeMode` is enabled in production.
- **Twig Sandbox**: Preserve `system/twig/SecurityPolicy.php`; never weaken blocked methods or permit raw SQL execution from templates.
- **Production Configuration Defaults**: Ensure production environments run with `APP_DEBUG=false`, secure session cookies, CSRF protection enabled, and a persistent queue driver.
- **SQL & Input Handling**: Use parameterized queries for untrusted input. Never concatenate user input into raw SQL. Dynamic identifiers (table names, column names) must be strictly allowlisted.
- **Dependency Life Cycle**: Never add dependencies or ignore security advisories without recorded governance approval.

---

## 3. Where to Look (in this order)

BeresCMS sits on top of Storm (`modules/storm/`), which sits on top of Laravel, which sits on top of Symfony components. The helper you want almost certainly already exists at one of those layers. **Search Storm → Laravel → Symfony before writing any "small utility"**:

1. **Storm itself (`modules/storm/src/`)**:
   - `Filesystem/` — `PathResolver` (`resolve`, `within`, `join`, `standardize`), `Filesystem` (`isAbsolutePath`, `symbolizePath`, `existsInsensitive`, `chmodRecursive`)
   - `Support/` — strings, arrays, class loading
   - `Database/` — Model base class, extended relations, deferred bindings
   - `Network/`, `Html/`, `Parse/`, `Halcyon/`, `Auth/`
   - Path helpers (autoloaded): `themes_path()`, `plugins_path()`, `media_path()`, `uploads_path()`, `temp_path()` — use these instead of `base_path('themes')` etc.

2. **Laravel (Illuminate)**:
   - `Illuminate\Support\Str` — `startsWith/endsWith/contains/before/after/between/slug/camel/snake/kebab/studly/random/uuid/limit/mask/finish/start/of/headline/title`.
   - `Illuminate\Support\Arr` — `get/set/has/forget/only/except/dot/undot/flatten/pluck/wrap/first/last/where`.
   - `Illuminate\Support\Collection` (via `collect()`) — chainable map/filter/reduce.
   - `Illuminate\Filesystem\Filesystem` — `deleteDirectory()`, `cleanDirectory()`, `copyDirectory()`, `moveDirectory()`, `allFiles()`, `glob()`, `isDirectory()`, `prepend()`, `append()`, `replace()`.
   - Global helpers: `data_get/set/fill`, `value`, `tap`, `optional`, `transform`, `head`, `last`, `class_basename`, `now`, `today`, `e`, `__`/`trans`, `cache`, `config`, `env`, `app`, `resolve`, `route`, `url`, `report`, `rescue`, `retry`, `throw_if`/`unless`, `abort`.
   - Facades: `Cache`, `Config`, `DB`, `Event`, `File`, `Hash`, `Http`, `Lang`, `Log`, `Mail`, `Queue`, `Redis`, `Route`, `Schema`, `Session`, `Storage`, `URL`, `Validator`, `View`.

3. **Symfony components** at `vendor/symfony/`:
   - `Finder` (`Finder::create()->files()->name('*.less')->in($dir)`)
   - `Filesystem` (`dumpFile()` atomic write, `mirror()`, `mkdir()`, `remove()`, `symlink()`)
   - `Process` (`Process([$cmd, ...$args])->mustRun()`)
   - `Yaml` (`Yaml::parse()`)

---

## 4. Concrete Substitutions Worth Memorising

Paths and filesystem:

| If you reach for… | Use this instead |
|---|---|
| `realpath()` + null-check + slash-trim | `\Winter\Storm\Filesystem\PathResolver::resolve()` |
| `str_starts_with($path, $root)` to gate file access | `PathResolver::within($path, $root)` — separator-boundary safe |
| Manual `base_path('themes')` / `base_path('plugins')` | `themes_path()` / `plugins_path()` (Storm's autoloaded helpers) |
| Recursive `rmrf` in tests | `\File::deleteDirectory($path)` (Laravel facade) |
| Detect absolute path | `(new \Winter\Storm\Filesystem\Filesystem())->isAbsolutePath($path)` |
| Custom path-symbol resolution (`~/...`) | `(new \Winter\Storm\Filesystem\Filesystem())->symbolizePath($path)` |
| `str_replace('\\', '/', $path)` (cross-platform comparison) | `(new \Winter\Storm\Filesystem\Filesystem())->normalizePath($path)` |
| `str_replace('/', DIRECTORY_SEPARATOR, $path)` (handing to OS API) | `\Winter\Storm\Filesystem\PathResolver::standardize($path)` |
| Atomic file write (avoid partial-write races) | `(new \Symfony\Component\Filesystem\Filesystem())->dumpFile($path, $contents)` |
| Find files matching a pattern | `\Symfony\Component\Finder\Finder::create()->files()->name('*.ext')->in($dir)` |

Strings and arrays:

| If you reach for… | Use this instead |
|---|---|
| `preg_match('/^prefix/', $s)` | `Str::startsWith($s, 'prefix')` |
| Manual `strpos !== false` | `Str::contains($s, $needle)` |
| `strtolower`-then-replace slug generation | `Str::slug($s)` |
| Random hex/string for tmp paths, tokens | `Str::random()` / `Str::uuid()` |
| Deep array key access with null safety | `data_get($array, 'a.b.c', $default)` |
| Pulling subset of array keys | `Arr::only($array, [...])` / `Arr::except($array, [...])` |
| Chained map / filter / reduce on array | `collect($array)->filter(...)->map(...)->values()->all()` |

---

## 5. Autoloading & File Placement

Modules and plugins are autoloaded by `Winter\Storm\Support\ClassLoader` (`ClassLoader::load()`) alongside PSR-4 autoloading in `composer.json`:
- **Directory segments are lower-cased** to form the path, while class files keep PascalCase names (e.g. `System\Twig\SecurityPolicy\SafeCollection` resolves to `modules/system/twig/securitypolicy/SafeCollection.php`).
- New sub-namespace directories must be lowercase on disk (`System\Twig\Node` → `modules/system/twig/node/`, not `Node/`).

---

## 6. Testing Guidelines

- **Base Test Classes**: Backend/CMS/System tests extend `System\Tests\Bootstrap\PluginTestCase` (boots Laravel, plugins, auth) or `System\Tests\Bootstrap\TestCase` (boots framework without plugins). Storm unit tests extend `Winter\Storm\Tests\TestCase` or `Winter\Storm\Tests\DbTestCase`.
- **Fast Test Execution**: Reaching for `PluginTestCase` requires `use System\Tests\Concerns\MigratesDatabaseOnce;` to avoid migrating all 43 tables on every individual test method.
- **Fixture Hygiene**: Never mutate tracked files or shared `storage/` paths in tests. Clean up temporary files in `tearDown()`.

---

## 7. Code Quality & Pre-Commit Checks

CI enforces code quality with `phpcs` using the project's `phpcs.xml` ruleset. Run checks before pushing:

```bash
vendor/bin/phpcs -nq --report=full --extensions=php <changed .php files>
```
