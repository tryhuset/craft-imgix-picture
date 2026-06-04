# CLAUDE.md

Guidance for Claude Code when working in this repository.

## Project

`apt/craft-imgix-picture` — a Craft CMS 5 plugin that generates responsive image
tags and JSON objects from a config file using the imgIX service. Namespace:
`apt\craftimgixpicture\` (PSR-4, mapped to `src/`).

## Local environment: DDEV

This project runs in **DDEV** locally. There is no host-level PHP/Composer setup —
run all PHP, Composer, and Craft commands **inside the DDEV web container**, e.g.:

```bash
ddev composer <args>      # NOT bare `composer`
ddev php <args>
ddev craft <args>
```

DDEV details: PHP 8.2, nginx-fpm, MariaDB 10.11, project URL
`https://craft-imgix-picture.ddev.site`. Use `ddev describe` / `ddev start` to check
or bring up services.

## Dependencies & security

- Runtime requirements live in `composer.json`; the resolved tree is in `composer.lock`.
  Dependabot alerts are almost always against **transitive** deps in `composer.lock`.
- Patch them with a targeted `ddev composer update <pkg ...> --with-all-dependencies`.
  Many transitive versions are pinned by `craftcms/cms`, so include `craftcms/cms` in
  the update when a patched version is otherwise blocked (it stays within the
  `^5.9.18` constraint).
- Verify with `ddev composer audit` (expect "No security vulnerability advisories found.").

## Releasing

1. Bump `version` in `composer.json`.
2. Add a `CHANGELOG.md` entry.
3. Commit, then create a git tag and a GitHub release (`gh release create`).
   Repo: `tryhuset/craft-imgix-picture`, default branch `master`.
