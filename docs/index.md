---
title: Dark Mode Extension Documentation
---

# Dark Mode Extension

Adds dark mode to the TastyIgniter admin dashboard and any storefront theme. Powered by DarkReader, it works out of the box without requiring theme-specific CSS — just enable and go.

---

## Features

- **Toggle dark mode** on admin dashboard, storefront, or both
- **Plug and play** — works with any theme, no theme-specific CSS or setup needed
- **Customizable appearance** with brightness, contrast, and sepia controls
- **Anti-flicker protection** prevents white flash on page load
- **Admin toolbar toggle** for quick switching (optional)
- **Livewire component** for storefront integration
- **Smart scheduling** with time-based or sunset/sunrise activation
- **Per-user preferences** stored in localStorage for instant access
- **Cross-tab synchronization** via storage events
- **JavaScript API** for advanced customization

---

## Quick Start

### Installation

```bash
composer require tipowerup/ti-ext-darkmode
php artisan igniter:up
```

### Enable Dark Mode

1. Go to **System > Settings > Dark Mode**
2. Toggle **Enable Dark Mode** to ON
3. Choose where to apply: Admin, Frontend, or Both
4. Adjust settings (brightness, contrast, sepia)
5. Click **Save**

### Add to Storefront

Use the Livewire component in your theme:

```blade
<livewire:tipowerup-darkmode::darkmode-toggle />
```

---

## Configuration

### Settings Overview

| Setting | Type | Default | Description |
|---------|------|---------|-------------|
| Enable Dark Mode | Switch | OFF | Master toggle for dark mode |
| Apply To | Radio | both | Where to apply (admin, frontend, both) |
| Brightness | Number | 100 | DarkReader brightness (0-200) |
| Contrast | Number | 90 | DarkReader contrast (0-200) |
| Sepia | Number | 10 | DarkReader sepia level (0-200) |
| Admin Toolbar Toggle | Switch | ON | Show toggle in admin navbar |
| Schedule Enabled | Switch | OFF | Enable time-based activation |
| Schedule Type | Radio | time | Schedule mode (time or sunset_sunrise) |
| Start Time | Time | 20:00 | Dark mode start time (HH:MM) |
| End Time | Time | 06:00 | Dark mode end time (HH:MM) |
| Latitude | Number | - | For sunset/sunrise calculations |
| Longitude | Number | - | For sunset/sunrise calculations |

### DarkReader Options

Adjust the visual appearance of dark mode:

- **Brightness** (0-200): Higher values brighten the content. Default 100 is neutral.
- **Contrast** (0-200): Higher values increase contrast. Default 90 is recommended.
- **Sepia** (0-200): Adds warm tone to reduce eye strain. Default 10 is subtle.

---

## Storefront Integration

### Basic Component Usage

Add a simple dark mode toggle button to your storefront:

```blade
<livewire:tipowerup-darkmode::darkmode-toggle />
```

### Component Customization

Customize the toggle button with attributes:

```blade
<livewire:tipowerup-darkmode::darkmode-toggle
    class="btn btn-dark"
    showLabel="true"
    darkLabel="Dark"
    lightLabel="Light"
/>
```

### Component Attributes

| Attribute | Type | Default | Description |
|-----------|------|---------|-------------|
| `class` | string | `btn btn-outline-secondary` | CSS classes for the button |
| `showLabel` | boolean | false | Show text label with icon |
| `darkLabel` | string | Localized default | Text when dark mode is OFF |
| `lightLabel` | string | Localized default | Text when dark mode is ON |

### Real-World Examples

**In a Header/Navbar:**

```blade
<div class="navbar-item">
    <livewire:tipowerup-darkmode::darkmode-toggle class="btn btn-sm btn-link" />
</div>
```

**With Custom Styling:**

```blade
<livewire:tipowerup-darkmode::darkmode-toggle
    class="custom-toggle-btn"
    showLabel="true"
/>
```

**With Custom Labels:**

```blade
<livewire:tipowerup-darkmode::darkmode-toggle
    class="btn btn-outline-primary"
    showLabel="true"
    darkLabel="Switch to Dark Mode"
    lightLabel="Switch to Light Mode"
/>
```

---

## Scheduling

Enable time-based or location-based automatic dark mode activation.

### Time-Based Scheduling

Schedule dark mode to activate at specific hours:

1. Go to **System > Settings > Dark Mode**
2. Toggle **Schedule Enabled** to ON
3. Select **Schedule Type**: Time
4. Set **Start Time** (e.g., 20:00 for 8 PM)
5. Set **End Time** (e.g., 06:00 for 6 AM)
6. Save settings

The extension handles overnight ranges correctly (e.g., 20:00 → 06:00).

### Sunset/Sunrise Scheduling

Schedule dark mode based on your location's sunset and sunrise times:

1. Go to **System > Settings > Dark Mode**
2. Toggle **Schedule Enabled** to ON
3. Select **Schedule Type**: Sunset/Sunrise
4. Enter **Latitude** (e.g., 40.7128 for New York)
5. Enter **Longitude** (e.g., -74.0060 for New York)
6. Save settings

The extension uses NOAA solar calculation to determine sunrise/sunset times, rechecking every 60 seconds to account for transitions.

### Manual Override

When scheduling is enabled, users can toggle dark mode manually within the current schedule period. The override expires when the schedule transitions (day ↔ night).

---

## JavaScript API

Access dark mode programmatically via the `window.TiDarkmode` object.

### Methods

#### `toggle()`

Toggle dark mode on/off.

```javascript
window.TiDarkmode.toggle();
```

#### `enable()`

Enable dark mode.

```javascript
window.TiDarkmode.enable();
```

#### `disable()`

Disable dark mode.

```javascript
window.TiDarkmode.disable();
```

#### `isActive()`

Check if dark mode is currently active. Returns boolean.

```javascript
const isDark = window.TiDarkmode.isActive();
console.log('Dark mode:', isDark); // true or false
```

### Events

Listen for dark mode changes with the `ti:darkmode-toggled` event:

```javascript
window.addEventListener('ti:darkmode-toggled', (event) => {
    const isDarkMode = event.detail.enabled;
    console.log('Dark mode:', isDarkMode ? 'ON' : 'OFF');
});
```

### Advanced Usage

**React to dark mode changes in Alpine.js:**

```blade
<div x-data="{ isDark: window.TiDarkmode?.isActive() ?? false }"
     @ti:darkmode-toggled.window="isDark = $event.detail.enabled">
    <span x-text="isDark ? 'Dark Mode' : 'Light Mode'"></span>
</div>
```

**Programmatically toggle based on user preference:**

```javascript
const userPrefersDark = window.matchMedia('(prefers-color-scheme: dark)').matches;
if (userPrefersDark) {
    window.TiDarkmode.enable();
}
```

---

## Admin Features

### Admin Toolbar Toggle

When enabled, a moon icon appears in the admin toolbar for quick dark mode switching.

- **Enable/Disable**: Go to **System > Settings > Dark Mode** and toggle **Admin Toolbar Toggle**
- **Requires Permission**: User must have `Tipowerup.Darkmode.ManageSettings` permission

---

## Permissions

### Manage Settings Permission

**Code**: `Tipowerup.Darkmode.ManageSettings`

Controls who can:
- Access dark mode settings in System > Settings
- See and use the admin toolbar toggle

Assign this permission to admin roles that should manage dark mode configuration.

---

## Technical Details

### How It Works

1. **Middleware Injection**: Anti-flicker script injected via middleware before page renders
2. **DarkReader**: External library handles CSS inversion and style adjustments
3. **Preference Storage**: User preference stored in localStorage (`ti_darkmode` key)
4. **Schedule Checking**: JavaScript calculates schedule state and applies accordingly
5. **Cross-Tab Sync**: Storage events trigger synchronization across browser tabs

### localStorage Keys

| Key | Format | Purpose |
|-----|--------|---------|
| `ti_darkmode` | "on" or "off" | User preference |
| `ti_darkmode_override` | JSON | Schedule override with period tracking |

### Anti-Flicker Script

Injected into every HTML page to prevent white flash:

- Checks `ti_darkmode` preference on load
- Applies dark background and hides content while DarkReader loads
- Automatically removes when DarkReader is ready (max 3 seconds)

---

## Troubleshooting

### Dark Mode Not Applying

**Symptom**: Dark mode is enabled but not visible.

**Solution**:
1. Verify **Enable Dark Mode** is toggled ON
2. Verify **Apply To** is set to the correct location (Admin, Frontend, or Both)
3. Clear browser cache and reload
4. Check browser console for JavaScript errors

### White Flash on Page Load

**Symptom**: Page briefly appears white before dark mode loads.

**Solution**: Anti-flicker script should prevent this. If it occurs:
1. Clear browser cache
2. Verify middleware is registered (automatic with installation)
3. Check browser console for errors

### Schedule Not Activating

**Symptom**: Time-based or sunset/sunrise schedule isn't activating dark mode.

**Solution for Time-Based**:
- Verify **Schedule Type** is set to "Time"
- Check **Start Time** and **End Time** (use 24-hour format)
- Verify current time falls within the schedule range
- Note: Overnight ranges (e.g., 20:00 → 06:00) are supported

**Solution for Sunset/Sunrise**:
- Verify **Schedule Type** is set to "Sunset/Sunrise"
- Enter valid **Latitude** and **Longitude** (e.g., 40.7128, -74.0060)
- Check that coordinates are for your actual location
- Sunrise/sunset times update every hour

### Manual Override Not Working

**Symptom**: Manual toggle doesn't stick when schedule is enabled.

**Expected Behavior**: Manual overrides are period-scoped. When the schedule transitions (day ↔ night), the override expires and the schedule takes over. This is intentional.

**To Bypass**: Disable scheduling if you want permanent manual control.

---

## Browser Support

Dark mode works in all modern browsers that support localStorage and CSS filters.

---

## FAQ

**Q: Can I customize dark mode colors further?**

A: The extension uses DarkReader's CSS filter approach. For fine-grained control, adjust brightness, contrast, and sepia settings. For custom CSS overrides, you can add styles to your theme.

**Q: Does dark mode work offline?**

A: Yes. Preferences are stored in localStorage and applied client-side. Scheduling calculations also happen client-side.

**Q: Can users have different dark mode preferences?**

A: Yes. Each user's preference is stored in their browser's localStorage. Preferences are not synced across devices.

**Q: What happens if I disable the extension?**

A: All dark mode functionality stops. User preferences remain in localStorage but are not used. Uninstalling the extension can safely be done without data loss.

**Q: Can I use this with other theme/styling systems?**

A: Yes. Dark mode works alongside any theme system. It applies CSS filters on top of existing styles.

---

## Support

- **Issues & Contributions**: [GitHub Repository](https://github.com/tipowerup/ti-ext-darkmode)
- **Contact**: [tipowerup.com/contact](https://tipowerup.com/contact)
- **Discord Community**: [tipowerup.com/social/discord](https://tipowerup.com/social/discord)
