<button
    type="button"
    x-data="{ dark: window.TiDarkmode ? window.TiDarkmode.isActive() : localStorage.getItem('ti_darkmode') === 'on' }"
    x-on:click="window.TiDarkmode && window.TiDarkmode.toggle(); $nextTick(() => { dark = window.TiDarkmode ? window.TiDarkmode.isActive() : !dark })"
    x-on:ti:darkmode-toggled.window="dark = $event.detail.enabled"
    class="{{ $class }}"
>
    <i :class="dark ? 'fa fa-sun' : 'fa fa-moon'"></i>
    @if($showLabel)
        <span x-text="dark ? '{{ $lightLabel ?: lang('tipowerup.darkmode::default.text_light_mode') }}' : '{{ $darkLabel ?: lang('tipowerup.darkmode::default.text_dark_mode') }}'"></span>
    @endif
</button>
