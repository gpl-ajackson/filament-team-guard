# Filament Team Guard — Enhanced Laravel Starter Kit Built With Filament

[![Latest Version on Packagist](https://img.shields.io/packagist/v/mominalzaraa/filament-team-guard?style=flat-square&logo=packagist)](https://packagist.org/packages/mominalzaraa/filament-team-guard)
[![Total Downloads](https://img.shields.io/packagist/dt/mominalzaraa/filament-team-guard?style=flat-square&logo=packagist)](https://packagist.org/packages/mominalzaraa/filament-team-guard)
[![GitHub Tests](https://img.shields.io/github/actions/workflow/status/MominAlZaraa/filament-team-guard/run-tests.yml?branch=main&label=tests&style=flat-square&logo=github)](https://github.com/MominAlZaraa/filament-team-guard/actions/workflows/run-tests.yml)
[![Code Style](https://img.shields.io/github/actions/workflow/status/MominAlZaraa/filament-team-guard/code-style.yml?branch=main&label=code%20style&style=flat-square&logo=github)](https://github.com/MominAlZaraa/filament-team-guard/actions/workflows/code-style.yml)
[![License](https://img.shields.io/packagist/l/mominalzaraa/filament-team-guard?style=flat-square)](https://github.com/MominAlZaraa/filament-team-guard/blob/main/LICENSE.md)
[![PHP Version](https://img.shields.io/packagist/dependency-v/mominalzaraa/filament-team-guard/php?style=flat-square&logo=php)](https://packagist.org/packages/mominalzaraa/filament-team-guard)
[![Sponsor](https://img.shields.io/github/sponsors/MominAlZaraa?style=flat-square&logo=github)](https://github.com/sponsors/MominAlZaraa)
[![Plumb score](https://plumbphp.dev/badges/mominalzaraa/filament-team-guard/composite.svg)](https://plumbphp.dev/mominalzaraa/filament-team-guard)
[![Plumb security score](https://plumbphp.dev/badges/mominalzaraa/filament-team-guard/security.svg)](https://plumbphp.dev/mominalzaraa/filament-team-guard)

![Filament Team Guard Banner](https://raw.githubusercontent.com/MominAlZaraa/filament-team-guard/main/.github/plugin-banner.jpg)

**Requirements**: PHP ^8.3 | ^8.4 | ^8.5 · Laravel ^12.0 · Filament ^5.0 (Livewire ^4.0, Tailwind ^4.0)

Enhanced Laravel starter kit built with Filament, inspired by [Laravel Jetstream](https://github.com/laravel/jetstream) (discontinued). Brings **team features, mature data handling, and Filament UI**—auth, registration, 2FA, passkeys, session management, API tokens, teams. Skip boilerplate, start building.

**Supported**: PHP ^8.3–^8.5, Filament ^5.0, Laravel ^12.0, Livewire ^4.0, Tailwind ^4.0 · **Deprecated**: PHP &lt;8.3, Filament v4 (use [v1.x](https://github.com/MominAlZaraa/filament-team-guard/releases)), Laravel &lt;12.

---

## Installation

```bash
composer require mominalzaraa/filament-team-guard
php artisan filament-team-guard:install --teams --api
```

Omit `--teams` / `--api` if not needed. Action classes and email templates are published for customization.

---

## Features

- **Auth** — Login, register, password reset, email verification
- **Profile** — Photo, info, password, sessions, delete account
- **2FA & passkeys** — Embedded TOTP, recovery codes, [Spatie Laravel Passkeys](https://github.com/spatie/laravel-passkeys). Enable via `->twoFactorAuthentication()`.
- **Cloudflare Turnstile** — Optional `->turnstile()` on auth + 2FA challenge/recovery. [njoguamos/laravel-turnstile](https://github.com/njoguamos/laravel-turnstile); set `TURNSTILE_*` in `.env`, run `php artisan turnstile:install`.
- **Teams** — Create, invite, roles, member management (add/remove/update role)
- **API tokens** — Optional Sanctum-style tokens
- **i18n** — Publishable locale-first language files

---

## Enhanced vs stephenjude/filament-jetstream

- **Publishable Action classes** (Jetstream pattern): `UpdateUserProfileInformation`, `InviteTeamMember`, `AddTeamMember`, `RemoveTeamMember`, `UpdateTeamMemberRole`, `CreateTeam`, `UpdateTeamName`, `DeleteTeam`, `ValidateTeamDeletion`, `DeleteUser` — all with contracts.
- **Email invitations** — Registered/unregistered flow; “Create Account” when needed; session-based redirects; no auto-registration.
- **Profile fields** — Override `getFieldComponents()`, `getSectionHeading()`, `getSectionDescription()` in published Action; add translations in `lang/{locale}/filament-team-guard.php`.
- **Team management** — Custom `Role` rule, `UpdateTeamMemberRole`, `RemoveTeamMember`, `ValidateTeamDeletion`.
- **Publishable** — Actions, emails, lang; locale-first translations with merge/override.

---

## Customization

| What | Command |
|------|--------|
| Action classes | `php artisan vendor:publish --tag=filament-team-guard-actions` |
| Language files | `php artisan vendor:publish --tag=filament-team-guard-lang` |
| Email templates | `php artisan vendor:publish --tag=filament-team-guard-email-templates` |

**Profile field example**: Publish actions + lang → add key in `lang/en/filament-team-guard.php` → in `UpdateUserProfileInformation` override `getFieldComponents()` and add a `TextInput::make('surname')` (and translation) → add `surname` to User `$fillable`.

---

## Configuration (snippets)

**Profile (2FA, passkeys, photo, etc.)**
```php
JetstreamPlugin::make()
    ->configureUserModel(userModel: User::class)
    ->profilePhoto(condition: fn() => true, disk: 'public')
    ->deleteAccount(condition: fn() => true)
    ->updatePassword(condition: fn() => true, Password::default())
    ->profileInformation(condition: fn() => true)
    ->logoutBrowserSessions(condition: fn() => true)
    ->twoFactorAuthentication(
        condition: fn() => auth()->check(),
        forced: fn() => app()->isProduction(),
        enablePasskey: fn() => Feature::active('passkey'),
        requiresPassword: fn() => app()->isProduction(),
    );
```

**Teams**
```php
JetstreamPlugin::make()
    ->teams(condition: fn() => Feature::active('teams'), acceptTeamInvitation: fn($id) => JetstreamPlugin::make()->defaultAcceptTeamInvitation())
    ->configureTeamModels(teamModel: Team::class, roleModel: Role::class, membershipModel: Membership::class, teamInvitationModel: TeamInvitation::class);
```

**API tokens**
```php
JetstreamPlugin::make()->apiTokens(condition: fn() => Feature::active('api'), permissions: fn() => ['create','read','update','delete'], menuItemLabel: fn() => 'API Tokens', menuItemIcon: fn() => 'heroicon-o-key');
```

---

## Existing Laravel projects

- **Profile**: `php artisan vendor:publish --tag=filament-team-guard-migrations --tag=passkeys-migrations` → add `InteractsWithProfile`, `HasProfilePhoto`, implement `HasAvatar` & `HasPasskeys` on User; hide 2FA fields; append `profile_photo_url`.
- **Teams**: `php artisan vendor:publish --tag=filament-team-guard-team-migration` → add `InteractsWithTeams`, implement `HasTenants` on User.
- **API**: Same team migration tag → add `HasApiTokens` on User.

---

## Package development

Run `composer install` in the package root for tests. `vendor/` is in `.gitignore` and not distributed; `composer require mominalzaraa/filament-team-guard` in an app only pulls package source—the app’s Composer resolves Filament once (no duplication). `.gitattributes` has `/vendor export-ignore`.

---

## Testing · Changelog · Contributing

- **Tests**: `composer test`
- **Changelog**: [CHANGELOG.md](CHANGELOG.md)
- **Contributing**: [.github/CONTRIBUTING.md](.github/CONTRIBUTING.md)

## Credits

- [Laravel Jetstream](https://github.com/laravel/jetstream) (discontinued) — team features & Action pattern
- [stephenjude/filament-jetstream](https://github.com/stephenjude/filament-jetstream) — Filament port
- [Momin Al Zaraa](https://github.com/MominAlZaraa) — this enhanced version · [Issues](https://github.com/MominAlZaraa/filament-team-guard/issues) · support@mominpert.com

**License**: MIT. See [LICENSE.md](LICENSE.md).
