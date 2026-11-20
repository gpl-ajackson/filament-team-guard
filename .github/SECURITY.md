# Security Policy

## Supported versions

Security fixes are applied to the latest release line of this package.

| Version | Supported |
| ------- | --------- |
| 2.x     | ✅        |
| 1.x     | ❌ (Filament v4 line; upgrade to 2.x when possible) |
| < 1.0   | ❌        |

## Reporting a vulnerability

Please **do not** open a public GitHub issue for security vulnerabilities.

Report privately by emailing **[support@mominpert.com](mailto:support@mominpert.com)** with:

- A clear description of the issue and impact
- Steps to reproduce (or a minimal proof of concept)
- Affected package version(s) and your Laravel / Filament versions

You can also use [GitHub private vulnerability reporting](https://github.com/MominAlZaraa/filament-team-guard/security/advisories/new) if it is enabled on this repository.

## What to expect

- We aim to acknowledge reports within **72 hours**
- We will keep you informed while investigating and preparing a fix
- Once a fix is available, we will coordinate disclosure and credit you if you wish

## Scope

Filament Team Guard provides authentication, teams, 2FA/passkeys, API tokens, and related Filament panel features. Reports are especially welcome for issues that could:

- Bypass authentication, 2FA, passkeys, or invitation/authorization checks
- Escalate privileges across teams or roles
- Expose session tokens, recovery codes, or API tokens
- Abuse Cloudflare Turnstile integration in a way that weakens auth flows

Out of scope: vulnerabilities solely in upstream dependencies (Laravel, Filament, Sanctum, Spatie Passkeys, Turnstile packages) unless our usage introduces a distinct risk.

Thank you for helping keep Filament Team Guard and its users safe.
