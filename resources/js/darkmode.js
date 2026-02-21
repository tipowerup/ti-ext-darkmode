(function () {
    'use strict';

    var STORAGE_KEY = 'ti_darkmode';
    var OVERRIDE_KEY = 'ti_darkmode_override';
    var ACTIVE_KEY = 'ti_darkmode_active';
    var config = (window.app && window.app.tiDarkmode) || {};

    var drOptions = {
        brightness: config.brightness || 100,
        contrast: config.contrast || 90,
        sepia: config.sepia || 10
    };

    // NOAA solar calculation (simplified)
    function calcSunTimes(lat, lng, date) {
        var rad = Math.PI / 180;
        var dayOfYear = Math.floor((date - new Date(date.getFullYear(), 0, 0)) / 86400000);
        var gamma = (2 * Math.PI / 365) * (dayOfYear - 1);

        var eqTime = 229.18 * (0.000075 + 0.001868 * Math.cos(gamma)
            - 0.032077 * Math.sin(gamma)
            - 0.014615 * Math.cos(2 * gamma)
            - 0.040849 * Math.sin(2 * gamma));

        var decl = 0.006918 - 0.399912 * Math.cos(gamma) + 0.070257 * Math.sin(gamma)
            - 0.006758 * Math.cos(2 * gamma) + 0.000907 * Math.sin(2 * gamma)
            - 0.002697 * Math.cos(3 * gamma) + 0.00148 * Math.sin(3 * gamma);

        var latRad = lat * rad;
        var cosZenith = Math.cos(90.833 * rad);
        var cosHA = (cosZenith / (Math.cos(latRad) * Math.cos(decl))) - Math.tan(latRad) * Math.tan(decl);

        // Clamp to valid acos range: polar night vs polar day
        if (cosHA > 1) return { sunrise: 1440, sunset: 0 }; // sun never rises
        if (cosHA < -1) return { sunrise: 0, sunset: 1440 }; // sun never sets

        var hourAngle = Math.acos(cosHA);

        var sunriseMin = 720 - 4 * (lng + hourAngle / rad) - eqTime;
        var sunsetMin = 720 - 4 * (lng - hourAngle / rad) - eqTime;

        // Convert UTC minutes to local: local = UTC - getTimezoneOffset()
        var tzOffset = date.getTimezoneOffset();
        return {
            sunrise: sunriseMin - tzOffset,
            sunset: sunsetMin - tzOffset
        };
    }

    function parseTime(str) {
        var parts = (str || '').split(':');
        return { h: parseInt(parts[0], 10) || 0, m: parseInt(parts[1], 10) || 0 };
    }

    function currentMinutes() {
        var now = new Date();
        return now.getHours() * 60 + now.getMinutes();
    }

    function isInTimeRange(startStr, endStr) {
        var start = parseTime(startStr);
        var end = parseTime(endStr);
        var startMin = start.h * 60 + start.m;
        var endMin = end.h * 60 + end.m;
        var now = currentMinutes();

        // Overnight range (e.g., 20:00 -> 06:00)
        if (startMin > endMin) {
            return now >= startMin || now < endMin;
        }

        return now >= startMin && now < endMin;
    }

    function isInSunsetSunrise() {
        var lat = parseFloat(config.latitude);
        var lng = parseFloat(config.longitude);
        if (isNaN(lat) || isNaN(lng)) {
            return false;
        }

        var times = calcSunTimes(lat, lng, new Date());
        var now = currentMinutes();

        // Dark between sunset and sunrise (overnight)
        return now >= times.sunset || now < times.sunrise;
    }

    // Returns the current schedule period identifier.
    // Changes when the schedule transitions (e.g. day→night or night→day).
    function getSchedulePeriod() {
        var scheduled = shouldScheduleEnable();
        if (scheduled === null) {
            return null;
        }
        return scheduled ? 'dark' : 'light';
    }

    function shouldScheduleEnable() {
        if (!config.schedule_enabled) {
            return null; // No schedule
        }

        if (config.schedule_type === 'sunset_sunrise') {
            return isInSunsetSunrise();
        }

        return isInTimeRange(config.start_time, config.end_time);
    }

    function getPreference() {
        return localStorage.getItem(STORAGE_KEY);
    }

    function setPreference(val) {
        localStorage.setItem(STORAGE_KEY, val);
    }

    // Store override with the current schedule period so it expires on transition
    function setOverride(val) {
        var period = getSchedulePeriod();
        if (period) {
            localStorage.setItem(OVERRIDE_KEY, JSON.stringify({ value: val, period: period }));
        }
        setPreference(val);
    }

    // Get override only if it's still valid for the current period
    function getOverride() {
        var raw = localStorage.getItem(OVERRIDE_KEY);
        if (!raw) {
            return null;
        }
        try {
            var data = JSON.parse(raw);
            var currentPeriod = getSchedulePeriod();
            if (currentPeriod && data.period === currentPeriod) {
                return data.value;
            }
            // Period changed — override expired, clear it
            localStorage.removeItem(OVERRIDE_KEY);
            return null;
        } catch (e) {
            localStorage.removeItem(OVERRIDE_KEY);
            return null;
        }
    }

    function shouldBeActive() {
        var scheduled = shouldScheduleEnable();

        // If schedule is active, check for a cycle-scoped override
        if (scheduled !== null) {
            var override = getOverride();
            if (override === 'on') {
                return true;
            }
            if (override === 'off') {
                return false;
            }
            return scheduled;
        }

        // No schedule — use plain preference
        var pref = getPreference();
        if (pref === 'on') {
            return true;
        }
        return false;
    }

    function enable() {
        localStorage.setItem(ACTIVE_KEY, '1');
        if (typeof DarkReader !== 'undefined') {
            DarkReader.setFetchMethod(window.fetch);
            DarkReader.enable(drOptions);
        }
        if (typeof window.__tiDmReady === 'function') {
            window.__tiDmReady();
        }
    }

    function disable() {
        localStorage.removeItem(ACTIVE_KEY);
        if (typeof DarkReader !== 'undefined') {
            DarkReader.disable();
        }
        if (typeof window.__tiDmReady === 'function') {
            window.__tiDmReady();
        }
    }

    function apply() {
        if (shouldBeActive()) {
            enable();
        } else {
            disable();
        }
    }

    function toggle() {
        var isActive = typeof DarkReader !== 'undefined' && DarkReader.isEnabled();
        var newState = isActive ? 'off' : 'on';

        if (config.schedule_enabled) {
            setOverride(newState);
        } else {
            setPreference(newState);
        }

        if (isActive) {
            disable();
        } else {
            enable();
        }

        // Dispatch event for Alpine/Livewire reactivity
        document.dispatchEvent(new CustomEvent('ti:darkmode-toggled', {
            detail: { enabled: !isActive },
            bubbles: true
        }));
    }

    // Expose API
    window.TiDarkmode = {
        toggle: toggle,
        enable: enable,
        disable: disable,
        isActive: function () {
            return typeof DarkReader !== 'undefined' && DarkReader.isEnabled();
        }
    };

    // Sync across browser tabs when localStorage changes
    window.addEventListener('storage', function (e) {
        if (e.key === STORAGE_KEY || e.key === OVERRIDE_KEY || e.key === ACTIVE_KEY) {
            var wasActive = typeof DarkReader !== 'undefined' && DarkReader.isEnabled();
            apply();
            var isNowActive = typeof DarkReader !== 'undefined' && DarkReader.isEnabled();
            if (wasActive !== isNowActive) {
                document.dispatchEvent(new CustomEvent('ti:darkmode-toggled', {
                    detail: { enabled: isNowActive }, bubbles: true
                }));
            }
        }
    });

    // Initial application
    apply();

    // Schedule re-check every 60 seconds
    if (config.schedule_enabled) {
        window.TiDarkmode._interval = setInterval(function () {
            var wasActive = typeof DarkReader !== 'undefined' && DarkReader.isEnabled();
            apply();
            var isNowActive = typeof DarkReader !== 'undefined' && DarkReader.isEnabled();

            if (wasActive !== isNowActive) {
                document.dispatchEvent(new CustomEvent('ti:darkmode-toggled', {
                    detail: { enabled: isNowActive }, bubbles: true
                }));
            }
        }, 60000);
    }
})();
