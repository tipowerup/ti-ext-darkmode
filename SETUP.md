# TastyIgniter Extension Template Setup

This is a template for creating TastyIgniter extensions. Follow the steps below to set up your new extension.

## Quick Setup

Run the interactive setup script:

```bash
php setup.php
```

The script will guide you through configuring your extension.

## Manual Setup

If you prefer to set up manually:

### 1. Update `composer.json`

Replace the following values:
- `tipowerup/ti-ext-template` → `your-vendor/ti-ext-your-extension`
- `Tipowerup\\Template` → `YourVendor\\YourExtension`
- `tipowerup.template` → `yourvendor.yourextension`
- `TiPowerUp Template` → `Your Extension Name`

### 2. Update `src/Extension.php`

Change the namespace:
```php
namespace YourVendor\YourExtension;
```

### 3. Update Tests

Update the namespace in these test files:
- `tests/TestCase.php` — change namespace and Extension class reference
- `tests/Pest.php` — change TestCase namespace
- `tests/Feature/ExtensionTest.php` — change Extension use statement

### 4. Install Dependencies

```bash
composer install
```

### 5. Run Tests

```bash
composer test
```

## Directory Structure

```
ti-ext-{name}/
├── composer.json           # Package configuration
├── setup.php               # Interactive setup script (delete after use)
├── SETUP.md                # This file (delete after use)
├── src/
│   └── Extension.php       # Main extension class
├── resources/
│   ├── lang/en/            # Language files
│   └── views/              # Blade templates (create as needed)
├── database/
│   └── migrations/         # Database migrations
└── tests/
    ├── TestCase.php         # Base TestCase (extends tipowerup/testbench)
    ├── Pest.php             # Pest configuration
    ├── Feature/             # Integration tests (full Laravel + TI context)
    └── Unit/                # Pure unit tests (no framework needed)
```

## Extension Class Methods

The `Extension.php` class can override these methods:

| Method | Purpose |
|--------|---------|
| `register()` | Register services, singletons, commands |
| `boot()` | Bootstrap the extension |
| `registerNavigation()` | Admin navigation menu items |
| `registerPermissions()` | Backend permissions |
| `registerSettings()` | Settings pages |
| `registerComponents()` | Livewire components |
| `registerMailTemplates()` | Mail templates |
| `registerSchedule($schedule)` | Scheduled tasks |

## Next Steps

After setup, delete `setup.php` and `SETUP.md`, then start building your extension!
