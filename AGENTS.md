# AGENTS.md

Context index for AI coding agents working in this repo. Index only — follow the links, don't duplicate content here.

## What this repo is

Omnisend **core WordPress plugin** (wordpress.org slug `omnisend`). Connects a WP site to an Omnisend account (OAuth) and provides the `Omnisend\SDK\V1` client that other plugins (e.g. `omnisend-connect` for WooCommerce) use to send contacts, products, categories, and events. Plugin source lives in `omnisend/` (`includes/SDK`, `includes/Internal`); JS assets build inside `omnisend/` too.

## Commands

- `composer install` — PHP deps
- `./lint.sh check` / `./lint.sh fix` — PHPCS (`./vendor/bin/phpcs -s --ignore=node_modules,build/* omnisend`; bare phpcs errors with no path)
- `./vendor/bin/phpunit tests` — unit tests (no phpunit.xml; do NOT pass `--bootstrap`, each test includes `tests/dependencies/dependencies.php` itself)
- `cd omnisend && npm run build` / `npm run lint:js` — JS build/lint
- `./zip_plugin.sh` — build the distributable plugin ZIP

## QA context

`docs/qa/` is the canonical store for this plugin's testing context — business cases carry stable `WP-BC-xxx` IDs referenced by test titles here and in polar-bear. Conventions: `docs/qa/README.md`.

## Conventions that bite agents

- The plugin is only active when installed AND connected — SDK consumers must check `is_plugin_active`, `class_exists`, `Omnisend::is_connected()` before sending.
- `readme.txt` headers (`Requires PHP`, `Tested up to`) control wordpress.org compatibility surfacing — keep them honest.
- Never commit credentials; test stores/keys live in secrets, referenced by name only.
