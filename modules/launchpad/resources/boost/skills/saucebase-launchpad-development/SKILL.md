---
name: saucebase-launchpad-development
description: Work on the Launchpad module (modules/launchpad). Use when changing its routes, models, pages, or tests.
---

# Launchpad Module

Paths are relative to `modules/launchpad/` unless they say otherwise. Commands run from the application root.

## Key Files

| Layer | Files |
|-------|-------|
| Provider | `LaunchpadServiceProvider` |
| Controller | `LaunchpadController` |
| Filament | `LaunchpadPlugin` |

## Testing

```bash
php artisan test --testsuite=Modules --filter='^Modules\\Launchpad\\Tests'  # PHPUnit
npx playwright test --project="@launchpad*"                               # E2E
```
