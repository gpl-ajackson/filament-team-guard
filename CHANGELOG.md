# Changelog

All notable changes to `mominalzaraa/filament-team-guard` will be documented in this file.

This is an enhanced version of [stephenjude/filament-jetstream](https://github.com/stephenjude/filament-jetstream), which itself is inspired by the original [Laravel Jetstream](https://github.com/laravel/jetstream) package.

## v2.1.1 - 2026-07-03

### Fixes

- **`UpdateTeamName` Livewire component** — Team name updates now delegate to the `UpdatesTeamNames` contract instead of calling `$team->update()` directly. Custom published `App\Actions\FilamentJetstream\UpdateTeamName` classes are respected again (reported by [@gpl-ajackson](https://github.com/gpl-ajackson) in [#1](https://github.com/MominAlZaraa/filament-team-guard/pull/1)).

### Tests

- Added **integration and regression tests** for default and custom `UpdatesTeamNames` bindings, plus isolated SQLite test configuration for the package test suite.

## v2.1.0 - 2026-03-25

### Summary

Major compatibility release for **Laravel 13**, **Filament 5**, and **PHP 8.3+**, plus stronger test coverage for team invitations and a more reliable GitHub Actions pipeline.

### What’s new

- **Platform**: **PHP ^8.3**; **Laravel 13**-compatible dependencies; **Filament 5**; **Orchestra Testbench 11** and **Pest 4** for package development.
- **Composer**: Valid **`repositories`** schema (publish-friendly); refreshed **lockfile** and dependency graph for the new stack.
- **Stubs / apps**: Filament panel stubs updated to use **`PreventRequestForgery`** (Laravel 13 CSRF middleware naming).
- **Tests**: New **feature tests** around **team invitation** authorization / cross-team boundaries; existing Pest suite kept green on the upgraded stack.
- **DX / repo hygiene**: **`.phpunit.cache/`** ignored and removed from version control; **Pint** formatting across factories, `src/`, and stubs.
- **CI (GitHub Actions)**:
  - Matrix: **PHP 8.3–8.5**, **Laravel 13.***, **Testbench 11.***
  - **`actions/checkout@v6`** and **`FORCE_JAVASCRIPT_ACTIONS_TO_NODE24`** to align with current Actions runner guidance
  - **`pest --ci --no-coverage`** (no PCOV/Xdebug on runner)
  - **`imagick`** dropped from `setup-php` extensions list (reduces flaky installs)
  - **`phpunit.xml.dist`**: `failOnDeprecation="false"`, `beStrictAboutOutputDuringTests="false"` to avoid false failures from vendor noise on newer PHP minors
  - Ensures **`build/logs`** exists before tests for report output paths
  

### Upgrade notes for consumers

- Target **PHP ≥ 8.3**, **Laravel 13**, and **Filament 5** when adopting this release.
- If you vendor or merge package stubs, re-sync **`AppPanelProvider`** (or equivalent) for **`PreventRequestForgery`**.
- After `composer update`, run your panel/asset publish steps if your workflow requires them (`filament:upgrade`, etc., per your app).

## v2.0.4 - 2026-03-05

### Fixes

#### 1) Turnstile enforcement on default auth pages

Fixed a security/validation gap where Turnstile UI could show invalid state, but authentication could still proceed when panels used Filament default auth pages.

##### Root cause

- `->turnstile()` rendered the widget via hooks.
- Default Filament auth pages (`->login()`, `->passwordReset()`, `->emailVerification()`, etc.) were not automatically swapped to the package Turnstile-validating pages.
- Result: widget visible, but package Turnstile validation logic did not run in those default routes.

##### Fix

When `->turnstile()` is enabled, the plugin now automatically swaps **Filament default auth page actions** to Turnstile-aware package pages:

- `Filament\Auth\Pages\Login` → `Filament\Jetstream\Pages\Auth\Login`
- `Filament\Auth\Pages\Register` → `Filament\Jetstream\Pages\Auth\Register`
- `Filament\Auth\Pages\PasswordReset\RequestPasswordReset` → `Filament\Jetstream\Pages\Auth\PasswordReset\RequestPasswordReset`
- `Filament\Auth\Pages\PasswordReset\ResetPassword` → `Filament\Jetstream\Pages\Auth\PasswordReset\ResetPassword`
- `Filament\Auth\Pages\EmailVerification\EmailVerificationPrompt` → `Filament\Jetstream\Pages\Auth\EmailVerification\EmailVerificationPrompt`

Custom auth page overrides remain untouched.

##### Regression coverage

- Added tests to confirm:
  - default pages are swapped when Turnstile is enabled
  - custom auth pages are not overridden
  


---

#### 2) Filament route override compatibility (PHPStan/Larastan CI fix)

Fixed static analysis failure caused by Filament signature changes for page route registration.

##### Problem

`Filament\Jetstream\Pages\ApiTokens::registerRoutes()` overrode Filament `Page::registerRoutes()` but missed the optional second `$configuration` parameter (reported by CI).

##### Fix

Updated method signature in `ApiTokens` page to accept the optional second argument in a cross-version-safe way:

- from: `registerRoutes(Panel $panel): void`
- to: `registerRoutes(Panel $panel, mixed $configuration = null): void`

This restores compatibility with Filament’s current contract and avoids override errors in Larastan/PHPStan.


---

#### Validation

- Pint formatting passed
- PHPStan/Larastan passed (`vendor/bin/phpstan analyse src tests --memory-limit=2G`)
- Pest test suite passed

## v2.0.3 - 2026-02-01

### Bug Fixes

**Profile page when teams are disabled**

- `EditProfile::mount()` no longer assumes the user has a `currentTeam` relationship. It now checks `Jetstream::plugin()->hasTeamsFeatures()` and `method_exists($user, 'currentTeam')` before reading `currentTeam` or setting the tenant. Fixes "Public property [$turnstileResponse] not found" / profile error when using the plugin without teams (no `->teams()`, User without `InteractsWithTeams`).

**Delete account redirect after sign-out**

- `DeleteAccount` success redirect now uses `Filament::getLoginUrl()` instead of `route('login')`. Filament panels do not register a named `login` route; using it caused "Route [login] not defined" when opening the profile page (which renders the delete-account section). Redirect after account deletion now correctly goes to the panel login URL (e.g. `/admin/login`).


---

## Bug Fix: Turnstile auto enabling auth pages - 2026-01-31

### v2.0.1 - 2026-01-31

#### Bug Fixes

**Turnstile no longer enables panel auth routes automatically**

- Fixed bug where enabling `->turnstile()` caused the plugin to automatically set `->login()`, `->passwordReset()`, and `->emailVerification()` on the panel, enabling Filament auth routes even when the application uses Fortify or other auth for login/register.
- Turnstile is now applied only via render hooks; it no longer enables login, register, password reset, or email verification pages. Those routes are only used when the application explicitly sets them on the panel.
- Turnstile continues to apply on 2FA challenge/recovery pages (when two-factor is enabled) and on any panel auth pages that the application explicitly configures.
- Updated `turnstile()` docblock to clarify that it only applies to auth forms that are explicitly enabled on the panel.

## v2.0.2 - 2026-01-31

### Changed

- **PHP support** — Composer requirement relaxed from `^8.3|^8.4|^8.5` to `^8.2` so the package can be used on PHP 8.2, 8.3, 8.4, and 8.5 (e.g. servers on 8.4 until 8.5 is available).

## v2.0.1 - 2026-01-31

### Bug Fixes

**Turnstile no longer enables panel auth routes automatically**

- Fixed bug where enabling `->turnstile()` caused the plugin to automatically set `->login()`, `->passwordReset()`, and `->emailVerification()` on the panel, enabling Filament auth routes even when the application uses Fortify or other auth for login/register.
- Turnstile is now applied only via render hooks; it no longer enables login, register, password reset, or email verification pages. Those routes are only used when the application explicitly sets them on the panel.
- Turnstile continues to apply on 2FA challenge/recovery pages (when two-factor is enabled) and on any panel auth pages that the application explicitly configures.
- Updated `turnstile()` docblock to clarify that it only applies to auth forms that are explicitly enabled on the panel.

## v2.0.0 - 2026-01-30

### Added

- **Embedded 2FA & passkeys** — TOTP, recovery codes, and [Spatie Laravel Passkeys](https://github.com/spatie/laravel-passkeys) integrated into Filament panels (challenge/recovery pages, profile 2FA/passkey UI). New `Filament\Jetstream\TwoFactor` namespace: `TwoFactorAuthenticatable`, actions (enable/confirm/disable 2FA, regenerate recovery codes), middleware, `TwoFactorAuthenticationPlugin`. Translation file `resources/lang/en/two_factor.php`. No dependency on `stephenjude/filament-two-factor-authentication`.
- **Cloudflare Turnstile** — Optional `->turnstile()` on auth (login, register, password reset, email verification) and on 2FA challenge/recovery pages via [njoguamos/laravel-turnstile](https://github.com/njoguamos/laravel-turnstile). Token passed as action parameter for reliable server-side validation.
- **PHP 8.5** — Composer allows PHP `^8.5`. Internal use of `array_first()` where available (PHP 8.5+), with fallbacks for 8.3/8.4.
- **2FA event dispatching** — Two-factor events extend base with `Dispatchable`, `InteractsWithSockets`, `SerializesModels` so `::dispatch()` works.
- **README** — Version support table; "Package development" (vendor not distributed, no Filament duplication); features list (Turnstile, 2FA/passkeys).
- **.gitattributes** — `/vendor export-ignore` so dist archives never include vendor.

### Changed (Breaking)

- **Filament** — Requirement `^4.0` → `^5.0` (Livewire ^4.0, Tailwind ^4.0). v2.x is Filament 5 only; use [v1.x](https://github.com/MominAlZaraa/filament-team-guard/releases) for Filament 4.
- **Livewire v4** — Component names use **dot notation** (e.g. `filament-team-guard.livewire.profile.update-profile-information`) instead of `::` so Finder resolves registered aliases.
- **2FA** — Removed `stephenjude/filament-two-factor-authentication`; all 2FA in-package. `filament-team-guard:install` no longer publishes `filament-two-factor-authentication-migrations`; uses Jetstream migrations + `passkeys-migrations`.
- **2FA Challenge/Recovery** — Translation key `filament-team-guard::form.code.hint` → `filament-team-guard::default.form.code.hint`. Redirects use Livewire `redirectIntended()` (no `redirect()->intended()` return).
- **README** — Compact layout; installation, features, customization, and config summarized.

### Credits

- 2FA implementation adapted from [stephenjude/filament-two-factor-authentication](https://github.com/stephenjude/filament-two-factor-authentication) by Stephen Jude; attribution in classes and lang.
- Upgrading from v1.x: use Filament ^5.0 and Livewire ^4.0; remove `stephenjude/filament-two-factor-authentication`; keep `InteractsWIthProfile` on User (it uses embedded 2FA).


---

## v1.0.4 - 2026-01-01

### 🐛 Bug Fixes

**Translation Loader Custom Locale Merging Issue**

- Fixed custom translation keys in `lang/{locale}/filament-team-guard.php` not being merged with package translations
- Root cause: Translation loader extension was registered too late in the service provider lifecycle (`packageBooted()`), causing it to miss service resolution
- Solution: Moved `registerCustomTranslationPaths()` from `packageBooted()` to `packageRegistered()` to ensure translation loader extension is registered before service resolution
- Improved merging logic to use `array_merge_recursive()` followed by `array_replace_recursive()` to ensure custom keys are added even if they don't exist in package translations, while still allowing custom translations to override package defaults
- Prevents translation keys like `filament-team-guard::default.form.surname.label` from displaying as raw keys instead of translated text
- Enables developers to add custom translation keys to their application's locale files that will properly merge with and override package translations

## v1.0.2 - 2025-12-10

### Enhanced Avatar Generation with Context-Aware Naming

#### Enhanced

- **Context-aware avatar generation** - Default profile photo avatars now use `getFilamentName()` method when available, ensuring avatar initials match the context-aware display names shown throughout the Filament UI
- Avatar generation now respects custom naming logic (e.g., "Mr. John Doe", "Ms. Jane Doe") instead of using only the raw `name` column
- Falls back gracefully to `$this->name` if `getFilamentName()` method doesn't exist, maintaining backward compatibility

#### Technical Details

- `HasProfilePhoto::defaultProfilePhotoUrl()` now checks for `getFilamentName()` method before generating avatar initials
- Avatar initials are generated from the full context-aware name (including titles/prefixes)
- Maintains compatibility with models that don't implement `getFilamentName()`

#### Benefits

- **Consistency** - Avatar initials now match the display names shown in Filament UI
- **Context-Aware** - Supports applications with custom naming logic (e.g., gender-based titles, role-based prefixes)
- **Backward Compatible** - Works seamlessly with existing implementations
- **Better UX** - Users see consistent naming across all UI elements

#### Example

For a user with `getFilamentName()` returning "Mr. John Doe":

- **Before:** Avatar shows initials "JD" (from `name` column: "John Doe")
- **After:** Avatar shows initials "MJD" (from context-aware name: "Mr. John Doe")

This ensures the avatar reflects the same context-aware naming used throughout the application.

## v1.0.3 - 2026-01-01

### Fixed Team Name Update Serialization Issue

#### Fixed

- **UpdateTeamName Livewire Component** - Fixed issue where updating team name attempted to INSERT a new team instead of UPDATE existing team
  
  - Root cause: Team model lost its "exists" state after Livewire serialization, causing `forceFill()->save()` to attempt INSERT
  - Solution: Store `teamId` separately as a public property to avoid serialization issues
  - Solution: Use `findOrFail($teamId)` to get fresh instance from database before updating
  - Solution: Use `update()` method instead of `forceFill()->save()` to ensure UPDATE operation
  
- **UpdateTeamName Action Class** - Applied same fix for consistency
  
  - Uses `findOrFail()` to get fresh team instance
  - Uses `update()` method to ensure UPDATE operation
  

#### Technical Details

- Team model serialization through Livewire can cause the model to lose its "exists" state
- Storing team ID separately as a simple integer property ensures reliable serialization
- Always refreshing from database using `findOrFail()` ensures we have a valid existing record
- Using `update()` method is safer than `forceFill()->save()` for updating existing records

#### Impact

- Team name updates now correctly UPDATE existing teams instead of attempting to INSERT
- Prevents database errors: "Field 'user_id' doesn't have a default value"
- Prevents 404 errors when team model loses its state after serialization 1b5c00c2 (Fix: Team name update serialization issue (v1.0.2))

## v1.0.1 - 2025-01-18

### Enhanced Team Invitation Acceptance & Improved Action Customization

#### Added

- `AcceptsTeamInvitations` contract interface for team invitation acceptance
- `AcceptTeamInvitation` base action class implementing the contract
- `AcceptTeamInvitation` publishable stub with commented default code examples
- Automatic custom action discovery for team invitation acceptance
- Enhanced all action stubs with commented default code examples

#### Enhanced

- `HasTeamsFeatures` trait now uses contract binding for team invitation acceptance
- `JetstreamServiceProvider` registers `AcceptsTeamInvitations` contract
- All action stubs include comprehensive commented examples showing customization patterns
- Improved code consistency and documentation across all stubs

#### Improved

- Team invitation acceptance is now fully customizable like other actions
- Backward compatible with closure-based customization
- Cleaner stub format with generic examples (no project-specific code)
- Better developer experience with ready-to-use code snippets

#### Technical Details

- Contract-based architecture for team invitation acceptance
- Automatic discovery of custom `AcceptTeamInvitation` action
- Falls back to default behavior if custom action doesn't exist
- All stubs follow consistent formatting and include required imports

## v1.0.0 - 2025-11-18

### Release Notes - Enhanced Version

#### 🎉 Major Release: Enhanced Filament Jetstream

This release represents a **significant enhancement** over the previous `stephenjude/filament-jetstream` package, bringing back all the powerful features from the original Laravel Jetstream package with mature data handling patterns and seamless Filament UI integration.


---

#### 📦 What's New

##### ✨ Enhanced Features

###### 1. **Complete Team Management System**

- ✅ **Add Team Members** - Invite users via email with role assignment
- ✅ **Remove Team Members** - Remove members with proper authorization checks
- ✅ **Update Team Member Roles** - Change roles dynamically with validation
- ✅ **Team Deletion Validation** - Prevents accidental deletion of personal teams
- ✅ **Proper Team Invitation Flow** - Users must register/login to accept invitations (aligned with Jetstream's mature handling)

###### 2. **Contract-Based Architecture**

All actions now use contracts/interfaces, matching Laravel Jetstream's architecture:

- `UpdatesUserProfileInformation` - Profile updates
- `InvitesTeamMembers` - Team invitations
- `AddsTeamMembers` - Adding team members
- `RemovesTeamMembers` - Removing team members (NEW)
- `CreatesTeams` - Team creation
- `UpdatesTeamNames` - Team name updates
- `DeletesTeams` - Team deletion
- `DeletesUsers` - User deletion

###### 3. **Publishable Action Classes**

All action classes are now publishable for complete customization:

```bash
php artisan vendor:publish --tag=filament-team-guard-actions






```
**Available Action Stubs:**

- `UpdateUserProfileInformation.php` - Customize profile fields and section metadata
- `InviteTeamMember.php` - Customize team invitations with role validation
- `AddTeamMember.php` - Customize adding team members
- `RemoveTeamMember.php` - Customize removing team members (NEW)
- `CreateTeam.php` - Customize team creation
- `UpdateTeamName.php` - Customize team updates
- `DeleteTeam.php` - Customize team deletion
- `DeleteUser.php` - Customize user deletion with team handling

###### 4. **Enhanced Profile Field Customization**

New methods in `UpdateUserProfileInformation` action:

- `getFieldComponents()` - Returns form fields without Section wrapper (easier to customize)
- `getSectionHeading()` - Customize section title
- `getSectionDescription()` - Customize section description

**Example: Adding a surname field**

```php
public function getFieldComponents(): array
{
    return [
        // ... existing fields ...
        TextInput::make('surname')
            ->label(__('filament-team-guard::default.form.surname.label'))
            ->required(),
    ];
}






```
###### 5. **Publishable Language Files**

Language files now publish to `lang/{locale}/filament-team-guard.php` for better locale organization:

```bash
php artisan vendor:publish --tag=filament-team-guard-lang






```
**Features:**

- Automatic merging with package translations
- Custom translations override package defaults
- Support for multiple locales (en, fr, es, el, etc.)
- Easy to add custom fields and translations

###### 6. **Enhanced Email Templates**

- Conditional "Create Account" button (if registration is enabled)
- Aligned messaging with Laravel Jetstream
- Publishable for customization:

```bash
php artisan vendor:publish --tag=filament-team-guard-email-templates






```
###### 7. **Custom Validation Rules**

- `Filament\Jetstream\Rules\Role` - Validates team roles (matches Jetstream's Role rule)

###### 8. **Improved Team Invitation Flow**

**Previous behavior:** Auto-registered new users (incorrect)
**New behavior:** Users must register/login to accept invitations (correct, aligned with Jetstream)

**How it works:**

1. User receives invitation email
2. If not registered → Redirected to registration (if enabled)
3. If registered but not logged in → Redirected to login
4. After authentication → Invitation automatically accepted
5. Session-based invitation ID storage for security


---

#### 🔧 Technical Improvements

##### Package Structure

- ✅ Updated ownership to **Momin Al Zaraa**
- ✅ Added `PLUGIN_INFO.json` for Filament directory integration
- ✅ Added plugin banner image
- ✅ Updated all GitHub workflows and references
- ✅ Added `FUNDING.yml` for GitHub Sponsors

##### Code Quality

- ✅ PHPStan level increased to **5** (highest level)
- ✅ Simplified PHPStan configuration (resolved memory issues)
- ✅ All workflows tested and verified
- ✅ Code style improvements (Laravel Pint)

##### Workflow Improvements

- ✅ Fixed unstaged changes handling in CI workflows
- ✅ Updated PHP version requirements to `^8.3|^8.4`
- ✅ Improved test workflows to match localization package


---

#### 📊 Comparison with Previous Version

| Feature | Previous (`stephenjude/filament-jetstream`) | This Enhanced Version |
|---------|---------------------------------------------|----------------------|
| **Team Member Management** | Basic (add only) | Complete (add, remove, update roles) |
| **Invitation Flow** | Auto-registration (incorrect) | Register/login required (correct) |
| **Action Classes** | Limited publishability | Fully publishable with contracts |
| **Profile Customization** | Hardcoded fields | Dynamic with `getFieldComponents()` |
| **Language Files** | Vendor path only | Locale-first structure with auto-merge |
| **Email Templates** | Basic | Enhanced with conditional buttons |
| **Validation Rules** | Standard Laravel | Custom Role rule (Jetstream pattern) |
| **Team Deletion** | Basic | With validation (prevents personal team deletion) |
| **Contracts** | Partial | Complete contract-based architecture |
| **Data Handling** | Simple | Mature patterns aligned with Jetstream |


---

#### 🚀 Migration Guide

##### From `stephenjude/filament-jetstream`

1. **Update Composer**
   
   ```bash
   composer remove stephenjude/filament-jetstream
   composer require mominalzaraa/filament-team-guard
   
   
   
   
   
   
   ```
2. **Publish New Components**
   
   ```bash
   php artisan vendor:publish --tag=filament-team-guard-actions
   php artisan vendor:publish --tag=filament-team-guard-lang
   php artisan vendor:publish --tag=filament-team-guard-email-templates
   
   
   
   
   
   
   ```
3. **Update Language Files**
   
   - Old location: `lang/vendor/filament-team-guard/{locale}/default.php`
   - New location: `lang/{locale}/filament-team-guard.php`
   - Custom translations will automatically merge
   
4. **Team Invitation Flow Changes**
   
   - **Important:** The invitation flow now requires users to register/login
   - If you had custom invitation handling, review and update accordingly
   - Session-based invitation ID storage is now used
   
5. **Custom Action Classes**
   
   - If you published action classes, they should still work
   - Consider updating to use new methods like `getFieldComponents()`
   


---

#### ⚠️ Breaking Changes

##### 1. Team Invitation Flow

**Previous:** Auto-registered users when accepting invitations
**New:** Users must register/login first

**Impact:** Existing invitation links will redirect to registration/login if user is not authenticated.

##### 2. Language File Location

**Previous:** `lang/vendor/filament-team-guard/{locale}/default.php`
**New:** `lang/{locale}/filament-team-guard.php`

**Migration:** Copy your custom translations to the new location. The custom translation loader will automatically merge them.

##### 3. PHP Version Requirement

**Previous:** PHP ^8.2|^8.3|^8.4
**New:** PHP ^8.3|^8.4

**Impact:** PHP 8.2 is no longer supported.


---

#### 🎯 Key Benefits

1. **Mature Data Handling** - Aligned with Laravel Jetstream's proven patterns
2. **Complete Customization** - All components are publishable and customizable
3. **Better Developer Experience** - Contract-based architecture for easy extension
4. **Improved Security** - Proper invitation flow prevents unauthorized access
5. **Enhanced Flexibility** - Easy to add custom fields, translations, and behaviors
6. **Production Ready** - Tested workflows, PHPStan level 5, comprehensive error handling


---

#### 📝 Credits

**Enhanced by:** Momin Al Zaraa
**Based on:** [stephenjude/filament-jetstream](https://github.com/stephenjude/filament-jetstream)
**Inspired by:** [Laravel Jetstream](https://github.com/laravel/jetstream) (discontinued)


---

#### 🔗 Resources

- **Repository:** https://github.com/MominAlZaraa/filament-team-guard
- **Documentation:** See README.md for detailed usage instructions
- **Issues:** https://github.com/MominAlZaraa/filament-team-guard/issues
- **Support:** support@mominpert.com


---

#### 🙏 Acknowledgments

Special thanks to:

- **Stephen Jude** - Original Filament Jetstream implementation
- **Laravel Team** - Original Jetstream package and framework
- **Filament Team** - Amazing Filament framework


---

**Version:** 1.0.0
**Release Date:** November 18, 2025
**PHP Requirement:** ^8.3|^8.4
**Laravel Requirement:** ^12.0
**Filament Requirement:** ^4.0

## Enhanced Version - 2025-01-XX

### What's Enhanced

This enhanced version by **Momin Al Zaraa** brings complete Jetstream features and mature data handling patterns:

* ✅ Complete team management features (add, remove, update roles)
* ✅ Proper invitation flow (register to accept, not auto-registration)
* ✅ Publishable Action classes with contracts (Jetstream pattern)
* ✅ Publishable language files with locale-first structure
* ✅ Enhanced email templates matching Jetstream's UI
* ✅ Custom validation rules (Role rule matching Jetstream)
* ✅ Better data handling aligned with Jetstream's mature patterns
* ✅ Contract-based architecture matching Laravel Jetstream
* ✅ Enhanced team member management (UpdateTeamMemberRole, RemoveTeamMember)
* ✅ Team deletion validation (ValidateTeamDeletion)
* ✅ Custom translation loader with automatic merging

### Credits

This enhanced package builds upon:

- **Laravel Jetstream** (discontinued): Original inspiration for team features and Action class patterns
- **stephenjude/filament-jetstream**: Original Filament port
- **Enhanced by**: Momin Al Zaraa - Complete Jetstream features and patterns

**Repository**: https://github.com/MominAlZaraa/filament-team-guard


---

## Previous Version History (from stephenjude/filament-jetstream)

## 1.2.11 - 2025-10-13

### What's Changed

* chore(phpstan): update configuration to use supported methods only by @MominAlZaraa in https://github.com/stephenjude/filament-jetstream/pull/77
* chore(phpstan): replace PHPStan with Larastan for enhanced built-in features by @MominAlZaraa in https://github.com/stephenjude/filament-jetstream/pull/78
* Bump stefanzweifel/git-auto-commit-action from 6 to 7 by @dependabot[bot] in https://github.com/stephenjude/filament-jetstream/pull/83
* fixed Larastan dev dependency by @stephenjude in https://github.com/stephenjude/filament-jetstream/pull/84

**Full Changelog**: https://github.com/stephenjude/filament-jetstream/compare/1.2.10...1.2.11

## 1.2.10 - 2025-10-10

### What's Changed

* Added phpstan for code editing to fix action phpstan.yml action by @MominAlZaraa in https://github.com/stephenjude/filament-jetstream/pull/76

**Full Changelog**: https://github.com/stephenjude/filament-jetstream/compare/1.2.9...1.2.10

## 1.2.9 - 2025-10-05

### What's Changed

* Fix: DeleteAccount flow & replace deprecated modal form usage by @momin-00 in https://github.com/stephenjude/filament-jetstream/pull/75

**Full Changelog**: https://github.com/stephenjude/filament-jetstream/compare/1.2.6...1.2.9

## 1.2.8 - 2025-10-01

### What's Changed

* Revert: Added tenant slug for friendly URL #71

**Full Changelog**: https://github.com/stephenjude/filament-jetstream/compare/1.2.5...1.2.8

## 1.2.1 - 2025-09-01

### What's Changed

* Update php version in  phpstan.yml by @wotta in https://github.com/stephenjude/filament-jetstream/pull/53

### New Contributors

* @wotta made their first contribution in https://github.com/stephenjude/filament-jetstream/pull/53

**Full Changelog**: https://github.com/stephenjude/filament-jetstream/compare/1.2.0...1.2.1

## 1.2.0 - 2025-08-31

### What's Changed

* Guide for installing and configuring existing Laravel applications by @stephenjude in https://github.com/stephenjude/filament-jetstream/pull/52

**Full Changelog**: https://github.com/stephenjude/filament-jetstream/compare/1.0.1...1.2.0

## 1.0.1 - 2025-08-29

### What's Changed

* php artisan optimize compatible form formats by @momin-00 in https://github.com/stephenjude/filament-jetstream/pull/51

### New Contributors

* @momin-00 made their first contribution in https://github.com/stephenjude/filament-jetstream/pull/51

**Full Changelog**: https://github.com/stephenjude/filament-jetstream/compare/1.0.0...1.0.1

## 0.1.0 - 2025-08-16

### What's Changed

* Use translatable labels by @stephenjude in https://github.com/stephenjude/filament-jetstream/pull/23
* Bump dependabot/fetch-metadata from 2.2.0 to 2.4.0 by @dependabot[bot] in https://github.com/stephenjude/filament-jetstream/pull/35
* Bump aglipanci/laravel-pint-action from 2.4 to 2.5 by @dependabot[bot] in https://github.com/stephenjude/filament-jetstream/pull/37
* Bump stefanzweifel/git-auto-commit-action from 5 to 6 by @dependabot[bot] in https://github.com/stephenjude/filament-jetstream/pull/36
* Bump aglipanci/laravel-pint-action from 2.5 to 2.6 by @dependabot[bot] in https://github.com/stephenjude/filament-jetstream/pull/40

**Full Changelog**: https://github.com/stephenjude/filament-jetstream/compare/0.0.14...0.1.0

## 0.0.16 - 2025-03-05

### What's Changed

* Bump dependabot/fetch-metadata from 2.2.0 to 2.3.0 by @dependabot in https://github.com/stephenjude/filament-jetstream/pull/28
* Bump aglipanci/laravel-pint-action from 2.4 to 2.5 by @dependabot in https://github.com/stephenjude/filament-jetstream/pull/29
* Add support for Laravel 12. by @LucaPipolo in https://github.com/stephenjude/filament-jetstream/pull/30

### New Contributors

* @LucaPipolo made their first contribution in https://github.com/stephenjude/filament-jetstream/pull/30

**Full Changelog**: https://github.com/stephenjude/filament-jetstream/compare/0.0.15...0.0.16

## 0.0.15 - 2024-11-27

- Make Team Settings label translate reactive by @zvizvi in #24

## 0.0.14 - 2024-11-16

- Use translatable label by @zvizvi in #23

## 0.0.13 - 2024-08-03

- Fixed duplicate team creation during user registration

## 0.0.12 - 2024-06-20

- Fixed user profile URL bug

## 0.0.11 - 2024-06-12

- Fixed Laravel 11 User model scaffold bug

## 0.0.10 - 2024-05-14

- Fixes current_team_id not being updated by @tomhatzer

## 0.0.9 - 2024-04-18

- Use `ServiceProvider::addProviderToBootstrapFile` from L11

## 0.0.8 - 2024-04-16

- Laravel 11 support by @gpibarra

## 0.0.7 - 2024-03-09

- Check features on profile edit by @fabpl
- Check gate on delete team @fabpl
- Removed undefined filament guard by @stephenjude

## 0.0.6 - 2024-03-02

- Added stubs
- Make package removable.
- Clean up.

## 0.0.5 - 2024-03-02

- Scaffold filament assets.

## 0.0.4 - 2024-02-29

- Fixed installation error.

## 0.0.3 - 2024-02-29

- Fixed: Install jetstream from command line.

## 0.0.2 - 2024-02-29

- Fixed class not found

## 0.0.1 - 2024-02-29

- Initial release.

## 1.0.0 - 202X-XX-XX

- initial release
