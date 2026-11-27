# BeresCMS

BeresCMS is a free, open-source, self-hosted content management system built on
the Laravel PHP framework. It is an independent fork of [Winter CMS](https://github.com/wintercms/winter)
(itself originally October CMS), developed with its own roadmap.

> [!NOTE]
> BeresCMS is not affiliated with, or endorsed by, Winter CMS or October CMS.
> Their documentation and support channels do not cover BeresCMS-specific changes.

## Project status

BeresCMS is in early development. It currently targets single-site installations.
The codebase still shares most of its structure with Winter CMS 1.2, and some
internal names (such as `winter:*` Artisan commands) remain from upstream.
These will change over time as the project diverges.

## Requirements

- PHP 8.2 or newer (tested on 8.2 – 8.5)
- Composer
- A database supported by Laravel (MySQL/MariaDB, PostgreSQL, SQLite, or SQL Server)

## Installation

```shell
git clone https://github.com/DyanGalih/berescms.git
cd berescms
composer install
```

Generate an environment file with your configuration settings:

```shell
php artisan winter:env
```

Run the database migrations and create an administrator account. The generated
password for the `admin` user is displayed in your terminal:

```shell
php artisan winter:up
```

For production, set `APP_DEBUG=false`, enable secure session cookies
(`SESSION_SECURE_COOKIE=true`), keep CSRF protection enabled, enable CMS safe
mode, and configure a real queue worker instead of the `sync` driver.

## Development

Contributor guidance, test conventions, and code-style rules are in
[AGENTS.md](AGENTS.md).

```shell
php artisan winter:test -m system -m backend -m cms   # PHPUnit test suites
vendor/bin/phpcs -nq --report=full --extensions=php <files>  # code style
```

## Relationship to Winter CMS

BeresCMS follows a tracking-fork model:

- Changes to the core modules are made where the BeresCMS roadmap requires them.
- Upstream Winter CMS fixes, especially security fixes, are merged periodically
  when they fit the project.
- BeresCMS releases and integrations do not use Winter CMS infrastructure
  (marketplace, release manifests, or Docker images).

Plugins and themes written for Winter CMS 1.2 may work, but compatibility is not
guaranteed as the projects diverge.

## Contributing

Issues and pull requests are welcome at
[github.com/DyanGalih/berescms](https://github.com/DyanGalih/berescms).
Please follow the coding standards in [phpcs.xml](phpcs.xml) and include tests for
new behavior.

## Security vulnerabilities

Please do not report security vulnerabilities in public issues. Use GitHub's
private vulnerability reporting on the
[BeresCMS repository](https://github.com/DyanGalih/berescms/security).

## License and copyright

BeresCMS is open-source software licensed under the [MIT license](LICENSE).

```text
Copyright (c) 2013-2021.03.01 October CMS
Copyright (c) 2021 Winter CMS
Copyright (c) 2026 Dyan Galih and BeresCMS contributors
```

The October CMS and Winter CMS copyright notices are retained as required by the
MIT license. Each core module (`modules/system`, `modules/backend`, `modules/cms`)
also carries its original license file.
