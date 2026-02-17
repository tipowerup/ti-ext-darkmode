# Introduction
This extension adds dark mode support to your [TastyIgniter](https://tastyigniter.com) admin panel and storefront using the [DarkReader](https://darkreader.org/) library. It provides a seamless dark mode experience with per-user preference persistence and optional scheduling.

# Features
* Toggle dark mode on admin panel, storefront, or both.
* Configurable brightness, contrast, and sepia levels via admin settings.
* Anti-flicker loading prevents white flash on page load.
* Admin toolbar toggle icon for quick switching.
* Livewire component for storefront theme integration.
* Schedule support with time-based or sunset/sunrise activation.
* Per-user preference stored in localStorage for instant access.

# Requirements
* TastyIgniter v4.0+
* PHP 8.2+

# Installation

```bash
composer require tipowerup/ti-ext-darkmode
```

Then run migrations:

```bash
php artisan igniter:up
```

# Usage
1. Go to **System > Settings > Dark Mode** in the admin panel.
2. Enable dark mode and choose where to apply it (admin, storefront, or both).
3. Adjust brightness, contrast, and sepia to your preference.
4. Optionally enable the admin toolbar toggle or configure a schedule.

To add a toggle button to your storefront theme, use the Livewire component:

```blade
<livewire:tipowerup-darkmode::darkmode-toggle />
```

# License
This extension is released under the [MIT License](LICENSE.md).
