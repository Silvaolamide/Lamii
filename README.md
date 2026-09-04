# Lami

Lami helps you discover and connect with people around you in real time.

> **Brand:** The product name is **Lami**. The GitHub repository remains `Silvaolamide/Lamii` for continuity; `Lamii` should not be used as the customer-facing product name.

## Development

Lami is being developed as a Laravel 12 application.

## Product and UI/UX direction

The current UI/UX source of truth is documented in [`docs/UI-UX-PLAN.md`](docs/UI-UX-PLAN.md).

The selected visual direction is the second Lami discovery/profile concept supplied during product design: deep teal foundations, strong saturated color blending, medium scrollable discovery cards, nearby-person counts, immersive profile details, fluid interactions, and a mobile-first/PWA experience.

## Branch strategy

- `main` — integration/default branch
- `production` — production/deployment branch
- `feature/*` — isolated feature development

Feature work is developed and validated on feature branches. Production-ready changes are promoted deliberately to `production` after review and passing CI.
