# jooservices/dto — Agent notes

This repository belongs to JOOservices. Follow workspace root `AGENTS.md`.

Project-specific:

- PHP `>= 8.5`, single runtime Composer `require`: `psr/http-message` (interface-only)
- First public line: **`v3.0.0`**; current line: **`v3.2.0`**
- All PHP tooling via Docker (`php:8.5-cli-bookworm`)
- CI on GitHub-hosted runners; dedicated workflows only — never `jooservices/workflows`
- Constructor + `with()` are both first-class; `from()` uses source keys, `with()` uses property names only
- Lints at **max** with **no ignore**: PHPStan max, full PSR-12 PHPCS, full PHPMD rulesets, Pint `per`
- IDE: install recommended extensions (Cursor/VS Code `.vscode/`); format-on-save uses `tools/pint` (Docker). PHPStorm uses `.idea` inspection profile + Pint file watcher
