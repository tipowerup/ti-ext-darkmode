<button
    type="button"
    x-data="{ dark: localStorage.getItem('ti_darkmode') === 'on' }"
    x-on:click="window.TiDarkmode && window.TiDarkmode.toggle(); dark = !dark"
    x-on:ti:darkmode-toggled.window="dark = $event.detail.enabled"
    class="btn btn-outline-secondary"
>
    <i :class="dark ? 'fa fa-sun' : 'fa fa-moon'"></i>
    <span x-text="dark ? '{{ lang('tipowerup.darkmode::default.text_light_mode') }}' : '{{ lang('tipowerup.darkmode::default.text_dark_mode') }}'"></span>
</button>
